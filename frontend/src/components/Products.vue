<template>
  <section class="py-20 bg-daba-cream" id="products" data-aos="fade-up">
    <div class="container mx-auto px-4 md:px-6">
      <!-- Header Section -->
      <div class="flex flex-col lg:flex-row lg:items-end justify-between mb-16 gap-8">
        <div class="max-w-2xl">
          <h2 class="text-4xl md:text-6xl font-black text-daba-navy mb-6 leading-tight uppercase italic">
            Nos <span class="text-daba-orange">Essentiels</span>
          </h2>
          <p class="text-lg text-daba-slate font-medium leading-relaxed">
            Une sélection unique de produits de beauté, bien-être et accessoires. Tout ce dont vous avez besoin pour rayonner, livré directement chez vous.
          </p>
        </div>

        <!-- Filter Controls -->
        <div class="flex flex-wrap items-center gap-4">
          <div class="relative group">
            <button @click="toggleSortDropdown" 
              class="flex items-center gap-3 px-6 py-4 bg-daba-cream-alt border border-daba-cream-alt rounded-3xl font-black text-daba-navy uppercase tracking-widest text-xs hover:bg-daba-cream hover:shadow-xl transition-all">
              <Icon icon="solar:sort-from-top-to-bottom-line-duotone" class="w-5 h-5 text-daba-orange" />
              Trier par: {{ selectedSort.label }}
            </button>
            <Transition name="fade">
              <div v-if="showSortDropdown" class="absolute right-0 mt-4 w-56 bg-daba-cream rounded-[2rem] shadow-2xl shadow-daba-cream-alt/50 border border-daba-cream-alt py-3 z-50">
                <div v-for="option in sortOptions" :key="option.value" @click="sortProducts(option.value)"
                  class="px-6 py-3 text-xs font-black uppercase tracking-widest hover:bg-daba-cream-alt cursor-pointer transition-colors"
                  :class="{ 'text-daba-orange': selectedSort.value === option.value, 'text-daba-slate': selectedSort.value !== option.value }">
                  {{ option.label }}
                </div>
              </div>
            </Transition>
          </div>
        </div>
      </div>

      <!-- Categories Tabs -->
      <div class="flex overflow-x-auto pb-8 mb-12 scrollbar-hide gap-3">
        <button v-for="cat in categories" :key="cat" @click="filterByCategory(cat)"
          class="shrink-0 px-8 py-3 rounded-2xl font-black uppercase tracking-widest text-xs transition-all border outline-none"
          :class="selectedCategory === cat ? 'bg-daba-navy border-daba-cream-alt text-white shadow-xl shadow-daba-navy/20' : 'bg-daba-cream border-daba-cream-alt text-daba-slate hover:border-daba-orange hover:text-daba-orange'">
          {{ cat === 'all' ? 'Tous les produits' : cat }}
        </button>
      </div>

      <!-- Products Grid -->
      <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <div v-for="n in 8" :key="n" class="animate-pulse">
          <div class="bg-daba-cream-alt aspect-[4/5] rounded-[2.5rem] mb-6"></div>
          <div class="h-4 bg-daba-cream-alt rounded w-2/3 mb-4"></div>
          <div class="h-4 bg-daba-cream-alt rounded w-1/2"></div>
        </div>
      </div>

      <div v-else-if="filteredProducts.length === 0" class="py-20 text-center">
        <div class="w-24 h-24 bg-daba-cream-alt rounded-full flex items-center justify-center mx-auto mb-6">
          <Icon icon="solar:box-minimalistic-linear" class="w-12 h-12 text-daba-slate-dark" />
        </div>
        <h3 class="text-xl font-bold text-daba-navy mb-2">Aucun produit trouvé</h3>
        <p class="text-daba-slate max-w-md mx-auto">Essayez de changer de catégorie ou de filtre. Si le problème persiste, vérifiez votre connexion ou rechargez la page.</p>
        <div class="mt-8 flex items-center justify-center gap-4">
          <button @click="selectedCategory = 'all'" class="px-6 py-3 bg-daba-navy text-white rounded-2xl font-black uppercase tracking-widest text-[10px] hover:bg-daba-orange transition-all">Voir tout</button>
          <button @click="window.location.reload()" class="px-6 py-3 bg-daba-cream border border-daba-cream-alt text-daba-navy rounded-2xl font-black uppercase tracking-widest text-[10px] hover:bg-daba-cream-alt transition-all flex items-center gap-2">
            <Icon icon="solar:restart-linear" class="w-4 h-4" />
            Recharger
          </button>
        </div>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-12">
        <div v-for="(product, index) in filteredProducts.slice(0, visibleCount)" :key="product.id" data-aos="fade-up" :data-aos-delay="index % 4 * 100" class="group">
          <!-- Card Image & Actions -->
          <div class="relative bg-daba-cream-alt rounded-3xl p-3 aspect-[4/5] overflow-hidden mb-6 transition-all duration-500 group-hover:bg-daba-cream group-hover:shadow-2xl group-hover:shadow-daba-cream-alt/50 border border-transparent group-hover:border-daba-cream-alt">
            <div @click="goToProduct(product)" class="w-full h-full rounded-2xl overflow-hidden cursor-pointer">
              <OptimizedImage 
                :src="product.thumbnail" 
                :alt="product.title" 
                imageClass="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" 
              />
            </div>

            <!-- Quick Action Overlay -->
            <div class="absolute inset-x-6 bottom-6 flex gap-2 translate-y-10 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-500">
              <button @click="addToCart(product)" class="flex-1 bg-daba-navy text-white font-black uppercase tracking-widest text-[10px] py-4 rounded-2xl hover:bg-daba-orange transition-colors flex items-center justify-center gap-2">
                <Icon icon="solar:cart-large-minimalistic-bold" class="w-4 h-4" />
                Ajouter
              </button>
            </div>
          </div>

          <!-- Content -->
          <div @click="goToProduct(product)" class="px-2 cursor-pointer">
            <div class="flex items-center justify-between mb-2">
              <p class="text-[10px] font-black uppercase tracking-widest text-daba-orange opacity-60">{{ product.category }}</p>
              <div class="flex items-center gap-1 text-[10px] font-black text-daba-slate">
                <Icon icon="solar:star-bold" class="text-daba-navy" />
                {{ product.rating || '4.5' }}
              </div>
            </div>
            <h3 class="text-lg font-black text-daba-navy leading-tight group-hover:text-daba-orange transition-colors mb-2 line-clamp-1">{{ product.title }}</h3>
            <p class="text-xl font-black text-daba-navy">{{ product.price?.toLocaleString('fr-FR') }} <span class="text-xs text-daba-slate ml-1 uppercase">fcfa</span></p>
          </div>
        </div>
      </div>

      <!-- Load More Button -->
      <div v-if="visibleCount < filteredProducts.length" class="mt-20 text-center">
        <button @click="visibleCount += 8" 
          class="px-12 py-5 bg-daba-cream-alt border border-daba-cream-alt text-daba-navy rounded-3xl font-black uppercase tracking-widest text-xs hover:bg-daba-navy hover:text-white transition-all active:scale-95 shadow-lg shadow-daba-cream-alt/50">
          Charger plus de produits
        </button>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { Icon } from '@iconify/vue';
import OptimizedImage from './OptimizedImage.vue';
import { useProductStore } from '../stores/products';
import { useCartStore } from '../stores/cart';
import { notifyAddToCart } from '@/utils/notifications';

const addToCart = async (product) => {
  await cartStore.addToCart(product);
  notifyAddToCart(product.title, 1);
};

const router = useRouter();
const productStore = useProductStore();
const cartStore = useCartStore();

const loading = computed(() => productStore.loading);
const products = computed(() => productStore.products);
const selectedCategory = ref('all');
const visibleCount = ref(12);

const sortOptions = [
  { value: 'newest', label: 'Plus récents' },
  { value: 'price-asc', label: 'Prix croissant' },
  { value: 'price-desc', label: 'Prix décroissant' },
  { value: 'alphabetical', label: 'A - Z' },
];

const selectedSort = ref(sortOptions[0]);
const showSortDropdown = ref(false);

const categories = computed(() => {
  const cats = new Set(products.value.map(p => p.category));
  return ['all', ...Array.from(cats)].filter(c => c);
});

const filteredProducts = computed(() => {
  // On exclut les produits de la boutique (Nouvel Arrivage) et ceux des tendances
  // pour éviter la redondance sur la page d'accueil
  let result = products.value.filter(p => p.source !== 'store' && p.source !== 'tendance');
  
  if (selectedCategory.value !== 'all') {
    result = result.filter(p => p.category === selectedCategory.value);
  }
  
  switch (selectedSort.value.value) {
    case 'price-asc': result.sort((a, b) => a.price - b.price); break;
    case 'price-desc': result.sort((a, b) => b.price - a.price); break;
    case 'alphabetical': result.sort((a, b) => a.title.localeCompare(b.title)); break;
    default: result.sort((a, b) => b.id - a.id);
  }
  
  return result;
});

const toggleSortDropdown = () => showSortDropdown.value = !showSortDropdown.value;

const sortProducts = (option) => {
  selectedSort.value = sortOptions.find(o => o.value === option);
  showSortDropdown.value = false;
};

const filterByCategory = (cat) => {
  selectedCategory.value = cat;
  visibleCount.value = 12;
};

const goToProduct = (product) => {
  router.push(`/produit/${product.slug || product.id}`);
};



onMounted(async () => {
  if (products.value.length === 0) {
    await productStore.fetchProducts();
  }
});
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
