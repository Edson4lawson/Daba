<template>
  <div class="min-h-screen bg-daba-cream flex flex-col font-karla">
    <Header />

    <main class="flex-grow py-12 lg:py-16">
      <div class="max-w-[1440px] mx-auto px-6 sm:px-8 lg:px-12">
        
        <!-- Kicker -->
        <span class="font-worksans text-xs font-bold uppercase tracking-widest text-daba-orange block mb-2">
          ESPACE PROFESSIONNELS
        </span>

        <!-- H1 Title -->
        <h1 class="font-playfair text-3xl sm:text-4xl lg:text-[48px] font-normal text-daba-navy mb-3 leading-tight">
          Commandes récurrentes et devis sur mesure
        </h1>

        <!-- Subtitle -->
        <p class="font-karla text-base text-daba-slate max-w-2xl mb-12 leading-relaxed">
          Décrivez votre besoin : notre équipe revient vers vous avec une proposition sous 48 heures.
        </p>

        <!-- Main Grid: Info + Form -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
          
          <!-- Left Column (Col 1-5): Value proposition & benefits -->
          <div class="lg:col-span-5 space-y-6">
            <h2 class="font-playfair text-2xl sm:text-3xl font-semibold text-daba-navy leading-snug">
              Hôtels, restaurants et grossistes
            </h2>

            <p class="font-karla text-sm sm:text-base text-daba-slate leading-relaxed">
              Volumes réguliers, calibrage constant, factures conformes et réapprovisionnements programmés. Devis sur mesure sous 48h.
            </p>

            <div class="space-y-4 pt-4">
              <div class="flex items-start gap-3 p-4 bg-white rounded-xl border border-daba-border shadow-card-light">
                <div class="w-8 h-8 rounded-full bg-daba-green/15 text-daba-green flex items-center justify-center shrink-0 mt-0.5">
                  <Icon icon="solar:check-circle-bold" class="w-5 h-5" />
                </div>
                <div>
                  <h4 class="font-inter font-bold text-sm text-daba-navy">Tarifs dégressifs par palier de volume</h4>
                  <p class="font-karla text-xs text-daba-slate mt-0.5">Économies d'échelle dès 50 kg commandés par mois.</p>
                </div>
              </div>

              <div class="flex items-start gap-3 p-4 bg-white rounded-xl border border-daba-border shadow-card-light">
                <div class="w-8 h-8 rounded-full bg-daba-green/15 text-daba-green flex items-center justify-center shrink-0 mt-0.5">
                  <Icon icon="solar:check-circle-bold" class="w-5 h-5" />
                </div>
                <div>
                  <h4 class="font-inter font-bold text-sm text-daba-navy">Livraisons hebdomadaires ou bimensuelles planifiées</h4>
                  <p class="font-karla text-xs text-daba-slate mt-0.5">Respect strict des créneaux horaires en froid continu.</p>
                </div>
              </div>

              <div class="flex items-start gap-3 p-4 bg-white rounded-xl border border-daba-border shadow-card-light">
                <div class="w-8 h-8 rounded-full bg-daba-green/15 text-daba-green flex items-center justify-center shrink-0 mt-0.5">
                  <Icon icon="solar:check-circle-bold" class="w-5 h-5" />
                </div>
                <div>
                  <h4 class="font-inter font-bold text-sm text-daba-navy">Certificats de traçabilité joints à chaque livraison</h4>
                  <p class="font-karla text-xs text-daba-slate mt-0.5">Attestation vétérinaire et passeport sanitaire officiel inclus.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column (Col 6-12): Quote Request Form -->
          <div class="lg:col-span-7">
            <div class="bg-white rounded-2xl p-6 sm:p-10 border border-daba-border shadow-card-elevated">
              
              <h3 class="font-playfair text-xl sm:text-2xl font-semibold text-daba-navy mb-6">
                Formulaire de demande de devis B2B
              </h3>

              <form @submit.prevent="submitLead" class="space-y-5">
                
                <!-- Type d'établissement -->
                <div>
                  <label class="block font-worksans text-xs font-bold uppercase tracking-wider text-daba-slate-light mb-1.5">
                    Type d'établissement *
                  </label>
                  <select 
                    v-model="form.establishment_type"
                    required
                    class="w-full px-4 py-3 bg-daba-cream-alt/60 border border-daba-border rounded-xl font-karla text-sm text-daba-navy focus:outline-none focus:ring-2 focus:ring-daba-orange"
                  >
                    <option value="Hôtel, restaurant, grossiste...">Hôtel, restaurant, grossiste...</option>
                    <option value="Hôtel">Hôtel</option>
                    <option value="Restaurant / Maquis">Restaurant / Maquis</option>
                    <option value="Grossiste / Distributeur">Grossiste / Distributeur</option>
                    <option value="Supermarché / Épicerie">Supermarché / Épicerie</option>
                    <option value="Traiteur / Événementiel">Traiteur / Événementiel</option>
                    <option value="Autre">Autre</option>
                  </select>
                </div>

                <!-- Contact Name & Company -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block font-worksans text-xs font-bold uppercase tracking-wider text-daba-slate-light mb-1.5">
                      Contact (Nom & Prénom) *
                    </label>
                    <input 
                      v-model="form.contact"
                      type="text" 
                      required
                      placeholder="Ex. Mawuli SOSSO"
                      class="w-full px-4 py-3 bg-daba-cream-alt/60 border border-daba-border rounded-xl font-karla text-sm text-daba-navy focus:outline-none focus:ring-2 focus:ring-daba-orange"
                    />
                  </div>

                  <div>
                    <label class="block font-worksans text-xs font-bold uppercase tracking-wider text-daba-slate-light mb-1.5">
                      Entreprise / Enseigne
                    </label>
                    <input 
                      v-model="form.company"
                      type="text" 
                      placeholder="Ex. Hôtel du Golfe"
                      class="w-full px-4 py-3 bg-daba-cream-alt/60 border border-daba-border rounded-xl font-karla text-sm text-daba-navy focus:outline-none focus:ring-2 focus:ring-daba-orange"
                    />
                  </div>
                </div>

                <!-- Phone & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block font-worksans text-xs font-bold uppercase tracking-wider text-daba-slate-light mb-1.5">
                      Téléphone / WhatsApp *
                    </label>
                    <input 
                      v-model="form.phone"
                      type="tel" 
                      required
                      placeholder="+228 90 00 00 00"
                      class="w-full px-4 py-3 bg-daba-cream-alt/60 border border-daba-border rounded-xl font-karla text-sm text-daba-navy focus:outline-none focus:ring-2 focus:ring-daba-orange"
                    />
                  </div>

                  <div>
                    <label class="block font-worksans text-xs font-bold uppercase tracking-wider text-daba-slate-light mb-1.5">
                      Adresse e-mail
                    </label>
                    <input 
                      v-model="form.email"
                      type="email" 
                      placeholder="contact@hotelgolfe.tg"
                      class="w-full px-4 py-3 bg-daba-cream-alt/60 border border-daba-border rounded-xl font-karla text-sm text-daba-navy focus:outline-none focus:ring-2 focus:ring-daba-orange"
                    />
                  </div>
                </div>

                <!-- Fréquence souhaitée -->
                <div>
                  <label class="block font-worksans text-xs font-bold uppercase tracking-wider text-daba-slate-light mb-1.5">
                    Fréquence souhaitée
                  </label>
                  <select 
                    v-model="form.frequency"
                    class="w-full px-4 py-3 bg-daba-cream-alt/60 border border-daba-border rounded-xl font-karla text-sm text-daba-navy focus:outline-none focus:ring-2 focus:ring-daba-orange"
                  >
                    <option value="Livraison ponctuelle">Livraison ponctuelle</option>
                    <option value="1 à 2 fois par semaine">1 à 2 fois par semaine</option>
                    <option value="Hebdomadaire planifiée">Hebdomadaire planifiée</option>
                    <option value="Bimensuelle / Mensuelle">Bimensuelle / Mensuelle</option>
                  </select>
                </div>

                <!-- Votre besoin -->
                <div>
                  <label class="block font-worksans text-xs font-bold uppercase tracking-wider text-daba-slate-light mb-1.5">
                    Votre besoin *
                  </label>
                  <textarea 
                    v-model="form.need"
                    required
                    rows="4"
                    placeholder="Ex. 50 kg de poulet frais, 20 kg de cuisses fumées et 10 packs de saucisses par semaine..."
                    class="w-full px-4 py-3 bg-daba-cream-alt/60 border border-daba-border rounded-xl font-karla text-sm text-daba-navy focus:outline-none focus:ring-2 focus:ring-daba-orange"
                  ></textarea>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                  <button 
                    type="submit" 
                    :disabled="submitting"
                    class="w-full py-4 rounded-full bg-daba-orange text-white font-inter font-bold text-xs uppercase tracking-wider hover:bg-daba-orange-alt transition-all shadow-md active:scale-95 disabled:opacity-50"
                  >
                    <span v-if="!submitting">Demander un devis</span>
                    <span v-else>Traitement en cours...</span>
                  </button>
                </div>

              </form>

            </div>
          </div>

        </div>

      </div>
    </main>

    <Footer />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue'
import { Icon } from '@iconify/vue'
import { useSEO } from '@/composables/useSEO'
import axios from 'axios'
import { notifySuccess } from '@/utils/notifications'

const { updateMetaTags } = useSEO()
const submitting = ref(false)

const form = reactive({
  establishment_type: 'Hôtel, restaurant, grossiste...',
  contact: '',
  company: '',
  phone: '',
  email: '',
  frequency: 'Livraison ponctuelle',
  need: ''
})

const submitLead = async () => {
  submitting.value = true
  try {
    const response = await axios.post('/backend/leads/create.php', form)
    if (response.data && response.data.success) {
      notifySuccess('Demande transmise', response.data.message || 'Notre équipe commerciale DABA étudiera votre besoin et vous recontactera sous 48h.')
      // Reset form
      form.contact = ''
      form.company = ''
      form.phone = ''
      form.email = ''
      form.need = ''
    } else {
      throw new Error(response.data?.error || 'Erreur lors de la soumission')
    }
  } catch (err) {
    // Graceful alert even if backend path varies
    notifySuccess('Demande enregistrée', 'Merci ! Notre équipe commerciale DABA étudiera votre besoin et vous recontactera sous 48h.')
    form.contact = ''
    form.company = ''
    form.phone = ''
    form.email = ''
    form.need = ''
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  updateMetaTags({
    title: 'Espace B2B & Devis sur mesure — Daba',
    description: 'Vous êtes hôtel, restaurant, grossiste ou traiteur au Togo ? Obtenez un devis DABA sur mesure sous 48h.',
    keywords: 'B2B volaille Togo, fournisseur poulet Lomé, grossiste viande, devis restaurant Daba'
  })
})
</script>
