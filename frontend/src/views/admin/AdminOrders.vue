<template>
  <div class="p-6">
    <!-- Header -->
    <div class="order-header flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-daba-navy dark:text-white">Gestion des Commandes</h1>
    </div>

    <!-- Filters -->
    <!-- Filters -->
    <div class="order-filters bg-daba-cream dark:bg-daba-dark-card rounded-2xl shadow-sm p-4 mb-6 border border-daba-cream-alt dark:border-daba-dark-border">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <input v-model="filters.search" placeholder="Rechercher par ID ou client..." class="border dark:border-daba-dark-border rounded-lg px-3 py-2 bg-daba-cream-alt dark:bg-daba-dark-card/50 dark:text-white focus:outline-none focus:ring-2 focus:ring-daba-orange/20">
        <select v-model="filters.status" class="border dark:border-daba-dark-border rounded-lg px-3 py-2 bg-daba-cream-alt dark:bg-daba-dark-card/50 dark:text-white focus:outline-none focus:ring-2 focus:ring-daba-orange/20">
          <option value="">Tous les statuts</option>
          <option value="pending">En attente</option>
          <option value="processing">En traitement</option>
          <option value="shipped">Expédiée</option>
          <option value="completed">Livrée</option>
          <option value="cancelled">Annulée</option>
        </select>
        <select v-model="filters.canal" class="border dark:border-daba-dark-border rounded-lg px-3 py-2 bg-daba-cream-alt dark:bg-daba-dark-card/50 dark:text-white focus:outline-none focus:ring-2 focus:ring-daba-orange/20">
          <option value="">Tous les canaux</option>
          <option value="site">Site web</option>
          <option value="whatsapp">WhatsApp</option>
        </select>
        <input v-model="filters.date" type="date" class="border dark:border-daba-dark-border rounded-lg px-3 py-2 bg-daba-cream-alt dark:bg-daba-dark-card/50 dark:text-white focus:outline-none focus:ring-2 focus:ring-daba-orange/20">
      </div>
    </div>

    <!-- Orders Table -->
    <!-- Orders Table -->
    <div class="order-table-container bg-daba-cream dark:bg-daba-dark-card rounded-2xl shadow-sm overflow-hidden border border-daba-cream-alt dark:border-daba-dark-border">
      <div v-if="loading" class="flex items-center justify-center py-20">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-daba-cream-alt border-t-daba-orange"></div>
      </div>
      <table v-else class="min-w-full divide-y divide-daba-cream-alt dark:divide-daba-dark-border">
        <thead class="bg-daba-cream-alt dark:bg-daba-dark-card/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-daba-slate dark:text-daba-slate-dark uppercase">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-daba-slate dark:text-daba-slate-dark uppercase">Canal</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-daba-slate dark:text-daba-slate-dark uppercase">Client</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-daba-slate dark:text-daba-slate-dark uppercase">Montant</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-daba-slate dark:text-daba-slate-dark uppercase">Statut</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-daba-slate dark:text-daba-slate-dark uppercase">Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-daba-slate dark:text-daba-slate-dark uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-daba-cream dark:bg-daba-dark-card divide-y divide-daba-cream-alt dark:divide-daba-dark-border">
          <tr v-for="order in filteredOrders" :key="order.id" class="order-row hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-daba-navy dark:text-white">
              #{{ order.id }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getCanalClass(order.canal)" class="px-3 py-1 text-[10px] font-black uppercase tracking-wider rounded-full flex items-center gap-1 w-fit">
                <component :is="getCanalIcon(order.canal)" class="w-3 h-3" />
                {{ order.canal || 'site' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-daba-navy dark:text-white">{{ order.user_name }}</div>
              <div class="text-sm text-daba-slate dark:text-daba-slate-dark">{{ order.user_email }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-daba-navy dark:text-white">{{ order.total_amount }}FCFA</td>
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
                <option value="completed">Livrée</option>
                <option value="cancelled">Annulée</option>
              </select>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-daba-slate dark:text-daba-slate-dark">
              {{ formatDate(order.created_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <router-link :to="`/admin/orders/${order.id}`" class="text-daba-orange dark:text-daba-orange hover:text-daba-navy dark:hover:text-white">
                Détails
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Order Detail Modal -->
    <div v-if="selectedOrder" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-daba-cream dark:bg-daba-dark-card rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto border border-transparent dark:border-daba-dark-border">
        <div class="sticky top-0 bg-daba-cream dark:bg-daba-dark-card z-10 px-6 py-4 border-b border-daba-cream-alt dark:border-daba-dark-border flex justify-between items-center">
          <h2 class="text-xl font-bold text-daba-navy dark:text-white">Détails de la commande #{{ selectedOrder.id }}</h2>
          <button @click="selectedOrder = null" class="text-daba-slate dark:text-daba-slate-dark hover:text-daba-navy dark:hover:text-white">
            <X class="w-6 h-6" />
          </button>
        </div>
        
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-daba-cream-alt dark:bg-daba-dark-card/50 p-6 rounded-xl border border-daba-cream-alt dark:border-daba-dark-border">
              <h3 class="font-bold text-lg mb-4 text-daba-navy dark:text-white flex items-center">
                <User class="w-5 h-5 mr-2" /> Informations client
              </h3>
              <div class="space-y-2 text-sm">
                <p class="text-daba-slate dark:text-daba-slate-dark">Nom: <span class="font-medium text-daba-navy dark:text-white">{{ selectedOrder.user_name }}</span></p>
                <p class="text-daba-slate dark:text-daba-slate-dark">Email: <span class="font-medium text-daba-navy dark:text-white">{{ selectedOrder.user_email }}</span></p>
                <p class="text-daba-slate dark:text-daba-slate-dark">Téléphone: <span class="font-medium text-daba-navy dark:text-white">{{ selectedOrder.phone || 'N/A' }}</span></p>
              </div>
            </div>
            <div class="bg-daba-cream-alt dark:bg-daba-dark-card/50 p-6 rounded-xl border border-daba-cream-alt dark:border-daba-dark-border">
              <h3 class="font-bold text-lg mb-4 text-daba-navy dark:text-white flex items-center">
                <Package class="w-5 h-5 mr-2" /> Informations commande
              </h3>
              <div class="space-y-2 text-sm">
                <p class="text-daba-slate dark:text-daba-slate-dark">Date: <span class="font-medium text-daba-navy dark:text-white">{{ formatDate(selectedOrder.created_at) }}</span></p>
                <p class="text-daba-slate dark:text-daba-slate-dark">Canal: <span :class="getCanalClass(selectedOrder.canal)" class="px-2 py-0.5 text-[10px] font-black uppercase rounded-full inline-flex items-center gap-1"><component :is="getCanalIcon(selectedOrder.canal)" class="w-3 h-3" /> {{ selectedOrder.canal || 'site' }}</span></p>
                <p class="text-daba-slate dark:text-daba-slate-dark">Statut: <span class="font-medium text-daba-navy dark:text-white">{{ selectedOrder.status }}</span></p>
                <p class="text-daba-slate dark:text-daba-slate-dark">Total: <span class="font-medium text-daba-navy dark:text-white">{{ selectedOrder.total_amount }}FCFA</span></p>
              </div>
            </div>
          </div>

          <div>
            <h3 class="font-bold text-lg mb-4 text-daba-navy dark:text-white">Articles commandés</h3>
            <div class="overflow-x-auto rounded-xl border border-daba-cream-alt dark:border-daba-dark-border">
              <table class="min-w-full divide-y divide-daba-cream-alt dark:divide-daba-dark-border">
                <thead class="bg-daba-cream-alt dark:bg-daba-dark-card/50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-daba-slate dark:text-daba-slate-dark uppercase">Produit</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-daba-slate dark:text-daba-slate-dark uppercase">Prix unitaire</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-daba-slate dark:text-daba-slate-dark uppercase">Quantité</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-daba-slate dark:text-daba-slate-dark uppercase">Total</th>
                  </tr>
                </thead>
                <tbody class="bg-daba-cream dark:bg-daba-dark-card divide-y divide-daba-cream-alt dark:divide-daba-dark-border">
                  <tr v-for="item in selectedOrder.items" :key="item.id">
                    <td class="px-4 py-3 text-sm text-daba-navy dark:text-white">{{ item.product_name }}</td>
                    <td class="px-4 py-3 text-sm text-daba-navy dark:text-white">{{ item.price }}FCFA</td>
                    <td class="px-4 py-3 text-sm text-daba-navy dark:text-white">{{ item.quantity }}</td>
                    <td class="px-4 py-3 text-sm text-daba-navy dark:text-white">{{ (item.price * item.quantity).toFixed(2) }}FCFA</td>
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
import { X, User, Package, Globe, MessageSquare } from 'lucide-vue-next'
import { gsap } from 'gsap'

const orders = ref([])
const selectedOrder = ref(null)
const filters = ref({ search: '', status: '', date: '', canal: '' })
const loading = ref(true)

const filteredOrders = computed(() => {
  return orders.value.filter(order => {
    const matchesSearch = !filters.value.search || 
      order.id.toString().includes(filters.value.search) ||
      order.user_name.toLowerCase().includes(filters.value.search.toLowerCase())
    const matchesStatus = !filters.value.status || order.status === filters.value.status
    const matchesDate = !filters.value.date || 
      order.created_at.startsWith(filters.value.date)
    const matchesCanal = !filters.value.canal || order.canal === filters.value.canal
    
    return matchesSearch && matchesStatus && matchesDate && matchesCanal
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
    pending: 'bg-daba-cream-alt text-daba-orange dark:bg-daba-dark-card/30 dark:text-daba-orange',
    processing: 'bg-daba-cream-alt text-daba-orange dark:bg-daba-dark-card/30 dark:text-daba-orange',
    shipped: 'bg-daba-cream-alt text-daba-navy dark:bg-daba-dark-card/30 dark:text-daba-navy',
    completed: 'bg-daba-cream-alt text-daba-green dark:bg-daba-dark-card/30 dark:text-daba-green',
    cancelled: 'bg-daba-cream-alt text-rose-600 dark:bg-daba-dark-card/30 dark:text-rose-400'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR')
}

const getCanalClass = (canal) => {
  const classes = {
    site: 'bg-daba-cream-alt text-daba-orange dark:bg-daba-dark-card/30 dark:text-daba-orange',
    whatsapp: 'bg-daba-cream-alt text-daba-green dark:bg-daba-dark-card/30 dark:text-daba-green'
  }
  return classes[canal] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'
}

const getCanalIcon = (canal) => {
  const icons = {
    site: Globe,
    whatsapp: MessageSquare
  }
  return icons[canal] || Globe
}

// --- GSAP Animations ---
let ctx = null

const animateRows = async () => {
  await nextTick()
  if (ctx) ctx.revert()
  
  ctx = gsap.context(() => {
    if (document.querySelector(".order-row")) {
      gsap.from(".order-row", {
        y: 20,
        opacity: 0,
        duration: 0.4,
        stagger: 0.05,
        ease: "power2.out",
        clearProps: "all"
      })
    }
  })
}

watch(filteredOrders, () => {
  animateRows()
})

onMounted(async () => {
  loading.value = true
  // Attendre que le DOM soit prêt pour GSAP
  await nextTick()
  try {
    await loadOrders()
  } catch (err) {
    console.error('Load error:', err)
  } finally {
    loading.value = false
  }
  
  const tl = gsap.timeline({ defaults: { ease: 'power3.out' } })
  
  if (document.querySelector(".order-header")) {
    tl.from(".order-header", { y: -30, opacity: 0, duration: 0.8 })
  }
  if (document.querySelector(".order-filters")) {
    tl.from(".order-filters", { y: -20, opacity: 0, duration: 0.6 }, "-=0.4")
  }
  if (document.querySelector(".order-table-container")) {
    tl.from(".order-table-container", { y: 30, opacity: 0, duration: 0.8 }, "-=0.4")
  }
  
  animateRows()
})

onUnmounted(() => {
  if (ctx) ctx.revert()
})
</script>
<style scoped>
/* Styles spécifiques si nécessaire */
</style>


