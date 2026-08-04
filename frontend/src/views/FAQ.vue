<template>
  <div class="min-h-screen bg-daba-cream-alt pt-32 pb-20">
    <div class="container mx-auto px-4 md:px-6 max-w-4xl">
      <!-- Header Section -->
      <div class="text-center mb-16" data-aos="fade-up">
        <h1 class="text-4xl md:text-5xl font-black text-daba-navy mb-4 tracking-tight">Questions Fréquentes</h1>
        <p class="text-daba-slate font-medium max-w-xl mx-auto">Tout ce que vous devez savoir sur vos achats chez Daba. Vous avez une autre question ? Notre équipe est là pour vous.</p>
        <div class="w-20 h-1.5 bg-daba-orange mx-auto mt-8 rounded-full"></div>
      </div>

      <!-- FAQ Accordion -->
      <div class="space-y-4 mb-20" data-aos="fade-up" data-aos-delay="100">
        <div v-for="(faq, index) in faqs" :key="index" 
          class="group bg-daba-cream rounded-3xl border border-daba-cream-alt overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-daba-cream-alt/50"
          :class="{ 'ring-2 ring-daba-orange/20 border-daba-cream-alt shadow-xl shadow-daba-orange/50': expandedFAQ === index }">
          
          <button 
            @click="toggleFAQ(index)"
            class="w-full px-8 py-6 text-left flex justify-between items-center transition-colors"
          >
            <span class="text-lg font-bold text-daba-navy transition-colors" :class="{ 'text-daba-orange': expandedFAQ === index }">
              {{ faq.question }}
            </span>
            <div class="w-10 h-10 rounded-xl bg-daba-cream-alt group-hover:bg-daba-cream-alt flex items-center justify-center transition-all duration-300"
              :class="{ 'bg-daba-orange rotate-180': expandedFAQ === index }">
              <Icon 
                :icon="expandedFAQ === index ? 'solar:alt-arrow-up-linear' : 'solar:alt-arrow-down-linear'" 
                class="w-5 h-5 transition-colors"
                :class="expandedFAQ === index ? 'text-white' : 'text-daba-slate-dark group-hover:text-daba-orange'"
              />
            </div>
          </button>
          
          <transition 
            enter-active-class="transition-all duration-300 ease-out"
            leave-active-class="transition-all duration-300 ease-in"
            enter-from-class="max-h-0 opacity-0"
            enter-to-class="max-h-96 opacity-100"
            leave-from-class="max-h-96 opacity-100"
            leave-to-class="max-h-0 opacity-0"
          >
            <div v-show="expandedFAQ === index" class="px-8 pb-8">
              <div class="w-full h-px bg-daba-cream-alt mb-6"></div>
              <p class="text-daba-slate leading-relaxed text-lg">{{ faq.answer }}</p>
            </div>
          </transition>
        </div>
      </div>

      <!-- Support CTA -->
      <div class="bg-daba-navy rounded-[2.5rem] p-10 md:p-16 text-center relative overflow-hidden" data-aos="fade-up">
        <div class="absolute top-0 right-0 w-64 h-64 bg-daba-orange/10 rounded-full blur-3xl -mr-32 -mt-32"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-daba-green/10 rounded-full blur-3xl -ml-32 -mb-32"></div>
        
        <div class="relative z-10">
          <h3 class="text-3xl font-black text-white mb-4">Encore des doutes ?</h3>
          <p class="text-daba-slate-dark text-lg mb-10 max-w-lg mx-auto">
            Contactez notre service client via WhatsApp ou Email. Nous répondons généralement en moins de 2 heures.
          </p>
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="https://wa.me/+2290156783770" target="_blank" 
              class="w-full sm:w-auto px-8 py-4 bg-daba-green text-white font-black uppercase tracking-wider rounded-2xl hover:bg-daba-green transition-all active:scale-95 flex items-center justify-center gap-2">
              <Icon icon="logos:whatsapp-icon" class="w-6 h-6 brightness-0 invert" />
              WhatsApp Direct
            </a>
            <router-link to="/" 
              class="w-full sm:w-auto px-8 py-4 bg-daba-cream/10 text-white font-black uppercase tracking-wider rounded-2xl hover:bg-daba-cream/20 transition-all active:scale-95 flex items-center justify-center gap-2 backdrop-blur-sm">
              <Icon icon="solar:letter-bold" class="w-6 h-6" />
              Envoyer un Mail
            </router-link>
          </div>
        </div>
      </div>

      <!-- Back Button -->
      <div class="mt-12 flex justify-center">
        <router-link 
          to="/" 
          class="group flex items-center gap-2 text-daba-slate-dark font-bold hover:text-daba-navy transition-colors"
        >
          <Icon icon="solar:arrow-left-linear" class="group-hover:-translate-x-1 transition-transform" />
          Retour à la boutique
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Icon } from '@iconify/vue'

const expandedFAQ = ref(null)

const faqs = ref([
  {
    question: "Quels sont les délais de livraison ?",
    answer: "Pour Cotonou et Abomey-Calavi, nous livrons sous 24h à 48h. Pour les autres localités du Bénin, comptez entre 2 et 5 jours selon la destination."
  },
  {
    question: "Quels modes de paiement acceptez-vous ?",
    answer: "Nous acceptons principalement Mobile Money (MTN), Celtis Cash et les virements bancaires UBA. Le paiement à la livraison est possible sous conditions pour Cotonou."
  },
  {
    question: "Puis-je retourner mon produit ?",
    answer: "Oui, vous avez un délai de 48h pour nous signaler tout souci. Le produit doit être intact, non ouvert et dans son emballage d'origine pour des raisons d'hygiène."
  },
  {
    question: "Vos produits sont-ils authentiques ?",
    answer: "Absolument. Daba ne propose que des produits 100% authentiques sélectionnés avec soin auprès de fournisseurs certifiés."
  },
  {
    question: "Comment suivre l'état de ma commande ?",
    answer: "Une fois votre commande validée, vous pouvez suivre son statut en temps réel depuis votre espace 'Mon Compte' dans la section 'Mes Commandes'."
  },
  {
    question: "Avez-vous une boutique physique ?",
    answer: "Nous sommes actuellement une boutique 100% en ligne, ce qui nous permet de vous proposer les meilleurs prix et une livraison rapide partout au Bénin."
  }
])

const toggleFAQ = (index) => {
  expandedFAQ.value = expandedFAQ.value === index ? null : index
}
</script>
