<template>
  <section class="py-16 lg:py-24 bg-daba-cream overflow-hidden">
    <div class="max-w-[1440px] mx-auto px-6 sm:px-8 lg:px-12">
      
      <!-- Section Header -->
      <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6" data-aos="fade-up">
        <div>
          <span class="font-worksans text-xs font-bold uppercase tracking-widest text-daba-slate-light">
            LE CATALOGUE
          </span>
          <h2 class="font-playfair text-3xl sm:text-4xl lg:text-[48px] font-normal text-daba-navy mt-2 leading-tight">
            Nos produits phares
          </h2>
        </div>
        
        <p class="font-karla text-sm text-daba-slate max-w-sm">
          Prix indicatifs à Lomé. Commande minimum livrée à domicile : 10 000 FCFA.
        </p>
      </div>

      <!-- 6 Products Grid with Stagger -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <div 
          v-for="(product, index) in featuredProducts" 
          :key="product.id"
          data-aos="fade-up"
          :data-aos-delay="index * 80"
          class="bg-white rounded-card overflow-hidden border border-daba-border shadow-card-light hover:shadow-card-elevated hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between group"
        >
          <!-- Product Image Container (350x176 ratio) -->
          <div class="relative w-full h-[176px] overflow-hidden bg-daba-cream-alt">
            <img 
              :src="product.image" 
              :alt="product.title" 
              class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500"
            />
            <!-- Badge -->
            <div class="absolute top-3 left-3 bg-daba-green text-white font-worksans text-[11px] font-bold px-2.5 py-1 rounded uppercase tracking-wider">
              {{ product.badge }}
            </div>
          </div>

          <!-- Product Details -->
          <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
            <div>
              <h3 class="font-playfair text-xl font-semibold text-daba-navy group-hover:text-daba-orange transition-colors">
                {{ product.title }}
              </h3>
              <p class="font-karla text-xs text-daba-slate mt-1 line-clamp-2 leading-relaxed">
                {{ product.description }}
              </p>
            </div>

            <!-- Price & Commander Action -->
            <div class="pt-4 border-t border-daba-border-light flex items-center justify-between">
              <div>
                <span class="font-karla text-xs text-daba-slate-light block">Prix</span>
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

      <!-- Catalogue Bottom Link -->
      <div class="mt-12 text-center" data-aos="fade-up">
        <router-link 
          to="/produits"
          class="inline-flex items-center gap-2 font-inter text-xs font-bold uppercase tracking-wider text-daba-orange hover:text-daba-orange-alt transition-colors"
        >
          <span>VOIR TOUT LE CATALOGUE</span>
          <Icon icon="solar:arrow-right-linear" class="w-4 h-4" />
        </router-link>
      </div>

    </div>
  </section>
</template>

<script setup>
import { Icon } from '@iconify/vue'
import { ShoppingBasket } from 'lucide-vue-next'
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

import imgPouletEntier from '../../assets/Poulet frais entier.png'
import imgDecoupes from '../../assets/Découpes de volaille.png'
import imgSaucisses from '../../assets/Saucisses de volaille.png'
import imgJambon from '../../assets/Jambon de volaille.png'
import imgOeufs from '../../assets/Œufs frais de ferme.png'
import imgColis from '../../assets/Colis famille.png'

const cartStore = useCartStore()

const featuredProducts = [
  {
    id: 1,
    title: 'Poulet frais entier',
    badge: '100% FRAIS',
    description: '1,4 - 1,8 kg · vidé, prêt à cuire. Chair ferme et goût authentique.',
    price: 4500,
    priceLabel: 'À partir de 4 500 FCFA',
    image: imgPouletEntier
  },
  {
    id: 2,
    title: 'Découpes de volaille',
    badge: '100% FRAIS',
    description: 'Cuisses, ailes, filets calibrés. L’essentiel pour vos repas quotidiens.',
    price: 3200,
    priceLabel: '3 200 FCFA / kg',
    image: imgDecoupes
  },
  {
    id: 3,
    title: 'Saucisses de volaille',
    badge: '100% FRAIS',
    description: '500 g · recette artisanale préparée avec soin.',
    price: 2800,
    priceLabel: '2 800 FCFA',
    image: imgSaucisses
  },
  {
    id: 4,
    title: 'Jambon de volaille',
    badge: '100% FRAIS',
    description: 'Tranché finement · 300 g. Idéal pour vos petits déjeuners et sandwichs.',
    price: 3500,
    priceLabel: '3 500 FCFA',
    image: imgJambon
  },
  {
    id: 5,
    title: 'Œufs frais de ferme',
    badge: '100% FRAIS',
    description: 'Plateau de 30 · Ramassés chaque matin dans nos fermes à Kpomé.',
    price: 2600,
    priceLabel: '2 600 FCFA',
    image: imgOeufs
  },
  {
    id: 6,
    title: 'Colis famille',
    badge: '100% FRAIS',
    description: '2 poulets + 1 kg de découpes + 1 pack saucisses. Le meilleur rapport qualité/prix.',
    price: 12000,
    priceLabel: '12 000 FCFA',
    image: imgColis
  }
]


</script>
