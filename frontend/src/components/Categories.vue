<template>
    <section id="tendances" class="py-16 scroll-mt-20 ">
        <div class="container mx-auto px-4">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold mb-3">Nos Tendances du Moment 😊...</h2>
                <p class="text-lg max-w-2xl mx-auto">
                    Découvrez les articles les plus prisés et affirmez votre style.
                </p>
            </div>
            <!-- Categories Grid -->
            <div v-if="loading" class="flex justify-center py-20">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-daba-cream-alt border-t-daba-orange"></div>
            </div>
            <!-- Empty/Error State -->
            <div v-else-if="products.length === 0" class="py-12 text-center bg-daba-cream-alt rounded-[2.5rem] border border-dashed border-daba-cream-alt">
                <div class="w-16 h-16 bg-daba-cream rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                    <Icon icon="solar:fire-minimalistic-linear" class="w-8 h-8 text-daba-slate-dark" />
                </div>
                <h3 class="text-lg font-bold text-daba-navy mb-1">Aucune tendance trouvée</h3>
                <p class="text-daba-slate text-sm mb-4">Rechargez la page pour mettre à jour les tendances.</p>
                <button @click="fetchTrends" class="text-daba-orange font-black uppercase tracking-widest text-[10px] hover:underline">Rafraîchir</button>
            </div>
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="(product, index) in products" :key="product.id"
                    :data-aos="'fade-up'" :data-aos-delay="100 * index"
                    class="group relative overflow-hidden rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 aspect-[4/5] sm:aspect-square lg:aspect-[4/5]">
                    <!-- Image -->
                    <OptimizedImage 
                        :src="getProductImageUrl(product.image_url)" 
                        :alt="product.name"
                        imageClass="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" 
                    />
                    
                    <!-- Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-daba-navy/90 via-daba-navy/20 to-transparent opacity-60 group-hover:opacity-80 transition-opacity"></div>
                    
                    <!-- Content -->
                    <div class="absolute bottom-0 left-0 right-0 p-8 transform translate-y-2 group-hover:translate-y-0 transition-transform">
                        <h3 class="text-2xl font-bold text-white mb-2"> {{ product.name }} </h3>
                        <p class="text-daba-cream-alt text-sm opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            {{ product.description }}
                        </p>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-white font-black text-xl">{{ product.price?.toLocaleString() }} FCFA</span>
                            <div class="w-10 h-1 bg-daba-cream rounded-full"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Icon } from '@iconify/vue'
import OptimizedImage from './OptimizedImage.vue'
import { adminService } from '@/services/adminService'
import { getProductImageUrl } from '@/utils/imageHelper'

const products = ref([])
const loading = ref(true)

const fetchTrends = async () => {
    loading.value = true
    try {
        const res = await adminService.getProducts({ source: 'tendance' })
        if (res.success) {
            products.value = res.products
        }
    } catch (error) {
        console.error('Erreur chargement tendances:', error)
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    fetchTrends()
})
</script>


