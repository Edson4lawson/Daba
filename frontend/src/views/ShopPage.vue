<template>
  <div class="min-h-screen bg-daba-cream">
    <!-- Mini Header -->
    <header class="sticky top-0 z-50 bg-daba-cream/80 backdrop-blur-xl border-b border-daba-cream-alt/50 py-3">
      <div class="container mx-auto px-4 flex items-center justify-between">
        <router-link to="/" class="flex items-center gap-2">
          <img src="../assets/daba-icone.png" alt="Daba" class="w-12 h-12" />
        </router-link>
        <div class="flex items-center gap-4">
          <router-link to="/" class="text-sm font-bold text-daba-slate hover:text-daba-orange transition-colors">Accueil</router-link>
          <span class="text-sm font-bold text-daba-orange">Boutique</span>
        </div>
        <button @click="cartOpen = true" class="relative p-2 text-daba-slate hover:text-daba-orange">
          <Icon icon="solar:cart-large-minimalistic-bold" class="w-6 h-6" />
          <span v-if="cartStore.totalItems > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-daba-orange text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ cartStore.totalItems }}</span>
        </button>
      </div>
    </header>

    <main class="container mx-auto px-4 py-8">
      <!-- Page Title -->
      <div class="text-center mb-12">
        <h1 class="text-4xl md:text-6xl font-black text-daba-navy mb-3">Notre <span class="text-daba-orange">Boutique</span></h1>
        <p class="text-daba-slate font-medium">{{ filteredProducts.length }} produits disponibles</p>
      </div>

      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Filters -->
        <aside class="w-full lg:w-72 shrink-0">
          <div class="bg-white rounded-3xl p-6 border border-daba-cream-alt shadow-sm sticky top-24">
            <h3 class="text-sm font-black text-daba-navy uppercase tracking-wider mb-6">Filtres</h3>
            
            <!-- Search -->
            <div class="mb-6">
              <label class="text-xs font-bold text-daba-slate uppercase tracking-wider block mb-2">Recherche</label>
              <div class="relative">
                <Icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-daba-slate" />
                <input v-model="searchQuery" placeholder="Rechercher..." 
                  class="w-full pl-10 pr-4 py-2.5 bg-daba-cream-alt border border-daba-cream-alt rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-daba-cream-alt" />
              </div>
            </div>

            <!-- Categories -->
            <div class="mb-6">
              <label class="text-xs font-bold text-daba-slate uppercase tracking-wider block mb-3">Catégorie</label>
              <div class="space-y-1 max-h-48 overflow-y-auto">
                <button @click="selectedCategory = ''" 
                  class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-all"
                  :class="selectedCategory === '' ? 'bg-daba-cream-alt text-daba-navy' : 'text-daba-slate hover:bg-daba-cream-alt'">
                  Toutes ({{ productStore.products.length }})
                </button>
                <button v-for="cat in categories" :key="cat" @click="selectedCategory = cat"
                  class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-all"
                  :class="selectedCategory === cat ? 'bg-daba-cream-alt text-daba-navy' : 'text-daba-slate hover:bg-daba-cream-alt'">
                  {{ cat }}
                </button>
              </div>
            </div>

            <!-- Price Range -->
            <div class="mb-6">
              <label class="text-xs font-bold text-daba-slate uppercase tracking-wider block mb-3">Prix max : {{ priceRange.toLocaleString('fr-FR') }} FCFA</label>
              <input type="range" v-model.number="priceRange" :min="0" :max="maxPrice" step="500"
                class="w-full accent-daba-orange" />
            </div>

            <!-- Sort -->
            <div>
              <label class="text-xs font-bold text-daba-slate uppercase tracking-wider block mb-2">Trier par</label>
              <select v-model="sortBy" class="w-full px-3 py-2.5 bg-daba-cream-alt border border-daba-cream-alt rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-daba-cream-alt">
                <option value="default">Par défaut</option>
                <option value="price-asc">Prix croissant</option>
                <option value="price-desc">Prix décroissant</option>
                <option value="name">Nom A-Z</option>
                <option value="rating">Meilleures notes</option>
              </select>
            </div>

            <!-- Reset -->
            <button @click="resetFilters" class="w-full mt-6 py-2.5 border border-daba-cream-alt text-daba-orange font-bold text-sm rounded-xl hover:bg-daba-cream-alt transition-all">
              Réinitialiser
            </button>
          </div>
        </aside>

        <!-- Products Grid -->
        <div class="flex-1">
          <div v-if="loading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-4 border-daba-cream-alt border-t-daba-orange"></div>
          </div>

          <div v-else-if="filteredProducts.length === 0" class="text-center py-20">
            <Icon icon="solar:box-minimalistic-broken" class="w-16 h-16 text-daba-slate-dark mx-auto mb-4" />
            <h3 class="text-lg font-bold text-daba-slate mb-2">Aucun produit trouvé</h3>
            <p class="text-daba-slate text-sm">Essayez avec d'autres filtres</p>
          </div>

          <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
            <router-link 
              v-for="product in filteredProducts" 
              :key="product.id"
              :to="`/produit/${product.slug || product.id}`"
              class="group bg-white rounded-2xl overflow-hidden border border-daba-cream-alt shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1"
            >
              <div class="relative aspect-square overflow-hidden">
                <img :src="product.thumbnail" :alt="product.title" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                <div v-if="product.discount" class="absolute top-3 right-3 bg-daba-orange text-white text-[10px] font-black px-2 py-1 rounded-full">-{{ product.discount }}%</div>
                <!-- Quick Actions -->
                <div class="absolute bottom-3 right-3 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity" @click.prevent>
                  <button @click="addToWishlist(product)" class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-lg hover:bg-daba-cream-alt">
                    <Icon :icon="wishlistStore.isInWishlist(product.id) ? 'solar:heart-bold' : 'solar:heart-linear'" class="w-5 h-5" :class="wishlistStore.isInWishlist(product.id) ? 'text-daba-orange' : 'text-daba-slate'" />
                  </button>
                  <button @click="addToCart(product)" class="w-10 h-10 bg-daba-orange rounded-full flex items-center justify-center shadow-lg hover:bg-daba-orange-dark">
                    <Icon icon="solar:cart-plus-bold" class="w-5 h-5 text-white" />
                  </button>
                </div>
              </div>
              <div class="p-5">
                <p class="text-[10px] text-daba-orange font-black uppercase tracking-wider mb-1">{{ product.category }}</p>
                <h3 class="text-base font-bold text-daba-navy truncate mb-2 group-hover:text-daba-orange transition-colors">{{ product.title }}</h3>
                <div class="flex items-center justify-between">
                  <span class="text-lg font-black text-daba-navy">{{ product.price?.toLocaleString('fr-FR') }} <span class="text-xs text-daba-slate">FCFA</span></span>
                  <div class="flex items-center gap-1">
                    <Icon icon="solar:star-bold" class="w-3.5 h-3.5 text-yellow-400" />
                    <span class="text-xs font-bold text-daba-slate">{{ product.rating?.toFixed(1) || '4.5' }}</span>
                  </div>
                </div>
              </div>
            </router-link>
          </div>
        </div>
      </div>
    </main>

    <Footer />
    <CartDrawer :is-open="cartOpen" @close="cartOpen = false" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { Icon } from '@iconify/vue';
import { useProductStore } from '@/stores/products';
import { useCartStore } from '@/stores/cart';
import { useWishlistStore } from '@/stores/wishlist';
import { useSEO } from '@/composables/useSEO';
import Footer from '@/components/Footer.vue';
import CartDrawer from '@/components/CartDrawer.vue';
import { notifyAddToCart, notifyWishlist } from '@/utils/notifications';

const addToCart = (product) => {
  cartStore.addToCart(product);
  notifyAddToCart(product.title, 1);
};

const addToWishlist = (product) => {
  const added = wishlistStore.toggleWishlist(product);
  notifyWishlist(product.title, added);
};

const route = useRoute();
const productStore = useProductStore();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();
const { updateMetaTags } = useSEO();

const cartOpen = ref(false);
const searchQuery = ref('');
const selectedCategory = ref('');
const sortBy = ref('default');
const priceRange = ref(500000);

const loading = computed(() => productStore.loading);

const maxPrice = computed(() => {
  const prices = productStore.products.map(p => p.price);
  return prices.length > 0 ? Math.max(...prices) : 500000;
});

const categories = computed(() => {
  const cats = [...new Set(productStore.products.map(p => p.category).filter(Boolean))];
  return cats.sort();
});

const filteredProducts = computed(() => {
  let result = [...productStore.products];
  
  // Search
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(p => p.title?.toLowerCase().includes(q) || p.category?.toLowerCase().includes(q));
  }
  
  // Category
  if (selectedCategory.value) {
    result = result.filter(p => p.category === selectedCategory.value);
  }
  
  // Price
  result = result.filter(p => p.price <= priceRange.value);
  
  // Sort
  switch (sortBy.value) {
    case 'price-asc': result.sort((a, b) => a.price - b.price); break;
    case 'price-desc': result.sort((a, b) => b.price - a.price); break;
    case 'name': result.sort((a, b) => (a.title || '').localeCompare(b.title || '')); break;
    case 'rating': result.sort((a, b) => (b.rating || 0) - (a.rating || 0)); break;
  }
  
  return result;
});

const resetFilters = () => {
  searchQuery.value = '';
  selectedCategory.value = '';
  sortBy.value = 'default';
  priceRange.value = maxPrice.value;
};



onMounted(async () => {
  await productStore.fetchProducts();
  
  // Handle query param search
  if (route.query.q) {
    searchQuery.value = route.query.q;
  }
  if (route.query.category) {
    selectedCategory.value = route.query.category;
  }
  
  priceRange.value = maxPrice.value;
  
  updateMetaTags({
    title: 'Boutique',
    description: 'Découvrez tous nos produits — Beauté, bien-être, accessoires et plus encore. Livraison rapide au Bénin.',
    keywords: 'boutique, beauté, bien-être, accessoires, Daba, Bénin'
  });
});
</script>
