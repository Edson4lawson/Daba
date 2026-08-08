import { ref, watch, onUnmounted, toRef, isRef } from 'vue'

const activeLocksCount = ref(0)
let touchStartListener = null
let touchMoveListener = null
let originalOverflow = ''
let originalTouchAction = ''
let startY = 0
let startX = 0

function isScrollableElement(el) {
  while (el && el !== document.body && el !== document.documentElement) {
    if (el.getAttribute && el.getAttribute('data-scrollable') === 'true') {
      return true
    }
    const overflowY = window.getComputedStyle(el).overflowY
    if ((overflowY === 'auto' || overflowY === 'scroll') && el.scrollHeight > el.clientHeight) {
      return true
    }
    el = el.parentElement
  }
  return false
}

function handleTouchStart(e) {
  if (e.touches && e.touches.length > 0) {
    startY = e.touches[0].clientY
    startX = e.touches[0].clientX
  }
}

function preventBodyTouchMove(e) {
  if (!isScrollableElement(e.target)) {
    if (e.touches && e.touches.length > 0) {
      const deltaY = Math.abs(e.touches[0].clientY - startY)
      const deltaX = Math.abs(e.touches[0].clientX - startX)
      // Only prevent default if it's a drag/swipe gesture (>6px), preserving tap/click events
      if ((deltaY > 6 || deltaX > 6) && e.cancelable) {
        e.preventDefault()
      }
    } else if (e.cancelable) {
      e.preventDefault()
    }
  }
}

function lockScroll() {
  if (activeLocksCount.value === 0) {
    originalOverflow = document.body.style.overflow
    originalTouchAction = document.body.style.touchAction

    document.body.style.overflow = 'hidden'
    document.body.style.touchAction = 'none'

    touchStartListener = (e) => handleTouchStart(e)
    touchMoveListener = (e) => preventBodyTouchMove(e)

    document.addEventListener('touchstart', touchStartListener, { passive: true })
    document.addEventListener('touchmove', touchMoveListener, { passive: false })
  }
  activeLocksCount.value++
}

function unlockScroll() {
  if (activeLocksCount.value > 0) {
    activeLocksCount.value--
  }
  if (activeLocksCount.value === 0) {
    document.body.style.overflow = originalOverflow
    document.body.style.touchAction = originalTouchAction

    if (touchStartListener) {
      document.removeEventListener('touchstart', touchStartListener)
      touchStartListener = null
    }
    if (touchMoveListener) {
      document.removeEventListener('touchmove', touchMoveListener)
      touchMoveListener = null
    }
  }
}

/**
 * Composable for ref-counted body scroll locking.
 * Locks desktop wheel & keyboard scroll, and mobile touch drag scrolling while preserving tap clicks.
 * 
 * @param {Ref<boolean>|function} targetRef 
 */
export function useScrollLock(targetRef) {
  const lockRef = isRef(targetRef) ? targetRef : toRef(targetRef)
  let isCurrentlyLocked = false

  watch(lockRef, (val) => {
    if (val && !isCurrentlyLocked) {
      lockScroll()
      isCurrentlyLocked = true
    } else if (!val && isCurrentlyLocked) {
      unlockScroll()
      isCurrentlyLocked = false
    }
  }, { immediate: true })

  onUnmounted(() => {
    if (isCurrentlyLocked) {
      unlockScroll()
      isCurrentlyLocked = false
    }
  })
}
