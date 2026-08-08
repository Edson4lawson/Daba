<template>
  <div class="min-h-screen bg-daba-cream flex flex-col font-karla">
    <Header />

    <main class="flex-grow">
      
      <!-- Top Hero Section (Dark Navy Background) -->
      <section class="bg-daba-navy text-white py-16 lg:py-24">
        <div class="max-w-[1440px] mx-auto px-6 sm:px-8 lg:px-12 text-center">
          
          <span class="font-worksans text-xs font-bold uppercase tracking-widest text-daba-orange">
            VÉRIFICATION & TRAÇABILITÉ
          </span>

          <h1 class="font-playfair text-3xl sm:text-4xl lg:text-[48px] font-normal text-white mt-3 mb-2 leading-tight">
            Vérifiez un lot DABA en quelques secondes
          </h1>

          <p class="font-karla text-sm text-daba-border/80 mb-10">
            Exemples à tester : <button @click="testCode('DBA-2026-0142')" class="underline hover:text-daba-orange font-mono">#DBA-2026-0142</button>, <button @click="testCode('DBA-2026-0098')" class="underline hover:text-daba-orange font-mono">#DBA-2026-0098</button>, <button @click="testCode('DBA-2026-0201')" class="underline hover:text-daba-orange font-mono">#DBA-2026-0201</button>
          </p>

          <!-- Input Card -->
          <div class="max-w-3xl mx-auto bg-daba-navy-marine rounded-2xl p-6 sm:p-10 border border-white/10 shadow-2xl">
            <h2 class="font-playfair text-xl sm:text-2xl font-normal text-white mb-3">
              Saisissez un numéro de lot, découvrez son histoire
            </h2>
            <p class="font-karla text-xs sm:text-sm text-daba-border/80 max-w-xl mx-auto mb-6 leading-relaxed">
              Chaque emballage DABA porte un QR code et un numéro de lot. Ferme d'origine, alimentation, historique vétérinaire et preuve d'ancrage sanitaire : tout est consultable en quelques secondes.
            </p>

            <form @submit.prevent="verifyLot" class="flex flex-col sm:flex-row items-center gap-3 max-w-xl mx-auto">
              <input 
                v-model="inputCode"
                type="text" 
                placeholder="#DBA-2026-0142" 
                class="w-full px-5 py-3.5 bg-white border border-daba-border rounded-xl text-daba-navy font-mono text-sm focus:outline-none focus:ring-2 focus:ring-daba-orange"
              />
              <button 
                type="submit" 
                class="w-full sm:w-auto px-8 py-3.5 rounded-full bg-daba-orange text-white font-inter font-bold text-xs uppercase tracking-wider hover:bg-daba-orange-alt transition-all shadow-md active:scale-95 shrink-0"
              >
                Vérifier
              </button>
            </form>
          </div>

        </div>
      </section>

      <!-- Verification Results Display Section -->
      <section v-if="searched" class="py-12 bg-daba-cream border-b border-daba-border/60">
        <div class="max-w-[1440px] mx-auto px-6 sm:px-8 lg:px-12">
          
          <!-- Recognized Lot Result -->
          <div v-if="lotResult" class="max-w-3xl mx-auto bg-white rounded-2xl p-6 sm:p-8 border-2 border-daba-green shadow-card-elevated">
            <div class="flex items-center justify-between border-b border-daba-border-light pb-4 mb-6">
              <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-daba-green/15 text-daba-green font-worksans text-xs font-bold uppercase tracking-wider">
                  <Icon icon="solar:check-circle-bold" class="w-4 h-4" />
                  LOT AUTHENTIQUE CERTIFIÉ
                </span>
                <h3 class="font-playfair text-2xl font-semibold text-daba-navy mt-2">
                  Lot N° {{ lotResult.code }}
                </h3>
              </div>
              <span class="font-mono text-xs text-daba-slate-light font-bold">
                {{ lotResult.product }}
              </span>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 font-karla text-sm">
              <div class="space-y-1">
                <span class="text-xs text-daba-slate-light uppercase font-worksans font-bold">Ferme d'origine</span>
                <p class="font-bold text-daba-navy">{{ lotResult.ferme }}</p>
              </div>

              <div class="space-y-1">
                <span class="text-xs text-daba-slate-light uppercase font-worksans font-bold">Date & Heure d'Abattage</span>
                <p class="font-bold text-daba-navy">{{ lotResult.dateAbattage }}</p>
              </div>

              <div class="space-y-1">
                <span class="text-xs text-daba-slate-light uppercase font-worksans font-bold">Contrôle Vétérinaire</span>
                <p class="font-bold text-daba-green flex items-center gap-1">
                  <Icon icon="solar:verified-check-bold" class="w-4 h-4" />
                  {{ lotResult.veterinaire }}
                </p>
              </div>

              <div class="space-y-1">
                <span class="text-xs text-daba-slate-light uppercase font-worksans font-bold">Température Chaîne du Froid</span>
                <p class="font-bold text-daba-navy">{{ lotResult.temperature }}</p>
              </div>
            </div>

            <!-- Timeline steps -->
            <div class="mt-8 pt-6 border-t border-daba-border-light">
              <h4 class="font-worksans text-xs font-bold uppercase tracking-wider text-daba-slate-light mb-4">
                Historique de traçabilité
              </h4>
              <div class="space-y-3">
                <div v-for="(step, idx) in lotResult.steps" :key="idx" class="flex items-start gap-3 text-xs sm:text-sm">
                  <span class="w-5 h-5 rounded-full bg-daba-navy text-white flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">
                    {{ idx + 1 }}
                  </span>
                  <div>
                    <span class="font-bold text-daba-navy">{{ step.title }}</span> — <span class="text-daba-slate">{{ step.detail }}</span>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- Unrecognized Lot Error -->
          <div v-else class="max-w-2xl mx-auto bg-white rounded-2xl p-8 border-2 border-red-300 text-center shadow-card">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-4">
              <Icon icon="solar:close-circle-bold" class="w-7 h-7" />
            </div>
            <h3 class="font-playfair text-xl font-semibold text-daba-navy mb-2">
              Numéro de lot non reconnu
            </h3>
            <p class="font-karla text-sm text-daba-slate leading-relaxed mb-4">
              Le code "<span class="font-mono font-bold">{{ inputCode }}</span>" ne correspond à aucun lot enregistré dans notre base de données. Veuillez vérifier le code inscrit sur votre étiquette (ex. <button @click="testCode('DBA-2026-0142')" class="underline text-daba-orange font-mono">#DBA-2026-0142</button>).
            </p>
          </div>

        </div>
      </section>

      <!-- Section: Comment fonctionne notre système de traçabilité ? -->
      <section class="py-16 lg:py-24 bg-daba-cream-light border-b border-daba-border/60">
        <div class="max-w-[1440px] mx-auto px-6 sm:px-8 lg:px-12">
          
          <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="font-worksans text-xs font-bold uppercase tracking-widest text-daba-slate-light">
              NOTRE PROCESSUS EN 4 ÉTAPES
            </span>
            <h2 class="font-playfair text-3xl sm:text-4xl lg:text-[40px] font-normal text-daba-navy mt-2">
              Comment fonctionne notre système de traçabilité ?
            </h2>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <div 
              v-for="step in processSteps" 
              :key="step.number"
              class="bg-white rounded-card p-6 border border-daba-border shadow-card-light flex flex-col justify-between"
            >
              <div>
                <span class="font-playfair text-2xl font-bold text-daba-orange block mb-3">
                  {{ step.number }}
                </span>
                <h3 class="font-playfair text-lg font-semibold text-daba-navy mb-2">
                  {{ step.title }}
                </h3>
                <p class="font-karla text-xs text-daba-slate leading-relaxed">
                  {{ step.description }}
                </p>
              </div>
            </div>
          </div>

        </div>
      </section>

      <!-- Section: Où trouver votre numéro de lot ? -->
      <section class="py-16 lg:py-24 bg-daba-cream">
        <div class="max-w-[1440px] mx-auto px-6 sm:px-8 lg:px-12">
          <div class="bg-white rounded-2xl p-8 sm:p-12 border border-daba-border shadow-card-light grid grid-cols-1 lg:grid-cols-12 gap-8 items-center max-w-4xl mx-auto">
            
            <div class="lg:col-span-7 space-y-4">
              <span class="font-worksans text-xs font-bold uppercase tracking-widest text-daba-slate-light">
                GUIDE
              </span>
              <h3 class="font-playfair text-2xl sm:text-3xl font-normal text-daba-navy">
                Où trouver votre numéro de lot sur l'emballage DABA ?
              </h3>
              <p class="font-karla text-sm text-daba-slate leading-relaxed">
                Le numéro de lot (ex. #DBA-2026-0142) se trouve au dos du paquet, juste à côté de la date d'emballage et du QR code.
              </p>
              <ul class="space-y-2 text-xs sm:text-sm font-karla text-daba-navy pt-2">
                <li class="flex items-center gap-2">
                  <Icon icon="solar:check-read-linear" class="w-4 h-4 text-daba-orange" />
                  <span>Retournez le paquet / l'étiquette blanche collée au dos.</span>
                </li>
                <li class="flex items-center gap-2">
                  <Icon icon="solar:check-read-linear" class="w-4 h-4 text-daba-orange" />
                  <span>Repérez la ligne « LOT » sous la date d'emballage.</span>
                </li>
                <li class="flex items-center gap-2">
                  <Icon icon="solar:check-read-linear" class="w-4 h-4 text-daba-orange" />
                  <span>Scannez le QR code ou recopiez le code dans le champ ci-dessus.</span>
                </li>
              </ul>
            </div>

            <div class="lg:col-span-5 bg-daba-cream-light p-6 rounded-xl border border-daba-border text-center space-y-2 font-mono">
              <span class="text-xs text-daba-slate-light font-worksans uppercase font-bold block">DOS DE L'EMBALLAGE</span>
              <p class="text-xs text-daba-navy">Poulet Frais entier</p>
              <p class="text-xs text-daba-slate">Emballé le : 15/07/2026</p>
              <div class="py-2">
                <span class="px-3 py-1 bg-daba-navy text-white text-sm font-bold rounded">
                  LOT : #DBA-2026-0142
                </span>
              </div>
              <p class="text-[11px] text-daba-slate-light">À conserver jusqu'à la date de consommation.</p>
            </div>

          </div>
        </div>
      </section>

    </main>

    <Footer />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue'
import { Icon } from '@iconify/vue'
import { useSEO } from '@/composables/useSEO'

const route = useRoute()
const { updateMetaTags } = useSEO()

const inputCode = ref('')
const searched = ref(false)
const lotResult = ref(null)

const sampleLots = {
  'DBA-2026-0142': {
    code: '#DBA-2026-0142',
    product: 'Poulet frais entier (1,5 kg)',
    ferme: 'Ferme DABA Kpomé (Lot #KP-2026-042)',
    dateAbattage: '15/07/2026 à 05:30',
    veterinaire: 'Conforme (Dr. Lawson, N° Ordre #TOG-4542)',
    temperature: 'Livré sous température contrôlée (+2°C)',
    steps: [
      { title: 'Élevage responsable', detail: 'Élevé 49 jours en plein air à Kpomé, alimentation céréalière sans antibiotiques.' },
      { title: 'Contrôle sanitaire', detail: 'Certificat vétérinaire délivré à Kpomé avant transport.' },
      { title: 'Abattage & Conditionnement', detail: 'Transformé et emballé à l\'atelier Lomé le 15/07/2026.' },
      { title: 'Expédition', detail: 'Chaîne du froid maintenue à +2°C jusqu\'à la livraison.' }
    ]
  },
  'DBA-2026-0098': {
    code: '#DBA-2026-0098',
    product: 'Cuisses de poulet fumées (1 kg)',
    ferme: 'Ferme DABA Tsévié (Lot #TS-2026-018)',
    dateAbattage: '12/07/2026 à 06:15',
    veterinaire: 'Conforme (Dr. Kpante, N° Ordre #TOG-3891)',
    temperature: 'Fumage naturel & stockage à +4°C',
    steps: [
      { title: 'Élevage local', detail: 'Volailles nourries aux grains locaux à Tsévié.' },
      { title: 'Transformation & Fumage', detail: 'Fumage artisanal au bois d\'acacia à l\'atelier DABA Lomé.' },
      { title: 'Mise sous vide', detail: 'Conditionnement hermétique le 13/07/2026.' }
    ]
  },
  'DBA-2026-0201': {
    code: '#DBA-2026-0201',
    product: 'Jambon de volaille (300 g)',
    ferme: 'Ferme DABA Kpomé (Lot #KP-2026-055)',
    dateAbattage: '18/07/2026 à 04:45',
    veterinaire: 'Conforme (Dr. Lawson, N° Ordre #TOG-4542)',
    temperature: 'Conservation optimale à +3°C',
    steps: [
      { title: 'Sélection des blancs', detail: 'Sélection des plus beaux blancs de poulet à Kpomé.' },
      { title: 'Charcuterie fine', detail: 'Préparation et tranchage fin dans le respect des normes sanitaires.' },
      { title: 'Certification', detail: 'Contrôle qualité final validé par le laboratoire DABA.' }
    ]
  }
}

const processSteps = [
  {
    number: '01',
    title: 'Naissance & Élevage à Kpomé',
    description: 'Chaque bande de poussins est enregistrée dès son arrivée à la ferme. Densité maîtrisée et alimentation suivies.'
  },
  {
    number: '02',
    title: 'Contrôle vétérinaire strict',
    description: 'Avant tout départ pour l’atelier, un contrôle vétérinaire est effectué et consigné sur notre registre sanitaire.'
  },
  {
    number: '03',
    title: 'Transformation & Dépouillage à Lomé',
    description: 'Lors de la découpe et du conditionnement, un QR code unique est imprimé et associé à chaque paquet.'
  },
  {
    number: '04',
    title: 'Transparence totale pour le client',
    description: 'En scannant le QR code ou en entrant le code sur ce site, vous accédez à l’histoire complète de votre paquet.'
  }
]

const verifyLot = () => {
  const code = inputCode.value.trim().toUpperCase().replace('#', '')
  searched.value = true
  if (sampleLots[code]) {
    lotResult.value = sampleLots[code]
  } else {
    lotResult.value = null
  }
}

const testCode = (code) => {
  inputCode.value = code
  verifyLot()
}

onMounted(() => {
  updateMetaTags({
    title: 'Traçabilité & Contrôle Sanitaire — Daba',
    description: 'Saisissez votre numéro de lot DABA pour consulter l\'historique d\'élevage, le suivi vétérinaire et la chaîne du froid.',
    keywords: 'traçabilité poulet, contrôle vétérinaire, lot Daba, sécurité alimentaire Togo'
  })

  if (route.query.code) {
    inputCode.value = route.query.code
    verifyLot()
  }
})
</script>
