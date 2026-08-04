<template>
  <PageTransition>
    <div>
      <Header 
        @open-store="openFeaturedProduct" 
        @open-auth="showAuthModal = true"
        @open-payment="openPaymentFromCart"
      />
      <main class="relative bg-daba-cream min-h-screen overflow-hidden">
        <!-- Mesh Gradient Background -->
        <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
          <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] rounded-full bg-daba-cream-alt/50 blur-[120px] animate-pulse-slow"></div>
          <div class="absolute top-[20%] -right-[10%] w-[50%] h-[50%] rounded-full bg-daba-green/40 blur-[100px] animate-pulse-slow" style="animation-delay: 2s;"></div>
          <div class="absolute -bottom-[10%] left-[20%] w-[45%] h-[45%] rounded-full bg-daba-cream-alt/40 blur-[110px] animate-pulse-slow" style="animation-delay: 4s;"></div>
        </div>
        
        <div class="relative z-10">
          <Hero id="hero"/>
          
          <Suspense>
            <template #default>
              <Products id="products" @select-product="openProductDetails"/>
            </template>
            <template #fallback>
              <div class="flex justify-center items-center py-20">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-daba-cream-alt border-t-daba-orange"></div>
              </div>
            </template>
          </Suspense>
          
          <Suspense>
            <template #default>
              <Store id="store"/>
            </template>
            <template #fallback>
              <div class="h-48"></div>
            </template>
          </Suspense>
          
          <Suspense>
            <template #default>
              <Categories id="tendances"/>
            </template>
            <template #fallback>
              <div class="h-48"></div>
            </template>
          </Suspense>
          
          <Contacts id="contact"/>
          <Footer />
        </div>
      </main>

      <ProductDetails 
        :is-open="isProductDetailsOpen" 
        :product="selectedProduct" 
        @close="isProductDetailsOpen = false" 
      />

      <!-- Payment Modal -->
      <PaymentModal 
        :is-open="isPaymentOpen"
        :amount="cartStore.cartTotal"
        @close="isPaymentOpen = false"
        @success="handlePaymentSuccess"
      />

      <!-- Back to Top Button -->
      <Transition name="fade">
        <button 
          v-if="showBackToTop"
          @click="scrollToTop"
          class="fixed bottom-8 right-8 z-50 w-12 h-12 bg-daba-orange text-white rounded-full shadow-xl shadow-daba-orange/20 flex items-center justify-center hover:bg-daba-orange-dark hover:scale-110 active:scale-95 transition-all"
          aria-label="Retour en haut"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
          </svg>
        </button>
      </Transition>
    </div>
  </PageTransition>
</template>

<script setup>
import { onMounted, onUnmounted, ref, defineAsyncComponent } from 'vue';

// Critical components - loaded immediately
import Header from '@/components/Header.vue';
import Hero from '@/components/Hero.vue';
import PageTransition from '@/components/PageTransition.vue';
import Footer from '@/components/Footer.vue';

// Non-critical components - lazy loaded
const Products = defineAsyncComponent(() => import('@/components/Products.vue'));
const Store = defineAsyncComponent(() => import('@/components/Store.vue'));
const Categories = defineAsyncComponent(() => import('@/components/Categories.vue'));
const Contacts = defineAsyncComponent(() => import('@/components/Contacts.vue'));
const ProductDetails = defineAsyncComponent(() => import('@/components/ProductDetails.vue'));
const PaymentModal = defineAsyncComponent(() => import('@/components/PaymentModal.vue'));

import { useProductStore } from '@/stores/products';
import { useCartStore } from '@/stores/cart';

const productStore = useProductStore();
const cartStore = useCartStore();
const isProductDetailsOpen = ref(false);
const selectedProduct = ref({});
const isPaymentOpen = ref(false);
const showBackToTop = ref(false);

const openProductDetails = (product) => {
  selectedProduct.value = product;
  isProductDetailsOpen.value = true;
};

const openFeaturedProduct = async () => {
  if (productStore.products.length === 0) {
    await productStore.fetchProducts();
  }
  const featured = productStore.getFeaturedProduct();
  openProductDetails(featured);
};

const openPaymentFromCart = () => {
  isPaymentOpen.value = true;
};

const handlePaymentSuccess = (data) => {
  cartStore.clearCart();
  isPaymentOpen.value = false;
};

const handleScroll = () => {
  showBackToTop.value = window.scrollY > 600;
};

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
  
  // Inject global animation styles
  const style = document.createElement('style');
  style.id = 'daba-global-animations';
  if (!document.getElementById('daba-global-animations')) {
    style.textContent = `
      @keyframes pulse-slow {
        0%, 100% { opacity: 0.4; transform: scale(1); }
        50% { opacity: 0.7; transform: scale(1.1); }
      }
      .animate-pulse-slow {
        animation: pulse-slow 10s infinite ease-in-out;
      }
    `;
    document.head.appendChild(style);
  }
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
