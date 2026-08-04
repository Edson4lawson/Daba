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
              <div class="w-10 h-10 rounded-xl bg-daba-orange flex items-center justify-center text-white shadow-lg shadow-daba-orange/20">
                <Icon icon="solar:bag-3-bold" class="w-6 h-6" />
              </div>
              <h2 class="text-xl font-bold text-daba-navy">Votre Panier</h2>
            </div>
            <button @click="$emit('close')" class="p-2 text-daba-slate hover:text-daba-orange hover:bg-daba-cream rounded-full transition-all">
              <Icon icon="solar:close-circle-bold" class="w-7 h-7" />
            </button>
          </div>

          <!-- Items List -->
          <div class="flex-1 overflow-y-auto py-6 px-6">
            <div v-if="cartStore.items.length === 0" class="h-full flex flex-col items-center justify-center text-center space-y-4">
              <div class="w-24 h-24 bg-daba-cream-alt rounded-full flex items-center justify-center">
                <Icon icon="solar:bag-smile-linear" class="w-12 h-12 text-daba-slate-dark" />
              </div>
              <div>
                <p class="text-xl font-bold text-daba-navy">Votre panier est vide</p>
                <p class="text-daba-slate">Découvrez nos produits et faites-vous plaisir !</p>
              </div>
              <button @click="$emit('close')" class="mt-4 px-8 py-3 bg-daba-orange text-white rounded-full font-bold shadow-lg shadow-daba-orange/20 hover:bg-daba-orange-dark transition-all">
                Voir les produits
              </button>
            </div>

            <div v-else class="space-y-6">
              <div v-for="item in cartStore.items" :key="item.id" class="flex items-center space-x-4 p-3 rounded-2xl border border-daba-cream-alt hover:bg-daba-cream-alt transition-colors group">
                <div class="w-20 h-20 flex-shrink-0 bg-daba-cream-alt rounded-xl overflow-hidden">
                  <img :src="item.thumbnail || '/placeholder-perfume.jpg'" :alt="item.title" class="w-full h-full object-cover" />
                </div>
                <div class="flex-1 min-w-0">
                  <h4 class="font-bold text-daba-navy truncate">{{ item.title }}</h4>
                  <p class="text-daba-orange font-bold mb-2">{{ item.price }} Fcfa</p>
                  
                  <!-- Quantity Controls -->
                  <div class="flex items-center space-x-3">
                    <div class="flex items-center border border-daba-cream-alt rounded-lg p-1">
                      <button @click="cartStore.updateQuantity(item.id, item.quantity - 1)" 
                              class="p-1 text-daba-slate hover:text-daba-orange disabled:opacity-30"
                              :disabled="item.quantity <= 1">
                        <Icon icon="solar:minus-circle-linear" class="w-5 h-5" />
                      </button>
                      <span class="w-8 text-center text-sm font-bold text-daba-navy">{{ item.quantity }}</span>
                      <button @click="cartStore.updateQuantity(item.id, item.quantity + 1)" 
                              class="p-1 text-daba-slate hover:text-daba-orange">
                        <Icon icon="solar:add-circle-linear" class="w-5 h-5" />
                      </button>
                    </div>
                  </div>
                </div>
                <button @click="cartStore.removeFromCart(item.id)" class="p-2 text-daba-slate-dark hover:text-red-500 transition-colors">
                  <Icon icon="solar:trash-bin-trash-linear" class="w-6 h-6" />
                </button>
              </div>
            </div>
          </div>

          <!-- Footer / Checkout -->
          <div v-if="cartStore.items.length > 0" class="p-6 bg-daba-cream-alt/50 border-t border-daba-cream-alt space-y-4">
            <div class="space-y-2">
              <div class="flex justify-between text-daba-slate text-sm">
                <span>Sous-total</span>
                <span>{{ cartStore.subtotal }} Fcfa</span>
              </div>
              <div class="flex justify-between text-daba-slate text-sm">
                <span>Livraison</span>
                <span>{{ cartStore.shippingFee }} Fcfa</span>
              </div>
              <div class="flex justify-between text-daba-navy font-black text-xl pt-2">
                <span>Total</span>
                <span class="text-daba-orange">{{ cartStore.cartTotal }} Fcfa</span>
              </div>
            </div>
            
            <button @click="openPaymentModal" class="w-full py-4 bg-gradient-to-r from-daba-orange to-daba-orange-dark text-white rounded-2xl font-bold flex items-center justify-center space-x-3 shadow-xl shadow-daba-orange/20 hover:scale-[1.02] active:scale-95 transition-all">
              <span>Passer la commande</span>
              <Icon icon="solar:alt-arrow-right-linear" class="w-5 h-5" />
            </button>
            <p class="text-center text-[10px] text-daba-slate">Paiement Mobile Money MTN, Celtis Cash, Virement UBA Bank</p>
          </div>
        </div>
      </div>
    </div>
  </Transition>


</template>

<script setup>
import { ref } from 'vue';
import { Icon } from '@iconify/vue';
import { useCartStore } from '../stores/cart';
import { useAuthStore } from '../stores/auth';
import { orderService } from '../services/api';
import Swal from 'sweetalert2';

defineProps({
  isOpen: Boolean
});

const emit = defineEmits(['close']);

const cartStore = useCartStore();
const authStore = useAuthStore();

// Guard contre les doubles soumissions
const isSubmitting = ref(false);

const openPaymentModal = async () => {
  if (cartStore.items.length === 0) {
    Swal.fire({
      title: 'Panier vide',
      text: 'Votre panier est vide. Ajoutez des produits avant de passer commande.',
      icon: 'warning',
      confirmButtonColor: '#9333ea'
    });
    return;
  }

  // Vérifier si l'utilisateur est connecté
  if (!authStore.isAuthenticated) {
    emit('close'); // Fermer le panier pour laisser place À  la modal d'auth
    
    Swal.fire({
      title: 'Identification Requise',
      text: 'Veuillez vous connecter ou créer un compte pour procéder au paiement.',
      icon: 'info',
      confirmButtonText: 'Se connecter',
      confirmButtonColor: '#9333ea',
      showCancelButton: true,
      cancelButtonText: 'Continuer mes achats',
      reverseButtons: true
    }).then((result) => {
      if (result.isConfirmed) {
        // Signaler au Header qu'on veut payer après la connexion
        emit('request-payment-after-login');
        emit('request-login');
      }
    });
    return;
  }

  // Éviter les doubles soumissions
  if (isSubmitting.value) return;
  isSubmitting.value = true;

  emit('close');
  
  // Créer la commande
  Swal.fire({
    title: 'Traitement...',
    text: 'Création de votre commande',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  try {
    // shipping_address ne doit jamais être vide — le backend renvoie 400 sinon
    const shippingAddress =
      (authStore.user?.address && authStore.user.address.trim()) ||
      (authStore.user?.city && authStore.user.city.trim()) ||
      'Cotonou, Bénin';

    const orderData = {
      shipping_address: shippingAddress,
      payment_method: 'mobile_money',
      customer_note: ''
    };

    const response = await orderService.create(orderData);
    
    if (response.data.order_id) {
      Swal.close();
      // Signaler au Header d'ouvrir le modal de paiement avec les bonnes infos
      emit('open-payment', {
        amount: cartStore.cartTotal,
        orderId: response.data.order_id
      });
    } else {
      throw new Error('Erreur lors de la création de la commande');
    }
  } catch (err) {
    Swal.fire({
      title: 'Erreur',
      text: err.response?.data?.error || 'Impossible de créer la commande. Vérifiez votre stock.',
      icon: 'error',
      confirmButtonColor: '#9333ea'
    });
  } finally {
    isSubmitting.value = false;
  }
};

const handlePaymentSuccess = (paymentData) => {
  cartStore.clearCart();
  Swal.fire({
    title: 'Commande confirmée!',
    text: `Votre commande a été payée avec succès via ${paymentData.provider.replace('_', ' ').toUpperCase()}`,
    icon: 'success',
    confirmButtonColor: '#9333ea'
  });
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



