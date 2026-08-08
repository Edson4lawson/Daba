<template>
  <section class="py-16 lg:py-24 bg-daba-cream-light border-t border-daba-border/60 overflow-hidden">
    <div class="max-w-[1440px] mx-auto px-6 sm:px-8 lg:px-12">
      
      <!-- Section Header -->
      <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6" data-aos="fade-up">
        <div>
          <span class="font-worksans text-xs font-bold uppercase tracking-widest text-daba-slate-light">
            NOS ENGAGEMENTS
          </span>
          <h2 class="font-playfair text-3xl sm:text-4xl lg:text-[48px] font-normal text-daba-navy mt-2 leading-tight max-w-2xl">
            Une maîtrise complète de la chaîne pour une viande 100 % sûre et locale.
          </h2>
        </div>

        <router-link 
          to="/notre-histoire"
          class="inline-flex items-center gap-2 font-inter text-xs font-bold uppercase tracking-wider text-daba-orange hover:text-daba-orange-alt transition-colors shrink-0"
        >
          <span>DÉCOUVRIR NOTRE HISTOIRE</span>
          <Icon icon="solar:arrow-right-linear" class="w-4 h-4" />
        </router-link>
      </div>

      <!-- 4 Cards Grid with Stagger -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
        <div 
          v-for="(item, index) in engagements" 
          :key="item.title"
          data-aos="fade-up"
          :data-aos-delay="index * 100"
          class="bg-white rounded-card p-6 sm:p-8 border border-daba-border shadow-card-light hover:shadow-card-elevated hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between"
        >
          <div>
            <div class="w-12 h-12 rounded-xl bg-daba-cream flex items-center justify-center text-daba-navy mb-6">
              <Icon :icon="item.icon" class="w-6 h-6 text-daba-orange" />
            </div>
            <h3 class="font-playfair text-xl font-semibold text-daba-navy mb-3">
              {{ item.title }}
            </h3>
            <p class="font-karla text-sm text-daba-slate leading-relaxed">
              {{ item.description }}
            </p>
          </div>
        </div>
      </div>

    </div>
  </section>
</template>

<script setup>
import { Icon } from '@iconify/vue'

const engagements = [
  {
    icon: 'solar:leaf-bold-duotone',
    title: 'Aviculture responsable',
    description: 'Élevage au sol, densité maîtrisée, alimentation céréalière locale sans antibiotique de croissance.'
  },
  {
    icon: 'solar:shield-check-bold-duotone',
    title: 'Sécurité sanitaire',
    description: 'Suivi vétérinaire documenté et conforme aux normes sanitaires togolaises et CEDEAO.'
  },
  {
    icon: 'solar:chart-2-bold-duotone',
    title: 'Pilotage DABA One',
    description: 'Chaque étape — approvisionnement, production, vente — est enregistrée dans notre plateforme interne.'
  },
  {
    icon: 'solar:delivery-bold-duotone',
    title: 'Livraison chaîne du froid',
    description: 'Distribution sur Lomé et le grand sud du Togo, en froid continu jusqu’à votre table.'
  }
]
</script>
