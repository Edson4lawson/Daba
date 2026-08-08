<template>
  <Transition name="slide-right">
    <div v-if="isOpen" class="fixed inset-0 z-[9999] overflow-hidden font-karla">
      <!-- Overlay -->
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="closeDrawer"></div>

      <!-- Drawer Panel -->
      <div class="absolute inset-y-0 right-0 max-w-full flex">
        <div class="w-screen max-w-md bg-daba-cream shadow-2xl flex flex-col">

          <!-- ══ Header ══ -->
          <div class="px-6 py-4 border-b border-daba-border flex items-center justify-between bg-daba-cream-light shrink-0">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-full bg-daba-orange flex items-center justify-center text-white">
                <ShoppingBasket class="w-5 h-5" />
              </div>
              <div>
                <h2 class="text-base font-bold font-playfair text-daba-navy">
                  {{ isCheckoutStep ? 'Finaliser la commande' : 'Mon panier' }}
                </h2>
                <p class="text-[11px] text-daba-slate-light" v-if="!isCheckoutStep">
                  {{ cartStore.totalItems }} article{{ cartStore.totalItems > 1 ? 's' : '' }}
                </p>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <button v-if="isCheckoutStep" @click="isCheckoutStep = false"
                class="flex items-center gap-1 text-xs font-bold text-daba-slate hover:text-daba-navy transition-colors px-2 py-1">
                <ArrowLeft class="w-3.5 h-3.5" />
                Retour
              </button>
              <button @click="closeDrawer" class="p-1.5 text-daba-slate hover:text-daba-orange transition-colors" aria-label="Fermer le panier">
                <X class="w-5 h-5" />
              </button>
            </div>
          </div>

          <!-- ══ Mode 1 : Liste articles ══ -->
          <div v-if="!isCheckoutStep" class="flex-1 overflow-y-auto" data-scrollable="true">

            <!-- État vide -->
            <div v-if="cartStore.items.length === 0" class="h-full flex flex-col items-center justify-center px-8 py-16 text-center space-y-5">
              <div class="w-20 h-20 rounded-full bg-daba-cream-light border border-daba-border flex items-center justify-center">
                <ShoppingBasket class="w-9 h-9 text-daba-slate-light" />
              </div>
              <div>
                <p class="text-lg font-playfair font-semibold text-daba-navy mb-1">Votre panier est vide</p>
                <p class="text-sm text-daba-slate leading-relaxed max-w-xs mx-auto">
                  Nos volailles fraiches et fumées vous attendent — choisissez vos produits et on s'occupe du reste 🐔
                </p>
              </div>
              <router-link to="/produits" @click="closeDrawer"
                class="mt-2 px-7 py-3 bg-daba-orange text-white rounded-full font-inter font-bold text-xs uppercase tracking-wider shadow-md hover:bg-daba-orange-alt transition-all active:scale-95">
                Voir le catalogue
              </router-link>
            </div>

            <!-- Articles -->
            <div v-else class="py-4 px-5 space-y-3">

              <!-- Alerte commande minimum -->
              <div v-if="cartStore.cartTotal < MIN_ORDER"
                class="flex items-start gap-3 bg-orange-50 border border-orange-200 rounded-xl px-4 py-3 text-xs text-orange-800">
                <AlertTriangle class="w-4 h-4 shrink-0 mt-0.5 text-orange-500" />
                <div>
                  <p class="font-bold">Minimum de commande : 10 000 FCFA</p>
                  <p class="mt-0.5 opacity-80">
                    Il manque <strong>{{ (MIN_ORDER - cartStore.cartTotal).toLocaleString('fr-FR') }} FCFA</strong> pour atteindre le minimum de livraison.
                  </p>
                </div>
              </div>

              <!-- Liste des items -->
              <div v-for="item in cartStore.items" :key="item.id"
                class="flex items-center gap-3 p-3 bg-white rounded-xl border border-daba-border shadow-sm hover:shadow-card-light transition-shadow">
                <div class="w-16 h-16 shrink-0 rounded-lg overflow-hidden border border-daba-border bg-daba-cream-light">
                  <img :src="item.thumbnail" :alt="item.title" class="w-full h-full object-cover" />
                </div>

                <div class="flex-1 min-w-0">
                  <h4 class="font-bold text-sm text-daba-navy truncate leading-tight">{{ item.title }}</h4>
                  <p class="text-daba-orange font-bold text-xs mt-0.5">{{ item.price?.toLocaleString('fr-FR') }} FCFA</p>

                  <!-- Stepper quantité -->
                  <div class="flex items-center gap-2 mt-2">
                    <button @click="cartStore.updateQuantity(item.id, item.quantity - 1)"
                      :disabled="item.quantity <= 1"
                      class="w-7 h-7 rounded-lg bg-daba-cream-light border border-daba-border flex items-center justify-center text-daba-navy hover:bg-daba-border transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                      <Minus class="w-3 h-3" />
                    </button>
                    <span class="w-6 text-center text-sm font-bold text-daba-navy">{{ item.quantity }}</span>
                    <button @click="cartStore.updateQuantity(item.id, item.quantity + 1)"
                      class="w-7 h-7 rounded-lg bg-daba-cream-light border border-daba-border flex items-center justify-center text-daba-navy hover:bg-daba-border transition-colors">
                      <Plus class="w-3 h-3" />
                    </button>
                  </div>
                </div>

                <!-- Sous-total item + supprimer -->
                <div class="flex flex-col items-end gap-2 shrink-0">
                  <p class="text-xs font-bold text-daba-navy">{{ (item.price * item.quantity).toLocaleString('fr-FR') }} FCFA</p>
                  <button @click="cartStore.removeFromCart(item.id)"
                    class="p-1.5 rounded-lg text-daba-slate-light hover:text-red-500 hover:bg-red-50 transition-colors" aria-label="Supprimer">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- ══ Mode 2 : Formulaire Checkout ══ -->
          <div v-else class="flex-1 overflow-y-auto py-5 px-5 space-y-4" data-scrollable="true">
            <!-- Résumé rapide -->
            <div class="bg-daba-cream-light border border-daba-border rounded-xl p-4 text-xs">
              <p class="font-bold text-daba-navy mb-2 text-sm">Récapitulatif</p>
              <div class="flex justify-between text-daba-slate">
                <span>{{ cartStore.totalItems }} article{{ cartStore.totalItems > 1 ? 's' : '' }}</span>
                <span class="font-bold text-daba-navy">{{ cartStore.cartTotal?.toLocaleString('fr-FR') }} FCFA</span>
              </div>
            </div>

            <form @submit.prevent="submitOrder" class="space-y-4">
              <div>
                <label class="block font-worksans text-[11px] font-bold uppercase tracking-wider text-daba-slate-light mb-1.5">Nom & Prénom *</label>
                <input v-model="checkoutForm.name" type="text" required placeholder="Ex. Koffi Mensah"
                  class="w-full px-4 py-2.5 bg-white border border-daba-border rounded-xl text-sm font-karla text-daba-navy focus:outline-none focus:ring-2 focus:ring-daba-orange transition-shadow" />
              </div>
              <div>
                <label class="block font-worksans text-[11px] font-bold uppercase tracking-wider text-daba-slate-light mb-1.5">Téléphone / WhatsApp *</label>
                <input v-model="checkoutForm.phone" type="tel" required placeholder="+228 90 00 00 00"
                  class="w-full px-4 py-2.5 bg-white border border-daba-border rounded-xl text-sm font-karla text-daba-navy focus:outline-none focus:ring-2 focus:ring-daba-orange transition-shadow" />
              </div>
              <div>
                <label class="block font-worksans text-[11px] font-bold uppercase tracking-wider text-daba-slate-light mb-1.5">Adresse de livraison *</label>
                <input v-model="checkoutForm.address" type="text" required placeholder="Ex. Agbalépédogan, Lomé"
                  class="w-full px-4 py-2.5 bg-white border border-daba-border rounded-xl text-sm font-karla text-daba-navy focus:outline-none focus:ring-2 focus:ring-daba-orange transition-shadow" />
              </div>
              <div>
                <label class="block font-worksans text-[11px] font-bold uppercase tracking-wider text-daba-slate-light mb-1.5">Mode de paiement</label>
                <select v-model="checkoutForm.payment_method"
                  class="w-full px-4 py-2.5 bg-white border border-daba-border rounded-xl text-sm font-karla text-daba-navy focus:outline-none focus:ring-2 focus:ring-daba-orange">
                  <option value="cash_on_delivery">Paiement à la livraison (Espèces)</option>
                  <option value="mobile_money">T-Money / Flooz (Mobile Money)</option>
                </select>
              </div>
              <div>
                <label class="block font-worksans text-[11px] font-bold uppercase tracking-wider text-daba-slate-light mb-1.5">Notes (optionnel)</label>
                <textarea v-model="checkoutForm.customer_note" rows="2" placeholder="Heure, repère de livraison..."
                  class="w-full px-4 py-2.5 bg-white border border-daba-border rounded-xl text-sm font-karla text-daba-navy focus:outline-none focus:ring-2 focus:ring-daba-orange resize-none"></textarea>
              </div>

              <button type="submit" :disabled="isSubmitting"
                class="w-full py-3.5 bg-daba-orange text-white rounded-full font-inter font-bold text-xs uppercase tracking-wider hover:bg-daba-orange-alt transition-all shadow-md active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                {{ isSubmitting ? 'Traitement…' : `Confirmer — ${cartStore.cartTotal?.toLocaleString('fr-FR')} FCFA` }}
              </button>
            </form>
          </div>

          <!-- ══ Pied de tiroir sticky ══ -->
          <div v-if="cartStore.items.length > 0 && !isCheckoutStep"
            class="shrink-0 border-t border-daba-border bg-daba-cream-light px-5 py-4 space-y-3">

            <!-- Totaux -->
            <div class="space-y-1 text-sm">
              <div class="flex justify-between text-daba-slate text-xs">
                <span>Sous-total</span>
                <span>{{ cartStore.cartTotal?.toLocaleString('fr-FR') }} FCFA</span>
              </div>
              <div class="flex justify-between text-daba-slate text-xs">
                <span>Livraison</span>
                <span class="text-daba-green font-semibold">Selon quartier</span>
              </div>
              <div class="flex justify-between text-daba-navy font-bold text-base pt-2 border-t border-daba-border-light">
                <span>Total</span>
                <span class="text-daba-orange">{{ cartStore.cartTotal?.toLocaleString('fr-FR') }} FCFA</span>
              </div>
            </div>

            <!-- Badges de confiance -->
            <div class="flex items-center gap-4 py-2 border-y border-daba-border-light">
              <div class="flex items-center gap-1.5 text-[11px] text-daba-slate font-medium">
                <Snowflake class="w-3.5 h-3.5 text-daba-green shrink-0" />
                <span>Chaîne du froid respectée</span>
              </div>
              <div class="flex items-center gap-1.5 text-[11px] text-daba-slate font-medium">
                <HandCoins class="w-3.5 h-3.5 text-daba-green shrink-0" />
                <span>Paiement à la livraison</span>
              </div>
            </div>

            <!-- CTA Commander -->
            <button @click="proceedToCheckout" :disabled="cartStore.cartTotal < MIN_ORDER"
              class="w-full py-3.5 bg-daba-orange text-white rounded-full font-inter font-bold text-xs uppercase tracking-wider hover:bg-daba-orange-alt transition-all shadow-md active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
              <ShoppingBasket class="w-4 h-4" />
              Passer la commande
              <ArrowRight class="w-4 h-4" />
            </button>
            <p class="text-center text-[10px] text-daba-slate-light">Livraison 24-48h · Lomé & environs · Froid continu</p>
          </div>

        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, reactive, toRef } from 'vue'
import { useRouter } from 'vue-router'
import {
  ShoppingBasket, X, ArrowLeft, ArrowRight,
  Minus, Plus, Trash2, AlertTriangle,
  Snowflake, HandCoins
} from 'lucide-vue-next'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { orderService } from '@/services/api'
import { useScrollLock } from '@/composables/useScrollLock'
import { notifyOrderSuccess, notifyError } from '@/utils/notifications'

const props = defineProps({ isOpen: Boolean })
const emit = defineEmits(['close'])

useScrollLock(toRef(props, 'isOpen'))

const router = useRouter()
const cartStore = useCartStore()
const authStore = useAuthStore()

const MIN_ORDER = 10000
const isCheckoutStep = ref(false)
const isSubmitting = ref(false)

const checkoutForm = reactive({
  name: '',
  phone: '',
  address: '',
  payment_method: 'cash_on_delivery',
  customer_note: ''
})

const closeDrawer = () => {
  isCheckoutStep.value = false
  emit('close')
}

const proceedToCheckout = () => {
  if (cartStore.items.length === 0 || cartStore.cartTotal < MIN_ORDER) return
  if (authStore.isAuthenticated && authStore.user) {
    checkoutForm.name = authStore.user.first_name || authStore.user.name || ''
    checkoutForm.phone = authStore.user.phone || ''
    checkoutForm.address = authStore.user.address || ''
  }
  isCheckoutStep.value = true
}

const submitOrder = async () => {
  if (cartStore.items.length === 0 || isSubmitting.value) return
  isSubmitting.value = true
  try {
    const isGuest = !authStore.isAuthenticated
    const payload = isGuest ? {
      guest_checkout: true,
      name: checkoutForm.name,
      phone: checkoutForm.phone,
      address: checkoutForm.address,
      payment_method: checkoutForm.payment_method,
      customer_note: checkoutForm.customer_note,
      items: cartStore.items.map(item => ({ product_id: parseInt(item.id, 10), quantity: parseInt(item.quantity, 10) }))
    } : {
      shipping_address: checkoutForm.address || 'Lomé, Togo',
      payment_method: checkoutForm.payment_method,
      customer_note: checkoutForm.customer_note
    }

    const response = await orderService.create(payload)
    if (response.data && (response.data.order_id || response.data.order_number)) {
      const orderId = response.data.order_id || response.data.order_number
      cartStore.clearCart()
      closeDrawer()
      notifyOrderSuccess(response.data.order_number || orderId)
      router.push(`/commande-confirmee/${orderId}`)
    } else {
      throw new Error(response.data?.error || 'Erreur inattendue')
    }
  } catch (err) {
    notifyError('Erreur de commande', err.response?.data?.error || err.message || 'Impossible de créer la commande.')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.slide-right-enter-active {
  transition: opacity 0.3s ease;
}
.slide-right-leave-active {
  transition: opacity 0.25s ease;
}
.slide-right-enter-from,
.slide-right-leave-to {
  opacity: 0;
}
.slide-right-enter-active .w-screen {
  animation: slideInRight 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.slide-right-leave-active .w-screen {
  animation: slideOutRight 0.25s ease forwards;
}
@keyframes slideInRight {
  from { transform: translateX(100%); }
  to   { transform: translateX(0); }
}
@keyframes slideOutRight {
  from { transform: translateX(0); }
  to   { transform: translateX(100%); }
}
</style>
