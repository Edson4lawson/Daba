<template>
  <div class="min-h-screen bg-daba-cream-alt dark:bg-daba-navy flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center">
      <div class="mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-100 dark:bg-red-900/30 mb-6">
          <Icon icon="lucide:shield-alert" class="w-10 h-10 text-red-600 dark:text-red-400" />
        </div>
        <h1 class="text-3xl font-black text-daba-navy dark:text-white mb-2">Accès non autorisé</h1>
        <p class="text-daba-slate dark:text-daba-slate-dark">
          Vous n'avez pas les permissions nécessaires pour accéder à cette page.
        </p>
      </div>

      <div class="bg-daba-cream dark:bg-daba-navy-deep rounded-2xl shadow-lg p-6 mb-6">
        <div class="flex items-center justify-center gap-2 mb-4">
          <Icon icon="lucide:user" class="w-5 h-5 text-daba-slate-dark" />
          <span class="text-sm font-medium text-daba-slate dark:text-daba-slate-dark">
            Votre rôle : <span class="font-bold text-daba-navy dark:text-white">{{ userRole }}</span>
          </span>
        </div>
        <p class="text-xs text-daba-slate-dark dark:text-daba-slate-dark">
          Contactez votre administrateur si vous pensez qu'il s'agit d'une erreur.
        </p>
      </div>

      <div class="space-y-3">
        <router-link
          to="/admin/dashboard"
          class="inline-flex items-center justify-center w-full px-6 py-3 bg-daba-orange hover:bg-daba-orange-dark text-white font-bold rounded-xl transition-all duration-200"
        >
          <Icon icon="lucide:layout-dashboard" class="w-5 h-5 mr-2" />
          Retour au Dashboard
        </router-link>
        <router-link
          to="/"
          class="inline-flex items-center justify-center w-full px-6 py-3 bg-daba-cream dark:bg-daba-navy-deep hover:bg-daba-cream-alt dark:hover:bg-daba-navy text-daba-navy dark:text-daba-slate font-bold rounded-xl transition-all duration-200 border border-daba-cream-alt dark:border-daba-navy"
        >
          <Icon icon="lucide:home" class="w-5 h-5 mr-2" />
          Retour à l'accueil
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Icon } from '@iconify/vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const userRole = computed(() => authStore.user?.role || 'Non connecté')
</script>
