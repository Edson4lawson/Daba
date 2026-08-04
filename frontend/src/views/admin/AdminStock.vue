<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="stock-header flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-daba-navy dark:text-white">Gestion du Stock</h1>
        <p class="text-sm text-daba-slate dark:text-daba-slate-dark">Ajustez les quantités et surveillez les alertes</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="stock-filter bg-daba-cream dark:bg-daba-dark-card rounded-2xl shadow-sm border border-daba-cream-alt dark:border-daba-dark-border p-4 transition-colors">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="relative">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-daba-slate-dark" />
          <input 
            v-model="filters.search" 
            placeholder="Rechercher un produit..." 
            class="w-full pl-10 pr-4 py-2 bg-daba-cream-alt dark:bg-daba-dark-card/50 border border-daba-cream-alt dark:border-daba-dark-border rounded-lg focus:outline-none focus:ring-2 focus:ring-daba-orange/20 transition-all dark:text-white"
          >
        </div>
        <select v-model="filters.category" class="px-4 py-2 bg-daba-cream-alt dark:bg-daba-dark-card/50 border border-daba-cream-alt dark:border-daba-dark-border rounded-lg focus:outline-none focus:ring-2 focus:ring-daba-orange/20 dark:text-white">
          <option value="">Toutes les catégories</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>
        <button 
          @click="showAlertsOnly = !showAlertsOnly"
          class="px-4 py-2 rounded-lg font-bold text-sm transition-all flex items-center justify-center gap-2"
          :class="showAlertsOnly ? 'bg-rose-500 text-white hover:bg-rose-600' : 'bg-daba-cream-alt dark:bg-daba-dark-card/30 text-daba-slate dark:text-daba-slate-dark hover:bg-daba-cream-alt dark:hover:bg-slate-900/30'"
        >
          <AlertTriangle class="w-4 h-4" />
          {{ showAlertsOnly ? 'Tous les produits' : 'Alertes uniquement' }}
        </button>
      </div>
    </div>

    <!-- Stock Table -->
    <div class="stock-table-container bg-daba-cream dark:bg-daba-dark-card rounded-2xl shadow-sm border border-daba-cream-alt dark:border-daba-dark-border overflow-hidden transition-colors">
      <div v-if="loading" class="flex items-center justify-center py-20">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-daba-cream-alt border-t-daba-orange"></div>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-daba-cream-alt dark:divide-daba-dark-border">
          <thead class="bg-daba-cream-alt dark:bg-daba-dark-card/50">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-bold text-daba-slate dark:text-daba-slate-dark uppercase tracking-wider">Produit</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-daba-slate dark:text-daba-slate-dark uppercase tracking-wider">Catégorie</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-daba-slate dark:text-daba-slate-dark uppercase tracking-wider">Stock actuel</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-daba-slate dark:text-daba-slate-dark uppercase tracking-wider">Statut</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-daba-slate dark:text-daba-slate-dark uppercase tracking-wider">Dernière mise à jour</th>
            </tr>
          </thead>
          <tbody class="bg-daba-cream dark:bg-daba-dark-card divide-y divide-daba-cream-alt dark:divide-daba-dark-border">
            <tr v-for="product in filteredProducts" :key="product.id" class="stock-row hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-bold text-daba-navy dark:text-white">{{ product.name }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-daba-cream-alt dark:bg-daba-dark-card text-daba-slate dark:text-daba-slate-dark">
                  {{ product.category_name }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <button @click="adjustStock(product, -1)" class="w-6 h-6 rounded bg-daba-cream-alt dark:bg-daba-dark-card text-daba-slate dark:text-daba-slate-dark hover:bg-daba-cream-alt hover:text-rose-600 transition-colors text-xs font-bold">−</button>
                  <input
                    v-if="editingStock === product.id"
                    v-model.number="tempStock"
                    @blur="saveStock(product)"
                    @keyup.enter="saveStock(product)"
                    @keyup.esc="editingStock = null"
                    type="number"
                    class="w-16 px-1 py-0.5 text-center text-xs font-bold border border-daba-cream-alt dark:border-daba-dark-border rounded dark:bg-daba-dark-card dark:text-white focus:outline-none focus:ring-2 focus:ring-daba-orange/20"
                    ref="stockInput"
                  >
                  <span
                    v-else
                    @click="startEditStock(product)"
                    class="text-xs cursor-pointer hover:text-daba-orange transition-colors font-bold"
                    :class="getStockClass(product.stock)"
                  >
                    {{ product.stock }}
                  </span>
                  <button @click="adjustStock(product, 1)" class="w-6 h-6 rounded bg-daba-cream-alt dark:bg-daba-dark-card text-daba-slate dark:text-daba-slate-dark hover:bg-daba-cream-alt hover:text-daba-green transition-colors text-xs font-bold">+</button>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStockBadgeClass(product.stock)" class="px-3 py-1 text-[10px] font-black uppercase tracking-wider rounded-full">
                  {{ getStockStatus(product.stock) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-xs text-daba-slate dark:text-daba-slate-dark">
                {{ formatDate(product.updated_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { Search, AlertTriangle } from 'lucide-vue-next'
import adminService from '@/services/adminService.js'
import { gsap } from 'gsap'

const products = ref([])
const categories = ref([])
const showAlertsOnly = ref(false)
const editingStock = ref(null)
const tempStock = ref(0)
const loading = ref(true)
const filters = ref({ search: '', category: '' })

const filteredProducts = computed(() => {
  let result = products.value.filter(product => {
    const matchesSearch = !filters.value.search || 
      product.name.toLowerCase().includes(filters.value.search.toLowerCase())
    const matchesCategory = !filters.value.category || 
      product.category_id == filters.value.category
    return matchesSearch && matchesCategory
  })
  
  if (showAlertsOnly.value) {
    result = result.filter(p => p.stock <= 10)
  }
  
  return result
})

const loadProducts = async () => {
  try {
    const response = await adminService.getProducts({ per_page: 200 })
    if (response.success) products.value = response.products
  } catch (err) { console.error(err) }
}

const loadCategories = async () => {
  try {
    const response = await adminService.getCategories()
    if (response.success) categories.value = response.categories
  } catch (err) { console.error(err) }
}

const startEditStock = (product) => {
  editingStock.value = product.id
  tempStock.value = product.stock
  nextTick(() => {
    const input = document.querySelector('input[type="number"]')
    if (input) input.focus()
  })
}

const adjustStock = async (product, delta) => {
  const newStock = Math.max(0, product.stock + delta)
  if (newStock === product.stock) return
  
  try {
    const res = await adminService.updateProduct({ id: product.id, stock: newStock })
    if (res.success) {
      product.stock = newStock
    }
  } catch (err) {
    console.error('Stock adjustment error:', err)
  }
}

const saveStock = async (product) => {
  if (tempStock.value === product.stock) {
    editingStock.value = null
    return
  }
  
  try {
    const res = await adminService.updateProduct({ id: product.id, stock: tempStock.value })
    if (res.success) {
      product.stock = tempStock.value
      editingStock.value = null
    }
  } catch (err) {
    console.error('Stock save error:', err)
  }
}

const getStockClass = (stock) => {
  return stock > 10 ? 'text-daba-green dark:text-daba-green' : 'text-rose-600 dark:text-rose-400'
}

const getStockBadgeClass = (stock) => {
  return stock > 10 ? 'bg-daba-cream-alt text-daba-green' : 'bg-daba-cream-alt text-rose-600'
}

const getStockStatus = (stock) => {
  return stock > 10 ? 'OK' : 'Bas'
}

const formatDate = (dateString) => {
  if (!dateString) return '—'
  return new Date(dateString).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

// --- GSAP Animation ---
let ctx = null

const animateTableRows = async () => {
  await nextTick()
  if (ctx) ctx.revert()
  
  ctx = gsap.context(() => {
    if (document.querySelector('.stock-row')) {
      gsap.from('.stock-row', {
        y: 20,
        opacity: 0,
        duration: 0.4,
        stagger: 0.05,
        ease: 'power2.out',
        clearProps: 'all'
      })
    }
  })
}

watch(filteredProducts, () => {
  animateTableRows()
})

onMounted(async () => {
  loading.value = true
  await nextTick()
  try {
    await Promise.all([
      loadProducts(),
      loadCategories()
    ])
  } catch (err) {
    console.error('Load error:', err)
  } finally {
    loading.value = false
  }
  
  const tl = gsap.timeline({ defaults: { ease: 'power3.out' } })
  
  if (document.querySelector('.stock-header')) {
    tl.from('.stock-header', { y: -30, opacity: 0, duration: 0.8 })
  }
  if (document.querySelector('.stock-filter')) {
    tl.from('.stock-filter', { y: -20, opacity: 0, duration: 0.6 }, '-=0.4')
  }
  if (document.querySelector('.stock-table-container')) {
    tl.from('.stock-table-container', { y: 30, opacity: 0, duration: 0.8 }, '-=0.4')
  }
  
  animateTableRows()
})

onUnmounted(() => {
  if (ctx) ctx.revert()
})
</script>
