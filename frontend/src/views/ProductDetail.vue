<template>
  <div class="min-h-screen bg-daba-cream">
    <!-- Header simple -->
    <header class="sticky top-0 z-50 bg-daba-cream/80 backdrop-blur-xl border-b border-daba-cream-alt/50 py-3">
      <div class="container mx-auto px-4 flex items-center justify-between">
        <router-link to="/" class="flex items-center gap-2">
          <img src="../assets/daba-icone.png" alt="Daba" class="w-12 h-12" />
        </router-link>
        <nav class="hidden md:flex items-center gap-6">
          <router-link to="/" class="text-sm font-bold text-daba-slate hover:text-daba-orange transition-colors">Accueil</router-link>
          <router-link to="/boutique" class="text-sm font-bold text-daba-orange">Boutique</router-link>
        </nav>
        <div class="flex items-center gap-3">
          <button @click="cartOpen = true" class="relative p-2 text-daba-slate hover:text-daba-orange">
            <Icon icon="solar:cart-large-minimalistic-bold" class="w-6 h-6" />
            <span v-if="cartStore.totalItems > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-daba-orange text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ cartStore.totalItems }}</span>
          </button>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-4 py-8">
      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-32">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-daba-cream-alt border-t-daba-orange mb-4"></div>
        <p class="text-daba-slate font-medium">Chargement du produit...</p>
      </div>

      <!-- Product Not Found -->
      <div v-else-if="!product" class="text-center py-32">
        <Icon icon="solar:box-minimalistic-broken" class="w-20 h-20 text-daba-slate-dark mx-auto mb-4" />
        <h2 class="text-2xl font-bold text-daba-navy mb-2">Produit introuvable</h2>
        <p class="text-daba-slate mb-6">Ce produit n'existe pas ou a été retiré.</p>
        <router-link to="/boutique" class="px-6 py-3 bg-daba-orange text-white rounded-full font-bold hover:bg-daba-orange-dark transition-all">
          Voir la boutique
        </router-link>
      </div>

      <!-- Product Content -->
      <div v-else>
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm text-daba-slate mb-8">
          <router-link to="/" class="hover:text-daba-orange transition-colors">Accueil</router-link>
          <span>/</span>
          <router-link to="/boutique" class="hover:text-daba-orange transition-colors">Boutique</router-link>
          <span>/</span>
          <span class="text-daba-navy font-medium truncate max-w-[200px]">{{ product.title }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">
          <!-- Image Gallery -->
          <div class="space-y-4">
            <div class="relative aspect-square bg-daba-cream rounded-3xl overflow-hidden border border-daba-cream-alt shadow-xl group">
              <img 
                :src="currentImage" 
                :alt="product.title" 
                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
              />
              <div v-if="product.discount" class="absolute top-4 right-4 bg-daba-orange text-white text-xs font-black px-3 py-1.5 rounded-full shadow-lg">
                -{{ product.discount }}%
              </div>
              <!-- Wishlist button -->
              <button @click="toggleWishlist" class="absolute top-4 left-4 w-10 h-10 bg-daba-cream/90 backdrop-blur rounded-full flex items-center justify-center shadow-lg hover:scale-110 transition-all">
                <Icon :icon="isInWishlist ? 'solar:heart-bold' : 'solar:heart-linear'" class="w-5 h-5" :class="isInWishlist ? 'text-daba-orange' : 'text-daba-slate'" />
              </button>
            </div>
            <!-- Thumbnail strip -->
            <div v-if="galleryImages.length > 1" class="flex gap-3 overflow-x-auto pb-2">
              <button 
                v-for="(img, idx) in galleryImages" 
                :key="idx"
                @click="currentImageIndex = idx"
                class="w-20 h-20 rounded-xl overflow-hidden border-2 flex-shrink-0 transition-all"
                :class="currentImageIndex === idx ? 'border-daba-orange shadow-lg' : 'border-daba-cream-alt opacity-60 hover:opacity-100'"
              >
                <img :src="img" :alt="`Vue ${idx + 1}`" class="w-full h-full object-cover" />
              </button>
            </div>
          </div>

          <!-- Product Info -->
          <div class="flex flex-col">
            <span class="text-xs font-black text-daba-orange uppercase tracking-[0.2em] mb-3">{{ product.category }}</span>
            <h1 class="text-3xl md:text-4xl font-black text-daba-navy mb-4 leading-tight">{{ product.title }}</h1>
            
            <!-- Rating -->
            <div class="flex items-center gap-2 mb-6">
              <div class="flex items-center gap-0.5">
                <Icon v-for="i in 5" :key="i" 
                  :icon="i <= Math.round(product.rating) ? 'solar:star-bold' : 'solar:star-linear'" 
                  class="w-5 h-5" 
                  :class="i <= Math.round(product.rating) ? 'text-yellow-400' : 'text-daba-slate-dark'" />
              </div>
              <span class="text-sm text-daba-slate font-medium">({{ product.rating?.toFixed(1) || '4.5' }})</span>
            </div>

            <!-- Price -->
            <div class="flex items-baseline gap-3 mb-8">
              <span class="text-4xl font-black text-daba-navy">{{ product.price?.toLocaleString('fr-FR') }}</span>
              <span class="text-lg text-daba-slate font-bold">FCFA</span>
              <span v-if="product.discount" class="text-lg text-daba-slate line-through ml-2">
                {{ Math.round(product.price * (1 + product.discount / 100)).toLocaleString('fr-FR') }} FCFA
              </span>
            </div>

            <!-- Description -->
            <div class="mb-8">
              <h3 class="text-sm font-black text-daba-navy uppercase tracking-wider mb-3">Description</h3>
              <p class="text-daba-slate leading-relaxed">{{ product.description || 'Un produit de qualité premium, sélectionné avec soin pour votre quotidien. Découvrez l\'excellence Daba.' }}</p>
            </div>

            <!-- Stock Status -->
            <div class="flex items-center gap-2 mb-8">
              <div class="w-2 h-2 rounded-full" :class="product.stock > 0 ? 'bg-daba-green' : 'bg-red-500'"></div>
              <span class="text-sm font-bold" :class="product.stock > 0 ? 'text-daba-green' : 'text-red-600'">
                {{ product.stock > 0 ? `En stock (${product.stock} disponibles)` : 'Rupture de stock' }}
              </span>
            </div>

            <!-- Quantity & Add to Cart -->
            <div class="flex items-center gap-4 mb-6">
              <div class="flex items-center border border-daba-cream-alt rounded-xl overflow-hidden">
                <button @click="quantity = Math.max(1, quantity - 1)" class="px-4 py-3 text-daba-slate hover:bg-daba-cream-alt transition-colors">
                  <Icon icon="solar:minus-circle-linear" class="w-5 h-5" />
                </button>
                <span class="w-12 text-center font-bold text-daba-navy">{{ quantity }}</span>
                <button @click="quantity++" class="px-4 py-3 text-daba-slate hover:bg-daba-cream-alt transition-colors">
                  <Icon icon="solar:add-circle-linear" class="w-5 h-5" />
                </button>
              </div>
              <button 
                @click="addToCart" 
                :disabled="product.stock <= 0"
                class="flex-1 py-4 bg-daba-orange text-white rounded-2xl font-bold text-lg flex items-center justify-center gap-3 hover:bg-daba-orange-dark shadow-xl shadow-daba-orange/20 hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <Icon icon="solar:cart-large-minimalistic-bold" class="w-6 h-6" />
                Ajouter au panier
              </button>
            </div>

            <!-- Quick info badges -->
            <div class="grid grid-cols-3 gap-3 mt-auto">
              <div class="flex flex-col items-center p-3 bg-daba-cream-alt rounded-2xl">
                <Icon icon="solar:delivery-bold-duotone" class="w-6 h-6 text-daba-orange mb-1" />
                <span class="text-[10px] font-bold text-daba-slate text-center">Livraison rapide</span>
              </div>
              <div class="flex flex-col items-center p-3 bg-daba-cream-alt rounded-2xl">
                <Icon icon="solar:shield-check-bold-duotone" class="w-6 h-6 text-daba-orange mb-1" />
                <span class="text-[10px] font-bold text-daba-slate text-center">Paiement sécurisé</span>
              </div>
              <div class="flex flex-col items-center p-3 bg-daba-cream-alt rounded-2xl">
                <Icon icon="solar:refresh-bold-duotone" class="w-6 h-6 text-daba-orange mb-1" />
                <span class="text-[10px] font-bold text-daba-slate text-center">Retour 7 jours</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Similar Products -->
        <section v-if="similarProducts.length > 0" class="mb-16">
          <h2 class="text-2xl font-black text-daba-navy mb-8">Vous aimerez aussi</h2>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <router-link 
              v-for="sp in similarProducts" 
              :key="sp.id"
              :to="`/produit/${sp.slug || sp.id}`"
              class="group bg-daba-cream rounded-2xl overflow-hidden border border-daba-cream-alt shadow-sm hover:shadow-xl transition-all hover:-translate-y-1"
            >
              <div class="aspect-square overflow-hidden">
                <img :src="sp.thumbnail" :alt="sp.title" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
              </div>
              <div class="p-4">
                <p class="text-xs text-daba-orange font-bold uppercase tracking-wider mb-1">{{ sp.category }}</p>
                <h3 class="text-sm font-bold text-daba-navy truncate mb-2">{{ sp.title }}</h3>
                <p class="text-lg font-black text-daba-navy">{{ sp.price?.toLocaleString('fr-FR') }} <span class="text-xs text-daba-slate">FCFA</span></p>
              </div>
            </router-link>
          </div>
        </section>
      </div>
    </main>

    <!-- Simple Footer -->
    <Footer />
    <CartDrawer :is-open="cartOpen" @close="cartOpen = false" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { Icon } from '@iconify/vue';
import { useProductStore } from '@/stores/products';
import { useCartStore } from '@/stores/cart';
import { useWishlistStore } from '@/stores/wishlist';
import { useSEO } from '@/composables/useSEO';
import Footer from '@/components/Footer.vue';
import CartDrawer from '@/components/CartDrawer.vue';
import Swal from 'sweetalert2';

const route = useRoute();
const productStore = useProductStore();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();
const { updateMetaTags } = useSEO();

const loading = ref(true);
const product = ref(null);
const quantity = ref(1);
const currentImageIndex = ref(0);
const cartOpen = ref(false);

const currentImage = computed(() => {
  if (!product.value) return '';
  return galleryImages.value[currentImageIndex.value] || product.value.thumbnail;
});

const galleryImages = computed(() => {
  if (!product.value) return [];
  const images = [product.value.thumbnail];
  if (product.value.gallery) {
    images.push(...product.value.gallery);
  }
  return images.filter(Boolean);
});

const isInWishlist = computed(() => {
  return product.value ? wishlistStore.isInWishlist(product.value.id) : false;
});

const similarProducts = computed(() => {
  if (!product.value) return [];
  return productStore.products
    .filter(p => p.category === product.value.category && p.id !== product.value.id)
    .slice(0, 4);
});

const loadProduct = async () => {
  loading.value = true;
  const slug = route.params.slug;

  // Ensure products are loaded
  if (productStore.products.length === 0) {
    await productStore.fetchProducts();
  }

  // Find by slug or ID
  product.value = productStore.products.find(
    p => p.slug === slug || p.id === parseInt(slug) || String(p.id) === slug
  ) || null;

  if (product.value) {
    updateMetaTags({
      title: product.value.title,
      description: product.value.description || `${product.value.title} — Découvrez ce produit exclusif chez Bloom by Chloé`,
      image: product.value.thumbnail,
      url: window.location.href
    });
  }

  loading.value = false;
};

const addToCart = async () => {
  if (!product.value || product.value.stock <= 0) return;
  
  await cartStore.addToCart(product.value, quantity.value);
  
  Swal.fire({
    icon: 'success',
    title: 'Ajouté au panier !',
    text: `${quantity.value}x ${product.value.title}`,
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true
  });
};

const toggleWishlist = () => {
  if (!product.value) return;
  const added = wishlistStore.toggleWishlist(product.value);
  
  Swal.fire({
    icon: added ? 'success' : 'info',
    title: added ? 'Ajouté aux favoris' : 'Retiré des favoris',
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 1500
  });
};

onMounted(loadProduct);

// Watch route changes for SPA navigation between products
watch(() => route.params.slug, (newSlug) => {
  if (newSlug) {
    quantity.value = 1;
    currentImageIndex.value = 0;
    loadProduct();
  }
});
</script>
