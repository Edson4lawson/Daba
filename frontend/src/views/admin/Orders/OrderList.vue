<template>
  <div class="p-6">
    <!-- Header -->
    <div class="order-header flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion des Commandes</h1>
    </div>

    <!-- Filters -->
    <!-- Filters -->
    <div class="order-filters bg-white dark:bg-[rgb(43,44,43)] rounded-2xl shadow-sm p-4 mb-6 border border-slate-100 dark:border-slate-500">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input v-model="filters.search" placeholder="Rechercher par ID ou client..." class="border dark:border-slate-500 rounded-lg px-3 py-2 bg-slate-50 dark:bg-slate-900/50 dark:text-white focus:outline-none focus:ring-2 focus:ring-accent/20">
        <select v-model="filters.status" class="border dark:border-slate-500 rounded-lg px-3 py-2 bg-slate-50 dark:bg-slate-900/50 dark:text-white focus:outline-none focus:ring-2 focus:ring-accent/20">
          <option value="">Tous les statuts</option>
          <option value="pending">En attente</option>
          <option value="processing">En traitement</option>
          <option value="shipped">Expédiée</option>
          <option value="delivered">Livrée</option>
          <option value="cancelled">Annulée</option>
        </select>
        <input v-model="filters.date" type="date" class="border dark:border-slate-500 rounded-lg px-3 py-2 bg-slate-50 dark:bg-slate-900/50 dark:text-white focus:outline-none focus:ring-2 focus:ring-accent/20">
      </div>
    </div>

    <!-- Orders Table -->
    <!-- Orders Table -->
    <div class="order-table-container bg-white dark:bg-[rgb(43,44,43)] rounded-2xl shadow-sm overflow-hidden border border-slate-100 dark:border-slate-500">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-500">
        <thead class="bg-gray-50 dark:bg-slate-900/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Client</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Montant</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Statut</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-[rgb(43,44,43)] divide-y divide-gray-200 dark:divide-slate-500">
          <tr v-for="order in filteredOrders" :key="order.id" class="order-row hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
              #{{ order.id }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900 dark:text-white">{{ order.user_name }}</div>
              <div class="text-sm text-gray-500 dark:text-slate-400">{{ order.user_email }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ order.total_amount }}FCFA</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <select 
                v-model="order.status" 
                @change="updateOrderStatus(order.id, order.status)"
                :class="getStatusClass(order.status)"
                class="text-xs rounded-full px-2 py-1 border-0"
              >
                <option value="pending">En attente</option>
                <option value="processing">En traitement</option>
                <option value="shipped">Expédiée</option>
                <option value="delivered">Livrée</option>
                <option value="cancelled">Annulée</option>
              </select>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">
              {{ formatDate(order.created_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <router-link :to="`/admin/orders/${order.id}`" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                Détails
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Order Detail Modal -->
    <div v-if="selectedOrder" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-[rgb(43,44,43)] rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto border border-transparent dark:border-slate-500">
        <div class="sticky top-0 bg-white dark:bg-[rgb(43,44,43)] z-10 px-6 py-4 border-b border-gray-100 dark:border-slate-500 flex justify-between items-center">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white">Détails de la commande #{{ selectedOrder.id }}</h2>
          <button @click="selectedOrder = null" class="text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white">
            <X class="w-6 h-6" />
          </button>
        </div>
        
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-gray-50 dark:bg-slate-900/50 p-6 rounded-xl border border-gray-100 dark:border-slate-500">
              <h3 class="font-bold text-lg mb-4 text-gray-900 dark:text-white flex items-center">
                <User class="w-5 h-5 mr-2" /> Informations client
              </h3>
              <div class="space-y-2 text-sm">
                <p class="text-gray-500 dark:text-slate-400">Nom: <span class="font-medium text-gray-900 dark:text-white">{{ selectedOrder.user_name }}</span></p>
                <p class="text-gray-500 dark:text-slate-400">Email: <span class="font-medium text-gray-900 dark:text-white">{{ selectedOrder.user_email }}</span></p>
                <p class="text-gray-500 dark:text-slate-400">Téléphone: <span class="font-medium text-gray-900 dark:text-white">{{ selectedOrder.phone || 'N/A' }}</span></p>
              </div>
            </div>
            <div class="bg-gray-50 dark:bg-slate-900/50 p-6 rounded-xl border border-gray-100 dark:border-slate-500">
              <h3 class="font-bold text-lg mb-4 text-gray-900 dark:text-white flex items-center">
                <Package class="w-5 h-5 mr-2" /> Informations commande
              </h3>
              <div class="space-y-2 text-sm">
                <p class="text-gray-500 dark:text-slate-400">Date: <span class="font-medium text-gray-900 dark:text-white">{{ formatDate(selectedOrder.created_at) }}</span></p>
                <p class="text-gray-500 dark:text-slate-400">Statut: <span class="font-medium text-gray-900 dark:text-white">{{ selectedOrder.status }}</span></p>
                <p class="text-gray-500 dark:text-slate-400">Total: <span class="font-medium text-gray-900 dark:text-white">{{ selectedOrder.total_amount }}FCFA</span></p>
              </div>
            </div>
          </div>

          <div>
            <h3 class="font-bold text-lg mb-4 text-gray-900 dark:text-white">Articles commandés</h3>
            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-slate-500">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-500">
                <thead class="bg-gray-50 dark:bg-slate-900/50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-slate-400 uppercase">Produit</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-slate-400 uppercase">Prix unitaire</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-slate-400 uppercase">Quantité</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-slate-400 uppercase">Total</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-[rgb(43,44,43)] divide-y divide-gray-200 dark:divide-slate-500">
                  <tr v-for="item in selectedOrder.items" :key="item.id">
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ item.product_name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ item.price }}FCFA</td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ item.quantity }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ (item.price * item.quantity).toFixed(2) }}FCFA</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import adminService from '@/services/adminService.js'
import { X, User, Package } from 'lucide-vue-next'
import { gsap } from 'gsap'

const orders = ref([])
const selectedOrder = ref(null)
const filters = ref({ search: '', status: '', date: '' })

const filteredOrders = computed(() => {
  return orders.value.filter(order => {
    const matchesSearch = !filters.value.search || 
      order.id.toString().includes(filters.value.search) ||
      order.user_name.toLowerCase().includes(filters.value.search.toLowerCase())
    const matchesStatus = !filters.value.status || order.status === filters.value.status
    const matchesDate = !filters.value.date || 
      order.created_at.startsWith(filters.value.date)
    
    return matchesSearch && matchesStatus && matchesDate
  })
})

const loadOrders = async () => {
  try {
    const response = await adminService.getOrders()
    if (response.success) {
      orders.value = response.orders
    }
  } catch (error) {
    console.error('Erreur lors du chargement des commandes:', error)
  }
}

const viewOrderDetail = async (orderId) => {
  try {
    const response = await adminService.getOrderDetail(orderId)
    if (response.success) {
      selectedOrder.value = response.order
    }
  } catch (error) {
    console.error('Erreur lors du chargement des détails:', error)
  }
}

const updateOrderStatus = async (orderId, status) => {
  try {
    const response = await adminService.updateOrderStatus(orderId, status)
    if (response.success) {
      // Mettre à jour le statut localement
      const order = orders.value.find(o => o.id === orderId)
      if (order) order.status = status
    }
  } catch (error) {
    console.error('Erreur lors de la mise à jour du statut:', error)
  }
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    shipped: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
    delivered: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR')
}

// --- GSAP Animations ---
let ctx = null

const animateRows = async () => {
  await nextTick()
  if (ctx) ctx.revert()
  
  ctx = gsap.context(() => {
    gsap.from(".order-row", {
      y: 20,
      opacity: 0,
      duration: 0.4,
      stagger: 0.05,
      ease: "power2.out",
      clearProps: "all"
    })
  })
}

watch(filteredOrders, () => {
  animateRows()
})

onMounted(async () => {
  await loadOrders()
  
  const tl = gsap.timeline({ defaults: { ease: 'power3.out' } })
  
  tl.from(".order-header", {
    y: -30,
    opacity: 0,
    duration: 0.8
  })
  .from(".order-filters", {
    y: -20,
    opacity: 0,
    duration: 0.6
  }, "-=0.4")
  .from(".order-table-container", {
    y: 30,
    opacity: 0,
    duration: 0.8
  }, "-=0.4")
  
  animateRows()
})

onUnmounted(() => {
  if (ctx) ctx.revert()
})
</script>
<style scoped>
/* Styles spécifiques si nécessaire */
</style>


