<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="product-header flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Catalogue Produits</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Gérez vos produits, stocks et promotions</p>
      </div>
      <button 
        @click="showCreateForm = true" 
        class="inline-flex items-center px-4 py-2 bg-accent dark:bg-accent text-white text-sm font-bold rounded-xl hover:bg-slate-800 dark:hover:bg-accent/80 transition-all"
      >
        <Plus class="w-4 h-4 mr-2" />
        Nouveau Produit
      </button>
    </div>

    <!-- Filters -->
    <div class="product-filter bg-white dark:bg-[rgb(43,44,43)] rounded-2xl shadow-sm border border-slate-100 dark:border-slate-500 p-4 transition-colors">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="relative">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
          <input 
            v-model="filters.search" 
            placeholder="Rechercher un produit..." 
            class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent/20 transition-all dark:text-white"
          >
        </div>
        <select v-model="filters.category" class="px-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent/20 dark:text-white">
          <option value="">Toutes les catégories</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>
        <select v-model="filters.status" class="px-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent/20 dark:text-white">
          <option value="">Tous les statuts</option>
          <option value="active">Actif</option>
          <option value="inactive">Inactif</option>
        </select>
        <div class="flex items-center space-x-2 px-2">
          <label class="flex items-center cursor-pointer group">
            <input type="checkbox" v-model="filters.bestsellers" class="hidden">
            <div class="w-4 h-4 border-2 rounded mr-2 flex items-center justify-center transition-colors border-slate-300 dark:border-slate-600 group-hover:border-amber-500" :class="filters.bestsellers ? 'bg-amber-500 border-amber-500' : ''">
              <Check v-if="filters.bestsellers" class="w-3 h-3 text-white" />
            </div>
            <span class="text-xs font-medium text-slate-600 dark:text-slate-400">Top Ventes</span>
          </label>
        </div>
      </div>
    </div>

    <!-- Products Table -->
    <div class="product-table-container bg-white dark:bg-[rgb(43,44,43)] rounded-2xl shadow-sm border border-slate-100 dark:border-slate-500 overflow-hidden transition-colors">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-500">
          <thead class="bg-slate-50 dark:bg-slate-900/50">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Produit</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Catégorie</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Prix/Stock</th>
              <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Mises en avant</th>
              <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-[rgb(43,44,43)] divide-y divide-slate-100 dark:divide-slate-500">
            <tr v-for="product in filteredProducts" :key="product.id" class="product-row hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700 flex-shrink-0 overflow-hidden border border-slate-100 dark:border-slate-500">
                    <img :src="getImageUrl(product.main_image)" :alt="product.name" class="w-full h-full object-cover">
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-bold text-slate-900 dark:text-white">{{ product.name }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[200px]">{{ product.slug }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                  {{ product.category_name }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-bold text-slate-900 dark:text-white">{{ product.price }}FCFA</div>
                <div class="text-xs" :class="product.stock > 10 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                  {{ product.stock }} en stock
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center justify-center space-x-3">
                  <!-- Nouveauté -->
                  <button 
                    @click="toggleFeature(product, 'newest')"
                    class="p-1.5 rounded-lg transition-all"
                    :class="product.is_newest ? 'bg-blue-50 text-blue-600' : 'text-slate-300 hover:text-slate-400'"
                    :title="product.is_newest ? 'Retirer des nouveautés' : 'Marquer comme nouveauté'"
                  >
                    <Sparkles class="w-4 h-4" />
                  </button>
                  <!-- Bestseller -->
                  <button 
                    @click="toggleFeature(product, 'bestseller')"
                    class="p-1.5 rounded-lg transition-all"
                    :class="product.is_bestseller ? 'bg-amber-50 text-amber-600' : 'text-slate-300 hover:text-slate-400'"
                    :title="product.is_bestseller ? 'Retirer des tops ventes' : 'Marquer comme top vente'"
                  >
                    <TrendingUp class="w-4 h-4" />
                  </button>
                  <!-- Offre Spéciale -->
                  <button 
                    @click="toggleFeature(product, 'offer')"
                    class="p-1.5 rounded-lg transition-all"
                    :class="product.is_special_offer ? 'bg-rose-50 text-rose-600' : 'text-slate-300 hover:text-slate-400'"
                    :title="product.is_special_offer ? 'Retirer des offres' : 'Marquer comme offre spéciale'"
                  >
                    <Gift class="w-4 h-4" />
                  </button>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-2">
                  <button @click="editProduct(product)" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Modifier">
                    <Edit3 class="w-4 h-4" />
                  </button>
                  <router-link :to="`/admin/products/${product.id}/images`" class="p-2 text-slate-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-all" title="Images">
                    <ImageIcon class="w-4 h-4" />
                  </router-link>
                  <button @click="deleteProduct(product.id)" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="Supprimer">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit Modal (A bit simplified for now, redirects to images after) -->
    <div v-if="showCreateForm || editingProduct" class="fixed inset-0 bg-slate-900/50 dark:bg-slate-950/70 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white mt-20 mb-2 dark:bg-[rgb(43,44,43)] rounded-3xl shadow-2xl w-full max-w-md md:max-w-2xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in duration-200 border border-transparent dark:border-slate-500">
        <!-- Header du formulaire -->
        <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-500 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/20 flex-shrink-0">
          <h2 class="text-xl font-bold text-slate-800 dark:text-white">{{ editingProduct ? 'Éditer le produit' : 'Nouveau produit' }}</h2>
          <button @click="cancelEdit" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <X class="w-6 h-6" />
          </button>
        </div>
        
        <!-- Corps du formulaire scrollable -->
        <div class="overflow-y-auto flex-1 custom-scrollbar">
          <form @submit.prevent="saveProduct" class="p-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Nom du produit</label>
              <input v-model="productForm.name" @input="generateSlug" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-500 rounded-xl focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none dark:text-white">
            </div>
            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Catégorie</label>
              <select v-model="productForm.category_id" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-500 rounded-xl focus:ring-2 focus:ring-accent/20 outline-none dark:text-white">
                <option value="">Sélectionner...</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>
            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Prix (FCFA)</label>
              <input v-model="productForm.price" type="number" step="0.01" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-500 rounded-xl focus:ring-2 focus:ring-accent/20 outline-none dark:text-white">
            </div>
            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Stock</label>
              <input v-model="productForm.stock" type="number" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-500 rounded-xl focus:ring-2 focus:ring-accent/20 outline-none dark:text-white">
            </div>
            <div class="md:col-span-2 space-y-1">
              <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Slug (URL)</label>
              <input v-model="productForm.slug" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-500 rounded-xl focus:ring-2 focus:ring-accent/20 outline-none dark:text-white">
            </div>
            <div class="md:col-span-2 space-y-1">
              <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Description</label>
              <textarea v-model="productForm.description" rows="3" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-500 rounded-xl focus:ring-2 focus:ring-accent/20 outline-none dark:text-white"></textarea>
            </div>
          </div>

          <!-- Section Actions - Responsive -->
          <div class="flex flex-col sm:flex-row items-center justify-between gap-6 mt-8">
            <label class="flex items-center cursor-pointer self-start sm:self-auto">
              <div class="relative inline-flex items-center">
                <input type="checkbox" v-model="productForm.is_active" class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                <span class="ml-3 text-sm font-bold text-slate-600 dark:text-slate-400">Produit Actif</span>
              </div>
            </label>

            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 w-full sm:w-auto">
              <button 
                type="button" 
                @click="cancelEdit" 
                class="w-full sm:w-auto px-6 py-2.5 text-sm font-bold text-slate-500 border-1 border-slate-200 dark:border-slate-600 hover:cursor-pointer rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-75"
              >
                Annuler
              </button>
              <button 
                type="submit" 
                class="w-full sm:w-auto px-8 py-2.5 bg-accent text-white text-sm font-bold rounded-xl hover:bg-green-700 hover:cursor-pointer transition-all duration-75"
              >
                {{ editingProduct ? 'Enregistrer les modifications' : 'Enregistrer' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import adminService from '@/services/adminService.js'
import { 
  Plus, Search, Edit3, Trash2, ImageIcon, X, 
  Sparkles, TrendingUp, Gift, Check 
} from 'lucide-vue-next'
import { gsap } from 'gsap'
import { getProductImageUrl } from '@/utils/imageHelper'

const router = useRouter()
const products = ref([])
const categories = ref([])
const showCreateForm = ref(false)
const editingProduct = ref(null)
const filters = ref({ search: '', category: '', status: '', bestsellers: false })

const productForm = ref({
  name: '',
  slug: '',
  description: '',
  price: '',
  stock: '',
  category_id: '',
  is_active: true
})

const filteredProducts = computed(() => {
  return products.value.filter(product => {
    const matchesSearch = !filters.value.search || 
      product.name.toLowerCase().includes(filters.value.search.toLowerCase())
    const matchesCategory = !filters.value.category || 
      product.category_id == filters.value.category
    const matchesStatus = !filters.value.status || 
      (filters.value.status === 'active' ? product.is_active : !product.is_active)
    const matchesBestseller = !filters.value.bestsellers || product.is_bestseller
    
    return matchesSearch && matchesCategory && matchesStatus && matchesBestseller
  })
})

const loadProducts = async () => {
  try {
    const response = await adminService.getProducts()
    if (response.success) products.value = response.products
  } catch (err) { console.error(err) }
}

const loadCategories = async () => {
  try {
    const response = await adminService.getCategories()
    if (response.success) categories.value = response.categories
  } catch (err) { console.error(err) }
}

const editProduct = (product) => {
  editingProduct.value = product
  productForm.value = { ...product }
}

const toggleFeature = async (product, type) => {
  try {
    let res
    if (type === 'newest') {
      const target = !product.is_newest
      res = await adminService.toggleNewest(product.id, target)
      if (res.success) product.is_newest = target
    } else if (type === 'bestseller') {
      const target = !product.is_bestseller
      res = await adminService.toggleBestseller(product.id, target)
      if (res.success) product.is_bestseller = target
    } else if (type === 'offer') {
      const target = !product.is_special_offer
      res = await adminService.toggleSpecialOffer(product.id, target)
      if (res.success) product.is_special_offer = target
    }
  } catch (err) { console.error(err) }
}

const generateSlug = () => {
  if (productForm.value.name) {
    productForm.value.slug = productForm.value.name
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .replace(/[^a-z0-0]/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-|-$/g, '')
  }
}

const saveProduct = async () => {
  try {
    let response
    if (editingProduct.value) {
      response = await adminService.updateProduct({ ...productForm.value, id: editingProduct.value.id })
    } else {
      response = await adminService.createProduct(productForm.value)
    }
    
    if (response.success) {
      await loadProducts()
      const pid = editingProduct.value ? editingProduct.value.id : response.product_id
      if (!editingProduct.value) {
        // Redirection vers la gestion des images pour un nouveau produit
        router.push(`/admin/products/${pid}/images`)
      }
      cancelEdit()
    }
  } catch (err) { console.error(err) }
}

const deleteProduct = async (id) => {
  if (confirm('Supprimer ce produit définitivement ?')) {
    try {
      const res = await adminService.deleteProduct(id)
      if (res.success) loadProducts()
    } catch (err) { console.error(err) }
  }
}

const getImageUrl = (url) => getProductImageUrl(url)

const cancelEdit = () => {
  showCreateForm.value = false
  editingProduct.value = null
  productForm.value = { name: '', slug: '', description: '', price: '', stock: '', category_id: '', is_active: true }
}

// --- GSAP Animation ---
let ctx = null

const animateTableRows = async () => {
  await nextTick()
  if (ctx) ctx.revert()
  
  ctx = gsap.context(() => {
    gsap.from('.product-row', {
      y: 20,
      opacity: 0,
      duration: 0.4,
      stagger: 0.05,
      ease: 'power2.out',
      clearProps: 'all'
    })
  })
}

watch(filteredProducts, () => {
  animateTableRows()
})

onMounted(async () => {
  await loadProducts()
  await loadCategories()
  
  // Initial Entrance Animation
  const tl = gsap.timeline({ defaults: { ease: 'power3.out' } })
  
  tl.from('.product-header', {
    y: -30,
    opacity: 0,
    duration: 0.8
  })
  .from('.product-filter', {
    y: -20,
    opacity: 0,
    duration: 0.6
  }, '-=0.4')
  .from('.product-table-container', {
    y: 30,
    opacity: 0,
    duration: 0.8
  }, '-=0.4')
  
  // Trigger row animation
  animateTableRows()
})

onUnmounted(() => {
  if (ctx) ctx.revert()
})
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: #475569;
}
</style>


