<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <button @click="router.push('/admin/products')" class="p-2 hover:bg-white dark:hover:bg-[rgb(43,44,43)] rounded-xl transition-colors">
          <ArrowLeft class="w-6 h-6 text-slate-600 dark:text-slate-400" />
        </button>
        <div>
          <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Galerie Photos</h1>
          <p class="text-sm text-slate-500 dark:text-slate-400">{{ productName }}</p>
        </div>
      </div>
      
      <div class="flex items-center space-x-3">
        <input 
          ref="fileInput" 
          type="file" 
          @change="handleFileSelect" 
          accept="image/*" 
          multiple 
          class="hidden"
        >
        <button 
          @click="$refs.fileInput.click()"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-900/10"
        >
          <UploadCloud class="w-4 h-4 mr-2" />
          Ajouter des photos
        </button>
      </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Info Card -->
      <div class="lg:col-span-1 space-y-6">
        <div class="bg-white dark:bg-[rgb(43,44,43)] rounded-2xl p-6 border border-slate-100 dark:border-slate-500 shadow-sm">
          <h3 class="font-bold text-slate-800 dark:text-white mb-4">Instructions</h3>
          <ul class="space-y-4 text-sm text-slate-600 dark:text-slate-400">
            <li class="flex items-start">
              <div class="w-5 h-5 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center mr-3 mt-0.5 text-[10px] font-bold">1</div>
              <span>L'image <strong>Principale</strong> est celle affichée sur les listes et le slider.</span>
            </li>
            <li class="flex items-start">
              <div class="w-5 h-5 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center mr-3 mt-0.5 text-[10px] font-bold">2</div>
              <span>Vous pouvez ajouter plusieurs images à la fois.</span>
            </li>
            <li class="flex items-start">
              <div class="w-5 h-5 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center mr-3 mt-0.5 text-[10px] font-bold">3</div>
              <span>Format recommandés: JPG/PNG, min 800x800px.</span>
            </li>
          </ul>
        </div>

        <div v-if="images.length > 0" class="bg-blue-600 dark:bg-blue-700 rounded-2xl p-6 text-white shadow-xl shadow-blue-900/20">
          <div class="flex items-center justify-between mb-4">
            <span class="text-xs font-bold uppercase opacity-60">Photo Principale</span>
            <Star class="w-4 h-4 fill-white" />
          </div>
          <div class="aspect-video rounded-xl overflow-hidden bg-white/10 mb-4 border border-white/20">
            <img :src="getImageUrl(mainImage?.image_path)" class="w-full h-full object-cover">
          </div>
          <p class="text-xs opacity-80 text-center">C'est la photo que vos clients verront en premier.</p>
        </div>
      </div>

      <!-- Gallery Grid -->
      <div class="lg:col-span-2">
        <div v-if="images.length === 0" class="bg-white dark:bg-[rgb(43,44,43)] border-2 border-dashed border-slate-200 dark:border-slate-500 rounded-3xl p-20 text-center">
          <div class="w-20 h-20 bg-slate-50 dark:bg-slate-900/50 rounded-full flex items-center justify-center mx-auto mb-6">
            <ImageIcon class="w-10 h-10 text-slate-300 dark:text-slate-600" />
          </div>
          <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-2">Aucune photo</h3>
          <p class="text-slate-500 dark:text-slate-400 mb-6">Commencez par ajouter des images pour mettre en valeur votre produit.</p>
          <button @click="$refs.fileInput.click()" class="text-blue-600 dark:text-blue-400 font-bold hover:underline">Cliquez ici pour charger</button>
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-6">
          <div v-for="image in images" :key="image.id" class="group relative bg-white dark:bg-[rgb(43,44,43)] rounded-2xl border border-slate-100 dark:border-slate-500 p-2 shadow-sm hover:shadow-md transition-all">
            <div class="aspect-square rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-900">
              <img :src="getImageUrl(image.image_path)" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
            </div>
            
            <!-- Badge -->
            <div v-if="image.is_main" class="absolute top-4 left-4 bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-lg">
              PRINCIPALE
            </div>

            <!-- Actions Overlay -->
            <div class="absolute inset-2 bg-slate-900/60 backdrop-blur-sm rounded-xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center space-x-2">
              <button 
                v-if="!image.is_main"
                @click="setMain(image.id)" 
                class="p-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors"
                title="Définir comme principale"
              >
                <Star class="w-4 h-4" />
              </button>
              <button 
                @click="remove(image.id)" 
                class="p-2 bg-white text-rose-600 rounded-lg hover:bg-rose-50 transition-colors"
                title="Supprimer"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Progress Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-slate-900/80 backdrop-blur-md flex items-center justify-center z-[100]">
      <div class="text-center">
        <div class="w-16 h-16 border-4 border-blue-500/20 border-t-blue-500 rounded-full animate-spin mx-auto mb-6"></div>
        <p class="text-white font-bold text-lg">{{ loadingMessage }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import adminService from '@/services/adminService.js'
import { 
  ArrowLeft, UploadCloud, ImageIcon, 
  Trash2, Star, Plus, X 
} from 'lucide-vue-next'
import { getProductImageUrl } from '@/utils/imageHelper'

const route = useRoute()
const router = useRouter()
const productId = route.params.id

const images = ref([])
const productName = ref('Chargement...')
const loading = ref(false)
const loadingMessage = ref('')

const mainImage = computed(() => images.value.find(img => img.is_main) || images.value[0])

const loadImages = async () => {
  try {
    const res = await adminService.getProductImages(productId)
    if (res.success) {
      images.value = res.images
      // On récupère aussi les infos produit pour le nom
      const prodRes = await adminService.getProducts()
      const p = prodRes.products?.find(x => x.id == productId)
      if (p) productName.value = p.name
    }
  } catch (err) { console.error(err) }
}

const handleFileSelect = async (event) => {
  const files = Array.from(event.target.files)
  if (files.length === 0) return

  loading.value = true
  try {
    for (let i = 0; i < files.length; i++) {
      loadingMessage.value = `Chargement de la photo ${i + 1}/${files.length}...`
      const formData = new FormData()
      formData.append('image', files[i])
      formData.append('product_id', productId)
      formData.append('is_main', (images.value.length === 0 && i === 0) ? '1' : '0')
      await adminService.uploadProductImage(formData)
    }
    await loadImages()
  } catch (err) {
    alert("Erreur lors de l'envoi des photos")
  } finally {
    loading.value = false
    event.target.value = ''
  }
}

const setMain = async (imageId) => {
  loading.value = true
  loadingMessage.value = "Mise à jour..."
  try {
    const res = await adminService.setMainImage(productId, imageId)
    if (res.success) await loadImages()
  } finally { loading.value = false }
}

const remove = async (imageId) => {
  if (!confirm("Supprimer cette photo ?")) return
  loading.value = true
  loadingMessage.value = "Suppression..."
  try {
    const res = await adminService.deleteProductImage(productId, imageId)
    if (res.success) await loadImages()
  } finally { loading.value = false }
}

const getImageUrl = (url) => getProductImageUrl(url)

onMounted(loadImages)
</script>


