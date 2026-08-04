<template>
  <Transition name="slide-right">
    <div v-if="isOpen" class="fixed inset-0 z-[200] overflow-hidden">
      <!-- Overlay -->
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>

      <!-- Drawer Content -->
      <div class="absolute inset-y-0 right-0 max-w-full flex">
        <div class="w-screen max-w-md bg-daba-cream shadow-2xl flex flex-col">
          <!-- Header -->
          <div class="px-6 py-5 border-b border-daba-cream-alt flex items-center justify-between bg-daba-cream-alt/50">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 rounded-xl bg-daba-green flex items-center justify-center text-white shadow-lg shadow-daba-green/20">
                <Icon icon="solar:heart-bold" class="w-6 h-6" />
              </div>
              <h2 class="text-xl font-bold text-daba-navy">Vos Favoris</h2>
            </div>
            <button @click="$emit('close')" class="p-2 text-daba-slate hover:text-daba-orange hover:bg-daba-cream rounded-full transition-all">
              <Icon icon="solar:close-circle-bold" class="w-7 h-7" />
            </button>
          </div>

          <!-- Items List -->
          <div class="flex-1 overflow-y-auto py-6 px-6">
            <div v-if="wishlistStore.isEmpty" class="h-full flex flex-col items-center justify-center text-center space-y-4">
              <div class="w-24 h-24 bg-daba-cream-alt rounded-full flex items-center justify-center">
                <Icon icon="solar:heart-angle-linear" class="w-12 h-12 text-daba-slate-dark" />
              </div>
              <div>
                <p class="text-xl font-bold text-daba-navy">Votre liste est vide</p>
                <p class="text-daba-slate">Sauvegardez vos coups de cÅ“ur pour plus tard.</p>
              </div>
              <button @click="$emit('close')" class="mt-4 px-8 py-3 bg-daba-orange text-white rounded-full font-bold shadow-lg shadow-daba-green/20 hover:bg-daba-orange-dark transition-all">
                Découvrir
              </button>
            </div>

            <div v-else class="space-y-6">
              <div v-for="item in wishlistStore.items" :key="item.id" class="flex items-center space-x-4 p-3 rounded-2xl border border-daba-cream-alt hover:bg-daba-cream-alt transition-colors group">
                <div class="w-20 h-20 flex-shrink-0 bg-daba-cream-alt rounded-xl overflow-hidden relative">
                  <img :src="item.thumbnail || '/placeholder-perfume.jpg'" :alt="item.title" class="w-full h-full object-cover" />
                  <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                     <button @click="addToCart(item)" class="p-1.5 bg-daba-cream rounded-full text-daba-orange hover:text-daba-orange-dark shadow-sm" title="Ajouter au panier">
                        <Icon icon="solar:cart-plus-bold" class="w-5 h-5" />
                     </button>
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <span class="text-[10px] uppercase font-bold text-daba-slate tracking-wider">{{ item.category }}</span>
                  <h4 class="font-bold text-daba-navy truncate">{{ item.title }}</h4>
                  <p class="text-daba-orange font-bold mb-2">{{ item.price }} Fcfa</p>
                </div>
                <button @click="wishlistStore.removeFromWishlist(item.id)" class="p-2 text-daba-slate-dark hover:text-daba-orange transition-colors">
                  <Icon icon="solar:trash-bin-trash-linear" class="w-6 h-6" />
                </button>
              </div>
            </div>
          </div>

          <!-- Footer Actions -->
          <div v-if="!wishlistStore.isEmpty" class="p-6 bg-daba-cream-alt/50 border-t border-daba-cream-alt space-y-4">
            <button @click="addAllToCart" class="w-full py-4 bg-daba-cream border-2 border-daba-cream-alt text-daba-navy rounded-2xl font-bold flex items-center justify-center space-x-3 hover:bg-daba-cream-alt transition-all">
              <Icon icon="solar:cart-large-minimalistic-bold" class="w-5 h-5" />
              <span>Tout ajouter au panier</span>
            </button>
            <button @click="wishlistStore.clearWishlist" class="w-full py-3 text-daba-slate hover:text-red-500 text-sm font-bold transition-colors">
              Vider la liste
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { Icon } from '@iconify/vue';
import { useWishlistStore } from '../stores/wishlist';
import { useCartStore } from '../stores/cart';
import Swal from 'sweetalert2';

defineProps({
  isOpen: Boolean
});

const emit = defineEmits(['close']);

const wishlistStore = useWishlistStore();
const cartStore = useCartStore();

const addToCart = (item) => {
    cartStore.addToCart(item);
    Swal.fire({
      icon: 'success',
      title: 'Ajouté au panier',
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 1500
    });
};

const addAllToCart = () => {
    wishlistStore.items.forEach(item => {
        cartStore.addToCart(item);
    });
    Swal.fire({
      icon: 'success',
      title: 'Tout a été ajouté !',
      text: 'Vos produits favoris sont dans le panier.',
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 2000
    });
    emit('close');
};
</script>

<style scoped>
.slide-right-enter-active, .slide-right-leave-active {
  transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-right-enter-from, .slide-right-leave-to {
  opacity: 0;
}
.slide-right-enter-from .w-screen, .slide-right-leave-to .w-screen {
  transform: translateX(100%);
}
</style>



