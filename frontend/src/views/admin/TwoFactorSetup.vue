<template>
  <div class="min-h-screen bg-daba-cream-alt/50 dark:bg-daba-dark-bg flex items-center justify-center p-4 transition-colors duration-500">
    <!-- Background Effects -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-daba-orange/5 rounded-full blur-3xl animate-pulse"></div>
      <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-daba-navy/5 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <div class="relative z-10 w-full max-w-lg">
      <!-- Logo -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center gap-3 mb-4">
           <img src="/src/assets/daba-icone.png" class="w-12 h-12 rounded-xl shadow-lg border border-daba-cream-alt dark:border-daba-dark-border" alt="Daba">
           <div class="text-left">
             <h1 class="text-3xl font-black text-daba-navy dark:text-white tracking-tight leading-none">Daba</h1>
             <p class="text-[10px] text-daba-orange dark:text-daba-orange font-black uppercase tracking-[0.3em] mt-1">Configuration 2FA</p>
           </div>
        </div>
      </div>

      <!-- Setup Card -->
      <div class="bg-daba-cream dark:bg-daba-dark-card border border-daba-cream-alt dark:border-daba-dark-border rounded-3xl p-8 shadow-2xl shadow-daba-orange/20 dark:shadow-none">
        <!-- Error Alert -->
        <div v-if="error" class="p-4 bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 rounded-2xl text-rose-600 dark:text-rose-400 text-sm font-bold text-center mb-6">
          {{ error }}
        </div>

        <!-- Success Alert -->
        <div v-if="success" class="p-4 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-2xl text-emerald-600 dark:text-emerald-400 text-sm font-bold text-center mb-6">
          {{ success }}
        </div>

        <!-- Step 1: Choose Method -->
        <div v-if="step === 1">
          <div class="text-center mb-6">
            <h2 class="text-lg font-black text-daba-navy dark:text-white mb-2">Choisissez votre méthode de 2FA</h2>
            <p class="text-sm text-daba-slate dark:text-daba-slate-dark">
              Sélectionnez comment vous souhaitez recevoir vos codes de vérification
            </p>
          </div>

          <div class="space-y-4">
            <!-- QR Code Option -->
            <button 
              @click="method = 'qr'; step = 2"
              class="w-full p-6 bg-white dark:bg-daba-dark-bg/50 border-2 border-daba-cream-alt dark:border-daba-dark-border hover:border-daba-orange rounded-2xl text-left transition-all hover:scale-[1.02]"
            >
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-daba-orange/10 rounded-xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-daba-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="font-black text-daba-navy dark:text-white">Application d'authentification</h3>
                  <p class="text-xs text-daba-slate dark:text-daba-slate-dark">Google Authenticator, Authy, etc.</p>
                </div>
              </div>
            </button>

            <!-- Email Option -->
            <button 
              @click="method = 'email'; step = 3"
              class="w-full p-6 bg-white dark:bg-daba-dark-bg/50 border-2 border-daba-cream-alt dark:border-daba-dark-border hover:border-daba-orange rounded-2xl text-left transition-all hover:scale-[1.02]"
            >
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-daba-navy/10 rounded-xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-daba-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="font-black text-daba-navy dark:text-white">Email</h3>
                  <p class="text-xs text-daba-slate dark:text-daba-slate-dark">Recevoir un code par email</p>
                </div>
              </div>
            </button>
          </div>
        </div>

        <!-- Step 2: Generate QR Code -->
        <div v-if="step === 2">
          <div class="text-center mb-6">
            <h2 class="text-lg font-black text-daba-navy dark:text-white mb-2">Activer la double authentification</h2>
            <p class="text-sm text-daba-slate dark:text-daba-slate-dark">
              Scannez le QR code avec votre application d'authentification (Google Authenticator, Authy, etc.)
            </p>
          </div>

          <button 
            @click="generateQRCode"
            :disabled="loading"
            class="w-full py-4 bg-gradient-to-r from-daba-orange to-daba-navy text-white rounded-2xl font-black text-sm uppercase tracking-[0.2em] shadow-xl shadow-daba-orange dark:shadow-none hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed mb-6"
          >
            <span v-if="!loading">Générer le QR Code</span>
            <span v-else class="flex items-center justify-center gap-2">
              <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
              Génération...
            </span>
          </button>

          <!-- QR Code Display -->
          <div v-if="qrCodeData" class="bg-white p-6 rounded-2xl mb-6">
            <div class="flex justify-center mb-4">
              <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(qrCodeData.otpauth_url)}`" alt="QR Code" class="w-48 h-48">
            </div>
            <div class="text-center">
              <p class="text-xs text-daba-slate mb-2">Secret (copiez-le si nécessaire):</p>
              <code class="text-xs bg-daba-cream-alt px-3 py-2 rounded-lg select-all">{{ qrCodeData.secret }}</code>
            </div>
            <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl">
              <p class="text-xs text-amber-700 dark:text-amber-400 font-bold">
                ⚠️ Sauvegardez vos codes de secours avant de continuer
              </p>
            </div>
          </div>

          <!-- Backup Codes -->
          <div v-if="qrCodeData" class="bg-daba-cream-alt dark:bg-daba-dark-bg/50 rounded-2xl p-4 mb-6">
            <p class="text-xs font-black uppercase text-daba-slate dark:text-daba-slate-dark tracking-widest mb-3">Codes de secours</p>
            <div class="grid grid-cols-2 gap-2">
              <code v-for="(code, index) in qrCodeData.backup_codes" :key="index" class="text-xs bg-white dark:bg-daba-dark-card px-2 py-1 rounded text-center select-all">
                {{ code }}
              </code>
            </div>
          </div>

          <button 
            v-if="qrCodeData"
            @click="step = 3"
            class="w-full py-4 bg-daba-navy text-white rounded-2xl font-black text-sm uppercase tracking-[0.2em] shadow-xl hover:scale-[1.02] active:scale-[0.98] transition-all"
          >
            Continuer
          </button>

          <button 
            @click="step = 1"
            class="w-full mt-4 py-3 text-daba-slate dark:text-daba-slate-dark font-bold text-sm hover:text-daba-orange dark:hover:text-white transition-colors"
          >
            ← Retour
          </button>
        </div>

        <!-- Step 3: Verify QR Code -->
        <div v-if="step === 3 && method === 'qr'">
          <div class="text-center mb-6">
            <h2 class="text-lg font-black text-daba-navy dark:text-white mb-2">Vérifier le code</h2>
            <p class="text-sm text-daba-slate dark:text-daba-slate-dark">
              Entrez le code à 6 chiffres généré par votre application d'authentification
            </p>
          </div>

          <div class="space-y-4">
            <div class="space-y-2">
              <label class="text-[10px] font-black uppercase text-daba-slate dark:text-daba-slate-dark tracking-widest pl-2">Code 2FA</label>
              <input
                v-model="verificationCode"
                type="text"
                maxlength="6"
                placeholder="123456"
                class="w-full bg-daba-cream-alt dark:bg-daba-dark-bg/50 border-2 border-daba-cream-alt dark:border-daba-dark-border focus:border-daba-orange rounded-2xl py-4 px-6 text-daba-navy dark:text-white font-bold text-center text-2xl tracking-[0.5em] outline-none transition-all"
              />
            </div>

            <button 
              @click="verifyCode"
              :disabled="loading || verificationCode.length !== 6"
              class="w-full py-4 bg-gradient-to-r from-daba-orange to-daba-navy text-white rounded-2xl font-black text-sm uppercase tracking-[0.2em] shadow-xl shadow-daba-orange dark:shadow-none hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="!loading">Activer le 2FA</span>
              <span v-else class="flex items-center justify-center gap-2">
                <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                Vérification...
              </span>
            </button>
          </div>

          <button 
            @click="step = 2"
            class="w-full mt-4 py-3 text-daba-slate dark:text-daba-slate-dark font-bold text-sm hover:text-daba-orange dark:hover:text-white transition-colors"
          >
            ← Retour
          </button>
        </div>

        <!-- Step 3: Send Email Code -->
        <div v-if="step === 3 && method === 'email'">
          <div class="text-center mb-6">
            <h2 class="text-lg font-black text-daba-navy dark:text-white mb-2">Recevoir un code par email</h2>
            <p class="text-sm text-daba-slate dark:text-daba-slate-dark">
              Un code à 6 chiffres sera envoyé à votre adresse email
            </p>
          </div>

          <button 
            @click="sendEmailCode"
            :disabled="loading"
            class="w-full py-4 bg-gradient-to-r from-daba-orange to-daba-navy text-white rounded-2xl font-black text-sm uppercase tracking-[0.2em] shadow-xl shadow-daba-orange dark:shadow-none hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed mb-6"
          >
            <span v-if="!loading">Envoyer le code par email</span>
            <span v-else class="flex items-center justify-center gap-2">
              <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
              Envoi...
            </span>
          </button>

          <div v-if="emailSent" class="p-4 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-2xl text-emerald-600 dark:text-emerald-400 text-sm font-bold text-center mb-6">
            Code envoyé ! Vérifiez votre email.
          </div>

          <button 
            v-if="emailSent"
            @click="step = 4"
            class="w-full py-4 bg-daba-navy text-white rounded-2xl font-black text-sm uppercase tracking-[0.2em] shadow-xl hover:scale-[1.02] active:scale-[0.98] transition-all"
          >
            Continuer
          </button>

          <button 
            @click="step = 1"
            class="w-full mt-4 py-3 text-daba-slate dark:text-daba-slate-dark font-bold text-sm hover:text-daba-orange dark:hover:text-white transition-colors"
          >
            ← Retour
          </button>
        </div>

        <!-- Step 4: Verify Email Code -->
        <div v-if="step === 4">
          <div class="text-center mb-6">
            <h2 class="text-lg font-black text-daba-navy dark:text-white mb-2">Vérifier le code email</h2>
            <p class="text-sm text-daba-slate dark:text-daba-slate-dark">
              Entrez le code à 6 chiffres reçu par email
            </p>
          </div>

          <div class="space-y-4">
            <div class="space-y-2">
              <label class="text-[10px] font-black uppercase text-daba-slate dark:text-daba-slate-dark tracking-widest pl-2">Code reçu par email</label>
              <input
                v-model="verificationCode"
                type="text"
                maxlength="6"
                placeholder="123456"
                class="w-full bg-daba-cream-alt dark:bg-daba-dark-bg/50 border-2 border-daba-cream-alt dark:border-daba-dark-border focus:border-daba-orange rounded-2xl py-4 px-6 text-daba-navy dark:text-white font-bold text-center text-2xl tracking-[0.5em] outline-none transition-all"
              />
            </div>

            <button 
              @click="verifyEmailCode"
              :disabled="loading || verificationCode.length !== 6"
              class="w-full py-4 bg-gradient-to-r from-daba-orange to-daba-navy text-white rounded-2xl font-black text-sm uppercase tracking-[0.2em] shadow-xl shadow-daba-orange dark:shadow-none hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="!loading">Activer le 2FA</span>
              <span v-else class="flex items-center justify-center gap-2">
                <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                Vérification...
              </span>
            </button>
          </div>

          <button 
            @click="step = 3"
            class="w-full mt-4 py-3 text-daba-slate dark:text-daba-slate-dark font-bold text-sm hover:text-daba-orange dark:hover:text-white transition-colors"
          >
            ← Retour
          </button>
        </div>

        <!-- Step 5: Success -->
        <div v-if="step === 5">
          <div class="text-center">
            <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
              <svg class="w-10 h-10 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
            <h2 class="text-xl font-black text-daba-navy dark:text-white mb-2">2FA activé avec succès !</h2>
            <p class="text-sm text-daba-slate dark:text-daba-slate-dark mb-6">
              Votre compte est maintenant protégé par la double authentification.
            </p>
            <router-link 
              to="/admin/dashboard"
              class="inline-block py-4 px-8 bg-gradient-to-r from-daba-orange to-daba-navy text-white rounded-2xl font-black text-sm uppercase tracking-[0.2em] shadow-xl shadow-daba-orange dark:shadow-none hover:scale-[1.02] active:scale-[0.98] transition-all"
            >
              Accéder au dashboard
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()

const step = ref(1)
const method = ref('qr')
const loading = ref(false)
const error = ref(null)
const success = ref(null)
const qrCodeData = ref(null)
const verificationCode = ref('')
const emailSent = ref(false)

const generateQRCode = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await api.post('/auth/2fa/setup')
    qrCodeData.value = response.data
  } catch (err) {
    error.value = err.response?.data?.error || 'Erreur lors de la génération du QR code'
  } finally {
    loading.value = false
  }
}

const verifyCode = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await api.post('/auth/2fa/verify', { code: verificationCode.value })
    success.value = response.data.message
    step.value = 5
  } catch (err) {
    error.value = err.response?.data?.error || 'Code incorrect'
  } finally {
    loading.value = false
  }
}

const sendEmailCode = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await api.post('/auth/2fa/email')
    success.value = response.data.message
    emailSent.value = true
  } catch (err) {
    error.value = err.response?.data?.error || 'Erreur lors de l\'envoi de l\'email'
  } finally {
    loading.value = false
  }
}

const verifyEmailCode = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await api.post('/auth/2fa/verify-email', { code: verificationCode.value })
    success.value = response.data.message
    step.value = 5
  } catch (err) {
    error.value = err.response?.data?.error || 'Code incorrect'
  } finally {
    loading.value = false
  }
}
</script>
