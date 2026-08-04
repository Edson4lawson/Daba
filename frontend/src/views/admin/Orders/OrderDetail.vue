<template>
  <div class="p-6">
    <div class="order-detail-header flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Commande #{{ orderId }}</h1>
      <router-link to="/admin/orders" class="text-gray-600 dark:text-slate-400 hover:text-gray-800 dark:hover:text-white">
        â† Retour aux commandes
      </router-link>
    </div>

    <div v-if="order" class="space-y-6">
      <!-- Status et actions -->
      <div class="order-card-anim bg-white dark:bg-[rgb(43,44,43)] rounded-2xl shadow-sm border border-slate-100 dark:border-slate-500 p-6">
        <div class="flex justify-between items-center">
          <div>
            <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">Statut de la commande</h3>
            <select 
              v-model="order.status" 
              @change="updateStatus"
              :class="getStatusClass(order.status)"
              class="px-3 py-2 rounded-lg border-0 text-sm font-medium"
            >
              <option value="pending">En attente</option>
              <option value="processing">En traitement</option>
              <option value="shipped">Expédiée</option>
              <option value="delivered">Livrée</option>
              <option value="cancelled">Annulée</option>
            </select>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-500 dark:text-slate-400">Total de la commande</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ order.total_amount }}FCFA</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Informations client -->
        <div class="order-card-anim bg-white dark:bg-[rgb(43,44,43)] rounded-2xl shadow-sm border border-slate-100 dark:border-slate-500 p-6">
          <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white flex items-center gap-2">
            <User class="w-5 h-5 text-red-600" />
            Informations client
          </h3>
          <div class="space-y-4">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-gray-50 dark:bg-slate-800 rounded-lg text-gray-400">
                <User class="w-4 h-4" />
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Nom complet</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ order.user_name }}</p>
              </div>
            </div>
            
            <div class="flex items-center gap-3">
              <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-blue-600 dark:text-blue-400">
                <Mail class="w-4 h-4" />
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Email</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ order.user_email }}</p>
              </div>
            </div>
            
            <div class="flex items-center gap-3">
              <div class="p-2 bg-green-50 dark:bg-green-900/20 rounded-lg text-green-600 dark:text-green-400">
                <Phone class="w-4 h-4" />
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Téléphone</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ order.phone || 'Non renseigné' }}</p>
              </div>
            </div>

            <div v-if="order.shipping_address" class="flex items-start gap-3">
              <div class="p-2 bg-purple-50 dark:bg-purple-900/20 rounded-lg text-purple-600 dark:text-purple-400">
                <MapPin class="w-4 h-4" />
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Adresse de livraison</p>
                <p class="font-medium whitespace-pre-line text-gray-900 dark:text-white">{{ order.shipping_address }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Informations commande -->
        <div class="order-card-anim bg-white dark:bg-[rgb(43,44,43)] rounded-2xl shadow-sm border border-slate-100 dark:border-slate-500 p-6">
          <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white flex items-center gap-2">
            <Clock class="w-5 h-5 text-red-600" />
            Détails de la commande
          </h3>
          <div class="space-y-4">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-orange-50 dark:bg-orange-900/20 rounded-lg text-orange-600 dark:text-orange-400">
                <Calendar class="w-4 h-4" />
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Date de commande</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ formatDate(order.created_at) }}</p>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <div class="p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg text-indigo-600 dark:text-indigo-400">
                <CreditCard class="w-4 h-4" />
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Méthode de paiement</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ order.payment_method || 'Non spécifiée' }}</p>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <div class="p-2 bg-gray-50 dark:bg-slate-800 rounded-lg text-gray-400">
                <Clock class="w-4 h-4" />
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Statut du paiement</p>
                <span :class="getPaymentStatusClass(order.payment_status)" class="px-2 py-1 text-xs rounded-full">
                  {{ getPaymentStatusText(order.payment_status) }}
                </span>
              </div>
            </div>
            
            <div v-if="order.notes" class="flex items-start gap-3">
              <div class="p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg text-yellow-600 dark:text-yellow-400">
                <AlertCircle class="w-4 h-4" />
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Notes</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ order.notes }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Articles commandés -->
      <div class="order-card-anim bg-white dark:bg-[rgb(43,44,43)] rounded-2xl shadow-sm border border-slate-100 dark:border-slate-500 p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Articles commandés</h3>
        <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-500">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-500">
            <thead class="bg-gray-50 dark:bg-slate-900/50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Produit</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Prix unitaire</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Quantité</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Total</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-[rgb(43,44,43)] divide-y divide-gray-200 dark:divide-slate-500">
              <tr v-for="item in order.items" :key="item.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <img 
                      :src="getImageUrl(item.product_image)" 
                      :alt="item.product_name" 
                      class="h-12 w-12 rounded object-cover"
                    >
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900 dark:text-white">{{ item.product_name }}</div>
                      <div class="text-sm text-gray-500 dark:text-slate-400">ID: {{ item.product_id }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ item.price }}FCFA</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ item.quantity }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                  {{ (item.price * item.quantity).toFixed(2) }}FCFA
                </td>
              </tr>
            </tbody>
            <tfoot class="bg-gray-50 dark:bg-slate-900/50">
              <tr>
                <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900 dark:text-white">Total:</td>
                <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">{{ order.total_amount }}FCFA</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Historique des statuts -->
      <div class="bg-white dark:bg-[rgb(43,44,43)] rounded-2xl shadow-sm border border-slate-100 dark:border-slate-500 p-6" v-if="order.status_history?.length">
        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Historique des statuts</h3>
        <div class="space-y-3">
          <div v-for="history in order.status_history" :key="history.id" class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-slate-500 last:border-b-0">
            <div class="flex items-center space-x-3">
              <span :class="getStatusClass(history.status)" class="px-2 py-1 text-xs rounded-full">
                {{ history.status }}
              </span>
              <span class="text-sm text-gray-600 dark:text-slate-400">{{ history.notes || 'Changement de statut' }}</span>
            </div>
            <span class="text-sm text-gray-500 dark:text-slate-400">{{ formatDate(history.created_at) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading state -->
    <div v-else class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import adminService from '@/services/adminService.js'
import { gsap } from 'gsap'
import { Mail, Phone, User, MapPin, Calendar, CreditCard, Clock, AlertCircle } from 'lucide-vue-next'
import { getProductImageUrl } from '@/utils/imageHelper'

const route = useRoute()
const orderId = route.params.id

const order = ref(null)

const loadOrder = async () => {
  try {
    const response = await adminService.getOrderDetail(orderId)
    if (response.success) {
      order.value = response.order
    }
  } catch (error) {
    console.error('Erreur lors du chargement de la commande:', error)
  }
}

const updateStatus = async () => {
  try {
    const response = await adminService.updateOrderStatus(orderId, order.value.status)
    if (response.success) {
      // Recharger pour obtenir l'historique mis à jour
      await loadOrder()
    }
  } catch (error) {
    console.error('Erreur lors de la mise À  jour du statut:', error)
  }
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    shipped: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
    delivered: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'
}

const getPaymentStatusClass = (status) => {
  const classes = {
    pending: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    refunded: 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusText = (status) => {
  const texts = {
    pending: 'En attente',
    paid: 'Payé',
    failed: 'Échoué',
    refunded: 'Remboursé'
  }
  return texts[status] || status
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

/**
 * Construit l'URL absolue de l'image
 */
const getImageUrl = (url) => getProductImageUrl(url)

// --- GSAP Animation ---
let ctx = null

const runAnimations = async () => {
  await nextTick()
  if (ctx) ctx.revert()
  
  ctx = gsap.context(() => {
    const tl = gsap.timeline({ defaults: { ease: 'power3.out' } })
    
    tl.from(".order-detail-header", {
      y: -30,
      opacity: 0,
      duration: 0.8
    })
    .from(".order-card-anim", {
      y: 30,
      opacity: 0,
      stagger: 0.1,
      duration: 0.6
    }, "-=0.4")
  })
}

watch(order, (newVal) => {
  if (newVal) {
    runAnimations()
  }
})

onMounted(() => {
  loadOrder()
})

onUnmounted(() => {
  if (ctx) ctx.revert()
})
</script>
<style scoped>
/* Styles spécifiques si nécessaire */
</style>


