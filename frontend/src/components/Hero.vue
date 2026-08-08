<template>
  <section class="relative bg-daba-cream pt-8 pb-16 lg:py-20 overflow-hidden">
    <div class="max-w-[1440px] mx-auto px-6 sm:px-8 lg:px-12">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

        <!-- Left Content -->
        <div class="lg:col-span-7 space-y-6">
          <div class="inline-flex items-center gap-2 font-worksans text-xs font-bold uppercase tracking-widest text-daba-orange">
            <span class="w-2 h-2 rounded-full bg-daba-orange animate-pulse"></span>
            MADE IN TOGO · DEPUIS LA FERME
          </div>

          <h1 class="font-playfair text-4xl sm:text-5xl lg:text-[60px] leading-[1.15] font-normal text-daba-navy">
            De notre ferme <br class="hidden sm:block" />
            à votre table
          </h1>

          <p class="font-karla text-base sm:text-lg text-daba-slate max-w-xl leading-relaxed">
            DABA SAS élève, transforme et livre une volaille togolaise d'exception. Chaque lot est tracé, vérifiable et livré en froid continu.
          </p>

          <div class="pt-4 flex flex-wrap items-center gap-4">
            <a href="https://wa.me/+22890000000?text=Bonjour%20DABA,%20je%20souhaite%20passer%20commande"
              target="_blank" rel="noopener noreferrer"
              class="inline-flex items-center gap-2.5 px-7 py-4 rounded-full bg-daba-orange text-white font-inter font-bold text-xs uppercase tracking-wider hover:bg-daba-orange-alt transition-all shadow-hero-accent hover:scale-[1.02] active:scale-95">
              <Icon icon="fa6-brands:whatsapp" class="w-4 h-4" />
              Commander sur WhatsApp
            </a>
            <router-link to="/tracabilite"
              class="inline-flex items-center justify-center px-7 py-4 rounded-full border-2 border-daba-navy text-daba-navy font-inter font-bold text-xs uppercase tracking-wider hover:bg-daba-navy hover:text-white transition-all active:scale-95">
              Vérifier un lot
            </router-link>
          </div>

          <!-- Stats — refs pour GSAP CountUp -->
          <div class="pt-8 sm:pt-12 grid grid-cols-3 gap-6 border-t border-daba-border/60 max-w-lg" ref="statsRef">
            <div>
              <p class="font-playfair text-3xl sm:text-4xl font-normal text-daba-navy" ref="stat1">0</p>
              <p class="font-worksans text-[11px] font-bold uppercase tracking-wider text-daba-slate-light mt-1">FERMES PARTENAIRES</p>
            </div>
            <div>
              <p class="font-playfair text-3xl sm:text-4xl font-normal text-daba-navy" ref="stat2">0%</p>
              <p class="font-worksans text-[11px] font-bold uppercase tracking-wider text-daba-slate-light mt-1">LOTS TRACÉS</p>
            </div>
            <div>
              <p class="font-playfair text-3xl sm:text-4xl font-normal text-daba-navy" ref="stat3">0h</p>
              <p class="font-worksans text-[11px] font-bold uppercase tracking-wider text-daba-slate-light mt-1">FERME &rarr; TABLE</p>
            </div>
          </div>
        </div>

        <!-- Right Image + Floating Badge -->
        <div class="lg:col-span-5 relative">
          <div class="relative rounded-2xl overflow-hidden shadow-card-elevated border border-daba-border bg-white">
            <img :src="heroFermeImg" alt="Ferme DABA — Élevage de poules au Togo"
              class="w-full h-[420px] sm:h-[500px] object-cover object-center" />
            <div class="absolute bottom-6 left-6 right-6 p-4 rounded-xl bg-white/80 backdrop-blur-md border border-white/60 shadow-lg flex items-start gap-3.5">
              <div class="w-10 h-10 rounded-full bg-daba-green/15 flex items-center justify-center shrink-0 mt-0.5">
                <Icon icon="solar:check-circle-bold" class="w-6 h-6 text-daba-green" />
              </div>
              <div class="text-left">
                <h4 class="font-inter font-bold text-sm text-daba-navy">Qualité Certifiée</h4>
                <p class="font-karla text-xs text-daba-slate mt-0.5 leading-snug">
                  Élevage responsable sans antibiotiques de croissance, nourri aux céréales locales.
                </p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Icon } from '@iconify/vue'
import heroFermeImg from '../assets/Élevage de poules en plein air dans une ferme DABA au Togo.png'

const statsRef = ref(null)
const stat1 = ref(null)
const stat2 = ref(null)
const stat3 = ref(null)

onMounted(() => {
  // Respect prefers-reduced-motion
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches

  if (prefersReduced) {
    if (stat1.value) stat1.value.textContent = '12'
    if (stat2.value) stat2.value.textContent = '100%'
    if (stat3.value) stat3.value.textContent = '48h'
    return
  }

  // GSAP CountUp animation triggered by IntersectionObserver
  const animate = async () => {
    const { gsap } = await import('gsap')

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return
        observer.disconnect()

        // Stat 1: 12 fermes
        const obj1 = { val: 0 }
        gsap.to(obj1, {
          val: 12, duration: 1.2, ease: 'power2.out',
          onUpdate: () => {
            if (stat1.value) stat1.value.textContent = Math.round(obj1.val)
          }
        })

        // Stat 2: 100%
        const obj2 = { val: 0 }
        gsap.to(obj2, {
          val: 100, duration: 1.4, ease: 'power2.out',
          onUpdate: () => {
            if (stat2.value) stat2.value.textContent = Math.round(obj2.val) + '%'
          }
        })

        // Stat 3: 48h
        const obj3 = { val: 0 }
        gsap.to(obj3, {
          val: 48, duration: 1.0, ease: 'power2.out',
          onUpdate: () => {
            if (stat3.value) stat3.value.textContent = Math.round(obj3.val) + 'h'
          }
        })
      })
    }, { threshold: 0.4 })

    if (statsRef.value) observer.observe(statsRef.value)
  }

  animate()
})
</script>
