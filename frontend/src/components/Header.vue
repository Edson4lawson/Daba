<template>
  <header :class="[
    'sticky top-0 left-0 right-0 z-[100] transition-all duration-300 border-b border-daba-border/60',
    isScrolled
      ? 'py-2 shadow-card-light backdrop-blur-md bg-daba-cream/95'
      : 'py-4 bg-daba-cream'
  ]">
    <div class="max-w-[1440px] mx-auto px-6 sm:px-8 lg:px-12">
      <nav class="flex items-center justify-between">
        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-3 group shrink-0 relative z-[951]" @click="closeMenu">
          <img :src="logoImg" alt="DABA — Élevage & Transformation"
            :class="['w-auto object-contain transition-all', isScrolled ? 'h-9' : 'h-10 sm:h-12']" />
        </router-link>

        <!-- Desktop Nav -->
        <div class="hidden md:flex items-center space-x-8">
          <router-link v-for="link in navLinks" :key="link.to" :to="link.to"
            class="text-sm font-karla font-semibold tracking-wide uppercase transition-colors relative py-1"
            :class="isActiveRoute(link.to) ? 'text-daba-orange font-bold' : 'text-daba-navy hover:text-daba-orange'">
            {{ link.name }}
            <span v-if="isActiveRoute(link.to)"
              class="absolute bottom-0 left-0 w-full h-[2px] bg-daba-orange rounded-full"></span>
          </router-link>
        </div>

        <!-- Desktop Actions -->
        <div class="hidden md:flex items-center space-x-4">
          <button @click="isCartOpen = true"
            class="relative p-2.5 rounded-full bg-white border border-daba-border text-daba-navy hover:text-daba-orange hover:border-daba-orange transition-all shadow-sm cursor-pointer"
            aria-label="Voir le panier">
            <ShoppingBasket class="w-5 h-5" />
            <span v-if="cartStore.totalItems > 0"
              class="absolute -top-1.5 -right-1.5 min-w-[20px] h-[20px] px-1 bg-daba-orange text-white text-[11px] font-bold font-worksans rounded-full flex items-center justify-center border-2 border-daba-cream shadow-sm">
              {{ cartStore.totalItems }}
            </span>
          </button>

          <router-link to="/produits"
            class="inline-flex items-center justify-center px-6 py-2.5 rounded-full bg-daba-orange text-white font-inter font-bold text-xs uppercase tracking-wider hover:bg-daba-orange-alt transition-all shadow-md hover:shadow-lg active:scale-95">
            Catalogue
          </router-link>
        </div>

        <!-- Mobile: Cart + Animated GSAP Burger Button -->
        <div class="flex items-center space-x-3 md:hidden relative z-[951]">
          <button @click="isCartOpen = true"
            class="relative p-2 rounded-full bg-white/80 border border-daba-border text-daba-navy hover:text-daba-orange transition-colors cursor-pointer"
            aria-label="Panier">
            <ShoppingBasket class="w-5 h-5" />
            <span v-if="cartStore.totalItems > 0"
              class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-daba-orange text-white text-[10px] font-bold rounded-full flex items-center justify-center border border-daba-cream">
              {{ cartStore.totalItems }}
            </span>
          </button>

          <!-- GSAP Burger Button -->
          <button
            @click="toggleMenu"
            class="burger-btn relative w-12 h-9 flex items-center justify-center rounded-full bg-white/80 border border-daba-border text-daba-navy hover:border-daba-orange transition-colors focus:outline-none cursor-pointer"
            aria-label="Toggle Navigation Menu"
          >
            <!-- Burger lines container -->
            <div class="burger-lines flex flex-col justify-between h-3.5 w-5 pointer-events-none">
              <span ref="line1Ref" class="burger-line-1 w-full h-[2px] bg-daba-navy rounded-full transform-gpu origin-center"></span>
              <span ref="line2Ref" class="burger-line-2 w-full h-[2px] bg-daba-navy rounded-full transform-gpu origin-center"></span>
            </div>

            <!-- FERMER text -->
            <span ref="closeTextRef" class="burger-text-close absolute inset-0 flex items-center justify-center font-inter font-bold text-[10px] uppercase tracking-wider text-daba-orange opacity-0 scale-75 pointer-events-none">
              FERMER
            </span>
          </button>
        </div>
      </nav>
    </div>

    <!-- Mobile Menu Overlay (Teleported to Body for clean Z-Index & Click behavior) -->
    <Teleport to="body">
      <div
        ref="mobileMenuRef"
        class="md:hidden fixed inset-0 bg-daba-cream/95 backdrop-blur-lg z-[900] flex flex-col justify-between px-8 pt-24 pb-8 overflow-y-auto opacity-0 invisible"
        data-scrollable="true"
      >
        <div class="flex flex-col space-y-3 text-center max-w-sm mx-auto w-full my-auto">
          <router-link
            v-for="link in navLinks"
            :key="link.to"
            :to="link.to"
            @click="closeMenu"
            class="mobile-nav-link text-2xl font-playfair font-bold uppercase tracking-wide py-2.5 border-b border-daba-border/40 last:border-0 transition-colors opacity-0 transform-gpu cursor-pointer"
            :class="isActiveRoute(link.to) ? 'text-daba-orange font-bold' : 'text-daba-navy hover:text-daba-orange'"
          >
            {{ link.name }}
          </router-link>
        </div>

        <div class="pt-6 flex flex-col gap-3 max-w-sm mx-auto w-full shrink-0">
          <button
            @click="openCartFromMenu"
            class="mobile-nav-link w-full inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-full bg-daba-navy text-white font-inter font-bold text-sm uppercase tracking-wider hover:bg-daba-navy-marine transition-all shadow-md active:scale-95 opacity-0 transform-gpu cursor-pointer"
          >
            <ShoppingBasket class="w-5 h-5" />
            Panier ({{ cartStore.totalItems }})
          </button>

          <button
            @click="openCatalogueFromMenu"
            class="mobile-nav-link w-full inline-flex items-center justify-center px-6 py-3.5 rounded-full bg-daba-orange text-white font-inter font-bold text-sm uppercase tracking-wider hover:bg-daba-orange-alt transition-all shadow-md active:scale-95 text-center opacity-0 transform-gpu cursor-pointer"
          >
            Voir le catalogue
          </button>

          <div class="mt-2 pt-4 border-t border-daba-border/60 text-center">
            <p class="text-xs font-karla text-daba-slate">DABA — Élevage & Transformation Volaille</p>
            <p class="text-[11px] font-karla text-daba-slate-light mt-0.5">Lomé, Togo · +228 90 00 00 00</p>
          </div>
        </div>
      </div>
    </Teleport>

  </header>

  <!-- Cart Drawer teleported to body to escape header stacking context -->
  <Teleport to="body">
    <CartDrawer :is-open="isCartOpen" @close="isCartOpen = false" />
  </Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ShoppingBasket } from 'lucide-vue-next'
import gsap from 'gsap'
import logoImg from '../assets/Logo DABA élevage & transformation.png'
import CartDrawer from '@/components/CartDrawer.vue'
import { useCartStore } from '@/stores/cart'
import { useScrollLock } from '@/composables/useScrollLock'

const route = useRoute()
const router = useRouter()
const cartStore = useCartStore()

const isScrolled = ref(false)
const isMenuOpen = ref(false)
const isCartOpen = ref(false)

// Scroll lock active when mobile menu is open
useScrollLock(isMenuOpen)

const line1Ref = ref(null)
const line2Ref = ref(null)
const closeTextRef = ref(null)
const mobileMenuRef = ref(null)

let menuTl = null

const navLinks = [
  { name: 'Accueil', to: '/' },
  { name: 'Produits', to: '/produits' },
  { name: 'Notre Histoire', to: '/notre-histoire' },
  { name: 'Traçabilité', to: '/tracabilite' },
  { name: 'Espace B2B', to: '/espace-b2b' },
]

const isActiveRoute = (path) => {
  if (path === '/') return route.path === '/'
  return route.path.startsWith(path)
}

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

const closeMenu = () => {
  isMenuOpen.value = false
}

const openCartFromMenu = () => {
  isCartOpen.value = true
  closeMenu()
}

const openCatalogueFromMenu = () => {
  closeMenu()
  router.push('/produits')
}

const handleScroll = () => {
  isScrolled.value = window.scrollY > 30
}

watch(isMenuOpen, (isOpen) => {
  if (!menuTl) return
  if (isOpen) {
    menuTl.play()
  } else {
    menuTl.reverse()
  }
})

// Close menu on route change
watch(() => route.path, () => {
  if (isMenuOpen.value) {
    closeMenu()
  }
})

onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true })

  // Build GSAP Timeline matching reference portfolio animation
  menuTl = gsap.timeline({ paused: true })

  menuTl
    // Burger lines separate & fade out
    .to(line1Ref.value, { x: -30, opacity: 0, duration: 0.3, ease: 'power2.inOut' }, 0)
    .to(line2Ref.value, { x: 30, opacity: 0, duration: 0.3, ease: 'power2.inOut' }, 0)
    // FERMER text appears
    .to(closeTextRef.value, { opacity: 1, scale: 1, duration: 0.3, ease: 'back.out(1.5)' }, 0.05)
    // Overlay fades in & becomes visible
    .to(mobileMenuRef.value, { autoAlpha: 1, duration: 0.3, ease: 'power2.out' }, 0)
    // Links cascade stagger
    .fromTo(
      '.mobile-nav-link',
      { opacity: 0, y: 20 },
      { opacity: 1, y: 0, duration: 0.35, stagger: 0.06, ease: 'power2.out' },
      0.1
    )
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  if (menuTl) {
    menuTl.kill()
  }
})
</script>

<style scoped>
.burger-btn {
  transition: border-color 0.2s ease, background-color 0.2s ease;
}
</style>
