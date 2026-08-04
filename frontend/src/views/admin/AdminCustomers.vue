<template>
  <div class="space-y-8">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-black text-daba-navy dark:text-white">Gestion des Clients</h1>
        <p class="text-sm text-daba-slate dark:text-daba-slate-dark">{{ customers.length }} clients enregistrés</p>
      </div>
      <div class="relative">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-daba-slate-dark" />
        <input v-model="searchQuery" type="text" placeholder="Rechercher un client..."
          class="pl-10 pr-4 py-2.5 bg-daba-cream dark:bg-daba-dark-card/50 border border-daba-cream-alt dark:border-daba-dark-border rounded-xl text-sm focus:ring-2 focus:ring-daba-orange/20 outline-none dark:text-white w-64" />
      </div>
    </div>

    <div class="bg-daba-cream dark:bg-daba-dark-card rounded-3xl border border-daba-cream-alt dark:border-daba-dark-border overflow-hidden shadow-sm">
      <div v-if="loading" class="flex items-center justify-center py-20">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-daba-cream-alt border-t-daba-orange"></div>
      </div>

      <table v-else class="w-full">
        <thead>
          <tr class="bg-daba-cream-alt dark:bg-daba-dark-card/30 text-left">
            <th class="px-6 py-4 text-[10px] font-black text-daba-slate dark:text-daba-slate-dark uppercase tracking-wider">Client</th>
            <th class="px-6 py-4 text-[10px] font-black text-daba-slate dark:text-daba-slate-dark uppercase tracking-wider">Email</th>
            <th class="px-6 py-4 text-[10px] font-black text-daba-slate dark:text-daba-slate-dark uppercase tracking-wider">Inscrit le</th>
            <th class="px-6 py-4 text-[10px] font-black text-daba-slate dark:text-daba-slate-dark uppercase tracking-wider">Rôle</th>
            <th class="px-6 py-4 text-[10px] font-black text-daba-slate dark:text-daba-slate-dark uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="customer in filteredCustomers" :key="customer.id" class="border-t border-daba-cream-alt dark:border-daba-dark-border hover:bg-daba-cream-alt/50 dark:hover:bg-slate-800/20 transition-colors">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3 cursor-pointer hover:bg-daba-cream-alt dark:hover:bg-slate-800/20 rounded-lg p-2 transition-colors" @click="viewCustomerOrders(customer)">
                <div class="w-9 h-9 rounded-full bg-daba-orange/10 flex items-center justify-center text-daba-orange text-xs font-bold uppercase">
                  {{ (customer.first_name || customer.email || '?').charAt(0) }}
                </div>
                <p class="text-sm font-bold text-daba-navy dark:text-white">{{ customer.first_name || '' }} {{ customer.last_name || '' }}</p>
              </div>
            </td>
            <td class="px-6 py-4 text-sm text-daba-slate dark:text-daba-slate-dark">{{ customer.email }}</td>
            <td class="px-6 py-4 text-xs text-daba-slate">{{ formatDate(customer.created_at) }}</td>
            <td class="px-6 py-4">
              <span :class="customer.role === 'admin' ? 'bg-daba-cream-alt text-daba-navy' : 'bg-daba-cream-alt text-daba-slate'"
                class="px-2 py-1 text-[10px] font-bold rounded-full uppercase">{{ customer.role || 'client' }}</span>
            </td>
            <td class="px-6 py-4">
              <button @click="toggleRole(customer)" class="text-xs font-bold text-daba-orange hover:text-daba-green transition-colors">
                {{ customer.role === 'admin' ? 'Retirer admin' : 'Rendre admin' }}
              </button>
            </td>
          </tr>
          <tr v-if="filteredCustomers.length === 0">
            <td colspan="5" class="px-6 py-12 text-center text-sm text-daba-slate-dark">Aucun client trouvé</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Customer Orders Modal -->
    <div v-if="selectedCustomer" class="fixed inset-0 bg-daba-dark-bg/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-daba-cream dark:bg-daba-dark-card rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col border border-transparent dark:border-daba-dark-border">
        <div class="px-8 py-6 border-b border-daba-cream-alt dark:border-daba-dark-border flex items-center justify-between bg-daba-cream-alt/50 dark:bg-daba-dark-card/20 flex-shrink-0">
          <div>
            <h2 class="text-xl font-bold text-daba-navy dark:text-white">Commandes de {{ selectedCustomer.first_name }} {{ selectedCustomer.last_name }}</h2>
            <p class="text-sm text-daba-slate dark:text-daba-slate-dark">{{ selectedCustomer.email }}</p>
          </div>
          <button @click="selectedCustomer = null" class="text-daba-slate-dark hover:text-daba-navy dark:hover:text-white">
            <X class="w-6 h-6" />
          </button>
        </div>

        <div class="flex-1 overflow-y-auto p-8">
          <div v-if="loadingOrders" class="flex items-center justify-center py-20">
            <div class="animate-spin rounded-full h-8 w-8 border-2 border-daba-cream-alt border-t-daba-orange"></div>
          </div>

          <div v-else-if="customerOrders.length === 0" class="text-center py-12 text-daba-slate-dark">
            Aucune commande pour ce client
          </div>

          <div v-else class="space-y-4">
            <div v-for="order in customerOrders" :key="order.id" class="bg-daba-cream-alt dark:bg-daba-dark-card/30 rounded-xl p-6 border border-daba-cream-alt dark:border-daba-dark-border">
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                  <span class="text-sm font-bold text-daba-navy dark:text-white">#{{ order.id }}</span>
                  <span :class="getStatusClass(order.status)" class="px-3 py-1 text-[10px] font-black uppercase tracking-wider rounded-full">
                    {{ order.status }}
                  </span>
                </div>
                <span class="text-sm font-bold text-daba-navy dark:text-white">{{ order.total_amount }} FCFA</span>
              </div>
              <div class="text-xs text-daba-slate dark:text-daba-slate-dark">
                {{ formatDate(order.created_at) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Search, X } from 'lucide-vue-next'
import adminService from '@/services/adminService'

const customers = ref([])
const loading = ref(true)
const searchQuery = ref('')
const selectedCustomer = ref(null)
const customerOrders = ref([])
const loadingOrders = ref(false)

const filteredCustomers = computed(() => {
  if (!searchQuery.value) return customers.value
  const q = searchQuery.value.toLowerCase()
  return customers.value.filter(c =>
    (c.first_name || '').toLowerCase().includes(q) ||
    (c.last_name || '').toLowerCase().includes(q) ||
    (c.email || '').toLowerCase().includes(q)
  )
})

const formatDate = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('fr-FR', { year: 'numeric', month: 'short', day: 'numeric' })
}

const toggleRole = async (customer) => {
  const newRole = customer.role === 'admin' ? 'client' : 'admin'
  try {
    await adminService.updateCustomerRole(customer.id, newRole)
    customer.role = newRole
  } catch (err) {
    console.error('Role update error:', err)
  }
}

const viewCustomerOrders = async (customer) => {
  selectedCustomer.value = customer
  loadingOrders.value = true
  customerOrders.value = []
  
  try {
    const result = await adminService.getOrders({ user_id: customer.id, per_page: 50 })
    if (result.success || result.orders) {
      customerOrders.value = result.orders || []
    }
  } catch (err) {
    console.error('Error loading customer orders:', err)
    customerOrders.value = []
  } finally {
    loadingOrders.value = false
  }
}

const getStatusClass = (status) => {
  const s = status.toLowerCase()
  if (s.includes('completed') || s.includes('livré') || s.includes('delivered')) return 'bg-daba-cream-alt text-daba-green'
  if (s.includes('pending') || s.includes('en attente')) return 'bg-daba-cream-alt text-daba-orange'
  if (s.includes('cancel')) return 'bg-daba-cream-alt text-rose-600'
  return 'bg-daba-cream-alt text-daba-orange'
}

onMounted(async () => {
  loading.value = true
  try {
    const result = await adminService.getCustomers()
    customers.value = result.customers || []
  } catch (err) {
    console.error('Load customers error:', err)
  } finally {
    loading.value = false
  }
})
</script>
