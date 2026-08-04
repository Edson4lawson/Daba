<template>
  <div class="min-h-screen bg-daba-cream flex flex-col items-center justify-center px-4 py-20">
    <div class="text-center">
      <div class="w-32 h-32 bg-daba-cream-alt rounded-full flex items-center justify-center mx-auto mb-8">
        <Icon icon="solar:ghost-smile-bold-duotone" class="w-16 h-16 text-daba-orange" />
      </div>
      <h1 class="text-6xl font-black text-daba-navy mb-4">404</h1>
      <h2 class="text-2xl font-bold text-daba-slate mb-4">Page introuvable</h2>
      <p class="text-daba-slate-dark mb-8 max-w-md mx-auto">
        La page que vous recherchez n'existe pas ou a été déplacée. Retournez à l'accueil pour continuer votre shopping.
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <router-link to="/" class="px-8 py-3 bg-daba-orange text-white rounded-full font-bold hover:bg-daba-orange-dark transition-all shadow-xl shadow-daba-orange/20">
          Retour à l'accueil
        </router-link>
        <router-link to="/boutique" class="px-8 py-3 border-2 border-daba-cream-alt text-daba-navy rounded-full font-bold hover:bg-daba-cream-alt transition-all">
          Voir la boutique
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Icon } from '@iconify/vue';
</script>
