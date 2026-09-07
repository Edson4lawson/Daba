<template>
  <div class="space-y-6">
    <div class="newsletter-header flex items-center justify-between">
      <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Inscriptions Newsletter</h1>
      <button 
        @click="exportToCSV"
        class="flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-bold rounded-xl hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-900/20"
      >
        <Download class="w-4 h-4 mr-2" />
        Exporter CSV
      </button>
    </div>

    <!-- Summary -->
    <div class="newsletter-summary bg-white dark:bg-[rgb(43,44,43)] rounded-2xl p-6 border border-slate-100 dark:border-slate-500 shadow-sm flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total des abonnés</p>
        <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ subscribers.length }}</h3>
      </div>
      <div class="p-4 bg-orange-50 dark:bg-orange-900/20 rounded-2xl">
        <Mail class="w-8 h-8 text-orange-600 dark:text-orange-400" />
      </div>
    </div>

    <!-- List -->
    <div class="bg-white dark:bg-[rgb(43,44,43)] rounded-2xl shadow-sm border border-slate-100 dark:border-slate-500 overflow-hidden">
      <div class="newsletter-search p-6 border-b border-slate-100 dark:border-slate-500 flex items-center justify-between">
        <div class="relative w-full max-w-sm">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
          <input 
            v-model="search"
            type="text" 
            placeholder="Rechercher un email..." 
            class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent/20 transition-all dark:text-white"
          />
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-500">
          <thead class="bg-slate-50 dark:bg-slate-900/50">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date d'inscription</th>
              <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-[rgb(43,44,43)] divide-y divide-slate-100 dark:divide-slate-500">
            <tr v-for="sub in filteredSubscribers" :key="sub.id" class="newsletter-row hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center mr-3">
                    <User class="w-4 h-4 text-slate-400" />
                  </div>
                  <span class="text-sm font-medium text-slate-900 dark:text-white">{{ sub.email }}</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                {{ new Date(sub.created_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <button 
                  class="p-2 text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                  @click="copyEmail(sub.email)"
                  title="Copier l'email"
                >
                  <Copy class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr v-if="filteredSubscribers.length === 0">
              <td colspan="3" class="px-6 py-12 text-center text-slate-400">
                <div class="flex flex-col items-center">
                  <Search class="w-8 h-8 mb-2 opacity-20" />
                  <p>Aucun abonné trouvé</p>
                </div>
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
import adminService from '@/services/adminService'
import { Mail, Search, User, Copy, Download } from 'lucide-vue-next'
import { gsap } from 'gsap'

const subscribers = ref([])
const search = ref('')

const fetchSubscribers = async () => {
  try {
    const res = await adminService.getSubscribers()
    if (res.success) {
      subscribers.value = res.subscribers
    }
  } catch (err) {
    console.error(err)
  }
}

const filteredSubscribers = computed(() => {
  return subscribers.value.filter(s => 
    s.email.toLowerCase().includes(search.value.toLowerCase())
  )
})

const copyEmail = (email) => {
  navigator.clipboard.writeText(email)
  alert('Email copié dans le presse-papier')
}

const exportToCSV = () => {
  const content = "Email,Date\n" + subscribers.value.map(s => `${s.email},${s.created_at}`).join("\n")
  const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement("a")
  const url = URL.createObjectURL(blob)
  link.setAttribute("href", url)
  link.setAttribute("download", "subscribers_Daba.csv")
  link.style.visibility = 'hidden'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

// --- GSAP Animation ---
let ctx = null

const animateRows = async () => {
    await nextTick()
    if (ctx) ctx.revert()
    
    ctx = gsap.context(() => {
        gsap.from(".newsletter-row", {
            y: 20,
            opacity: 0,
            duration: 0.4,
            stagger: 0.05,
            ease: "power2.out",
            clearProps: "all"
        })
    })
}

watch(filteredSubscribers, () => {
    animateRows()
})

onMounted(async () => {
    await fetchSubscribers()
    
    const tl = gsap.timeline({ defaults: { ease: 'power3.out' } })
    
    tl.from(".newsletter-header", {
        y: -30,
        opacity: 0,
        duration: 0.8
    })
    .from(".newsletter-summary", {
        scale: 0.95,
        opacity: 0,
        duration: 0.6
    }, "-=0.4")
    .from(".newsletter-search", {
        y: 20,
        opacity: 0,
        duration: 0.6
    }, "-=0.2")

    animateRows()
})

onUnmounted(() => {
    if (ctx) ctx.revert()
})
</script>


