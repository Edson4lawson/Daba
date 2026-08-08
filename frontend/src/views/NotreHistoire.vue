<template>
  <div class="min-h-screen bg-daba-cream flex flex-col font-karla">
    <Header />

    <main class="flex-grow py-12 lg:py-16">
      <div class="max-w-[1440px] mx-auto px-6 sm:px-8 lg:px-12">
        
        <!-- Header / Hero Section -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center mb-16 lg:mb-24 overflow-hidden">
          <div class="lg:col-span-7 space-y-6" data-aos="fade-up">
            <span class="font-worksans text-xs font-bold uppercase tracking-widest text-daba-orange">
              DEPUIS 2021 AU TOGO
            </span>

            <h1 class="font-playfair text-3xl sm:text-4xl lg:text-[54px] leading-[1.15] font-normal text-daba-navy">
              Une aviculture moderne, responsable et passionnée
            </h1>

            <p class="font-karla text-base sm:text-lg text-daba-slate max-w-xl leading-relaxed">
              De nos fermes jusqu'à vos assiettes, DABA SAS réinvente la chaîne de la volaille locale avec rigueur, fraîcheur et authenticité.
            </p>

            <div class="pt-2">
              <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-daba-cream-light border border-daba-border text-daba-navy font-inter font-semibold text-xs uppercase tracking-wider">
                <Icon icon="solar:star-bold" class="w-4 h-4 text-daba-orange" />
                5 ans d'excellence — 2021 – 2026
              </span>
            </div>
          </div>

          <div class="lg:col-span-5" data-aos="fade-up" data-aos-delay="150">
            <div class="rounded-2xl overflow-hidden shadow-card-elevated border border-daba-border bg-white hover:-translate-y-1 transition-all duration-200">
              <img 
                :src="imgFerme" 
                alt="Ferme DABA à Kpomé" 
                class="w-full h-[380px] sm:h-[450px] object-cover object-center"
              />
            </div>
          </div>
        </div>

        <!-- Section Notre Mission -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start py-12 border-t border-daba-border/60 mb-16 lg:mb-24 overflow-hidden">
          <div class="lg:col-span-4" data-aos="fade-up">
            <span class="font-worksans text-xs font-bold uppercase tracking-widest text-daba-slate-light block mb-2">
              NOTRE MISSION
            </span>
            <h2 class="font-playfair text-2xl sm:text-3xl lg:text-[36px] font-normal text-daba-navy leading-tight">
              De la terre à votre table : l'aventure DABA
            </h2>
          </div>

          <div class="lg:col-span-8 space-y-4 text-daba-slate text-sm sm:text-base leading-relaxed" data-aos="fade-up" data-aos-delay="100">
            <p>
              Fondée en 2021, DABA SAS est née d'une ambition simple mais forte : offrir aux familles et aux professionnels du Togo une alternative locale, saine et d'une fraîcheur irréprochable face aux importations.
            </p>
            <p>
              En 5 ans d'existence, nous avons bâti un modèle basé sur la transparence et la responsabilité. Nos fermes situées à Kpomé et dans les régions des Plateaux et Maritime nourrissent avec une alimentation locale équilibrée et bénéficient d'un suivi vétérinaire strict.
            </p>
            <p>
              Dans notre atelier de transformation moderne à Lomé, nous allions tradition et normes d'hygiène rigoureuses pour découper, fumer et transformer nos produits (saucisses, jambons, merguez, chipolatas, panés crispy) sans jamais rompre la chaîne du froid.
            </p>
          </div>
        </div>

        <!-- Section Timeline : 5 années, 4 étapes clés -->
        <div class="py-12 border-t border-daba-border/60 mb-16 lg:mb-24 overflow-hidden">
          <div class="text-center max-w-2xl mx-auto mb-16" data-aos="fade-up">
            <span class="font-worksans text-xs font-bold uppercase tracking-widest text-daba-slate-light">
              NOTRE PARCOURS
            </span>
            <h2 class="font-playfair text-3xl sm:text-4xl lg:text-[40px] font-normal text-daba-navy mt-2">
              Cinq années, quatre étapes clés
            </h2>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 relative">
            <div 
              v-for="(step, idx) in timeline" 
              :key="step.year"
              data-aos="fade-up"
              :data-aos-delay="idx * 100"
              class="bg-white rounded-card p-6 border border-daba-border shadow-card-light hover:shadow-card-elevated hover:-translate-y-1 transition-all duration-200 relative flex flex-col justify-between"
            >
              <div>
                <div class="flex items-center gap-2 mb-4">
                  <span class="w-3 h-3 rounded-full bg-daba-orange"></span>
                  <span class="font-playfair text-3xl font-bold text-daba-orange">{{ step.year }}</span>
                </div>
                <h3 class="font-playfair text-lg font-semibold text-daba-navy mb-2">
                  {{ step.title }}
                </h3>
                <p class="font-karla text-xs text-daba-slate leading-relaxed">
                  {{ step.description }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Section 4 Piliers -->
        <div class="py-12 border-t border-daba-border/60 mb-16 lg:mb-24 overflow-hidden">
          <div class="text-center max-w-2xl mx-auto mb-16" data-aos="fade-up">
            <span class="font-worksans text-xs font-bold uppercase tracking-widest text-daba-slate-light">
              NOS ENGAGEMENTS
            </span>
            <h2 class="font-playfair text-3xl sm:text-4xl lg:text-[40px] font-normal text-daba-navy mt-2">
              Quatre piliers qui guident chaque lot
            </h2>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div 
              v-for="(pil, idx) in piliers" 
              :key="pil.title" 
              data-aos="fade-up"
              :data-aos-delay="idx * 100"
              class="bg-daba-cream-light rounded-card p-6 border border-daba-border hover:shadow-card-elevated hover:-translate-y-1 transition-all duration-200"
            >
              <div class="w-10 h-10 rounded-xl bg-daba-cream flex items-center justify-center text-daba-navy mb-4">
                <Icon :icon="pil.icon" class="w-5 h-5 text-daba-orange" />
              </div>
              <h3 class="font-playfair text-lg font-semibold text-daba-navy mb-2">{{ pil.title }}</h3>
              <p class="font-karla text-xs text-daba-slate leading-relaxed">{{ pil.description }}</p>
            </div>
          </div>
        </div>

        <!-- Stats Banner Navy -->
        <div class="bg-daba-navy text-white rounded-2xl p-8 sm:p-12 mb-16 lg:mb-24 shadow-2xl" data-aos="fade-up">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-8 items-center text-center md:text-left">
            <div>
              <p class="font-playfair text-4xl lg:text-5xl font-normal text-white">5</p>
              <p class="font-karla text-xs text-daba-border/80 mt-1">Ans d'expérience et d'innovation avicole.</p>
            </div>
            <div>
              <p class="font-playfair text-4xl lg:text-5xl font-normal text-white">100 %</p>
              <p class="font-karla text-xs text-daba-border/80 mt-1">Volaille locale élevée au Togo.</p>
            </div>
            <div class="flex items-start gap-3">
              <Icon icon="solar:map-point-bold" class="w-6 h-6 text-daba-orange shrink-0 mt-1" />
              <div>
                <h4 class="font-inter font-bold text-sm text-white">La ferme : Kpomé</h4>
                <p class="font-karla text-xs text-daba-border/80 mt-0.5">Production & élevage, région Maritime.</p>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <Icon icon="solar:shop-bold" class="w-6 h-6 text-daba-orange shrink-0 mt-1" />
              <div>
                <h4 class="font-inter font-bold text-sm text-white">La boutique : Lomé</h4>
                <p class="font-karla text-xs text-daba-border/80 mt-0.5">Agbalépédogan, Lomé.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Photo Gallery Section -->
        <div class="py-12 border-t border-daba-border/60 overflow-hidden">
          <div class="text-center max-w-2xl mx-auto mb-12" data-aos="fade-up">
            <span class="font-worksans text-xs font-bold uppercase tracking-widest text-daba-slate-light">
              EN IMAGES
            </span>
            <h2 class="font-playfair text-3xl sm:text-4xl lg:text-[40px] font-normal text-daba-navy mt-2">
              La ferme, l'atelier et la boutique
            </h2>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <div 
              v-for="(img, idx) in galleryImages" 
              :key="idx" 
              data-aos="fade-up"
              :data-aos-delay="idx * 80"
              class="rounded-xl overflow-hidden h-64 border border-daba-border shadow-card-light hover:shadow-card-elevated hover:-translate-y-1 transition-all duration-200"
            >
              <img :src="img.src" :alt="img.alt" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" />
            </div>
          </div>

          <div class="text-center" data-aos="fade-up">
            <router-link 
              to="/produits"
              class="inline-flex items-center justify-center px-8 py-3.5 rounded-full bg-daba-orange text-white font-inter font-bold text-xs uppercase tracking-wider hover:bg-daba-orange-alt transition-all shadow-md active:scale-95"
            >
              Voir le catalogue
            </router-link>
          </div>
        </div>

      </div>
    </main>

    <Footer />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue'
import { Icon } from '@iconify/vue'
import { useSEO } from '@/composables/useSEO'

import imgFerme from '../assets/Élevage de volailles DABA à Kpomé, au Togo.png'
import imgPoulailler from '../assets/Poulaillers ouverts de la ferme DABA à Kpomé, au Togo.png'
import imgAtelier from '../assets/Atelier de découpe et de transformation DABA à Lomé.png'
import imgEquipe from '../assets/Équipe DABA en tenue d\'hygiène dans l\'atelier de transformation.png'
import imgGamme from '../assets/Gamme de produits DABA _ découpes, charcuterie et produits fumés.png'
import imgPoules from '../assets/Élevage de poules en plein air dans une ferme DABA au Togo.png'

const { updateMetaTags } = useSEO()

const timeline = [
  {
    year: '2021',
    title: 'Les débuts à Kpomé',
    description: 'Création de DABA SAS et installation de la première ferme d’élevage à Kpomé, axée sur le bien-être animal.'
  },
  {
    year: '2023',
    title: 'L’atelier de transformation',
    description: 'Mise en service de notre atelier de découpe et de charcuterie à Lomé : poulets fumés, saucisses, jambon et charcuterie fine.'
  },
  {
    year: '2024',
    title: 'Le point de vente de Lomé',
    description: 'Ouverture de notre boutique vitrine à Agbalépédogan (Lomé) pour être au plus près des consommateurs.'
  },
  {
    year: '2026',
    title: '5 ans d’excellence',
    description: 'Un réseau solide reliant les éleveurs togolais aux ménages, restaurants et distributeurs avec des dizaines de références au catalogue.'
  }
]

const piliers = [
  {
    icon: 'solar:leaf-bold-duotone',
    title: 'Qualité d’élevage responsable',
    description: 'Alimentation locale garantie, densité maîtrisée dans nos bâtiments et zéro produit nocif pour une viande saine.'
  },
  {
    icon: 'solar:shield-check-bold-duotone',
    title: 'Traçabilité & froid continu',
    description: 'Chaque lot est enregistré. De l’abattage sur commande jusqu’à la livraison, la chaîne du froid n’est jamais interrompue.'
  },
  {
    icon: 'solar:shop-2-bold-duotone',
    title: 'Soutien à l’économie locale',
    description: 'Nous valorisons le savoir-faire des éleveurs togolais et créons des emplois dans les régions Maritime et des Plateaux.'
  },
  {
    icon: 'solar:chef-hat-bold-duotone',
    title: 'Savoir-faire charcutier',
    description: 'Recettes savoureuses, fumage artisanal au bois et produits panés prêts-à-cuire pour régaler toute la famille.'
  }
]

const galleryImages = [
  { src: imgFerme, alt: 'Élevage de volailles DABA à Kpomé' },
  { src: imgPoulailler, alt: 'Poulaillers ouverts Kpomé' },
  { src: imgAtelier, alt: 'Atelier de découpe Lomé' },
  { src: imgEquipe, alt: 'Équipe DABA en tenue d\'hygiène' },
  { src: imgGamme, alt: 'Gamme de produits DABA' },
  { src: imgPoules, alt: 'Élevage de poules en plein air' }
]

onMounted(() => {
  updateMetaTags({
    title: 'Notre Histoire — Daba',
    description: 'Découvrez l\'histoire de DABA SAS : de nos débuts à Kpomé en 2021 à notre atelier de transformation moderne à Lomé.',
    keywords: 'histoire Daba, élevage Kpomé, volaille Togo, atelier charcuterie Lomé'
  })
})
</script>
