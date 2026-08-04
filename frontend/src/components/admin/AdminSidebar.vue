<template>
  <div class="sidebar-container w-64 bg-daba-cream dark:bg-daba-dark-bg text-daba-navy dark:text-white flex flex-col h-screen shadow-xl border-r border-daba-cream-alt dark:border-daba-dark-border transition-all duration-300">
    <!-- Logo -->
    <div class="sidebar-logo p-6 border-b border-daba-cream-alt dark:border-daba-dark-border flex items-center justify-start px-6 gap-3">
      <div class="w-10 h-10 rounded-xl bg-daba-cream-alt dark:bg-daba-dark-card flex items-center justify-center overflow-hidden shadow-sm border border-daba-cream-alt dark:border-daba-dark-border">
        <img src="/src/assets/daba-icone.png" class="w-full h-full object-cover" alt="Daba Logo">
      </div>
      <div>
        <h2 class="text-xl font-black tracking-tight text-daba-navy dark:text-white leading-none">Daba</h2>
        <p class="text-[8px] text-daba-orange dark:text-daba-slate-dark uppercase tracking-[0.4em] font-bold mt-1">{{ authStore.user?.role || '' }}</p>
      </div>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto custom-scrollbar">
      <router-link
        v-for="item in menuItems"
        :key="item.name"
        :to="item.path"
        class="sidebar-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group"
        :class="isActive(item.path) 
          ? 'bg-daba-orange/10 dark:bg-daba-orange/50 text-daba-navy dark:text-white shadow-sm' 
          : 'text-daba-slate dark:text-daba-slate-dark hover:bg-daba-cream-alt dark:hover:bg-daba-dark-card/50 hover:text-daba-navy dark:hover:text-white'"
      >
        <component 
          :is="item.icon" 
          class="w-5 h-5 mr-3 transition-colors"
          :class="isActive(item.path) ? 'text-daba-navy dark:text-white' : 'text-daba-slate-dark dark:text-daba-slate group-hover:text-daba-orange dark:group-hover:text-white'"
        />
        {{ item.name }}
      </router-link>
    </nav>

    <!-- Bottom Actions -->
    <div class="sidebar-bottom p-4 border-t border-daba-cream-alt dark:border-daba-dark-border space-y-1">
      <router-link 
        to="/" 
        class="flex items-center px-4 py-3 text-sm font-medium text-daba-slate dark:text-daba-slate-dark hover:text-daba-navy dark:hover:text-white transition-colors rounded-xl hover:bg-daba-cream-alt dark:hover:bg-daba-dark-card"
      >
        <ExternalLink class="w-5 h-5 mr-3" />
        Voir le site
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router'
import { computed } from 'vue'
import { onMounted, onUnmounted } from 'vue'
import { gsap } from 'gsap'
import { useAuthStore } from '@/stores/auth'
import {
  LayoutDashboard,
  Package,
  ShoppingCart,
  Users,
  BarChart3,
  Settings,
  ExternalLink,
  FileText,
  Warehouse
} from 'lucide-vue-next'

const route = useRoute()
const authStore = useAuthStore()

const userRole = computed(() => authStore.user?.role || 'customer')

const menuItems = computed(() => {
  const role = userRole.value

  const allItems = [
    { name: 'Dashboard', path: '/admin/dashboard', icon: LayoutDashboard, roles: ['admin', 'commercial', 'magasinier', 'comptable'] },
    { name: 'Produits', path: '/admin/products', icon: Package, roles: ['admin', 'magasinier'] },
    { name: 'Stock', path: '/admin/stock', icon: Warehouse, roles: ['admin', 'magasinier'] },
    { name: 'Commandes', path: '/admin/orders', icon: ShoppingCart, roles: ['admin', 'commercial', 'comptable'] },
    { name: 'Clients', path: '/admin/users', icon: Users, roles: ['admin'] },
    { name: 'Factures', path: '/admin/invoices', icon: FileText, roles: ['admin', 'comptable'] },
    { name: 'Analytiques', path: '/admin/analytics', icon: BarChart3, roles: ['admin', 'commercial', 'comptable'] },
    { name: 'Paramètres', path: '/admin/settings', icon: Settings, roles: ['admin'] },
  ]

  return allItems.filter(item => item.roles.includes(role))
})

const isActive = (path) => {
  return route.path === path || route.path.startsWith(path + '/')
}

let ctx;

onMounted(() => {
  ctx = gsap.context(() => {
    const tl = gsap.timeline({ defaults: { ease: 'power3.out' } })
    tl.fromTo('.sidebar-container', { x: -50, opacity: 0 }, { x: 0, opacity: 1, duration: 0.6 })
    tl.fromTo('.sidebar-logo', { scale: 0.8, opacity: 0 }, { scale: 1, opacity: 1, duration: 0.5 }, '-=0.3')
    tl.fromTo('.sidebar-item', { x: -20, opacity: 0 }, { x: 0, opacity: 1, duration: 0.5, stagger: 0.05 }, '-=0.2')
    tl.fromTo('.sidebar-bottom', { y: 20, opacity: 0 }, { y: 0, opacity: 1, duration: 0.5 }, '-=0.2')
  })
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
  background: #334155;
  border-radius: 10px;
}
</style>


