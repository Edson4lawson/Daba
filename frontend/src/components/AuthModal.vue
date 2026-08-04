<template>
  <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[200] p-4 overflow-y-auto">
    <div class="bg-daba-cream rounded-2xl shadow-2xl w-full max-w-md p-8 relative max-h-[90vh] overflow-y-auto">
      <!-- Close Button -->
      <button @click="$emit('close')" class="absolute top-4 right-4 text-daba-slate-dark hover:text-daba-navy">
        <Icon icon="mdi:close" class="w-6 h-6" />
      </button>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin" class="space-y-4">
        <h2 class="text-2xl font-bold text-daba-navy mb-4">Bienvenue !</h2>
        
        <div>
          <label class="block text-sm font-medium text-daba-slate mb-1">Email</label>
          <input 
            v-model="loginForm.email" 
            type="email" 
            required
            class="w-full px-4 py-2 border border-daba-cream-alt rounded-lg focus:ring-2 focus:ring-daba-orange focus:border-transparent"
            placeholder="votre@email.com">
        </div>

        <div>
          <label class="block text-sm font-medium text-daba-slate mb-1">Mot de passe</label>
          <input 
            v-model="loginForm.password" 
            type="password" 
            required
            class="w-full px-4 py-2 border border-daba-cream-alt rounded-lg focus:ring-2 focus:ring-daba-orange focus:border-transparent"
            placeholder="••••••••">
        </div>

        <div v-if="error" class="text-red-500 text-sm">{{ error }}</div>

        <button 
          type="submit" 
          :disabled="loading"
          class="w-full bg-daba-orange text-white py-3 rounded-lg font-medium hover:bg-daba-orange-dark transition-colors disabled:opacity-50">
          {{ loading ? 'Connexion...' : 'Se connecter' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Icon } from '@iconify/vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import Swal from 'sweetalert2';

const emit = defineEmits(['close', 'success']);

const authStore = useAuthStore();
const router = useRouter();
const loading = ref(false);
const error = ref('');

const loginForm = ref({
  email: '',
  password: ''
});

const handleLogin = async () => {
  loading.value = true;
  error.value = '';

  try {
    await authStore.login(loginForm.value.email, loginForm.value.password);

    Swal.fire({
      icon: 'success',
      title: 'Connexion réussie !',
      text: `Bienvenue ${authStore.user?.first_name || authStore.user?.email || 'Utilisateur'}`,
      timer: 2000,
      showConfirmButton: false
    });

    const user = authStore.user;
    if (user && user.role === 'admin') {
      router.push({ name: 'AdminDashboard' });
    } else {
      router.push({ name: 'Account' });
    }

    emit('success');
    emit('close');
  } catch (err) {
    error.value = err;
  } finally {
    loading.value = false;
  }
};
</script>



