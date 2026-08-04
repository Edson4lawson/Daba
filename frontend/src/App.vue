<template>
  <div id="app">
    <router-view />
    
    <!-- Cookie Banner (RGPD) -->
    <CookieBanner />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useProductStore } from '@/stores/products'
import { useCartStore } from '@/stores/cart'
import { useWishlistStore } from '@/stores/wishlist'
import { useSEO } from '@/composables/useSEO'
import CookieBanner from '@/components/CookieBanner.vue'

const authStore = useAuthStore()
const productStore = useProductStore()
const cartStore = useCartStore()
const wishlistStore = useWishlistStore()
const { updateMetaTags } = useSEO()

onMounted(async () => {
  // Initialize auth state
  authStore.initFromStorage()

  // Set default SEO meta
  updateMetaTags({
    title: 'Accueil',
    description: 'Daba — Votre boutique en ligne de beauté, bien-être et accessoires au Bénin. Livraison rapide à Cotonou.',
    keywords: 'boutique, beauté, bien-être, accessoires, Daba, Bénin, Cotonou'
  })

  // Pre-fetch products for search & homepage
  productStore.fetchProducts()

  // Sync backend data if authenticated
  if (authStore.isAuthenticated) {
    try {
      await Promise.all([
        cartStore.syncCartFromBackend(),
        wishlistStore.syncFromBackend()
      ])
    } catch (err) {
      console.warn('Sync on mount failed:', err)
    }
  }
})
</script>
