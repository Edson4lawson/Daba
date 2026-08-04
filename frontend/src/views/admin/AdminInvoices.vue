<template>
  <div class="p-6">
    <!-- Header -->
    <div class="invoice-header flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-daba-navy dark:text-white">Gestion des Factures</h1>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <div class="bg-daba-cream dark:bg-daba-dark-card rounded-2xl shadow-sm p-6 border border-daba-cream-alt dark:border-daba-dark-border">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm text-daba-slate dark:text-daba-slate-dark">Total Factures</span>
          <FileText class="w-5 h-5 text-daba-orange" />
        </div>
        <p class="text-2xl font-bold text-daba-navy dark:text-white">{{ kpis.totalInvoices }}</p>
      </div>
      <div class="bg-daba-cream dark:bg-daba-dark-card rounded-2xl shadow-sm p-6 border border-daba-cream-alt dark:border-daba-dark-border">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm text-daba-slate dark:text-daba-slate-dark">En Attente</span>
          <Clock class="w-5 h-5 text-daba-orange" />
        </div>
        <p class="text-2xl font-bold text-daba-navy dark:text-white">{{ kpis.pendingAmount.toLocaleString('fr-FR') }} FCFA</p>
      </div>
      <div class="bg-daba-cream dark:bg-daba-dark-card rounded-2xl shadow-sm p-6 border border-daba-cream-alt dark:border-daba-dark-border">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm text-daba-slate dark:text-daba-slate-dark">Payées</span>
          <CheckCircle class="w-5 h-5 text-daba-green" />
        </div>
        <p class="text-2xl font-bold text-daba-navy dark:text-white">{{ kpis.paidAmount.toLocaleString('fr-FR') }} FCFA</p>
      </div>
      <div class="bg-daba-cream dark:bg-daba-dark-card rounded-2xl shadow-sm p-6 border border-daba-cream-alt dark:border-daba-dark-border">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm text-daba-slate dark:text-daba-slate-dark">En Retard</span>
          <AlertTriangle class="w-5 h-5 text-rose-500" />
        </div>
        <p class="text-2xl font-bold text-daba-navy dark:text-white">{{ kpis.overdueCount }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="invoice-filters bg-daba-cream dark:bg-daba-dark-card rounded-2xl shadow-sm p-4 mb-6 border border-daba-cream-alt dark:border-daba-dark-border">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <input v-model="filters.search" placeholder="Rechercher par N° facture ou client..." class="border dark:border-daba-dark-border rounded-lg px-3 py-2 bg-daba-cream-alt dark:bg-daba-dark-card/50 dark:text-white focus:outline-none focus:ring-2 focus:ring-daba-orange/20">
        <select v-model="filters.status" class="border dark:border-daba-dark-border rounded-lg px-3 py-2 bg-daba-cream-alt dark:bg-daba-dark-card/50 dark:text-white focus:outline-none focus:ring-2 focus:ring-daba-orange/20">
          <option value="">Tous les statuts</option>
          <option value="pending">En attente</option>
          <option value="paid">Payée</option>
          <option value="cancelled">Annulée</option>
        </select>
        <input v-model="filters.dateFrom" type="date" placeholder="Date début" class="border dark:border-daba-dark-border rounded-lg px-3 py-2 bg-daba-cream-alt dark:bg-daba-dark-card/50 dark:text-white focus:outline-none focus:ring-2 focus:ring-daba-orange/20">
        <input v-model="filters.dateTo" type="date" placeholder="Date fin" class="border dark:border-daba-dark-border rounded-lg px-3 py-2 bg-daba-cream-alt dark:bg-daba-dark-card/50 dark:text-white focus:outline-none focus:ring-2 focus:ring-daba-orange/20">
      </div>
    </div>

    <!-- Invoices Table -->
    <div class="invoice-table-container bg-daba-cream dark:bg-daba-dark-card rounded-2xl shadow-sm overflow-hidden border border-daba-cream-alt dark:border-daba-dark-border">
      <div v-if="loading" class="flex items-center justify-center py-20">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-daba-cream-alt border-t-daba-orange"></div>
      </div>
      <div v-else-if="filteredInvoices.length === 0" class="flex items-center justify-center py-20">
        <p class="text-daba-slate dark:text-daba-slate-dark text-sm italic">Aucune facture pour le moment</p>
      </div>
      <table v-else class="min-w-full divide-y divide-daba-cream-alt dark:divide-daba-dark-border">
        <thead class="bg-daba-cream-alt dark:bg-daba-dark-card/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-daba-slate dark:text-daba-slate-dark uppercase">N° Facture</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-daba-slate dark:text-daba-slate-dark uppercase">Commande</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-daba-slate dark:text-daba-slate-dark uppercase">Client</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-daba-slate dark:text-daba-slate-dark uppercase">Montant</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-daba-slate dark:text-daba-slate-dark uppercase">Statut</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-daba-slate dark:text-daba-slate-dark uppercase">Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-daba-slate dark:text-daba-slate-dark uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-daba-cream dark:bg-daba-dark-card divide-y divide-daba-cream-alt dark:divide-daba-dark-border">
          <tr v-for="invoice in filteredInvoices" :key="invoice.id" class="invoice-row hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-daba-navy dark:text-white">
              {{ invoice.invoice_number }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <router-link :to="`/admin/orders/${invoice.order_id}`" class="text-sm text-daba-orange dark:text-daba-orange hover:text-daba-navy dark:hover:text-white">
                #{{ invoice.order_id }}
              </router-link>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-daba-navy dark:text-white">{{ invoice.first_name }} {{ invoice.last_name }}</div>
              <div class="text-sm text-daba-slate dark:text-daba-slate-dark">{{ invoice.phone }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-daba-navy dark:text-white">{{ invoice.amount.toLocaleString('fr-FR') }} FCFA</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getStatusClass(invoice.status)" class="px-3 py-1 text-[10px] font-black uppercase tracking-wider rounded-full">
                {{ invoice.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-daba-slate dark:text-daba-slate-dark">
              {{ formatDate(invoice.created_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <button @click="viewInvoiceDetail(invoice)" class="text-daba-orange dark:text-daba-orange hover:text-daba-navy dark:hover:text-white mr-3">
                Détails
              </button>
              <button 
                v-if="invoice.status === 'pending' && (userRole === 'admin' || userRole === 'comptable')"
                @click="markAsPaid(invoice.id)"
                class="text-daba-green dark:text-daba-green hover:text-daba-navy dark:hover:text-white"
              >
                Marquer payée
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Invoice Detail Modal -->
    <div v-if="selectedInvoice" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-daba-cream dark:bg-daba-dark-card rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto border border-transparent dark:border-daba-dark-border">
        <div class="sticky top-0 bg-daba-cream dark:bg-daba-dark-card z-10 px-6 py-4 border-b border-daba-cream-alt dark:border-daba-dark-border flex justify-between items-center">
          <h2 class="text-xl font-bold text-daba-navy dark:text-white">Détails de la facture {{ selectedInvoice.invoice_number }}</h2>
          <button @click="selectedInvoice = null" class="text-daba-slate dark:text-daba-slate-dark hover:text-daba-navy dark:hover:text-white">
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
                <p class="text-daba-slate dark:text-daba-slate-dark">Nom: <span class="font-medium text-daba-navy dark:text-white">{{ selectedInvoice.first_name }} {{ selectedInvoice.last_name }}</span></p>
                <p class="text-daba-slate dark:text-daba-slate-dark">Téléphone: <span class="font-medium text-daba-navy dark:text-white">{{ selectedInvoice.phone }}</span></p>
              </div>
            </div>
            <div class="bg-daba-cream-alt dark:bg-daba-dark-card/50 p-6 rounded-xl border border-daba-cream-alt dark:border-daba-dark-border">
              <h3 class="font-bold text-lg mb-4 text-daba-navy dark:text-white flex items-center">
                <FileText class="w-5 h-5 mr-2" /> Informations facture
              </h3>
              <div class="space-y-2 text-sm">
                <p class="text-daba-slate dark:text-daba-slate-dark">N° Facture: <span class="font-medium text-daba-navy dark:text-white">{{ selectedInvoice.invoice_number }}</span></p>
                <p class="text-daba-slate dark:text-daba-slate-dark">Commande liée: <router-link :to="`/admin/orders/${selectedInvoice.order_id}`" class="font-medium text-daba-orange dark:text-daba-orange hover:underline">#{{ selectedInvoice.order_id }}</router-link></p>
                <p class="text-daba-slate dark:text-daba-slate-dark">Statut: <span :class="getStatusClass(selectedInvoice.status)" class="px-2 py-0.5 text-[10px] font-black uppercase rounded-full inline-block">{{ selectedInvoice.status }}</span></p>
                <p class="text-daba-slate dark:text-daba-slate-dark">Montant: <span class="font-medium text-daba-navy dark:text-white">{{ selectedInvoice.amount.toLocaleString('fr-FR') }} FCFA</span></p>
                <p class="text-daba-slate dark:text-daba-slate-dark">Date: <span class="font-medium text-daba-navy dark:text-white">{{ formatDate(selectedInvoice.created_at) }}</span></p>
              </div>
            </div>
          </div>

          <div v-if="selectedInvoice.status === 'pending' && (userRole === 'admin' || userRole === 'comptable')" class="flex justify-end">
            <button @click="markAsPaid(selectedInvoice.id)" class="bg-daba-green text-white px-6 py-2 rounded-lg hover:bg-daba-green/80 transition-colors">
              Marquer comme payée
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth'
import adminService from '@/services/adminService.js'
import { X, User, FileText, Clock, CheckCircle, AlertTriangle } from 'lucide-vue-next'
import { gsap } from 'gsap'

const authStore = useAuthStore()
const userRole = computed(() => authStore.user?.role)

const invoices = ref([])
const selectedInvoice = ref(null)
const filters = ref({ search: '', status: '', dateFrom: '', dateTo: '' })
const loading = ref(true)

const kpis = computed(() => {
  const totalInvoices = invoices.value.length
  const pendingInvoices = invoices.value.filter(i => i.status === 'pending')
  const paidInvoices = invoices.value.filter(i => i.status === 'paid')
  
  const pendingAmount = pendingInvoices.reduce((sum, i) => sum + parseFloat(i.amount), 0)
  const paidAmount = paidInvoices.reduce((sum, i) => sum + parseFloat(i.amount), 0)
  
  // Factures en retard (pending depuis plus de 30 jours)
  const thirtyDaysAgo = new Date()
  thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30)
  const overdueCount = pendingInvoices.filter(i => new Date(i.created_at) < thirtyDaysAgo).length
  
  return {
    totalInvoices,
    pendingAmount,
    paidAmount,
    overdueCount
  }
})

const filteredInvoices = computed(() => {
  return invoices.value.filter(invoice => {
    const matchesSearch = !filters.value.search || 
      invoice.invoice_number.toLowerCase().includes(filters.value.search.toLowerCase()) ||
      (invoice.first_name && invoice.first_name.toLowerCase().includes(filters.value.search.toLowerCase())) ||
      (invoice.last_name && invoice.last_name.toLowerCase().includes(filters.value.search.toLowerCase())) ||
      (invoice.phone && invoice.phone.includes(filters.value.search))
    const matchesStatus = !filters.value.status || invoice.status === filters.value.status
    const matchesDateFrom = !filters.value.dateFrom || 
      new Date(invoice.created_at) >= new Date(filters.value.dateFrom)
    const matchesDateTo = !filters.value.dateTo || 
      new Date(invoice.created_at) <= new Date(filters.value.dateTo + ' 23:59:59')
    
    return matchesSearch && matchesStatus && matchesDateFrom && matchesDateTo
  })
})

const loadInvoices = async () => {
  try {
    const response = await adminService.getInvoices()
    if (response.success) {
      invoices.value = response.invoices
    }
  } catch (error) {
    console.error('Erreur lors du chargement des factures:', error)
  }
}

const viewInvoiceDetail = (invoice) => {
  selectedInvoice.value = invoice
}

const markAsPaid = async (invoiceId) => {
  try {
    const response = await adminService.updateInvoiceStatus(invoiceId, 'paid')
    if (response.success) {
      // Mettre à jour le statut localement
      const invoice = invoices.value.find(i => i.id === invoiceId)
      if (invoice) invoice.status = 'paid'
      
      // Fermer le modal si ouvert
      if (selectedInvoice.value && selectedInvoice.value.id === invoiceId) {
        selectedInvoice.value = null
      }
    }
  } catch (error) {
    console.error('Erreur lors de la mise à jour du statut:', error)
  }
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-daba-cream-alt text-daba-orange dark:bg-daba-dark-card/30 dark:text-daba-orange',
    paid: 'bg-daba-cream-alt text-daba-green dark:bg-daba-dark-card/30 dark:text-daba-green',
    cancelled: 'bg-daba-cream-alt text-rose-600 dark:bg-daba-dark-card/30 dark:text-rose-400'
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
    if (document.querySelector(".invoice-row")) {
      gsap.from(".invoice-row", {
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

watch(filteredInvoices, () => {
  animateRows()
})

onMounted(async () => {
  loading.value = true
  await nextTick()
  try {
    await loadInvoices()
  } catch (err) {
    console.error('Load error:', err)
  } finally {
    loading.value = false
  }
  
  const tl = gsap.timeline({ defaults: { ease: 'power3.out' } })
  
  if (document.querySelector(".invoice-header")) {
    tl.from(".invoice-header", { y: -30, opacity: 0, duration: 0.8 })
  }
  if (document.querySelector(".invoice-filters")) {
    tl.from(".invoice-filters", { y: -20, opacity: 0, duration: 0.6 }, "-=0.4")
  }
  if (document.querySelector(".invoice-table-container")) {
    tl.from(".invoice-table-container", { y: 30, opacity: 0, duration: 0.8 }, "-=0.4")
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
