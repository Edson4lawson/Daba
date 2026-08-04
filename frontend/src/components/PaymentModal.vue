<template>
  <div v-if="isOpen" class="fixed inset-0 z-[200] flex items-start justify-center bg-black/50 backdrop-blur-sm pt-32 md:pt-40 overflow-y-auto">
    <div class="bg-daba-cream rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-2xl font-bold text-daba-navy">Choisir le paiement</h3>
        <button @click="closeModal" class="text-daba-slate-dark hover:text-daba-navy">
          <Icon icon="solar:close-circle-bold" class="w-6 h-6" />
        </button>
      </div>

      <div class="space-y-4">
        <!-- Mobile Money BJ -->
        <div 
          @click="selectPayment('mobile_money_bj')"
          class="border-2 border-daba-cream-alt rounded-2xl p-4 cursor-pointer transition-all hover:border-daba-navy hover:bg-daba-cream-alt"
          :class="{ 'border-daba-navy bg-daba-cream-alt': selectedProvider === 'mobile_money_bj' }"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <div class="w-12 h-12 bg-daba-navy rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 48 48" class="w-6 h-6 text-yellow-400">
                  <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M27.514 22.84a4.973 4.973 0 0 1-4.977 4.97h0a4.973 4.973 0 0 1-4.977-4.97h0a4.973 4.973 0 0 1 4.977-4.97h0a4.973 4.973 0 0 1 4.977 4.97M31.153 4.5l-4.28 6.593c5.98 4.98 9.916 12.094 11.657 19.62c.41-6.817-.245-13.918-3.433-20.07C34 8.466 32.66 6.416 31.153 4.5M20.42 10.735L9.36 27.509c8.41 1.282 16.409 5.09 22.63 10.893c1.74 1.597 3.38 3.3 4.91 5.098c.734-9.014-1.584-18.514-7.56-25.47c-2.493-2.95-5.547-5.414-8.92-7.295" stroke-width="1"/>
                </svg>
              </div>
              <div>
                <h4 class="font-semibold text-daba-navy">MTN Mobile Money</h4>
                <p class="text-sm text-daba-slate">Paiement Mobile MTN</p>
              </div>
            </div>
            <div class="w-6 h-6 rounded-full border-2 border-daba-cream-alt" 
                 :class="{ 'bg-daba-navy border-daba-navy': selectedProvider === 'mobile_money_bj' }">
              <div v-if="selectedProvider === 'mobile_money_bj'" class="w-full h-full flex items-center justify-center">
                <Icon icon="mdi:check" class="w-4 h-4 text-white" />
              </div>
            </div>
          </div>
        </div>

        <!-- Celtis Cash BJ -->
        <div 
          @click="selectPayment('celtis_cash_bj')"
          class="border-2 border-daba-cream-alt rounded-2xl p-4 cursor-pointer transition-all hover:border-daba-green hover:bg-daba-cream-alt"
          :class="{ 'border-daba-green bg-daba-cream-alt': selectedProvider === 'celtis_cash_bj' }"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <div class="w-12 h-12 bg-daba-cream-alt rounded-full flex items-center justify-center">
                <Icon icon="mdi:cash" class="w-6 h-6 text-daba-green" />
              </div>
              <div>
                <h4 class="font-semibold text-daba-navy">Celtis Cash (Celtis)</h4>
                <p class="text-sm text-daba-slate">Portefeuille mobile</p>
              </div>
            </div>
            <div class="w-6 h-6 rounded-full border-2 border-daba-cream-alt" 
                 :class="{ 'bg-daba-green border-daba-green': selectedProvider === 'celtis_cash_bj' }">
              <div v-if="selectedProvider === 'celtis_cash_bj'" class="w-full h-full flex items-center justify-center">
                <Icon icon="mdi:check" class="w-4 h-4 text-white" />
              </div>
            </div>
          </div>
        </div>

        <!-- UBA Bank -->
        <div 
          @click="selectPayment('uba_bank')"
          class="border-2 border-daba-cream-alt rounded-2xl p-4 cursor-pointer transition-all hover:border-daba-orange hover:bg-daba-cream-alt"
          :class="{ 'border-daba-orange bg-daba-cream-alt': selectedProvider === 'uba_bank' }"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <div class="w-12 h-12 bg-daba-cream-alt rounded-full flex items-center justify-center">
                <Icon icon="mdi:bank" class="w-6 h-6 text-daba-orange" />
              </div>
              <div>
                <h4 class="font-semibold text-daba-navy">Virement Bancaire (UBA)</h4>
                <p class="text-sm text-daba-slate">Compte UBA Bank</p>
              </div>
            </div>
            <div class="w-6 h-6 rounded-full border-2 border-daba-cream-alt" 
                 :class="{ 'bg-daba-orange border-daba-orange': selectedProvider === 'uba_bank' }">
              <div v-if="selectedProvider === 'uba_bank'" class="w-full h-full flex items-center justify-center">
                <Icon icon="mdi:check" class="w-4 h-4 text-white" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Numéro de téléphone pour Mobile Money et Celtis Cash -->
      <div v-if="selectedProvider && selectedProvider !== 'uba_bank'" class="mt-6">
        <label class="block text-sm font-medium text-daba-slate mb-2">
          Numéro de téléphone
        </label>
        <input 
          v-model="phoneNumber"
          type="tel" 
          placeholder="+229 XX XX XX XX"
          class="w-full px-4 py-3 border border-daba-cream-alt rounded-xl focus:ring-2 focus:ring-daba-orange focus:border-transparent"
        >
      </div>

      <!-- Actions -->
      <div class="flex space-x-4 mt-8">
        <button 
          @click="closeModal"
          class="flex-1 px-6 py-3 border border-daba-cream-alt text-daba-slate rounded-xl font-medium hover:bg-daba-cream-alt transition-colors"
        >
          Annuler
        </button>
        <button 
          @click="processPayment"
          :disabled="!selectedProvider || (selectedProvider !== 'uba_bank' && !phoneNumber)"
          class="flex-1 px-6 py-3 bg-daba-orange text-white rounded-xl font-medium hover:bg-daba-orange-dark disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          Payer {{ amount }} Fcfa
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue'
import { Icon } from '@iconify/vue'
import { paymentService } from '@/services/api'
import Swal from 'sweetalert2'

const props = defineProps({
  isOpen: Boolean,
  amount: Number,
  orderId: Number
})

const emit = defineEmits(['close', 'success'])

const selectedProvider = ref('')
const phoneNumber = ref('')

const closeModal = () => {
  selectedProvider.value = ''
  phoneNumber.value = ''
  emit('close')
}

const selectPayment = (provider) => {
  selectedProvider.value = provider
}

const processPayment = async () => {
  try {
    const paymentData = {
      amount: props.amount,
      provider: selectedProvider.value,
      order_id: props.orderId
    }

    if (selectedProvider.value !== 'uba_bank') {
      paymentData.phone_number = phoneNumber.value
    }

    const response = await paymentService.process(paymentData)
    
    if (response.data.success) {
      Swal.fire({
        title: 'Paiement réussi!',
        text: response.data.message,
        icon: 'success',
        confirmButtonColor: '#9333ea'
      })
      emit('success', response.data)
      closeModal()
    } else {
      Swal.fire({
        title: 'Erreur de paiement',
        text: response.data.error || 'Une erreur est survenue',
        icon: 'error',
        confirmButtonColor: '#9333ea'
      })
    }
  } catch (error) {
    Swal.fire({
      title: 'Erreur de connexion',
      text: 'Impossible de traiter le paiement',
      icon: 'error',
      confirmButtonColor: '#9333ea'
    })
  }
}
</script>



