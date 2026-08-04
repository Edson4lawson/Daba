<template>
  <header :class="[
    'sticky top-0 left-0 right-0 z-[100] transition-all duration-700 ease-in-out',
    isScrolled ? 'py-2 bg-daba-cream/80 backdrop-blur-xl shadow-lg border-b border-daba-cream-alt/50' : 'py-4 bg-transparent'
  ]">
    <div class="container mx-auto px-4 md:px-6">
      <nav class="flex items-center justify-between gap-4">
        <!-- Logo -->
        <router-link to="/" class="group flex items-center shrink-0 z-[170] relative" @click="closeMenu">
          <img src="../assets/daba-icone.png" alt="Daba" class="w-14 h-14 md:w-20 md:h-20 transition-transform group-hover:scale-105">
        </router-link>

        <!-- Desktop Navigation -->
        <div class="hidden lg:flex items-center space-x-6">
          <a v-for="link in navLinks" :key="link.href" :href="link.href" @click="(e) => scrollToSection(e, link.href)"
            class="relative font-bold text-daba-navy hover:text-daba-orange transition-colors py-2 px-1 group text-sm uppercase tracking-wide">
            {{ link.name }}
            <span class="absolute bottom-0 left-0 w-full h-0.5 bg-daba-orange transform scale-x-0 transition-transform origin-left group-hover:scale-x-100"></span>
          </a>
        </div>

        <!-- Search Bar (Desktop) -->
        <div class="hidden md:block relative flex-1 max-w-md mx-4" ref="searchContainer">
          <div class="relative">
            <Icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-daba-slate-dark" />
            <input v-model="searchQuery" type="text" placeholder="Rechercher un produit..."
              class="w-full pl-10 pr-4 py-2.5 bg-daba-cream-alt/50 border border-daba-cream-alt rounded-2xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-daba-orange/20 focus:border-daba-orange focus:bg-daba-cream transition-all"
              @focus="showSearchResults = true" @input="handleSearch" />
          </div>
          <Transition name="fade">
            <div v-if="showSearchResults && searchQuery.length >= 2"
              class="absolute top-full left-0 right-0 mt-2 bg-daba-cream rounded-2xl shadow-2xl border border-daba-cream-alt max-h-80 overflow-y-auto z-[200]">
              <div v-if="searchResults.length === 0" class="p-6 text-center">
                <Icon icon="solar:box-minimalistic-linear" class="w-10 h-10 text-daba-slate-dark mx-auto mb-2" />
                <p class="text-sm text-daba-slate-dark font-medium">Aucun produit trouvé</p>
              </div>
              <router-link v-for="product in searchResults.slice(0, 6)" :key="product.id" :to="`/produit/${product.slug || product.id}`"
                class="flex items-center gap-3 p-3 hover:bg-daba-cream-alt transition-colors cursor-pointer border-b border-daba-cream-alt/50 last:border-0" @click="clearSearch">
                <div class="w-12 h-12 rounded-xl overflow-hidden bg-daba-cream-alt flex-shrink-0">
                  <img :src="product.thumbnail" :alt="product.title" class="w-full h-full object-cover" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-bold text-daba-navy truncate">{{ product.title }}</p>
                  <p class="text-xs text-daba-green font-bold">{{ product.price }} FCFA</p>
                </div>
              </router-link>
            </div>
          </Transition>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-1 md:gap-3">
          <!-- Search Mobile Toggle -->
          <button @click="isMobileSearchVisible = !isMobileSearchVisible"
            class="md:hidden p-2 text-daba-navy hover:text-daba-orange transition-colors">
            <Icon icon="solar:magnifer-linear" class="w-6 h-6" />
          </button>

          <!-- Wishlist Icon -->
          <button @click="viewWishlist" class="relative p-2 text-daba-navy hover:text-daba-orange transition-colors group">
            <Icon icon="solar:heart-bold-duotone" class="w-6 h-6 transform transition-transform group-hover:scale-110" />
            <span v-if="wishlistCount > 0"
              class="absolute top-0 right-0 flex h-4 w-4 items-center justify-center rounded-full bg-daba-orange text-[8px] font-bold text-white ring-2 ring-daba-cream">
              {{ wishlistCount }}
            </span>
          </button>

          <!-- Cart Icon -->
          <button @click="toggleCart" class="relative p-2 text-daba-navy hover:text-daba-orange transition-colors group">
            <Icon icon="solar:cart-large-minimalistic-bold" class="w-6 h-6 transform transition-transform group-hover:rotate-12" />
            <span v-if="cartStore.totalItems > 0"
              class="absolute top-0 right-0 flex h-4 w-4 items-center justify-center rounded-full bg-daba-orange text-[8px] font-bold text-white ring-2 ring-daba-cream">
              {{ cartStore.totalItems }}
            </span>
          </button>

          <!-- User Profile -->
          <div class="hidden sm:block">
            <button v-if="!authStore.isAuthenticated" @click="showAuthModal = true"
              class="flex items-center space-x-2 px-5 py-2 rounded-full bg-daba-orange text-white font-bold hover:bg-daba-orange-dark transition-all hover:shadow-xl active:scale-95 text-sm">
              <Icon icon="solar:user-circle-bold" class="w-5 h-5" />
              <span>S'identifier</span>
            </button>
            <div v-else class="relative group">
              <button class="flex items-center space-x-2 p-1 rounded-full bg-daba-cream-alt border border-daba-cream-alt hover:bg-daba-cream transition-colors">
                <div class="w-8 h-8 rounded-full bg-daba-orange flex items-center justify-center text-white text-xs font-bold uppercase">
                  {{ authStore.user?.first_name?.charAt(0) || 'U' }}
                </div>
                <Icon icon="solar:alt-arrow-down-linear" class="w-4 h-4 text-daba-slate" />
              </button>
              <div class="absolute right-0 mt-2 w-52 bg-daba-cream rounded-xl shadow-xl border border-daba-cream-alt opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all transform origin-top-right translate-y-2 group-hover:translate-y-0 py-2 z-[110]">
                <div class="px-4 py-2 border-b border-daba-cream-alt mb-1">
                  <p class="text-[10px] text-daba-slate uppercase font-black">Mon Espace</p>
                  <p class="text-sm font-bold truncate text-daba-navy">{{ authStore.user?.first_name || authStore.user?.email }}</p>
                </div>
                
                <!-- Lien Admin (Si applicable) -->
                <router-link v-if="authStore.user?.role === 'admin'" to="/admin/dashboard" class="w-full flex items-center space-x-2 px-4 py-2 text-daba-orange hover:bg-daba-cream-alt transition-colors text-left border-b border-daba-cream-alt/50">
                  <Icon icon="solar:settings-minimalistic-bold" class="w-4 h-4" />
                  <span class="text-sm font-bold uppercase tracking-tighter">Gestion Boutique</span>
                </router-link>

                <button @click="handleLogout" class="w-full flex items-center space-x-2 px-4 py-2 text-red-500 hover:bg-red-50 transition-colors text-left mt-1">
                  <Icon icon="solar:logout-linear" class="w-4 h-4" />
                  <span class="text-sm font-bold">Déconnexion</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Mobile Menu Button -->
          <button @click="isMenuOpen = !isMenuOpen"
            class="lg:hidden p-2 text-daba-navy hover:bg-daba-cream-alt rounded-xl transition-colors focus:outline-none z-[170] relative">
            <Icon :icon="isMenuOpen ? 'solar:close-circle-bold' : 'solar:hamburger-menu-bold'"
              class="w-7 h-7 transition-all duration-300" :class="{ 'rotate-90': isMenuOpen }" />
          </button>
        </div>
      </nav>

      <!-- Mobile Search Input Expanded -->
      <Transition name="search-slide">
        <div v-if="isMobileSearchVisible" class="md:hidden mt-4 pb-2" ref="mobileSearchRef">
          <div class="relative">
            <Icon icon="solar:magnifer-linear" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-daba-slate-dark" />
            <input v-model="searchQuery" type="text" placeholder="Rechercher un produit..."
              class="w-full pl-12 pr-4 py-3 bg-daba-cream border border-daba-cream-alt rounded-2xl text-base shadow-lg focus:outline-none focus:ring-2 focus:ring-daba-orange/30"
              @input="handleSearch" />
          </div>
          <!-- Mobile search results -->
          <div v-if="searchResults.length > 0 && searchQuery.length >= 2" class="mt-2 bg-daba-cream rounded-2xl shadow-xl overflow-hidden border border-daba-cream-alt max-h-64 overflow-y-auto">
            <router-link v-for="p in searchResults.slice(0, 5)" :key="p.id" :to="`/produit/${p.slug || p.id}`"
              class="flex items-center gap-3 p-3 hover:bg-daba-cream-alt border-b border-daba-cream-alt last:border-0" @click="clearSearch(); isMobileSearchVisible = false">
              <img :src="p.thumbnail" :alt="p.title" class="w-10 h-10 rounded-lg object-cover" />
              <div class="text-left">
                <p class="text-sm font-bold text-daba-navy truncate">{{ p.title }}</p>
                <p class="text-xs text-daba-green font-bold">{{ p.price }} FCFA</p>
              </div>
            </router-link>
          </div>
        </div>
      </Transition>
    </div>
  </header>

  <!-- Mobile Menu Overlay -->
  <Transition name="menu-fade">
    <div v-if="isMenuOpen" class="fixed inset-0 z-[160] lg:hidden bg-daba-cream overflow-y-auto">
      <div class="min-h-full flex flex-col justify-start items-center pt-24 pb-12 px-6 text-center">
        <nav class="flex flex-col items-center space-y-6 w-full">
          <a v-for="link in navLinks" :key="link.href" :href="link.href" @click="(e) => scrollToSectionWithDelay(e, link.href)"
            class="text-2xl font-medium text-daba-navy hover:text-daba-orange transition-all">
            {{ link.name }}
          </a>
          
          <div class="w-full h-px bg-daba-cream-alt my-4"></div>

          <button v-if="authStore.isAuthenticated" @click="isMenuOpen = false; handleLogout"
            class="flex items-center gap-3 px-6 py-4 bg-red-50 text-red-600 rounded-2xl w-full justify-center font-semibold">
            <Icon icon="solar:logout-bold" class="w-6 h-6" />
            <span>Déconnexion</span>
          </button>
          <button v-else @click="isMenuOpen = false; showAuthModal = true"
            class="flex items-center gap-3 px-6 py-4 bg-daba-orange text-white rounded-2xl w-full justify-center font-semibold">
            <Icon icon="solar:user-circle-bold" class="w-6 h-6" />
            <span>S'identifier</span>
          </button>
        </nav>
      </div>
    </div>
  </Transition>

  <AuthModal v-if="showAuthModal" @close="showAuthModal = false" />
  <CartDrawer :is-open="isCartOpen" @close="isCartOpen = false" @request-login="showAuthModal = true" @open-payment="handleOpenPayment" />
  <WishlistDrawer :is-open="isWishlistOpen" @close="isWishlistOpen = false" />
  <PaymentModal 
    v-if="showPaymentModal" 
    :is-open="showPaymentModal" 
    :amount="paymentAmount"
    :order-id="paymentOrderId"
    @close="showPaymentModal = false" 
    @success="handlePaymentSuccess"
  />
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { Icon } from '@iconify/vue';
import { useCartStore } from '../stores/cart';
import { useAuthStore } from '../stores/auth';
import { useWishlistStore } from '../stores/wishlist';
import { useProductStore } from '../stores/products';
import { useRouter } from 'vue-router';
import AuthModal from './AuthModal.vue';
import CartDrawer from './CartDrawer.vue';
import WishlistDrawer from './WishlistDrawer.vue';
import PaymentModal from './PaymentModal.vue';

// Déclaration des événements émis
const emit = defineEmits(['openStore', 'openAuth', 'openPayment']);

const cartStore = useCartStore();
const authStore = useAuthStore();
const wishlistStore = useWishlistStore();
const productStore = useProductStore();
const router = useRouter();

const isScrolled = ref(false);
const isMenuOpen = ref(false);
const isCartOpen = ref(false);
const isWishlistOpen = ref(false);
const showAuthModal = ref(false);
const showPaymentModal = ref(false);
const paymentAmount = ref(0);
const paymentOrderId = ref(null);
const isMobileSearchVisible = ref(false);
const searchQuery = ref('');
const searchResults = ref([]);
const showSearchResults = ref(false);
const searchContainer = ref(null);
const mobileSearchRef = ref(null);

const wishlistCount = computed(() => wishlistStore.totalItems);

const navLinks = [
  { name: 'Accueil', href: '/#hero' },
  { name: 'Boutique', href: '/boutique' },
  { name: 'Nouveautés', href: '/#store' },
  { name: 'Tendances', href: '/#tendances' },
  { name: 'Contact', href: '/#contact' },
];

const handleSearch = () => {
  if (searchQuery.value.length < 2) {
    searchResults.value = [];
    return;
  }
  searchResults.value = productStore.searchProducts(searchQuery.value);
};

const clearSearch = () => {
  searchQuery.value = '';
  searchResults.value = [];
  showSearchResults.value = false;
};

const toggleCart = () => { isCartOpen.value = !isCartOpen.value; isMenuOpen.value = false; };
const viewWishlist = () => { isWishlistOpen.value = !isWishlistOpen.value; isMenuOpen.value = false; };
const closeMenu = () => { isMenuOpen.value = false; };

const handleLogout = async () => {
  await authStore.logout();
  isMenuOpen.value = false;
};

const handleOpenPayment = (data) => {
  paymentAmount.value = data.amount;
  paymentOrderId.value = data.orderId;
  showPaymentModal.value = true;
};

const handlePaymentSuccess = () => {
  cartStore.clearCart();
  showPaymentModal.value = false;
};

const scrollToSection = (e, targetId) => {
  if (e) e.preventDefault();
  
  if (!targetId.startsWith('/#')) {
    router.push(targetId);
    return;
  }
  
  const hash = targetId.substring(1); // extract #hero
  
  if (window.location.pathname !== '/') {
    router.push('/');
    setTimeout(() => {
      const element = document.querySelector(hash);
      if (element) {
        const offset = 80;
        const elementPosition = element.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.scrollY - offset;
        window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
      }
    }, 500);
    return;
  }

  const element = document.querySelector(hash);
  if (element) {
    const offset = 80;
    const elementPosition = element.getBoundingClientRect().top;
    const offsetPosition = elementPosition + window.scrollY - offset;
    window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
  }
};

const scrollToSectionWithDelay = (e, targetId) => {
  if (e) e.preventDefault();
  isMenuOpen.value = false;
  setTimeout(() => scrollToSection(null, targetId), 400);
};

const handleScroll = () => { isScrolled.value = window.scrollY > 20; };
const handleClickOutside = (e) => {
  if (searchContainer.value && !searchContainer.value.contains(e.target)) showSearchResults.value = false;
  if (mobileSearchRef.value && !mobileSearchRef.value.contains(e.target) && !e.target.closest('button')) isMobileSearchVisible.value = false;
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.search-slide-enter-active, .search-slide-leave-active { transition: all 0.3s ease; }
.search-slide-enter-from, .search-slide-leave-to { opacity: 0; transform: translateY(-10px); }

.menu-fade-enter-active, .menu-fade-leave-active { transition: all 0.4s ease; }
.menu-fade-enter-from, .menu-fade-leave-to { opacity: 0; transform: scale(1.05); }
</style>
