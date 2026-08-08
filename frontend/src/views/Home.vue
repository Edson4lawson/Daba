<template>
  <div class="min-h-screen bg-daba-cream flex flex-col font-karla">
    <Header />

    <main class="flex-grow">
      <!-- Hero Section -->
      <Hero />

      <!-- Nos Engagements Section -->
      <EngagementsSection />

      <!-- Produits Phares Section -->
      <ProduitsPhares />

      <!-- Témoignages Section -->
      <TemoignagesSection />

      <!-- Bandeau Traçabilité -->
      <TracabiliteHomeBanner />

      <!-- Bandeau B2B -->
      <B2BBanner />
    </main>

    <Footer />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import Header from '@/components/Header.vue'
import Hero from '@/components/Hero.vue'
import EngagementsSection from '@/components/home/EngagementsSection.vue'
import ProduitsPhares from '@/components/home/ProduitsPhares.vue'
import TemoignagesSection from '@/components/home/TemoignagesSection.vue'
import TracabiliteHomeBanner from '@/components/home/TracabiliteHomeBanner.vue'
import B2BBanner from '@/components/home/B2BBanner.vue'
import Footer from '@/components/Footer.vue'
import { useSEO } from '@/composables/useSEO'

const { updateMetaTags } = useSEO()

onMounted(() => {
  updateMetaTags({
    title: 'Accueil — Daba',
    description: 'DABA SAS élève, transforme et livre une volaille togolaise d\'exception. Chaque lot est tracé, vérifiable et livré en froid continu.',
    keywords: 'volaille, poulet, charcuterie, Daba, Togo, Lomé, Kpomé, élevage, traçabilité'
  })
})
</script>
