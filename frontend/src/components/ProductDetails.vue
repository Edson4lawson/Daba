<template>
  <Transition name="modal-fade">
    <div v-if="isOpen" class="fixed inset-0 z-[200] mt-0 md:mt-30 flex items-center justify-center px-4 pb-4 pt-24 md:p-6" role="dialog" aria-modal="true">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" @click="close"></div>

      <!-- Modal Content -->
      <div class="relative w-250 h-130 bg-daba-cream rounded-[2rem] shadow-2xl overflow-hidden flex flex-col md:flex-row animate-scale-up ">
        
        <!-- Close Button (Retour) -->
        <button @click="close" class="absolute top-4 right-4 z-30 p-2.5 bg-daba-cream-alt rounded-full text-daba-orange hover:bg-daba-orange hover:text-white transition-all shadow-sm border border-daba-cream-alt group">
          <Icon icon="solar:arrow-left-bold" class="w-6 h-6 transition-transform group-hover:-translate-x-1" />
        </button>

        <!-- Product Image Section (Left) -->
        <div class="w-full md:w-1/2 h-64 md:h-full relative flex-shrink-0 bg-daba-cream-alt">
          <OptimizedImage 
            :src="product.thumbnail" 
            :alt="product.title"
            imageClass="w-full h-full object-cover" 
          />
          <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent"></div>
          
          <!-- Badges -->
          <div class="absolute top-4 left-4 flex flex-col gap-2 z-10">
            <span v-if="product.discount" class="px-3 py-1 bg-daba-orange text-white text-[10px] font-black uppercase tracking-wider rounded-full shadow-lg">
              -{{ product.discount }}%
            </span>
            <span class="px-3 py-1 bg-daba-cream/90 backdrop-blur text-daba-navy text-[10px] font-black uppercase tracking-wider rounded-full shadow-lg">
              {{ product.category }}
            </span>
          </div>
        </div>

        <!-- Product Details Section (Right) -->
        <div class="w-full md:w-1/2 flex-1 overflow-y-auto bg-daba-cream/80 backdrop-blur-xl p-6 pt-16 md:p-12 md:pt-16 flex flex-col relative">
          <!-- Background Decoration -->
          <div class="absolute top-0 right-0 w-64 h-64 bg-daba-cream-alt/50 rounded-full blur-[100px] -z-10 pointer-events-none"></div>

          <div class="mb-auto">
            <div class="flex items-center gap-2 text-daba-orange mb-3">
              <div class="flex">
                <Icon v-for="i in 5" :key="i" :icon="i <= Math.round(product.rating) ? 'solar:star-bold' : 'solar:star-linear'" class="w-4 h-4 md:w-5 md:h-5" />
              </div>
              <span v-if="product.stock > 0" class="text-xs md:text-sm text-daba-green font-bold">({{ product.stock }} en stock)</span>
              <span v-else class="text-xs md:text-sm text-daba-slate font-bold">(Rupture de stock)</span>
            </div>

            <h2 class="text-2xl md:text-4xl lg:text-5xl font-black text-daba-navy mb-4 leading-tight tracking-tight">{{ product.title }}</h2>
            
            <div class="flex items-end gap-3 md:gap-4 mb-6 md:mb-8">
              <span class="text-3xl md:text-4xl font-black text-daba-orange">{{ product.price }}Fcfa</span>
              <span v-if="product.discount" class="text-lg md:text-xl text-daba-slate-dark line-through mb-1">
                {{ (product.price * (1 + product.discount/100))}}Fcfa
              </span>
            </div>

            <div class="prose prose-sm md:prose-lg text-daba-slate mb-8 leading-relaxed max-w-none">
              <p>{{ product.description }}</p>
             </div>

            <div class="grid grid-cols-2 gap-3 md:gap-4 mb-8">
              <div class="p-3 md:p-4 rounded-2xl bg-daba-cream-alt border border-daba-cream-alt">
                <span class="block text-[10px] md:text-xs text-daba-slate-dark uppercase tracking-wider font-bold mb-1">Qualité</span>
                <span class="block text-sm md:text-lg font-bold text-daba-navy">100% Authentique</span>
              </div>
              <div class="p-3 md:p-4 rounded-2xl bg-daba-cream-alt border border-daba-cream-alt">
                <span class="block text-[10px] md:text-xs text-daba-slate-dark uppercase tracking-wider font-bold mb-1">Livraison</span>
                <span class="block text-sm md:text-lg font-bold text-daba-navy">Partout au Benin</span>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="pt-6 border-t border-daba-cream-alt flex flex-col md:flex-row items-stretch md:items-center gap-4 mt-auto">
            <div class="flex items-center justify-between bg-daba-cream-alt rounded-2xl p-2 md:w-auto">
              <button @click="quantity > 1 && quantity--" 
                      :disabled="product.stock <= 0"
                      class="w-10 h-10 flex items-center justify-center rounded-xl bg-daba-cream text-daba-slate shadow-sm hover:text-daba-orange transition-colors disabled:opacity-50">
                <Icon icon="solar:minus-circle-linear" class="w-6 h-6" />
              </button>
              <span class="w-12 text-center font-bold text-daba-navy">{{ product.stock > 0 ? quantity : 0 }}</span>
              <button @click="quantity < product.stock ? quantity++ : null" 
                      :disabled="product.stock <= 0 || quantity >= product.stock"
                      class="w-10 h-10 flex items-center justify-center rounded-xl bg-daba-cream text-daba-slate shadow-sm hover:text-daba-orange transition-colors disabled:opacity-50">
                <Icon icon="solar:add-circle-linear" class="w-6 h-6" />
              </button>
            </div>

            <button v-if="product.stock > 0" @click="handleAddToCart" class="flex-1 flex items-center justify-center gap-2 bg-gradient-to-r from-daba-orange to-daba-navy text-white font-bold py-3.5 px-6 rounded-2xl shadow-xl hover:shadow-2xl hover:scale-[1.02] transition-all active:scale-95">
              <Icon icon="solar:bag-check-bold" class="w-6 h-6" />
              <span>AJOUTER - {{ (product.price * quantity) }} FCFA</span>
            </button>
            <a v-else href="https://wa.me/22900000000" target="_blank" class="flex-1 flex items-center justify-center gap-2 bg-daba-navy text-white font-bold py-3.5 px-6 rounded-2xl shadow-xl hover:bg-daba-navy-deep transition-all">
              <Icon icon="solar:chat-round-dots-bold" class="w-6 h-6" />
              <span>S'INFORMER SUR LE STOCK</span>
            </a>
            
            <button @click="toggleWishlist" class="hidden md:flex p-4 rounded-2xl bg-daba-cream-alt text-daba-orange hover:bg-daba-cream-alt hover:text-daba-orange transition-colors">
               <Icon :icon="wishlistStore.isInWishlist(product.id) ? 'solar:heart-bold' : 'solar:heart-linear'" class="w-6 h-6" />
            </button>
          </div>

        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Icon } from '@iconify/vue';
import OptimizedImage from './OptimizedImage.vue';
import { useCartStore } from '../stores/cart';
import { useWishlistStore } from '../stores/wishlist';
import Swal from 'sweetalert2';

const props = defineProps({
  isOpen: Boolean,
  product: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['close']);
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();
const quantity = ref(1);

watch(() => props.product, () => {
  quantity.value = 1;
});

const close = () => {
  emit('close');
};

const handleAddToCart = async () => {
  try {
    await cartStore.addToCart(props.product, quantity.value);
    
    // Success Modal
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
      }
    })

    Toast.fire({
      icon: 'success',
      title: `${props.product.title} ajouté au panier!`
    });
    
    close();
  } catch (error) {
    console.error(error);
  }
};

const toggleWishlist = () => {
  const added = wishlistStore.toggleWishlist(props.product);
  
  const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 2000,
      timerProgressBar: true,
  });

  if (added) {
    Toast.fire({ icon: 'success', title: 'Ajouté aux favoris' });
  } else {
    Toast.fire({ icon: 'info', title: 'Retiré des favoris' });
  }
};
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.animate-scale-up {
  animation: scaleUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes scaleUp {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}
</style>



