<template>
  <div class="p-6">
    <div class="max-w-4xl mx-auto">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ productId ? 'Modifier le produit' : 'Nouveau produit' }}
        </h1>
        <router-link to="/admin/products" class="text-gray-600 dark:text-slate-400 hover:text-gray-800 dark:hover:text-white">
          ← Retour à la liste
        </router-link>
      </div>

      <div class="bg-white dark:bg-[rgb(43,44,43)] rounded-lg shadow p-6 border border-transparent dark:border-slate-500">
        <form @submit.prevent="saveProduct">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Informations de base -->
            <div class="space-y-4">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informations de base</h3>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Nom du produit</label>
                <input v-model="form.name" required class="w-full border dark:border-slate-500 rounded-lg px-3 py-2 bg-white dark:bg-slate-900/50 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Catégorie</label>
                <select v-model="form.category_id" required class="w-full border dark:border-slate-500 rounded-lg px-3 py-2 bg-white dark:bg-slate-900/50 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <option value="">Sélectionner une catégorie</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                  </option>
                </select>
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Prix (FCFA)</label>
                  <input v-model="form.price" type="number" step="0.01" required class="w-full border dark:border-slate-500 rounded-lg px-3 py-2 bg-white dark:bg-slate-900/50 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Stock</label>
                  <input v-model="form.stock_quantity" type="number" required class="w-full border dark:border-slate-500 rounded-lg px-3 py-2 bg-white dark:bg-slate-900/50 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Description</label>
                <textarea v-model="form.description" rows="4" class="w-full border dark:border-slate-500 rounded-lg px-3 py-2 bg-white dark:bg-slate-900/50 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
              </div>
              
              <div class="space-y-2">
                <label class="flex items-center cursor-pointer">
                  <input v-model="form.is_active" type="checkbox" class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                  <span class="text-sm font-medium text-gray-700 dark:text-slate-300">Produit actif</span>
                </label>
                <label class="flex items-center cursor-pointer">
                  <input v-model="form.is_bestseller" type="checkbox" class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                  <span class="text-sm font-medium text-gray-700 dark:text-slate-300">Meilleure vente</span>
                </label>
                <label class="flex items-center cursor-pointer">
                  <input v-model="form.is_newest" type="checkbox" class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                  <span class="text-sm font-medium text-gray-700 dark:text-slate-300">Nouveauté</span>
                </label>
              </div>
            </div>
            
            <!-- Gestion des images -->
            <div class="space-y-4">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Images du produit</h3>
              
              <!-- Upload d'image -->
              <div class="border-2 border-dashed border-gray-300 dark:border-slate-600 rounded-lg p-6 text-center hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                <input 
                  ref="fileInput" 
                  type="file" 
                  @change="handleFileSelect" 
                  accept="image/*" 
                  multiple 
                  class="hidden"
                >
                <button 
                  type="button" 
                  @click="$refs.fileInput.click()" 
                  class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
                >
                  Ajouter des images
                </button>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-2">PNG, JPG jusqu'à 5MB chacune</p>
              </div>
              
              <!-- Aperçu des images -->
              <div v-if="images.length" class="grid grid-cols-2 gap-4">
                <div v-for="(image, index) in images" :key="index" class="relative">
                  <img :src="image.url" :alt="`Image ${index + 1}`" class="w-full h-32 object-cover rounded-lg">
                  <button 
                    type="button" 
                    @click="removeImage(index)" 
                    class="absolute top-2 right-2 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-700"
                  >
                    ×
                  </button>
                  <div class="mt-2">
                    <label class="flex items-center text-sm text-gray-700 dark:text-slate-300 cursor-pointer">
                      <input 
                        v-model="image.is_primary" 
                        type="radio" 
                        :name="'primary_image'" 
                        class="mr-2 text-blue-600 focus:ring-blue-500"
                        @change="setPrimaryImage(index)"
                      >
                      Image principale
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Actions -->
          <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-100 dark:border-slate-600">
            <router-link 
              to="/admin/products" 
              class="px-6 py-2 text-gray-600 dark:text-slate-300 border border-gray-300 dark:border-slate-500 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors"
            >
              Annuler
            </router-link>
            <button 
              type="submit" 
              :disabled="loading"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >
              {{ loading ? 'Sauvegarde...' : (productId ? 'Modifier' : 'Créer') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import adminService from '@/services/adminService.js'

const route = useRoute()
const router = useRouter()
const productId = route.params.id

const loading = ref(false)
const categories = ref([])
const images = ref([])

const form = ref({
  name: '',
  description: '',
  price: '',
  stock_quantity: '',
  category_id: '',
  is_active: true,
  is_bestseller: false,
  is_newest: false
})

const loadCategories = async () => {
  try {
    const response = await adminService.getCategories()
    if (response.success) {
      categories.value = response.categories
    }
  } catch (error) {
    console.error('Erreur lors du chargement des catégories:', error)
  }
}

const loadProduct = async () => {
  if (!productId) return
  
  try {
    // Charger les détails du produit
    // const response = await adminService.getProduct(productId)
    // if (response.success) {
    //   form.value = response.product
    // }
    
    // Charger les images du produit
    const imagesResponse = await adminService.getProductImages(productId)
    if (imagesResponse.success) {
      images.value = imagesResponse.images.map(img => ({
        id: img.id,
        url: img.image_url,
        is_primary: img.is_primary
      }))
    }
  } catch (error) {
    console.error('Erreur lors du chargement du produit:', error)
  }
}

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files)
  
  files.forEach(file => {
    if (file.size > 5 * 1024 * 1024) {
      alert('Le fichier est trop volumineux (max 5MB)')
      return
    }
    
    const reader = new FileReader()
    reader.onload = (e) => {
      images.value.push({
        file,
        url: e.target.result,
        is_primary: images.value.length === 0
      })
    }
    reader.readAsDataURL(file)
  })
  
  event.target.value = ''
}

const removeImage = async (index) => {
  const image = images.value[index]
  
  if (image.id && productId) {
    try {
      await adminService.deleteProductImage(productId, image.id)
    } catch (error) {
      console.error('Erreur lors de la suppression de l\'image:', error)
      return
    }
  }
  
  images.value.splice(index, 1)
  
  // Si c'était l'image principale, définir la première comme principale
  if (image.is_primary && images.value.length > 0) {
    images.value[0].is_primary = true
  }
}

const setPrimaryImage = (index) => {
  images.value.forEach((img, i) => {
    img.is_primary = i === index
  })
}

const uploadImages = async (productId) => {
  const newImages = images.value.filter(img => img.file)
  
  for (const image of newImages) {
    const formData = new FormData()
    formData.append('image', image.file)
    formData.append('product_id', productId)
    formData.append('is_primary', image.is_primary ? '1' : '0')
    
    try {
      await adminService.uploadProductImage(formData)
    } catch (error) {
      console.error('Erreur lors de l\'upload d\'image:', error)
    }
  }
}

const saveProduct = async () => {
  loading.value = true
  
  try {
    let response
    if (productId) {
      response = await adminService.updateProduct({ ...form.value, id: productId })
    } else {
      response = await adminService.createProduct(form.value)
    }
    
    if (response.success) {
      const savedProductId = productId || response.product.id
      
      // Upload des nouvelles images
      await uploadImages(savedProductId)
      
      router.push('/admin/products')
    }
  } catch (error) {
    console.error('Erreur lors de la sauvegarde:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadCategories()
  loadProduct()
})
</script>
<style scoped>
/* Styles spécifiques si nécessaire */
</style>


