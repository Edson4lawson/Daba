<template>
  <footer class="bg-daba-navy border-t border-daba-navy-deep text-white pt-20 pb-8 relative overflow-hidden">
    <!-- Decorative elements -->
    <div class="absolute top-0 left-1/4 w-64 h-64 bg-daba-orange/5 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 right-1/4 w-64 h-64 bg-daba-green/5 rounded-full blur-3xl"></div>

    <div class="container mx-auto px-4 md:px-6 relative z-10">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
        <!-- Brand Column -->
        <div class="lg:col-span-1">
          <div class="flex items-center mb-6">
            <img src="../assets/daba-icone.png" alt="Daba" class="w-14 h-14 mr-3" />
            <div>
              <h4 class="text-lg font-black tracking-tight uppercase italic">Daba</h4>
              <p class="text-[10px] text-daba-orange font-black uppercase tracking-[0.3em]">Boucherie & Charcuterie</p>
            </div>
          </div>
          <p class="text-sm text-daba-slate leading-relaxed mb-6 font-medium">
            Votre destination premium pour la boucherie et la charcuterie. Produits frais de qualité, livraison rapide au Bénin.
          </p>
          <div class="flex space-x-3">
            <a v-for="social in socialLinks" :key="social.name" :href="social.url" target="_blank" rel="noopener noreferrer"
              class="w-10 h-10 bg-daba-navy-deep hover:bg-daba-cream rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 hover:shadow-lg hover:shadow-white/10 group">
              <Icon :icon="social.icon" class="w-5 h-5 text-daba-slate group-hover:text-daba-navy transition-colors" />
            </a>
          </div>
        </div>

        <!-- Quick Links -->
        <div>
          <h5 class="text-xs font-black text-white uppercase tracking-[0.3em] mb-6">Navigation</h5>
          <ul class="space-y-4">
            <li v-for="link in quickLinks" :key="link.label">
              <router-link :to="link.to" class="text-sm text-daba-slate hover:text-white transition-all font-bold uppercase tracking-widest text-[10px] flex items-center group">
                <span class="w-2 h-px bg-daba-orange mr-0 group-hover:mr-3 transition-all opacity-0 group-hover:opacity-100"></span>
                {{ link.label }}
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Service Client -->
        <div>
          <h5 class="text-xs font-black text-white uppercase tracking-[0.3em] mb-6">Support</h5>
          <ul class="space-y-4">
            <li v-for="link in serviceLinks" :key="link.to">
              <router-link :to="link.to" class="text-sm text-daba-slate hover:text-white transition-all font-bold uppercase tracking-widest text-[10px] flex items-center group">
                <span class="w-2 h-px bg-daba-orange mr-0 group-hover:mr-3 transition-all opacity-0 group-hover:opacity-100"></span>
                {{ link.label }}
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Newsletter -->
        <div>
          <h5 class="text-xs font-black text-white uppercase tracking-[0.3em] mb-6">Newsletter</h5>
          <p class="text-sm text-daba-slate mb-6 leading-relaxed font-medium">Rejoignez le club Daba pour recevoir nos offres privées.</p>
          <form @submit.prevent="handleNewsletter" class="space-y-3">
            <div class="relative">
              <Icon icon="solar:letter-linear" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-daba-slate-dark" />
              <input 
                v-model="newsletterEmail"
                type="email" 
                required
                placeholder="VOTRE EMAIL"
                class="w-full pl-11 pr-4 py-4 bg-daba-navy-deep border-none rounded-2xl text-[10px] font-black tracking-widest text-white placeholder-daba-slate-dark focus:bg-daba-slate-dark focus:outline-none transition-all uppercase"
              />
            </div>
            <button type="submit" 
              class="w-full py-4 bg-daba-cream text-daba-navy rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-daba-orange hover:text-white hover:shadow-xl hover:shadow-daba-orange/40 transition-all active:scale-95">
              S'inscrire
            </button>
          </form>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="border-t border-white/5 pt-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
          <p class="text-[10px] text-daba-slate-dark font-black uppercase tracking-widest">
            &copy; {{ currentYear }} Daba.
          </p>
          <div class="flex items-center space-x-6">
            <router-link to="/terms" class="text-[10px] text-daba-slate-dark font-black uppercase tracking-widest hover:text-white transition-colors">CGV</router-link>
            <router-link to="/privacy" class="text-[10px] text-daba-slate-dark font-black uppercase tracking-widest hover:text-white transition-colors">Confidentialité</router-link>
          </div>
          <div class="flex items-center gap-2 grayscale opacity-30 hover:grayscale-0 hover:opacity-100 transition-all">
            <Icon icon="logos:mtn" class="w-6 h-6" />
            <Icon icon="logos:visa" class="w-8 h-4 object-contain" />
            <Icon icon="logos:mastercard" class="w-8 h-4 object-contain" />
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Icon } from '@iconify/vue'
import Swal from 'sweetalert2'

const newsletterEmail = ref('')
const currentYear = computed(() => new Date().getFullYear())

const socialLinks = [
  { name: 'Facebook', icon: 'fa6-brands:facebook-f', url: 'https://www.facebook.com/share/1BhY2JCNV2/?mibextid=LQQJ4d' },
  { name: 'Instagram', icon: 'fa6-brands:instagram', url: 'https://www.instagram.com/jade889148?igsh=Z3UwMWwwc3MwejF3&utm_source=qr' },
  { name: 'WhatsApp', icon: 'fa6-brands:whatsapp', url: 'https://wa.me/+2290156783770' },
  { name: 'Snapchat', icon: 'fa6-brands:snapchat', url: 'https://snapchat.com/t/9pLClwle' }
]

const quickLinks = [
  { label: 'Accueil', to: '/' },
  { label: 'Nos Produits', to: '/#products' },
  { label: 'Aide & FAQ', to: '/faq' }
]

const serviceLinks = [
  { label: 'Livraison Express', to: '/shipping' },
  { label: 'Retours Faciles', to: '/returns' },
  { label: 'Conditions de vente', to: '/terms' },
  { label: 'Vie Privée', to: '/privacy' }
]

const handleNewsletter = () => {
  if (!newsletterEmail.value) return
  Swal.fire({
    icon: 'success', title: 'Bienvenue chez Daba ! 🎉', text: 'Inscription enregistrée.', confirmButtonColor: '#CE4600'
  })
  newsletterEmail.value = ''
}
</script>
