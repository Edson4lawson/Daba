<template>
  <div class="space-y-8">
    <div>
      <h1 class="text-2xl font-black text-slate-800 dark:text-white">Analytiques</h1>
      <p class="text-sm text-slate-500 dark:text-slate-400">Vue d'ensemble des performances de votre boutique</p>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div v-for="kpi in kpis" :key="kpi.label" class="bg-white dark:bg-[rgb(43,44,43)] rounded-2xl p-6 border border-slate-100 dark:border-slate-500 shadow-sm hover:shadow-lg transition-all">
        <div class="flex items-center justify-between mb-4">
          <div :class="`p-3 rounded-xl bg-${kpi.color}-50 dark:bg-${kpi.color}-900/20`">
            <component :is="kpi.icon" :class="`w-5 h-5 text-${kpi.color}-500`" />
          </div>
          <span :class="`text-xs font-bold px-2 py-1 rounded-full ${kpi.trend >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'}`">
            {{ kpi.trend >= 0 ? '+' : '' }}{{ kpi.trend }}%
          </span>
        </div>
        <p class="text-2xl font-black text-slate-800 dark:text-white">{{ kpi.value }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">{{ kpi.label }}</p>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Revenue Chart -->
      <div class="bg-white dark:bg-[rgb(43,44,43)] rounded-3xl p-8 border border-slate-100 dark:border-slate-500 shadow-sm">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-lg font-bold text-slate-800 dark:text-white">Revenus mensuels</h2>
          <select v-model="period" @change="loadAnalytics" class="text-xs font-bold bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-500 rounded-lg px-3 py-1.5 dark:text-white">
            <option value="7d">7 jours</option>
            <option value="30d">30 jours</option>
            <option value="90d">3 mois</option>
            <option value="1y">1 an</option>
          </select>
        </div>
        <div class="h-64">
          <Line v-if="revenueData.labels.length" :data="revenueData" :options="chartOptions" />
          <div v-else class="h-full flex items-center justify-center text-slate-400 text-sm">Aucune donnée disponible</div>
        </div>
      </div>

      <!-- Orders by Status -->
      <div class="bg-white dark:bg-[rgb(43,44,43)] rounded-3xl p-8 border border-slate-100 dark:border-slate-500 shadow-sm">
        <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-6">Commandes par statut</h2>
        <div class="space-y-4">
          <div v-for="status in orderStatuses" :key="status.label" class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: status.color }"></div>
              <span class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ status.label }}</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-32 h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all" :style="{ width: `${status.percent}%`, backgroundColor: status.color }"></div>
              </div>
              <span class="text-sm font-bold text-slate-800 dark:text-white w-8 text-right">{{ status.count }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Products -->
    <div class="bg-white dark:bg-[rgb(43,44,43)] rounded-3xl p-8 border border-slate-100 dark:border-slate-500 shadow-sm">
      <div class="flex items-center justify-between mb-8">
        <h2 class="text-lg font-bold text-slate-800 dark:text-white flex items-center">
          <TrendingUp class="w-5 h-5 mr-3 text-daba-orange" />
          Top 8 des meilleures ventes
        </h2>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="(product, idx) in topProducts" :key="product.id" class="group relative bg-slate-50 dark:bg-slate-900/30 border border-slate-100 dark:border-slate-500 rounded-2xl p-3 hover:shadow-xl transition-all duration-300">
          <!-- Position Badge -->
          <div class="absolute -top-2 -left-2 w-8 h-8 bg-daba-navy dark:bg-daba-orange text-white rounded-full flex items-center justify-center text-xs font-black z-20 shadow-lg">
            #{{ idx + 1 }}
          </div>
          
          <div class="relative w-full aspect-square rounded-2xl bg-white dark:bg-slate-800 overflow-hidden mb-4">
            <img :src="product.image_url" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
              <p class="text-[10px] text-white/80 font-medium">CA Généré: {{ product.revenue?.toLocaleString('fr-FR') }} FCFA</p>
            </div>
          </div>

          <div class="space-y-1">
            <h4 class="text-sm font-bold text-slate-800 dark:text-white truncate">{{ product.name }}</h4>
            <div class="flex items-center justify-between">
              <span class="text-xs font-black text-daba-orange dark:text-daba-orange">{{ product.sold }} UNITÉS VENDUES</span>
              <span class="text-[10px] text-slate-400 font-bold uppercase">{{ Math.round((product.revenue / (product.sold || 1))).toLocaleString('fr-FR') }} / u</span>
            </div>
          </div>
        </div>
      </div>
      <div v-if="!topProducts.length" class="h-40 flex items-center justify-center text-slate-400 italic text-sm">
        Aucune donnée de vente pour cette période.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { ShoppingCart, Users, Wallet, Package, TrendingUp } from 'lucide-vue-next'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip, Legend } from 'chart.js'
import { Line } from 'vue-chartjs'
import adminService from '@/services/adminService'
import { getProductImageUrl } from '@/utils/imageHelper'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip, Legend)

const period = ref('30d')
const analyticsData = ref(null)

const kpis = ref([
  { label: 'Revenus totaux', value: '0 FCFA', icon: Wallet, color: 'blue', trend: 0 },
  { label: 'Commandes', value: '0', icon: ShoppingCart, color: 'green', trend: 0 },
  { label: 'Clients actifs', value: '0', icon: Users, color: 'purple', trend: 0 },
  { label: 'Panier moyen', value: '0 FCFA', icon: Package, color: 'amber', trend: 0 },
])

const orderStatuses = ref([
  { label: 'En attente', count: 0, percent: 0, color: '#f59e0b' },
  { label: 'En traitement', count: 0, percent: 0, color: '#3b82f6' },
  { label: 'Expédiées', count: 0, percent: 0, color: '#8b5cf6' },
  { label: 'Livrées', count: 0, percent: 0, color: '#10b981' },
  { label: 'Annulées', count: 0, percent: 0, color: '#ef4444' },
])

const topProducts = ref([])

const revenueData = computed(() => {
  try {
    let sales = [...(analyticsData.value?.monthlySales || [])]
    
    // Si on n'a qu'un seul mois, on ajoute un point 0 au début pour dessiner une ligne
    if (sales.length === 1) {
      sales = [{ month: 'Début', revenue: 0 }, ...sales]
    }
    
    // Création d'un dégradé pour l'aire sous la courbe
    const ctx = document.createElement('canvas').getContext('2d')
    const gradient = ctx.createLinearGradient(0, 0, 0, 300)
    gradient.addColorStop(0, 'rgba(206, 70, 0, 0.4)')
    gradient.addColorStop(1, 'rgba(206, 70, 0, 0)')
    
    return {
      labels: sales.map(s => s.month || ''),
      datasets: [{
        label: 'Revenus (FCFA)',
        data: sales.map(s => parseFloat(s.revenue || 0)),
        borderColor: '#CE4600',
        borderWidth: 4,
        pointBackgroundColor: '#fff',
        pointBorderColor: '#CE4600',
        pointBorderWidth: 3,
        pointRadius: 5,
        pointHoverRadius: 8,
        backgroundColor: gradient,
        fill: true,
        tension: 0.4
      }]
    }
  } catch (e) {
    return { labels: [], datasets: [] }
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: 'rgba(17, 24, 39, 0.9)',
      titleColor: '#fff',
      bodyColor: '#fff',
      padding: 12,
      cornerRadius: 12,
      displayColors: false,
      callbacks: {
        label: (context) => new Intl.NumberFormat('fr-FR').format(context.raw) + ' FCFA'
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: { color: 'rgba(148, 163, 184, 0.1)', drawBorder: false },
      ticks: {
        color: '#94a3b8',
        font: { size: 10, weight: 'bold' },
        callback: (value) => value >= 1000 ? (value/1000) + 'k' : value
      }
    },
    x: {
      grid: { display: false },
      ticks: { color: '#94a3b8', font: { size: 10, weight: 'bold' } }
    }
  }
}

const loadAnalytics = async () => {
  try {
    // Load main stats
    const statsRes = await adminService.getStats(period.value)
    if (statsRes.success) {
      const s = statsRes.stats
      analyticsData.value = s
      
      // KPI Update
      kpis.value[0].value = `${(s.totalRevenue || 0).toLocaleString('fr-FR')} FCFA`
      kpis.value[1].value = String(s.totalOrders || 0)
      kpis.value[2].value = String(s.totalClients || 0)
      kpis.value[3].value = s.totalOrders > 0 
        ? `${Math.round(s.totalRevenue / s.totalOrders).toLocaleString('fr-FR')} FCFA` 
        : '0 FCFA'

      // Revenue chart - Handled by computed revenueData
      // No longer need manual updates here

      // Order status breakdown (Real data from backend)
      if (s.orderStatusCounts?.length) {
        const total = s.totalOrders || 1
        const statusMap = { pending: 0, processing: 1, shipped: 2, completed: 3, cancelled: 4 }
        
        // Reset counts first
        orderStatuses.value.forEach(os => { os.count = 0; os.percent = 0 })
        
        s.orderStatusCounts.forEach(item => {
          const statusKey = String(item.status).toLowerCase()
          const idx = statusMap[statusKey]
          if (idx !== undefined) {
            const count = parseInt(item.count) || 0
            orderStatuses.value[idx].count = count
            orderStatuses.value[idx].percent = Math.round((count / total) * 100)
          }
        })
      }
    }

    // Real Top products from backend
    if (statsRes.success && statsRes.stats.topProducts) {
      topProducts.value = statsRes.stats.topProducts.slice(0, 8).map(p => ({
        id: p.id,
        name: p.name,
        image_url: getProductImageUrl(p.image_url),
        sold: parseInt(p.total_sold),
        revenue: parseFloat(p.total_revenue)
      }))
    }
  } catch (err) {
    console.error('Analytics load error:', err)
  }
}

onMounted(loadAnalytics)
</script>
