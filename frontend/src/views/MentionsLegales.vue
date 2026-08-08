<template>
  <div class="min-h-screen bg-daba-cream-alt pt-32 pb-20 font-karla">
    <div class="container mx-auto px-4 md:px-6 max-w-4xl">
      <!-- Header Section -->
      <div class="text-center mb-16">
        <span class="font-worksans text-xs font-bold uppercase tracking-widest text-daba-slate-light block mb-3">
          INFORMATIONS LÉGALES
        </span>
        <h1 class="text-4xl md:text-5xl font-playfair font-normal text-daba-navy mb-4">Mentions Légales</h1>
        <p class="text-daba-slate text-sm">Dernière mise à jour : août 2026</p>
        <div class="w-20 h-1.5 bg-daba-orange mx-auto mt-8 rounded-full"></div>
      </div>

      <!-- Main Content -->
      <div class="bg-daba-cream rounded-[2.5rem] shadow-xl shadow-daba-cream-alt/50 p-8 md:p-16 border border-daba-cream-alt">
        <div class="prose prose-slate prose-lg max-w-none space-y-12">

          <section>
            <h2 class="text-2xl font-playfair font-semibold text-daba-navy mb-6 flex items-center">
              <span class="w-10 h-10 bg-daba-cream-alt text-daba-orange rounded-xl flex items-center justify-center mr-4 text-lg font-bold">1</span>
              Éditeur du site
            </h2>
            <div class="space-y-2 text-daba-slate leading-relaxed">
              <p><strong class="text-daba-navy">Raison sociale :</strong> DABA SAS</p>
              <p><strong class="text-daba-navy">Activité :</strong> Élevage, transformation et commercialisation de volaille locale</p>
              <p><strong class="text-daba-navy">Siège social :</strong> Lomé, Togo</p>
              <p><strong class="text-daba-navy">RCCM :</strong> [À compléter]</p>
              <p><strong class="text-daba-navy">NIF :</strong> [À compléter]</p>
              <p><strong class="text-daba-navy">Téléphone :</strong> +228 90 00 00 00</p>
              <p><strong class="text-daba-navy">E-mail :</strong> <a href="mailto:contact@daba.tg" class="text-daba-orange hover:underline">contact@daba.tg</a></p>
            </div>
          </section>

          <section>
            <h2 class="text-2xl font-playfair font-semibold text-daba-navy mb-6 flex items-center">
              <span class="w-10 h-10 bg-daba-cream-alt text-daba-orange rounded-xl flex items-center justify-center mr-4 text-lg font-bold">2</span>
              Hébergement
            </h2>
            <p class="text-daba-slate leading-relaxed">
              Le site internet <strong>daba.tg</strong> est hébergé par un prestataire technique établi. Les coordonnées exactes de l'hébergeur sont disponibles sur demande à l'adresse <a href="mailto:contact@daba.tg" class="text-daba-orange hover:underline">contact@daba.tg</a>.
            </p>
          </section>

          <section>
            <h2 class="text-2xl font-playfair font-semibold text-daba-navy mb-6 flex items-center">
              <span class="w-10 h-10 bg-daba-cream-alt text-daba-orange rounded-xl flex items-center justify-center mr-4 text-lg font-bold">3</span>
              Propriété intellectuelle
            </h2>
            <p class="text-daba-slate leading-relaxed">
              L'ensemble des contenus présents sur ce site (textes, images, photographies, logotype, marque DABA) sont la propriété exclusive de DABA SAS et sont protégés par les lois togolaises et internationales sur la propriété intellectuelle. Toute reproduction, représentation ou utilisation non autorisée est strictement interdite.
            </p>
          </section>

          <section>
            <h2 class="text-2xl font-playfair font-semibold text-daba-navy mb-6 flex items-center">
              <span class="w-10 h-10 bg-daba-cream-alt text-daba-orange rounded-xl flex items-center justify-center mr-4 text-lg font-bold">4</span>
              Limitation de responsabilité
            </h2>
            <p class="text-daba-slate leading-relaxed">
              DABA SAS s'efforce d'assurer l'exactitude et la mise à jour des informations diffusées sur ce site. Toutefois, DABA SAS ne peut garantir l'exactitude, la précision ou l'exhaustivité des informations mises à disposition sur ce site. En conséquence, DABA SAS décline toute responsabilité pour toute imprécision, inexactitude ou omission portant sur des informations disponibles sur ce site.
            </p>
          </section>

          <section>
            <h2 class="text-2xl font-playfair font-semibold text-daba-navy mb-6 flex items-center">
              <span class="w-10 h-10 bg-daba-cream-alt text-daba-orange rounded-xl flex items-center justify-center mr-4 text-lg font-bold">5</span>
              Données personnelles
            </h2>
            <p class="text-daba-slate leading-relaxed">
              Dans le cadre de la passation de commande sur notre site, DABA SAS collecte uniquement les données strictement nécessaires à la livraison et au traitement de votre commande (nom, numéro de téléphone, adresse de livraison). Ces données ne sont pas cédées à des tiers à des fins commerciales. Pour en savoir plus, consultez notre <router-link to="/privacy" class="text-daba-orange hover:underline">Politique de Confidentialité</router-link>.
            </p>
          </section>

          <section>
            <h2 class="text-2xl font-playfair font-semibold text-daba-navy mb-6 flex items-center">
              <span class="w-10 h-10 bg-daba-cream-alt text-daba-orange rounded-xl flex items-center justify-center mr-4 text-lg font-bold">6</span>
              Contact
            </h2>
            <p class="text-daba-slate leading-relaxed">
              Pour toute question relative aux présentes mentions légales ou à l'exercice de vos droits, vous pouvez contacter DABA SAS par email à <a href="mailto:contact@daba.tg" class="text-daba-orange hover:underline">contact@daba.tg</a> ou par téléphone au <a href="tel:+22890000000" class="text-daba-orange hover:underline">+228 90 00 00 00</a>.
            </p>
          </section>

          <div class="flex justify-center pt-8">
            <router-link to="/" class="group flex items-center gap-2 px-8 py-4 bg-daba-navy text-sm font-bold font-inter text-white uppercase tracking-widest rounded-2xl hover:bg-daba-orange transition-all active:scale-95 shadow-lg">
              <Icon icon="solar:arrow-left-linear" class="group-hover:-translate-x-1 transition-transform" />
              Retour à l'accueil
            </router-link>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Icon } from '@iconify/vue'
import { useSEO } from '@/composables/useSEO'

const { updateMetaTags } = useSEO()
updateMetaTags({
  title: 'Mentions légales — Daba',
  description: 'Mentions légales de DABA SAS, élevage et transformation de volaille à Lomé, Togo.'
})
</script>
