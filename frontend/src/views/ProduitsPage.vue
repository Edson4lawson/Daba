<template>
  <div class="min-h-screen bg-daba-cream flex flex-col font-karla">
    <Header />

    <main class="flex-grow py-12 lg:py-16">
      <div class="max-w-[1440px] mx-auto px-6 sm:px-8 lg:px-12">
        
        <!-- Kicker -->
        <span class="font-worksans text-xs font-bold uppercase tracking-widest text-daba-slate-light block mb-2" data-aos="fade-up">
          LE CATALOGUE
        </span>

        <!-- H1 Title -->
        <h1 class="font-playfair text-3xl sm:text-4xl lg:text-[48px] font-normal text-daba-navy mb-3 leading-tight" data-aos="fade-up" data-aos-delay="50">
          Frais, fumés, charcuterie et panés
        </h1>

        <!-- Subtitle -->
        <p class="font-karla text-base text-daba-slate max-w-3xl mb-8 leading-relaxed" data-aos="fade-up" data-aos-delay="100">
          Prix officiels à Lomé. Commande minimum livrée à domicile : 10 000 FCFA. Tous nos lots sont tracés et livrés en froid continu.
        </p>

        <!-- 3 Guarantees Top Bar -->
        <div class="flex flex-wrap items-center gap-6 sm:gap-10 py-4 px-6 bg-daba-cream-light border border-daba-border rounded-xl mb-10 text-xs sm:text-sm font-karla text-daba-navy font-medium" data-aos="fade-up" data-aos-delay="150">
          <div class="flex items-center gap-2">
            <Icon icon="solar:delivery-bold-duotone" class="w-5 h-5 text-daba-green" />
            <span>Livraison à domicile à Lomé</span>
          </div>
          <div class="flex items-center gap-2">
            <Icon icon="solar:snowflake-bold-duotone" class="w-5 h-5 text-daba-green" />
            <span>Respect de la chaîne du froid</span>
          </div>
          <div class="flex items-center gap-2">
            <Icon icon="solar:verified-check-bold-duotone" class="w-5 h-5 text-daba-green" />
            <span>Viande 100% locale</span>
          </div>
        </div>

        <!-- Search Bar Input -->
        <div class="relative max-w-xl mb-8" data-aos="fade-up" data-aos-delay="200">
          <Icon icon="solar:magnifer-linear" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-daba-slate-light" />
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Rechercher un produit..." 
            class="w-full pl-12 pr-4 py-3.5 bg-white border border-daba-border rounded-full text-sm font-karla text-daba-navy placeholder-daba-slate-light focus:outline-none focus:ring-2 focus:ring-daba-orange"
          />
        </div>

        <!-- Category Pills Filter -->
        <div class="flex items-center gap-3 overflow-x-auto pb-4 mb-10 scrollbar-none" data-aos="fade-up" data-aos-delay="250">
          <button 
            v-for="cat in categories" 
            :key="cat.id"
            @click="activeCategory = cat.id"
            class="px-5 py-2.5 rounded-full font-worksans text-xs font-bold uppercase tracking-wider whitespace-nowrap transition-all cursor-pointer"
            :class="activeCategory === cat.id ? 'bg-daba-navy text-white shadow-md' : 'bg-white text-daba-navy border border-daba-border hover:border-daba-orange'"
          >
            {{ cat.name }}
          </button>
        </div>

        <!-- Products Grid (3 columns desktop) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
          <div 
            v-for="(product, index) in filteredProducts" 
            :key="product.id"
            data-aos="fade-up"
            :data-aos-delay="(index % 6) * 60"
            class="bg-white rounded-card overflow-hidden border border-daba-border shadow-card-light hover:shadow-card-elevated hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between group"
          >
            <!-- Product Image (350x176 ratio) -->
            <div class="relative w-full h-[176px] overflow-hidden bg-daba-cream-alt">
              <img 
                :src="product.image" 
                :alt="product.title" 
                class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500"
              />
              <!-- Badge -->
              <div 
                class="absolute top-3 left-3 font-worksans text-[11px] font-bold px-2.5 py-1 rounded uppercase tracking-wider text-white"
                :class="{
                  'bg-daba-green': product.badgeType === 'frais',
                  'bg-daba-orange': product.badgeType === 'fume',
                  'bg-daba-navy-marine': product.badgeType === 'cuit'
                }"
              >
                {{ product.badge }}
              </div>
            </div>

            <!-- Content Area -->
            <div class="p-6 flex-1 flex flex-col justify-between">
              <div>
                <!-- Tip Pill -->
                <div v-if="product.tip" class="mb-3">
                  <span class="inline-block bg-orange-50 text-daba-orange font-karla text-[11px] font-semibold px-2.5 py-1 rounded-full border border-orange-100">
                    {{ product.tip }}
                  </span>
                </div>

                <h3 class="font-playfair text-xl font-semibold text-daba-navy group-hover:text-daba-orange transition-colors">
                  {{ product.title }}
                </h3>
                
                <p class="font-worksans text-[11px] font-bold uppercase tracking-wider text-daba-slate-light mt-1">
                  {{ product.categoryLabel }}
                </p>
              </div>

              <!-- Price & CTA Button -->
              <div class="pt-6 mt-6 border-t border-daba-border-light flex items-center justify-between">
                <div>
                  <span class="font-karla font-bold text-base text-daba-navy">
                    {{ product.priceLabel }}
                  </span>
                </div>

                <button 
                  @click="handleAddToCart(product)" 
                  class="inline-flex items-center justify-center gap-1.5 px-5 py-2 rounded-full bg-daba-orange text-white font-inter font-bold text-xs uppercase tracking-wider hover:bg-daba-orange-alt transition-all shadow-sm active:scale-95 cursor-pointer"
                >
                  <ShoppingBasket class="w-4 h-4" />
                  <span>Commander</span>
                </button>
              </div>
            </div>

          </div>
        </div>

        <!-- Bottom B2B Callout Card -->
        <div class="bg-daba-cream-light rounded-2xl p-8 sm:p-12 border border-daba-border text-center max-w-4xl mx-auto" data-aos="fade-up">
          <h3 class="font-playfair text-2xl sm:text-3xl font-normal text-daba-navy mb-3">
            Commande spéciale ou gros volume ?
          </h3>
          <p class="font-karla text-sm sm:text-base text-daba-slate max-w-xl mx-auto mb-6 leading-relaxed">
            Hôtels, restaurants, grossistes ou événements : nous préparons des devis sur mesure et des réapprovisionnements programmés sous 48 heures.
          </p>
          <router-link 
            to="/espace-b2b"
            class="inline-flex items-center justify-center px-8 py-3.5 rounded-full bg-daba-orange text-white font-inter font-bold text-xs uppercase tracking-wider hover:bg-daba-orange-alt transition-all shadow-md active:scale-95"
          >
            Demander un devis
          </router-link>
        </div>

      </div>
    </main>

    <Footer />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue'
import { Icon } from '@iconify/vue'
import { ShoppingBasket } from 'lucide-vue-next'
import { useSEO } from '@/composables/useSEO'
import { useCartStore } from '@/stores/cart'
import { notifyAddToCart } from '@/utils/notifications'

const handleAddToCart = async (product) => {
  await cartStore.addToCart({
    id: product.id,
    title: product.title,
    price: product.price,
    thumbnail: product.image
  }, 1)

  notifyAddToCart(product.title, 1)
}

import imgPouletEntier from '../assets/Poulet frais entier.png'
import imgBlancPoulet from '../assets/Blanc de poulet.png'
import imgCuissesPoulet from '../assets/Cuisses de poulet.png'
import imgAilesPoulet from '../assets/Ailes de poulet.png'
import imgGesiersPoulet from '../assets/Gésiers de poulet.png'
import imgPouletFume from '../assets/Poulet fumé-1.png'
import imgBlancFume from '../assets/Blanc de poulet fumé.png'
import imgCuissesFumees from '../assets/Cuisses de poulet fumées.png'
import imgCrispy from '../assets/Frame 1.png'
import imgJambon from '../assets/Jambon de volaille.png'
import imgJambonHerbes from '../assets/Jambon aux fines herbes.png'
import imgMerguez from '../assets/Saucisses de volaille-3.png'
import imgSaucissesCuites from '../assets/Saucisses cuites (410 g).png'
import imgSaucissons from '../assets/Saucissons (300 g).png'
import imgPateFoie from '../assets/Pâté de foie (200 g).png'

const { updateMetaTags } = useSEO()
const cartStore = useCartStore()

const activeCategory = ref('all')
const searchQuery = ref('')

const categories = [
  { id: 'all', name: 'TOUS LES PRODUITS' },
  { id: 'frais', name: 'FRAIS & DÉCOUPES' },
  { id: 'fume', name: 'GAMMES FUMÉES' },
  { id: 'charcuterie', name: 'CHARCUTERIE & SAUCISSES' },
  { id: 'snacking', name: 'SNACKING & PANÉS' },
]

const products = [
  {
    id: 1,
    title: 'Poulet entier',
    category: 'frais',
    categoryLabel: 'FRAIS & DÉCOUPES',
    badge: '100% FRAIS',
    badgeType: 'frais',
    tip: 'Idéal pour rôtis & sauce',
    price: 2600,
    priceLabel: '2 600 FCFA / kg',
    image: imgPouletEntier
  },
  {
    id: 2,
    title: 'Blanc de poulet',
    category: 'frais',
    categoryLabel: 'FRAIS & DÉCOUPES',
    badge: '100% FRAIS',
    badgeType: 'frais',
    tip: 'Parfait au beurre & fines herbes',
    price: 6000,
    priceLabel: '6 000 FCFA / kg',
    image: imgBlancPoulet
  },
  {
    id: 3,
    title: 'Cuisses de poulet',
    category: 'frais',
    categoryLabel: 'FRAIS & DÉCOUPES',
    badge: '100% FRAIS',
    badgeType: 'frais',
    tip: 'Délicieux en friture & barbecue',
    price: 2200,
    priceLabel: '2 200 FCFA / kg',
    image: imgCuissesPoulet
  },
  {
    id: 4,
    title: 'Ailes de poulet',
    category: 'frais',
    categoryLabel: 'FRAIS & DÉCOUPES',
    badge: '100% FRAIS',
    badgeType: 'frais',
    tip: 'Parfait pour friture',
    price: 2000,
    priceLabel: '2 000 FCFA / kg',
    image: imgAilesPoulet
  },
  {
    id: 5,
    title: 'Gésiers de poulet',
    category: 'frais',
    categoryLabel: 'FRAIS & DÉCOUPES',
    badge: '100% FRAIS',
    badgeType: 'frais',
    tip: 'Idéal pour sautés & piments',
    price: 1900,
    priceLabel: '1 900 FCFA / kg',
    image: imgGesiersPoulet
  },
  {
    id: 6,
    title: 'Poulet fumé',
    category: 'fume',
    categoryLabel: 'GAMMES FUMÉES',
    badge: '100% FUMÉ',
    badgeType: 'fume',
    tip: 'Incomparable dans les sauces graines',
    price: 3400,
    priceLabel: '3 400 FCFA / kg',
    image: imgPouletFume
  },
  {
    id: 7,
    title: 'Blanc de poulet fumé',
    category: 'fume',
    categoryLabel: 'GAMMES FUMÉES',
    badge: '100% FUMÉ',
    badgeType: 'fume',
    tip: 'Parfait pour les lardons de poulet',
    price: 6000,
    priceLabel: '6 000 FCFA / kg',
    image: imgBlancFume
  },
  {
    id: 8,
    title: 'Cuisses de poulet fumées',
    category: 'fume',
    categoryLabel: 'GAMMES FUMÉES',
    badge: '100% FUMÉ',
    badgeType: 'fume',
    tip: 'Savouré dans toutes vos sauces',
    price: 3000,
    priceLabel: '3 000 FCFA / kg',
    image: imgCuissesFumees
  },
  {
    id: 9,
    title: 'CRISPY (poulet pané)',
    category: 'snacking',
    categoryLabel: 'SNACKING & PANÉS',
    badge: '100% CUIT',
    badgeType: 'cuit',
    tip: 'Bon à grignoter & apéro',
    price: 2000,
    priceLabel: '2 000 FCFA / 500 g',
    image: imgCrispy
  },
  {
    id: 10,
    title: 'Jambon de volaille',
    category: 'charcuterie',
    categoryLabel: 'CHARCUTERIE & SAUCISSES',
    badge: '100% FRAIS',
    badgeType: 'frais',
    tip: 'Idéal sandwichs & petit-déjeuner',
    price: 6000,
    priceLabel: '6 000 FCFA / kg',
    image: imgJambon
  },
  {
    id: 11,
    title: 'Jambon aux fines herbes',
    category: 'charcuterie',
    categoryLabel: 'CHARCUTERIE & SAUCISSES',
    badge: '100% FRAIS',
    badgeType: 'frais',
    tip: 'Recette gourmande',
    price: 6500,
    priceLabel: '6 500 FCFA / kg',
    image: imgJambonHerbes
  },
  {
    id: 12,
    title: 'Merguez & Chipolata',
    category: 'charcuterie',
    categoryLabel: 'CHARCUTERIE & SAUCISSES',
    badge: '100% FRAIS',
    badgeType: 'frais',
    tip: 'Super pour BBQ & grillades',
    price: 6000,
    priceLabel: '6 000 FCFA / kg',
    image: imgMerguez
  },
  {
    id: 13,
    title: 'Saucisses cuites (410 g)',
    category: 'charcuterie',
    categoryLabel: 'CHARCUTERIE & SAUCISSES',
    badge: '100% CUIT',
    badgeType: 'cuit',
    tip: 'Délicieuses pour Hot-Dogs',
    price: 1000,
    priceLabel: '1 000 FCFA / paquet',
    image: imgSaucissesCuites
  },
  {
    id: 14,
    title: 'Saucissons (300 g)',
    category: 'charcuterie',
    categoryLabel: 'CHARCUTERIE & SAUCISSES',
    badge: '100% FRAIS',
    badgeType: 'frais',
    tip: 'Super pour les sandwichs',
    price: 2000,
    priceLabel: '2 000 FCFA / pièce',
    image: imgSaucissons
  },
  {
    id: 15,
    title: 'Pâté de foie (200 g)',
    category: 'charcuterie',
    categoryLabel: 'CHARCUTERIE & SAUCISSES',
    badge: '100% FRAIS',
    badgeType: 'frais',
    tip: 'Tartines & goûter des enfants',
    price: 1000,
    priceLabel: '1 000 FCFA / pot',
    image: imgPateFoie
  }
]

const filteredProducts = computed(() => {
  let list = products
  if (activeCategory.value !== 'all') {
    list = list.filter(p => p.category === activeCategory.value)
  }
  if (searchQuery.value.trim() !== '') {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(p => p.title.toLowerCase().includes(q) || p.categoryLabel.toLowerCase().includes(q))
  }
  return list
})



onMounted(() => {
  updateMetaTags({
    title: 'Catalogue Produits — Daba',
    description: 'Découvrez toute notre gamme de volaille togolaise fraîche, fumée, charcuterie et panés.',
    keywords: 'poulet frais, poulet fumé, jambon volaille, merguez, saucisses, Daba, Togo'
  })
})
</script>
