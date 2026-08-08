<template>
    <!-- 
    Tableau de Bord Administrateur (Dashboard)
    Point d'entrée de la gestion Bloom. Affiche les statistiques clés (KPI),
  -->
  <div class="space-y-8">
    <!-- En-tête contextuel avec Date Dynamique -->
    <div class="dashboard-header flex items-center justify-between space-y-8">
      <div>
        <h2 class="text-2xl font-black text-daba-navy dark:text-daba-cream tracking-tight space-y-2">Bonjour {{ authStore.user?.role || 'Admin' }}</h2>
        <p class="text-sm text-daba-slate dark:text-daba-slate-dark font-medium">Voici l'activité de votre boutique Daba aujourd'hui.</p>
      </div>
      <div class="flex items-center space-x-2 bg-daba-cream dark:bg-daba-dark-bg px-4 py-2 rounded-2xl shadow-sm border border-daba-cream-alt dark:border-daba-dark-border">
        <Calendar class="w-4 h-4 text-daba-slate-dark" />
        <span class="text-sm font-bold text-daba-navy dark:text-white">{{ currentDate }}</span>
      </div>
    </div>

    <!-- Section Cartes Statistiques (KPIs) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Admin: Tous les KPIs -->
      <template v-if="userRole === 'admin'">
        <StatsCard
          title="Total Produits"
          :value="stats?.totalProducts ?? 0"
          :icon="Package"
          color="purple"
          class="stats-card-anim mb-4"
          :loading="isLoading"
        />
        <StatsCard
          title="Commandes"
          :value="stats?.totalOrders ?? 0"
          :icon="ShoppingCart"
          color="green"
          class="stats-card-anim mb-4"
          :loading="isLoading"
        />
        <StatsCard
          title="Clients"
          :value="stats?.totalClients ?? 0"
          :icon="Users"
          color="purple"
          class="stats-card-anim mb-4"
          :loading="isLoading"
        />
        <StatsCard
          title="Chiffre d'Affaires"
          :value="stats?.totalRevenue ?? 0"
          :icon="Wallet"
          suffix="FCFA"
          color="blue"
          class="stats-card-anim mb-4"
          :loading="isLoading"
        />
      </template>
      
      <!-- Commercial: Commandes du jour + en attente + panier moyen -->
      <template v-else-if="userRole === 'commercial'">
        <StatsCard
          title="Commandes du Jour"
          :value="stats?.todayOrders ?? 0"
          :icon="ShoppingCart"
          color="green"
          class="stats-card-anim mb-4"
          :loading="isLoading"
        />
        <StatsCard
          title="En Attente"
          :value="stats?.pendingOrders ?? 0"
          :icon="Clock"
          color="amber"
          class="stats-card-anim mb-4"
          :loading="isLoading"
        />
        <StatsCard
          title="Panier Moyen"
          :value="stats?.avgCart ?? 0"
          :icon="Wallet"
          suffix="FCFA"
          color="amber"
          class="stats-card-anim mb-4"
          :loading="isLoading"
        />
      </template>
      
      <!-- Magasinier: Alertes stock + commandes en attente de préparation -->
      <template v-else-if="userRole === 'magasinier'">
        <StatsCard
          title="Alertes Stock"
          :value="stats?.stockAlerts ?? 0"
          :icon="AlertTriangle"
          color="rose"
          class="stats-card-anim mb-4"
          :loading="isLoading"
        />
        <StatsCard
          title="À Préparer"
          :value="stats?.pendingOrders ?? 0"
          :icon="Package"
          color="amber"
          class="stats-card-anim mb-4"
          :loading="isLoading"
        />
      </template>
      
      <!-- Comptable: CA période + factures + panier moyen -->
      <template v-else-if="userRole === 'comptable'">
        <StatsCard
          title="Chiffre d'Affaires"
          :value="stats?.totalRevenue ?? 0"
          :icon="Wallet"
          suffix="FCFA"
          color="blue"
          class="stats-card-anim mb-4"
          :loading="isLoading"
        />
        <StatsCard
          title="Factures Payées"
          :value="stats?.paidInvoices ?? 0"
          :icon="FileText"
          color="emerald"
          class="stats-card-anim mb-4"
          :loading="isLoading"
        />
        <StatsCard
          title="Factures En Attente"
          :value="stats?.pendingInvoices ?? 0"
          :icon="FileText"
          color="amber"
          class="stats-card-anim mb-4"
          :loading="isLoading"
        />
        <StatsCard
          title="Panier Moyen"
          :value="stats?.avgCart ?? 0"
          :icon="Wallet"
          suffix="FCFA"
          color="amber"
          class="stats-card-anim mb-4"
          :loading="isLoading"
        />
      </template>
    </div>

    <!-- Section Graphiques et Tableaux -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 space-y-8">
      
      <!-- Graphique d'Évolution des Revenus (Admin uniquement) -->
      <div v-if="userRole === 'admin'" class="dashboard-chart bg-daba-cream dark:bg-daba-dark-bg rounded-3xl shadow-sm border border-daba-cream-alt dark:border-daba-dark-border p-8 hover:shadow-xl transition-all duration-500">
        <div class="flex items-center justify-between mb-8">
          <div>
            <h2 class="text-lg font-bold text-daba-navy dark:text-daba-cream">Évolution des Revenus</h2>
            <p class="text-xs text-daba-slate dark:text-daba-slate-dark font-medium">Revenus mensuels cumulés</p>
          </div>
          <div class="p-3 bg-daba-cream-alt dark:bg-[rgb(43,44,43)] rounded-2xl">
            <TrendingUp class="w-5 h-5 text-daba-orange" />
          </div>
        </div>
        <div class="h-80">
          <Line 
            v-if="revenueChartData.labels.length > 0" 
            :key="stats.totalRevenue"
            :data="revenueChartData" 
            :options="chartOptions" 
          />
          <div v-else class="h-full flex items-center justify-center text-daba-slate-dark italic text-sm">
            Chargement des données...
          </div>
        </div>
      </div>

      <!-- Tableau des Commandes Récentes (Admin, Commercial, Magasinier) -->
      <div v-if="userRole === 'admin' || userRole === 'commercial' || userRole === 'magasinier'" class="dashboard-table bg-daba-cream dark:bg-daba-dark-bg rounded-3xl shadow-sm border border-daba-cream-alt dark:border-daba-dark-border overflow-hidden flex flex-col hover:shadow-xl transition-all duration-500">
        <div class="px-8 py-6 border-b border-daba-cream-alt dark:border-daba-dark-border flex items-center justify-between bg-daba-cream-alt/50 dark:bg-[rgb(43,44,43)]/30 ">
          <h2 class="text-lg font-bold text-daba-navy dark:text-daba-cream">
            {{ userRole === 'magasinier' ? 'Commandes à Préparer' : 'Commandes Récentes' }}
          </h2>
          <button @click="router.push('/admin/orders')" class="text-xs font-black uppercase text-daba-orange dark:text-daba-orange hover:text-daba-orange-dark tracking-widest">
            Tout voir
          </button>
        </div>
        <div class="overflow-x-auto flex-1">
          <table class="min-w-full divide-y divide-daba-cream-alt dark:divide-daba-dark-border">
            <thead class="bg-daba-cream-alt dark:bg-[rgb(43,44,43)]/30">
              <tr>
                <th class="px-8 py-4 text-left text-[10px] font-black text-daba-slate-dark uppercase tracking-widest">Client</th>
                <th class="px-8 py-4 text-center text-[10px] font-black text-daba-slate-dark uppercase tracking-widest">Montant</th>
                <th class="px-8 py-4 text-right text-[10px] font-black text-daba-slate-dark uppercase tracking-widest">Statut</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-daba-cream-alt dark:divide-daba-dark-border">
              <!-- Squelettes de chargement -->
              <tr v-if="isLoading" v-for="i in 3" :key="'skeleton-'+i" class="animate-pulse">
                <td class="px-8 py-4"><div class="h-4 bg-daba-cream-alt dark:bg-[rgb(43,44,43)] rounded w-24 mb-1"></div><div class="h-2 bg-daba-cream-alt dark:bg-[rgb(43,44,43)] rounded w-16"></div></td>
                <td class="px-8 py-4"><div class="h-4 bg-daba-cream-alt dark:bg-[rgb(43,44,43)] rounded w-16 mx-auto"></div></td>
                <td class="px-8 py-4 text-right"><div class="h-6 bg-daba-cream-alt dark:bg-[rgb(43,44,43)] rounded-full w-20 ml-auto"></div></td>
              </tr>
              <!-- État vide -->
              <tr v-else-if="stats.recentOrders.length === 0">
                <td colspan="3" class="px-8 py-12 text-center text-daba-slate dark:text-daba-slate-dark text-sm font-medium italic">
                  Aucune commande récente
                </td>
              </tr>
              <!-- Liste des commandes -->
              <tr v-else v-for="order in stats.recentOrders" :key="order.id" class="hover:bg-daba-cream-alt/50 dark:hover:bg-[rgb(43,44,43)]/30 transition-colors group">
                <td class="px-8 py-4 whitespace-nowrap">
                  <div class="text-sm font-bold text-daba-navy dark:text-white group-hover:text-daba-orange transition-colors">{{ order.user_name }}</div>
                  <div class="text-[10px] text-daba-slate dark:text-daba-slate-dark font-medium tracking-tighter uppercase">ID #{{ order.id }} • {{ formatDate(order.created_at) }}</div>
                </td>
                <td class="px-8 py-4 whitespace-nowrap text-center text-sm font-black text-daba-navy dark:text-daba-cream">
                  {{ formatNumber(order.total_amount) }} FCFA
                </td>
                <td class="px-8 py-4 whitespace-nowrap text-right">
                  <span :class="getStatusClass(order.status)" class="px-3 py-1 text-[10px] font-black uppercase tracking-wider rounded-full">
                    {{ order.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Section Accès Rapide : Meilleurs Produits et Catégories (Admin uniquement) -->
    <div v-if="userRole === 'admin'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
      <!-- Aperçu des Nouveaux Produits (Dashboard) -->
      <div class="dashboard-quick-view mt-8 lg:col-span-2 bg-daba-cream dark:bg-daba-dark-bg rounded-3xl shadow-sm border border-daba-cream-alt dark:border-daba-dark-border p-8 hover:shadow-xl transition-all duration-500">
        <div class="flex items-center justify-between mb-8 ">
          <h2 class="text-lg font-bold text-daba-navy dark:text-daba-cream flex items-center">
            <LucideBookSearch class="w-5 h-5 mr-3 text-daba-orange" />
             Catalogue Produit
          </h2>
          <button @click="router.push('/admin/products')" class="p-2 hover:bg-daba-cream-alt dark:hover:bg-[rgb(43,44,43)] rounded-xl transition-colors">
            <ArrowRight class="w-5 h-5 text-daba-slate dark:text-daba-slate-dark hover:text-daba-orange" />
          </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div v-for="p in recentProducts.slice(0, 3)" :key="p.id" class="p-4 rounded-2xl bg-daba-cream-alt dark:bg-[rgb(43,44,43)]/30 border border-daba-cream-alt dark:border-daba-dark-border hover:bg-daba-cream dark:hover:bg-[rgb(43,44,43)] hover:shadow-md transition-all cursor-pointer group" @click="router.push('/admin/products')">
            <div class="w-full aspect-square rounded-xl bg-daba-cream-alt dark:bg-[rgb(43,44,43)] mb-3 overflow-hidden">
               <img :src="getImageUrl(p.image_url)" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
            </div>
            <h4 class="text-sm font-bold text-white dark:text-white truncate">{{ p.name }}</h4>
            <div class="flex items-center justify-between mt-1">
              <span class="text-sm text-daba-orange dark:text-daba-orange font-bold">{{ formatNumber(p.price) }} FCFA</span>
              <span class="text-[10px] font-black text-daba-slate dark:text-daba-slate-dark uppercase tracking-tighter">{{ p.category_name }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Résumé des Catégories (Carte Noire Premium) -->
      <div class="dashboard-category bg-daba-navy dark:bg-daba-dark-bg rounded-3xl p-8 text-white relative overflow-hidden shadow-xl shadow-slate-900/20 mt-8">
        <div class="relative z-10 h-full flex flex-col"> 
          <h2 class="text-lg font-bold mb-6 flex items-center">
            <Layers class="w-5 h-5 mr-3 text-white/60" />
            Catégories
          </h2>
          <div class="space-y-4 flex-1">
            <div v-for="cat in categories" :key="cat.id" class="flex items-center justify-between border-b border-white/5 pb-2">
              <span class="text-sm font-medium text-white/80">{{ cat.name }}</span>
              <span class="px-2 py-0.5 rounded-lg bg-white/10 text-[10px] font-black">{{ cat.product_count }} items</span>
            </div>
          </div>
          <button @click="router.push('/admin/categories')" class="mt-8 w-full py-3 bg-white text-black text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-100 transition-all">
            Gérer les rayons
          </button>
        </div>
        <!-- Décoration visuelle (Blur effect) -->
        <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-daba-orange/70 rounded-full blur-3xl text-daba-orange"></div>
      </div>
    </div>
  </div>
</template>


<script setup>
/**
 * Logique du Dashboard d'Administration
 * Gère le chargement des statistiques globales, le rendu des graphiques de vente
 * et les raccourcis vers la gestion du catalogue.
 */
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth.js'
import { useRouter } from 'vue-router'
import adminService from '@/services/adminService.js'
import { useNotifications } from '@/services/notificationService.js'
import StatsCard from '@/components/admin/StatsCard.vue'
import { 
  Package, ShoppingCart, Users, Euro, 
  TrendingUp, Calendar, LucideBookSearch, ArrowRight, Layers, 
  Banknote,
  Coins,
  DollarSign,
  CircleDollarSign,
  Wallet,
  Clock,
  AlertTriangle,
  FileText
} from 'lucide-vue-next'
// Imports Chart.js pour les graphiques
import {
  Chart as ChartJS, CategoryScale, LinearScale, PointElement, 
  LineElement, BarElement, Title, Tooltip, Legend, Filler
} from 'chart.js'
import { Line } from 'vue-chartjs'
import { gsap } from 'gsap'
import { getProductImageUrl } from '@/utils/imageHelper'

// Enregistrement des composants nécessaires pour Chart.js
ChartJS.register(
  CategoryScale, LinearScale, PointElement, LineElement, 
  BarElement, Title, Tooltip, Legend, Filler
)

const router = useRouter()
const authStore = useAuthStore()
const { addNotification } = useNotifications()

let eventSource = null;

// Rôle de l'utilisateur connecté
const userRole = computed(() => authStore.user?.role || 'admin')

// Détermine quels KPI afficher selon le rôle
const showKPIs = computed(() => {
  const role = userRole.value
  return {
    totalProducts: role === 'admin',
    totalOrders: role === 'admin',
    totalClients: role === 'admin',
    totalRevenue: role === 'admin' || role === 'comptable',
    todayOrders: role === 'commercial',
    pendingOrders: role === 'commercial' || role === 'magasinier',
    stockAlerts: role === 'magasinier',
    avgCart: role === 'admin' || role === 'commercial' || role === 'comptable'
  }
})

// Détermine quelles sections afficher
const showSections = computed(() => {
  const role = userRole.value
  return {
    revenueChart: role === 'admin',
    recentOrders: role === 'admin' || role === 'commercial' || role === 'magasinier',
    productCatalog: role === 'admin',
    categories: role === 'admin'
  }
})

// État des statistiques globales (initialisé avec des valeurs vides)
const stats = ref({
  totalProducts: 0, totalOrders: 0, totalClients: 0, 
  totalRevenue: 0, recentOrders: [], monthlySales: [],
  todayOrders: 0, pendingOrders: 0, stockAlerts: 0, avgCart: 0
})
const recentProducts = ref([])
const categories = ref([])
const isLoading = ref(true)

let ctxn = null;

// Date formatée affichée en haut (ex: "lundi 4 février")
const currentDate = computed(() => {
  return new Date().toLocaleDateString('fr-FR', { 
    weekday: 'long', day: 'numeric', month: 'long' 
  })
})

/**
 * Charge les données consolidées depuis l'API Admin
 */
const loadStats = async () => {
  isLoading.value = true
  try {
    const role = userRole.value
    
    // Pour admin, on charge toutes les stats
    if (role === 'admin') {
      const [statsRes, catRes] = await Promise.all([
        adminService.getStats(),
        adminService.getCategories()
      ])

      if (statsRes.success) {
        stats.value = statsRes.stats;
        recentProducts.value = statsRes.stats.recentProducts || [];
        // Calculer le panier moyen
        if (stats.value.totalOrders > 0) {
          stats.value.avgCart = Math.round(stats.value.totalRevenue / stats.value.totalOrders)
        }
      }
      
      if (catRes.success) {
        categories.value = Array.isArray(catRes.categories) ? catRes.categories : [];
      }
    } 
    // Pour commercial: charger les commandes du jour et en attente
    else if (role === 'commercial') {
      const ordersRes = await adminService.getOrders()
      if (ordersRes.success) {
        const today = new Date().toDateString()
        const todayOrders = ordersRes.orders.filter(o => new Date(o.created_at).toDateString() === today)
        const pendingOrders = ordersRes.orders.filter(o => o.status === 'pending' || o.status === 'en attente')
        
        stats.value.todayOrders = todayOrders.length
        stats.value.pendingOrders = pendingOrders.length
        stats.value.recentOrders = ordersRes.orders.slice(0, 10)
        
        // Calculer le panier moyen
        if (ordersRes.orders.length > 0) {
          const total = ordersRes.orders.reduce((sum, o) => sum + (parseFloat(o.total_amount) || 0), 0)
          stats.value.avgCart = Math.round(total / ordersRes.orders.length)
        }
      }
    }
    // Pour magasinier: charger les alertes stock et commandes à préparer
    else if (role === 'magasinier') {
      const [productsRes, ordersRes] = await Promise.all([
        adminService.getProducts({ per_page: 200 }),
        adminService.getOrders()
      ])
      
      if (productsRes.success) {
        stats.value.stockAlerts = productsRes.products.filter(p => p.stock <= 10).length
      }
      
      if (ordersRes.success) {
        const pendingOrders = ordersRes.orders.filter(o => o.status === 'pending' || o.status === 'en attente' || o.status === 'confirmed')
        stats.value.pendingOrders = pendingOrders.length
        stats.value.recentOrders = pendingOrders.slice(0, 10)
      }
    }
    // Pour comptable: charger le CA et les factures
    else if (role === 'comptable') {
      const statsRes = await adminService.getStats()
      if (statsRes.success) {
        stats.value.totalRevenue = statsRes.stats.totalRevenue || 0
        stats.value.totalOrders = statsRes.stats.totalOrders || 0
        // Calculer le panier moyen
        if (stats.value.totalOrders > 0) {
          stats.value.avgCart = Math.round(stats.value.totalRevenue / stats.value.totalOrders)
        }
        // Simuler les factures (à remplacer par vrai endpoint)
        stats.value.paidInvoices = Math.floor(stats.value.totalOrders * 0.7)
        stats.value.pendingInvoices = Math.floor(stats.value.totalOrders * 0.3)
      }
    }
    
    console.log('[Dashboard] Données chargées pour', role, ':', stats.value);

    await nextTick();
    runAnimations();
  } catch (error) {
    console.error('Erreur Critique Dashboard Admin:', error);
    addNotification({ type: 'error', message: 'Erreur lors du chargement des données du dashboard.' });
  } finally {
    isLoading.value = false
  }
}

/**
 * Exécute les animations GSAP
 */
const runAnimations = async () => {
  await nextTick();
  if (ctxn) ctxn.revert();
  
  ctxn = gsap.context(() => {
    // On ne cache pas les éléments par défaut pour éviter l'écran blanc en cas d'erreur JS
    const tl = gsap.timeline({ defaults: { ease: 'power3.out', duration: 0.8 } });

    if (document.querySelector(".dashboard-header")) {
      tl.from(".dashboard-header", { y: -20, duration: 0.8 });
    }

    if (document.querySelector(".stats-card-anim")) {
      tl.from(".stats-card-anim", {
          y: 20,
          stagger: 0.05,
          ease: "back.out(1.2)"
      }, "-=0.4");
    }

    if (document.querySelector(".dashboard-chart")) {
      tl.from(".dashboard-chart", { x: -30, clearProps: 'all' }, "-=0.6");
    }

    if (document.querySelector(".dashboard-table")) {
      tl.from(".dashboard-table", { x: 30, clearProps: 'all' }, "-=0.8");
    }

    if (document.querySelector(".dashboard-quick-view")) {
      tl.from(".dashboard-quick-view", { y: 30 }, "-=0.6");
    }

    if (document.querySelector(".dashboard-category")) {
      tl.from(".dashboard-category", { scale: 0.98 }, "-=0.6");
    }
  });
}

/**
 * Formate un nombre vers le format monétaire français (espace pour les milliers)
 */
const formatNumber = (num) => {
  return new Intl.NumberFormat('fr-FR').format(num)
}

/**
 * Prépare les données pour le graphique linéaire de revenus
 */
const revenueChartData = computed(() => {
  try {
    let sales = [...(stats.value?.monthlySales || [])]
    
    // Si on n'a qu'un seul mois (début d'activité), on ajoute un point 0 au début pour dessiner une ligne
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
        data: sales.map(s => parseFloat(s.revenue || s.total || 0)),
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
    console.error('Erreur revenueChartData:', e);
    return { labels: [], datasets: [] };
  }
})

// Options de configuration esthétique du graphique
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      mode: 'index',
      intersect: false,
      backgroundColor: 'rgba(17, 24, 39, 0.9)',
      titleColor: '#fff',
      bodyColor: '#fff',
      borderColor: 'rgba(255, 255, 255, 0.1)',
      borderWidth: 1,
      padding: 12,
      displayColors: false,
      callbacks: {
        label: function(context) {
          return new Intl.NumberFormat('fr-FR').format(context.raw) + ' FCFA';
        }
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(148, 163, 184, 0.1)', // Couleur ardoise légère (visible partout)
        drawBorder: false
      },
      ticks: {
        color: '#94a3b8', // Slate 400 (lisible sur blanc et noir)
        font: { size: 11, weight: 'bold' },
        callback: (value) => value >= 1000 ? (value/1000) + 'k' : value
      }
    },
    x: {
      grid: { display: false },
      ticks: {
        color: '#94a3b8',
        font: { size: 11, weight: 'bold' }
      }
    }
  }
}
 
/**
 * Détermine la couleur de badge selon le statut de la commande
 */
const getStatusClass = (status) => {
  const s = status.toLowerCase()
  if (s.includes('completed') || s.includes('livré') || s.includes('delivered')) return 'bg-daba-cream-alt text-daba-green dark:bg-[rgb(43,44,43)]/30 dark:text-daba-green'
  if (s.includes('pending') || s.includes('en attente')) return 'bg-daba-cream-alt text-daba-orange dark:bg-[rgb(43,44,43)]/30 dark:text-daba-orange'
  if (s.includes('cancel')) return 'bg-daba-cream-alt text-rose-600 dark:bg-[rgb(43,44,43)]/30 dark:text-rose-400'
  if (s.includes('shipped') || s.includes('expédié')) return 'bg-daba-cream-alt text-daba-navy dark:bg-[rgb(43,44,43)]/30 dark:text-daba-navy'
  return 'bg-daba-cream-alt text-daba-slate dark:bg-[rgb(43,44,43)]/30 dark:text-daba-slate-dark'
}

/**
 * Formate une date au format court (JJ/MM)
 */
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' })
}

/**
 * Construit l'URL absolue de l'image pour l'administration
 */
const getImageUrl = (url) => getProductImageUrl(url)

// Initialisation au montage du composant
// Initialisation du flux SSE (Live updates)
const initSSE = () => {
  const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8080'
  const token = localStorage.getItem('access_token')
  if (eventSource) eventSource.close()
  eventSource = new EventSource(`${apiUrl}/admin/stream.php?token=${token}`)
  
  eventSource.onmessage = (event) => {
    try {
      const data = JSON.parse(event.data)
      
      if (data.type === 'new_order') {
        // Notification verte pour les vraies commandes uniquement
        addNotification({ 
          type: 'success', 
          title: 'Nouvelle commande !',
          message: data.message, 
          duration: 8000 
        })
        // Rafraichir les stats
        loadStats()
      }
    } catch (e) {
      console.error('Erreur traitement SSE', e)
    }
  }

  // Gestion de l'expiration du token SSE
  eventSource.addEventListener('auth_error', async () => {
    console.warn('SSE Auth error, tentative de refresh...')
    eventSource.close()
    const newToken = await authStore.refreshToken()
    if (newToken) initSSE()
  })
}

// Initialisation au montage du composant
onMounted(async () => {
  // 1. Priorité absolue : charger les données du Dashboard
  await loadStats()
  
  // 2. Lancer le flux SSE seulement APRÈS un court délai pour laisser le serveur respirer
  setTimeout(() => {
    initSSE()
  }, 1000)
})
 
onUnmounted(() => {
  if (ctxn) ctxn.revert();
  if (eventSource) eventSource.close();
})
</script>


