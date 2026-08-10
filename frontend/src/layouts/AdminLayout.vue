<template>
  <div class="flex h-screen bg-daba-cream-alt/50 dark:bg-daba-dark-bg">
    <AdminSidebar />
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Top Bar -->
      <header class="h-16 bg-daba-cream dark:bg-daba-dark-card border-b border-daba-cream-alt dark:border-daba-dark-border flex items-center justify-between px-8 shadow-sm">
        <div class="flex items-center gap-4">
          <h1 class="text-lg font-black text-daba-navy dark:text-white tracking-tight">Daba {{ authStore.user?.role || '' }}</h1>
        </div>
        <div class="flex items-center gap-4">
          <ThemeToggle />
          <button @click="handleLogout" class="px-4 py-2 text-xs font-black uppercase tracking-widest text-daba-slate dark:text-daba-cream hover:text-daba-orange dark:hover:text-daba-orange transition-colors">
            Déconnexion
          </button>
        </div>
      </header>
      <!-- Main Content -->
      <main class="flex-1 overflow-y-auto p-8">
        <router-view />
      </main>
    </div>
    <!-- Global Notifications -->
    <NotificationContainer />
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AdminSidebar from '@/components/admin/AdminSidebar.vue'
import ThemeToggle from '@/components/admin/ThemeToggle.vue'
import NotificationContainer from '@/components/admin/NotificationContainer.vue'

const router = useRouter()
const authStore = useAuthStore()

const handleLogout = async () => {
  await authStore.logout()
  router.push('/admin/login')
}
</script>


