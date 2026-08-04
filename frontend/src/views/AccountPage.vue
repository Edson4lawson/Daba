<template>
  <div class="min-h-screen bg-daba-cream-alt pt-32 pb-20">
    <div class="container mx-auto px-4 md:px-6">
      
      <!-- Top Action: Back to Home -->
      <div class="flex justify-end mb-8">
        <router-link to="/" class="flex items-center gap-2 px-6 py-3 bg-daba-cream text-daba-orange font-bold uppercase tracking-widest text-xs rounded-2xl hover:bg-daba-cream-alt transition-all shadow-sm border border-daba-cream-alt">
          <Icon icon="solar:home-bold-duotone" class="w-5 h-5" />
          Retour à l'accueil
        </router-link>
      </div>

      <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar Navigation -->
        <aside class="w-full lg:w-80 shrink-0">
          <div class="bg-daba-cream rounded-[2.5rem] shadow-xl shadow-daba-cream-alt/50 p-8 border border-daba-cream-alt">
            <div class="flex items-center gap-4 mb-10">
              <div class="w-16 h-16 rounded-full bg-daba-orange flex items-center justify-center text-white text-2xl font-black uppercase ring-4 ring-daba-cream-alt">
                {{ user?.first_name?.charAt(0) || 'U' }}
              </div>
              <div>
                <h2 class="text-xl font-black text-daba-navy truncate">{{ user?.first_name }} {{ user?.last_name }}</h2>
                <p class="text-sm text-daba-slate-dark font-bold uppercase tracking-widest">{{ user?.role === 'admin' ? 'Administrateur' : 'Client Privilège' }}</p>
              </div>
            </div>

            <nav class="space-y-2">
              <button 
                v-for="item in menuItems" 
                :key="item.id"
                @click="activeTab = item.id"
                class="w-full flex items-center justify-between px-6 py-4 rounded-2xl transition-all font-black uppercase tracking-widest text-[10px]"
                :class="activeTab === item.id ? 'bg-daba-navy text-white shadow-lg shadow-slate-900/20' : 'text-daba-slate-dark hover:bg-daba-cream-alt hover:text-daba-navy'">
                <div class="flex items-center gap-3">
                  <Icon :icon="item.icon" class="w-5 h-5" />
                  {{ item.label }}
                </div>
                <Icon v-if="activeTab === item.id" icon="solar:arrow-right-linear" class="w-4 h-4" />
              </button>

              <div class="pt-6 mt-6 border-t border-daba-cream-alt">
                <button @click="handleLogout" class="w-full flex items-center gap-3 px-6 py-4 rounded-2xl text-rose-500 font-black uppercase tracking-widest text-[10px] hover:bg-rose-50 transition-all">
                  <Icon icon="solar:logout-linear" class="w-5 h-5" />
                  Déconnexion
                </button>
              </div>
            </nav>
          </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1">
          <div class="bg-daba-cream rounded-[2.5rem] shadow-xl shadow-daba-cream-alt/50 p-8 md:p-12 border border-daba-cream-alt min-h-[600px]">
            
            <!-- Dashboard Overview -->
            <div v-if="activeTab === 'overview'" class="space-y-10">
              <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
                <h1 class="text-3xl font-black text-daba-navy italic uppercase">Tableau de <span class="text-daba-orange">bord</span></h1>
                <p class="text-daba-slate-dark font-bold">Bienvenue, {{ user?.first_name }} ! 👋</p>
              </div>

              <!-- Stats Grid -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-daba-cream-alt p-8 rounded-[2rem] border border-daba-cream-alt">
                  <Icon icon="solar:bag-bold-duotone" class="w-10 h-10 text-daba-orange mb-4" />
                  <p class="text-sm font-black text-daba-navy uppercase tracking-widest opacity-60">Commandes</p>
                  <p class="text-3xl font-black text-daba-navy">{{ orders.length }}</p>
                </div>
                <div class="bg-daba-cream-alt p-8 rounded-[2rem] border border-daba-cream-alt">
                  <Icon icon="solar:heart-bold-duotone" class="w-10 h-10 text-daba-orange mb-4" />
                  <p class="text-sm font-black text-daba-navy uppercase tracking-widest opacity-60">Favoris</p>
                  <p class="text-3xl font-black text-daba-navy">{{ wishlistCount }}</p>
                </div>
                <div class="bg-daba-cream-alt p-8 rounded-[2rem] border border-daba-cream-alt">
                  <Icon icon="solar:wallet-bold-duotone" class="w-10 h-10 text-daba-orange mb-4" />
                  <p class="text-sm font-black text-daba-navy uppercase tracking-widest opacity-60">Dépenses</p>
                  <p class="text-3xl font-black text-daba-navy">{{ totalSpent.toLocaleString() }} <span class="text-xs">FCFA</span></p>
                </div>
              </div>

              <!-- Recent Activity -->
              <div>
                <h3 class="text-xl font-black text-daba-navy mb-6 uppercase tracking-tight">Activité Récente</h3>
                <div v-if="orders.length === 0" class="text-center py-12 bg-daba-cream-alt rounded-[2rem]">
                  <p class="text-daba-slate-dark font-bold">Vous n'avez pas encore passé de commande.</p>
                  <router-link to="/" class="mt-4 inline-block text-daba-orange font-black uppercase tracking-widest text-[10px]">Commencer mes achats →</router-link>
                </div>
                <div v-else class="space-y-4">
                   <div v-for="order in orders.slice(0, 3)" :key="order.id" class="flex items-center justify-between p-6 bg-daba-cream-alt rounded-2xl border border-daba-cream-alt">
                     <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-daba-cream rounded-xl flex items-center justify-center font-black text-daba-navy shadow-sm border border-daba-cream-alt">#{{ order.id }}</div>
                        <div>
                          <p class="text-sm font-black text-daba-navy uppercase">Commande du {{ formatDate(order.created_at) }}</p>
                          <p class="text-xs text-daba-slate-dark font-bold">{{ order.total_amount.toLocaleString() }} FCFA • {{ order.status }}</p>
                        </div>
                     </div>
                     <button class="px-4 py-2 bg-daba-cream rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-600 hover:bg-daba-navy hover:text-white transition-all shadow-sm">Détails</button>
                   </div>
                </div>
              </div>
            </div>

            <!-- Orders Tab -->
            <div v-if="activeTab === 'orders'" class="space-y-8">
              <h1 class="text-3xl font-black text-daba-navy italic uppercase">Mes <span class="text-daba-orange">Commandes</span></h1>
              <div class="overflow-x-auto">
                <table class="w-full text-left">
                  <thead>
                    <tr class="border-b border-daba-cream-alt">
                      <th class="pb-4 text-[10px] font-black text-daba-slate-dark uppercase tracking-widest">N°</th>
                      <th class="pb-4 text-[10px] font-black text-daba-slate-dark uppercase tracking-widest">Date</th>
                      <th class="pb-4 text-[10px] font-black text-daba-slate-dark uppercase tracking-widest">Statut</th>
                      <th class="pb-4 text-[10px] font-black text-daba-slate-dark uppercase tracking-widest">Total</th>
                      <th class="pb-4"></th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-daba-cream-alt">
                    <tr v-for="order in orders" :key="order.id" class="group hover:bg-daba-cream-alt transition-colors">
                      <td class="py-6 font-black text-daba-navy">#{{ order.id }}</td>
                      <td class="py-6 text-sm text-daba-slate font-medium">{{ formatDate(order.created_at) }}</td>
                      <td class="py-6">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"
                          :class="getStatusClass(order.status)">
                          {{ order.status }}
                        </span>
                      </td>
                      <td class="py-6 font-black text-daba-navy">{{ order.total_amount.toLocaleString() }} FCFA</td>
                      <td class="py-6 text-right">
                        <button class="text-daba-orange hover:text-daba-navy font-black uppercase tracking-widest text-[10px]">Facture</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Profile Tab -->
            <div v-if="activeTab === 'profile'" class="space-y-10">
              <h1 class="text-3xl font-black text-daba-navy italic uppercase">Mon <span class="text-daba-orange">Profil</span></h1>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-6">
                  <div class="space-y-2">
                    <label class="text-[10px] font-black text-daba-slate-dark uppercase tracking-widest">Prénom</label>
                    <input type="text" v-model="userForm.first_name" class="w-full px-6 py-4 bg-daba-cream-alt border border-daba-cream-alt rounded-2xl focus:outline-none focus:ring-2 focus:ring-daba-cream-alt0/20 font-bold text-daba-navy">
                  </div>
                  <div class="space-y-2">
                    <label class="text-[10px] font-black text-daba-slate-dark uppercase tracking-widest">Nom</label>
                    <input type="text" v-model="userForm.last_name" class="w-full px-6 py-4 bg-daba-cream-alt border border-daba-cream-alt rounded-2xl focus:outline-none focus:ring-2 focus:ring-daba-cream-alt0/20 font-bold text-daba-navy">
                  </div>
                </div>
                <div class="space-y-6">
                  <div class="space-y-2">
                    <label class="text-[10px] font-black text-daba-slate-dark uppercase tracking-widest">Email</label>
                    <input type="email" :value="user?.email" disabled class="w-full px-6 py-4 bg-slate-100 border border-daba-cream-alt rounded-2xl focus:outline-none font-bold text-daba-slate-dark cursor-not-allowed">
                  </div>
                  <div class="space-y-2">
                    <label class="text-[10px] font-black text-daba-slate-dark uppercase tracking-widest">Téléphone</label>
                    <input type="text" v-model="userForm.phone" class="w-full px-6 py-4 bg-daba-cream-alt border border-daba-cream-alt rounded-2xl focus:outline-none focus:ring-2 focus:ring-daba-cream-alt0/20 font-bold text-daba-navy">
                  </div>
                </div>
                <!-- Adresse Field -->
                <div class="space-y-2 md:col-span-2">
                  <label class="text-[10px] font-black text-daba-slate-dark uppercase tracking-widest">Adresse</label>
                  <textarea v-model="userForm.address" rows="3" class="w-full px-6 py-4 bg-daba-cream-alt border border-daba-cream-alt rounded-2xl focus:outline-none focus:ring-2 focus:ring-daba-cream-alt0/20 font-bold text-daba-navy" placeholder="Votre adresse complète..."></textarea>
                </div>
              </div>
              <div class="flex justify-end pt-6">
                <button @click="updateProfile" class="px-10 py-5 bg-daba-navy text-white font-black uppercase tracking-widest text-xs rounded-2xl hover:bg-daba-orange transition-all shadow-xl shadow-slate-900/10">Sauvegarder les modifications</button>
              </div>
            </div>

            <!-- Addresses Tab -->
            <div v-if="activeTab === 'addresses'" class="space-y-10">
              <div class="flex items-center justify-between">
                <h1 class="text-3xl font-black text-daba-navy italic uppercase">Mes <span class="text-daba-orange">Adresses</span></h1>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-if="user?.address" class="p-8 border border-daba-cream-alt bg-daba-cream-alt rounded-[2rem] relative group w-full">
                  <div class="flex items-center gap-3 mb-4">
                    <Icon icon="solar:home-bold-duotone" class="w-6 h-6 text-daba-orange" />
                    <h4 class="font-black text-daba-navy uppercase text-xs tracking-widest">Adresse Enregistrée</h4>
                  </div>
                  <p class="text-daba-slate font-bold leading-relaxed whitespace-pre-line">{{ user.address }}</p>
                  <div class="mt-6 flex gap-4">
                    <button @click="activeTab = 'profile'" class="text-[10px] font-black text-daba-orange uppercase tracking-widest underline">Modifier dans mon profil</button>
                  </div>
                </div>
                <div v-else class="p-8 border border-daba-cream-alt bg-daba-cream-alt rounded-[2rem] text-center w-full md:col-span-2">
                  <Icon icon="solar:map-point-bold-duotone" class="w-12 h-12 text-slate-300 mx-auto mb-4" />
                  <p class="text-daba-slate-dark font-bold mb-4">Aucune adresse enregistrée pour le moment.</p>
                  <button @click="activeTab = 'profile'" class="px-6 py-3 bg-daba-orange text-white font-black uppercase tracking-widest text-[10px] rounded-2xl hover:bg-daba-orange-dark transition-all">
                    Ajouter une adresse
                  </button>
                </div>
              </div>
            </div>

          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Icon } from '@iconify/vue';
import { useAuthStore } from '../stores/auth';
import { useWishlistStore } from '../stores/wishlist';
import api from '../services/api';
import Swal from 'sweetalert2';

const authStore = useAuthStore();
const wishlistStore = useWishlistStore();
const user = computed(() => authStore.user);
const wishlistCount = computed(() => wishlistStore.totalItems);

const activeTab = ref('overview');
const orders = ref([]);
const totalSpent = ref(0);

const menuItems = [
  { id: 'overview', label: 'Vue d\'ensemble', icon: 'solar:widget-bold-duotone' },
  { id: 'orders', label: 'Commandes', icon: 'solar:bag-bold-duotone' },
  { id: 'profile', label: 'Profil', icon: 'solar:user-bold-duotone' },
  { id: 'addresses', label: 'Adresses', icon: 'solar:map-point-bold-duotone' },
];

const userForm = ref({
  first_name: '',
  last_name: '',
  phone: '',
  address: '',
});

// Watch user info updates to sync with form
watch(user, (newUser) => {
  if (newUser) {
    userForm.value.first_name = newUser.first_name || '';
    userForm.value.last_name = newUser.last_name || '';
    userForm.value.phone = newUser.phone || '';
    userForm.value.address = newUser.address || '';
  }
}, { immediate: true });

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: 'numeric', month: 'long', year: 'numeric'
  });
};

const getStatusClass = (status) => {
  switch (status?.toLowerCase()) {
    case 'livré': return 'bg-green-100 text-green-700';
    case 'en cours': return 'bg-blue-100 text-blue-700';
    case 'annulé': return 'bg-rose-100 text-rose-700';
    default: return 'bg-slate-100 text-slate-700';
  }
};

const fetchOrders = async () => {
  try {
    const response = await api.get('/orders/get_user_orders.php');
    orders.value = response.data.data || [];
    totalSpent.value = orders.value.reduce((acc, order) => acc + parseFloat(order.total_amount), 0);
  } catch (err) {
    console.warn('Orders fetch failed:', err);
  }
};

const handleLogout = async () => {
  const result = await Swal.fire({
    title: 'Déconnexion ?',
    text: 'Voulez-vous vraiment vous déconnecter ?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#9333ea',
    confirmButtonText: 'Oui, déconnexion',
    cancelButtonText: 'Annuler'
  });

  if (result.isConfirmed) {
    await authStore.logout();
    window.location.href = '/';
  }
};

const updateProfile = async () => {
  try {
    await authStore.updateProfile(userForm.value);
    Swal.fire({ 
      icon: 'success', 
      title: 'Profil mis à jour !', 
      toast: true, 
      position: 'top-end', 
      showConfirmButton: false, 
      timer: 3000 
    });
  } catch (err) {
    Swal.fire({ 
      icon: 'error', 
      title: err || 'Erreur lors de la mise à jour' 
    });
  }
};

onMounted(() => {
  if (authStore.isAuthenticated) {
    fetchOrders();
  }
});
</script>

<style scoped>
/* Custom animations if needed */
</style>

