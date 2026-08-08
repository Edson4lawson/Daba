<template>
  <section class="py-16 lg:py-24 bg-daba-cream-light border-t border-daba-border/60">
    <div class="max-w-[1440px] mx-auto px-6 sm:px-8 lg:px-12">
      
      <!-- Header -->
      <div class="text-center max-w-2xl mx-auto mb-16">
        <span class="font-worksans text-xs font-bold uppercase tracking-widest text-daba-slate-light">
          TÉMOIGNAGES
        </span>
        <h2 class="font-playfair text-3xl sm:text-4xl lg:text-[48px] font-normal text-daba-navy mt-2">
          Ce que nos clients disent de nous
        </h2>
      </div>

      <!-- 3 Testimonial Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div 
          v-for="item in testimonials" 
          :key="item.name"
          class="bg-white rounded-card p-8 border border-daba-border shadow-card-light flex flex-col justify-between"
        >
          <div class="space-y-4">
            <!-- 5 Orange Stars -->
            <div class="flex items-center space-x-1 text-daba-orange">
              <Icon v-for="n in 5" :key="n" icon="solar:star-bold" class="w-4 h-4" />
            </div>

            <!-- Quote -->
            <p class="font-karla text-sm text-daba-navy leading-relaxed italic">
              "{{ item.quote }}"
            </p>
          </div>

          <!-- Author Info -->
          <div class="pt-6 mt-6 border-t border-daba-border-light">
            <h4 class="font-karla font-bold text-sm text-daba-navy">
              {{ item.name }}
            </h4>
            <p class="font-worksans text-[11px] font-bold uppercase tracking-wider text-daba-slate-light mt-0.5">
              {{ item.role }}
            </p>
          </div>
        </div>
      </div>

    </div>
  </section>
</template>

<script setup>
import { Icon } from '@iconify/vue'

const testimonials = [
  {
    quote: "La qualité du poulet est constante, la livraison est rapide et la traçabilité rassure mes clients. Un partenaire fiable pour mon restaurant.",
    name: "Amévi K.",
    role: "CHEF CUISINIER À LOMÉ"
  },
  {
    quote: "Enfin une volaille locale savoureuse et bien calibrée pour ma famille. Je recommande les colis famille, pratiques et économiques.",
    name: "Kossi M.",
    role: "PARTICULIER À ADABLATO"
  },
  {
    quote: "Service B2B irréprochable : livraisons hebdomadaires ponctuelles, factures conformes et certificats de traçabilité joints.",
    name: "Mawuli S.",
    role: "RESTAURATEUR À BAGUIDA"
  }
]
</script>
