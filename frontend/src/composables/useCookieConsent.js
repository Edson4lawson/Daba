import { ref, onMounted } from 'vue'

// Cookie consent composable for global use
export function useCookieConsent() {
  const hasConsent = ref(false)
  const consentSettings = ref({
    essential: true,
    analytics: false,
    marketing: false,
    functional: false
  })

  const COOKIE_NAME = 'daba_cookie_consent'
  const CONSENT_VERSION = '1.0'

  // Get cookie value
  const getCookie = (name) => {
    const value = `; ${document.cookie}`
    const parts = value.split(`; ${name}=`)
    if (parts.length === 2) return parts.pop().split(';').shift()
    return null
  }

  // Check consent status
  const checkConsent = () => {
    const consent = getCookie(COOKIE_NAME)
    if (consent) {
      const data = JSON.parse(consent)
      if (data.version === CONSENT_VERSION) {
        hasConsent.value = true
        consentSettings.value = { ...data.settings }
        return true
      }
    }
    return false
  }

  // Check specific category consent
  const hasCategoryConsent = (category) => {
    if (category === 'essential') return true // Always allowed
    return consentSettings.value[category] || false
  }

  // Load scripts conditionally
  const loadScript = (src, category, id = null) => {
    if (!hasCategoryConsent(category)) return Promise.reject(new Error(`No consent for ${category} cookies`))

    return new Promise((resolve, reject) => {
      // Check if script already exists
      if (id && document.getElementById(id)) {
        resolve()
        return
      }

      const script = document.createElement('script')
      script.src = src
      script.async = true
      
      if (id) script.id = id
      
      script.onload = () => resolve(script)
      script.onerror = () => reject(new Error(`Failed to load script: ${src}`))
      
      document.head.appendChild(script)
    })
  }

  // Google Analytics integration
  const loadGoogleAnalytics = (measurementId) => {
    return loadScript(`https://www.googletagmanager.com/gtag/js?id=${measurementId}`, 'analytics', 'google-analytics')
      .then(() => {
        window.dataLayer = window.dataLayer || []
        function gtag(){dataLayer.push(arguments)}
        gtag('js', new Date())
        gtag('config', measurementId, {
          'anonymize_ip': true,
          'cookie_domain': 'auto'
        })
        window.gtag = gtag
      })
  }

  // Meta Pixel integration
  const loadMetaPixel = (pixelId) => {
    return loadScript('https://connect.facebook.net/en_US/fbevents.js', 'marketing', 'meta-pixel')
      .then(() => {
        window.fbq = function() {
          window.fbq.callMethod ? window.fbq.callMethod.apply(window.fbq, arguments) : window.fbq.queue.push(arguments)
        }
        window.fbq.queue = []
        window.fbq.version = '2.0'
        fbq('init', pixelId)
        fbq('track', 'PageView')
      })
  }

  // TikTok Pixel integration
  const loadTikTokPixel = (pixelId) => {
    return loadScript('https://analytics.tiktok.com/i18n/pixel/events.js', 'marketing', 'tiktok-pixel')
      .then(() => {
        window.ttq = window.ttq || []
        window.ttq.push = Array.prototype.slice.apply(window.ttq.push)
        window.ttq.load = function(pixelId) {
          window.ttq.methods.forEach(function(method) {
            window.ttq[method] = function() {
              window.ttq.push([method].concat(Array.prototype.slice.call(arguments, 0)))
            }
          })
          window.ttq.load(pixelId)
        }
        window.ttq.load(pixelId)
        window.ttq.page()
      })
  }

  // Initialize consent on mount
  onMounted(() => {
    checkConsent()
  })

  return {
    hasConsent,
    consentSettings,
    checkConsent,
    hasCategoryConsent,
    loadScript,
    loadGoogleAnalytics,
    loadMetaPixel,
    loadTikTokPixel
  }
}


