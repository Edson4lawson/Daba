<template>
  <div class="min-h-screen bg-daba-cream flex items-center justify-center px-4 py-20">
    <div class="text-center max-w-lg">
      <div class="w-24 h-24 bg-daba-green/20 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce">
        <Icon icon="solar:check-circle-bold" class="w-14 h-14 text-daba-green" />
      </div>
      <h1 class="text-3xl font-black text-daba-navy mb-3">Commande confirmée ! 🎉</h1>
      <p class="text-daba-slate mb-2">Merci pour votre achat chez <span class="font-bold text-daba-orange">Daba</span>.</p>
      <p class="text-daba-slate mb-8">Votre commande <span class="font-bold text-daba-navy">#{{ orderId }}</span> a bien été enregistrée.</p>
      
      <div class="bg-daba-cream rounded-2xl p-6 border border-daba-cream-alt shadow-sm mb-8">
        <div class="flex items-center justify-center gap-8 text-sm">
          <div class="text-center">
            <Icon icon="solar:box-minimalistic-bold-duotone" class="w-8 h-8 text-daba-orange mx-auto mb-1" />
            <span class="text-daba-slate">En préparation</span>
          </div>
          <div class="w-12 h-0.5 bg-daba-cream-alt"></div>
          <div class="text-center opacity-40">
            <Icon icon="solar:delivery-bold-duotone" class="w-8 h-8 text-daba-orange mx-auto mb-1" />
            <span class="text-daba-slate">En livraison</span>
          </div>
          <div class="w-12 h-0.5 bg-daba-cream-alt"></div>
          <div class="text-center opacity-40">
            <Icon icon="solar:check-circle-bold-duotone" class="w-8 h-8 text-daba-orange mx-auto mb-1" />
            <span class="text-daba-slate">Livré</span>
          </div>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <router-link to="/" class="px-6 py-3 border-2 border-daba-cream-alt text-daba-navy rounded-full font-bold hover:bg-daba-cream-alt transition-all">
          Retour à l'accueil
        </router-link>
        <router-link to="/#products" class="px-6 py-3 bg-daba-orange text-white rounded-full font-bold hover:bg-daba-orange-dark transition-all">
          Continuer mes achats
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router';
import { Icon } from '@iconify/vue';

const route = useRoute();
const orderId = route.params.orderId || '—';
</script>
