<template>
  <section class="py-16 lg:py-24 bg-daba-navy text-white relative overflow-hidden">
    <div class="max-w-[1440px] mx-auto px-6 sm:px-8 lg:px-12 text-center">
      
      <span class="font-worksans text-xs font-bold uppercase tracking-widest text-daba-orange">
        VÉRIFICATION & TRAÇABILITÉ
      </span>

      <h2 class="font-playfair text-3xl sm:text-4xl lg:text-[48px] font-normal text-white mt-3 mb-4 leading-tight max-w-3xl mx-auto">
        Saisissez un numéro de lot, découvrez son histoire
      </h2>

      <p class="font-karla text-sm sm:text-base text-daba-border/80 max-w-2xl mx-auto leading-relaxed mb-10">
        Chaque emballage DABA porte un QR code et un numéro de lot. Ferme d'origine, alimentation, historique vétérinaire et preuve d'ancrage sanitaire : tout est consultable en quelques secondes.
      </p>

      <!-- Input Form Card -->
      <div class="max-w-2xl mx-auto bg-white rounded-2xl p-3 sm:p-4 shadow-2xl">
        <form @submit.prevent="handleVerify" class="flex flex-col sm:flex-row items-center gap-3">
          <div class="relative flex-1 w-full">
            <input 
              v-model="lotCode" 
              type="text" 
              placeholder="#DBA-2026-0142" 
              class="w-full px-5 py-3.5 bg-daba-cream-alt/70 border border-daba-border rounded-xl text-daba-navy font-inter font-medium text-sm focus:outline-none focus:ring-2 focus:ring-daba-orange"
            />
          </div>
          <button 
            type="submit" 
            class="w-full sm:w-auto px-8 py-3.5 rounded-full bg-daba-orange text-white font-inter font-bold text-xs uppercase tracking-wider hover:bg-daba-orange-alt transition-all shadow-md active:scale-95 shrink-0"
          >
            Vérifier
          </button>
        </form>
      </div>

    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const lotCode = ref('')

const handleVerify = () => {
  const code = lotCode.value.trim() || 'DBA-2026-0142'
  router.push({ path: '/tracabilite', query: { code: code.replace('#', '') } })
}
</script>
