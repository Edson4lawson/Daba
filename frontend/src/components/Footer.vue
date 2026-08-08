<template>
  <footer class="bg-daba-navy text-white pt-16 pb-8 border-t border-daba-navy-deep">
    <div class="max-w-[1440px] mx-auto px-6 sm:px-8 lg:px-12">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 pb-12 border-b border-white/10">

        <!-- Column 1: Brand Info -->
        <div class="space-y-4">
          <router-link to="/" class="inline-block">
            <img :src="footerLogoImg" alt="DABA — Élevage & Transformation" class="h-12 w-auto object-contain" />
          </router-link>
          <p class="text-xs font-karla text-daba-border/80 leading-relaxed max-w-xs">
            Élevage responsable et transformation de volaille locale à Lomé, Togo.
          </p>
        </div>

        <!-- Column 2: MENU -->
        <div>
          <h4 class="text-xs font-worksans font-bold uppercase tracking-widest text-white/60 mb-4">
            MENU
          </h4>
          <ul class="space-y-2.5">
            <li v-for="item in menuLinks" :key="item.to">
              <router-link :to="item.to"
                class="text-sm font-karla text-daba-border/90 hover:text-daba-orange transition-colors">
                {{ item.name }}
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Column 3: INFORMATIONS LÉGALES -->
        <div>
          <h4 class="text-xs font-worksans font-bold uppercase tracking-widest text-white/60 mb-4">
            INFORMATIONS LÉGALES
          </h4>
          <ul class="space-y-2.5">
            <li v-for="item in legalLinks" :key="item.name">
              <router-link :to="item.to"
                class="text-sm font-karla text-daba-border/90 hover:text-daba-orange transition-colors">
                {{ item.name }}
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Column 4: CONTACT -->
        <div>
          <h4 class="text-xs font-worksans font-bold uppercase tracking-widest text-white/60 mb-4">
            CONTACT
          </h4>
          <ul class="space-y-3 text-sm font-karla text-daba-border/90">
            <li class="flex items-center gap-2.5">
              <Icon icon="solar:map-point-linear" class="w-4 h-4 text-daba-orange shrink-0" />
              <span>Lomé, Togo</span>
            </li>
            <li class="flex items-center gap-2.5">
              <Icon icon="solar:phone-calling-linear" class="w-4 h-4 text-daba-orange shrink-0" />
              <a href="tel:+22890000000" class="hover:text-daba-orange transition-colors">+228 90 00 00 00</a>
            </li>
            <li class="flex items-center gap-2.5">
              <Icon icon="solar:letter-linear" class="w-4 h-4 text-daba-orange shrink-0" />
              <a href="mailto:contact@daba.tg" class="hover:text-daba-orange transition-colors">contact@daba.tg</a>
            </li>
          </ul>

          <!-- Social Links: Facebook, Instagram, WhatsApp, TikTok -->
          <div class="flex items-center space-x-3 mt-6">
            <a href="https://facebook.com/daba.tg" target="_blank" rel="noopener noreferrer" aria-label="Facebook"
              class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-daba-orange transition-colors">
              <Icon icon="fa6-brands:facebook-f" class="w-4 h-4" />
            </a>
            <a href="https://instagram.com/daba.tg" target="_blank" rel="noopener noreferrer" aria-label="Instagram"
              class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-daba-orange transition-colors">
              <Icon icon="fa6-brands:instagram" class="w-4 h-4" />
            </a>
            <a href="https://wa.me/+22890000000" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"
              class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-daba-orange transition-colors">
              <Icon icon="fa6-brands:whatsapp" class="w-4 h-4" />
            </a>
            <a href="https://tiktok.com/@daba.tg" target="_blank" rel="noopener noreferrer" aria-label="TikTok"
              class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-daba-orange transition-colors">
              <Icon icon="fa6-brands:tiktok" class="w-4 h-4" />
            </a>
          </div>
        </div>

      </div>

      <!-- Copyright Bottom Bar -->
      <div class="pt-8 text-center">
        <p class="text-xs font-karla text-daba-border/60">
          &copy; {{ currentYear }} DABA SAS. Tous droits réservés.
        </p>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'
import { Icon } from '@iconify/vue'
import footerLogoImg from '../assets/daba-icone.png'

const currentYear = computed(() => new Date().getFullYear())

const menuLinks = [
  { name: 'Accueil', to: '/' },
  { name: 'Produits', to: '/produits' },
  { name: 'Notre Histoire', to: '/notre-histoire' },
  { name: 'Espace B2B', to: '/espace-b2b' },
]

const legalLinks = [
  { name: 'Mentions légales', to: '/mentions-legales' },
  { name: 'Politique de confidentialité', to: '/privacy' },
  { name: 'Conditions Générales de Vente', to: '/terms' },
  { name: 'Traçabilité & Contrôle sanitaire', to: '/tracabilite' },
]
</script>
