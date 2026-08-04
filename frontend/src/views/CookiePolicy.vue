<template>
  <div class="min-h-screen bg-daba-cream-alt pt-32 pb-20">
    <div class="container mx-auto px-4 md:px-6 max-w-4xl">
      <!-- Header Section -->
      <div class="text-center mb-16" data-aos="fade-up">
        <h1 class="text-4xl md:text-5xl font-black text-daba-navy mb-4 tracking-tight">Politique de Cookies</h1>
        <p class="text-daba-slate font-medium max-w-xl mx-auto">Nous utilisons des cookies pour améliorer votre expérience et personnaliser nos services.</p>
        <div class="w-20 h-1.5 bg-daba-green mx-auto mt-8 rounded-full"></div>
      </div>

      <!-- Main Content -->
      <div class="bg-daba-cream rounded-[2.5rem] shadow-xl shadow-daba-cream-alt/50 p-8 md:p-16 border border-daba-cream-alt" data-aos="fade-up" data-aos-delay="100">
        <div class="prose prose-slate prose-lg max-w-none space-y-12">
          
          <section>
            <h2 class="text-2xl font-black text-daba-navy mb-6 flex items-center gap-4">
              <div class="w-10 h-10 bg-daba-cream-alt text-daba-green rounded-xl flex items-center justify-center text-lg">
                <Icon icon="solar:info-circle-bold" />
              </div>
              Qu'est-ce qu'un cookie ?
            </h2>
            <p class="text-daba-slate leading-relaxed">
              Un cookie est un petit fichier texte stocké sur votre appareil (ordinateur, mobile, tablette) lors de la consultation d'un site internet. Il permet de mémoriser vos préférences et de faciliter votre navigation ultérieure sur notre boutique.
            </p>
          </section>

          <section>
            <h2 class="text-2xl font-black text-daba-navy mb-6 flex items-center gap-4">
              <div class="w-10 h-10 bg-daba-cream-alt text-daba-green rounded-xl flex items-center justify-center text-lg">
                <Icon icon="solar:settings-bold" />
              </div>
              Usage des Cookies
            </h2>
            
            <div class="grid grid-cols-1 gap-6">
              <div class="p-8 bg-daba-cream-alt rounded-3xl border border-daba-cream-alt">
                <div class="flex items-center gap-3 mb-4">
                  <Icon icon="solar:shield-check-bold" class="text-daba-green w-6 h-6" />
                  <h4 class="font-bold text-daba-navy">Cookies Essentiels</h4>
                </div>
                <p class="text-sm text-daba-slate leading-relaxed">
                  Indispensables au fonctionnement de la boutique. Ils gèrent votre panier d'achat, votre connexion sécurisée et la mémorisation de vos préférences de langue.
                </p>
                <p class="mt-4 text-[10px] font-black text-daba-green uppercase tracking-widest">Toujours actifs</p>
              </div>

              <div class="p-8 bg-daba-cream-alt rounded-3xl border border-daba-cream-alt">
                <div class="flex items-center gap-3 mb-4">
                  <Icon icon="solar:chart-square-bold" class="text-daba-orange w-6 h-6" />
                  <h4 class="font-bold text-daba-navy">Cookies Statistiques</h4>
                </div>
                <p class="text-sm text-daba-slate leading-relaxed">
                  Nous aident à comprendre comment vous utilisez le site (nombre de visites, pages les plus vues) afin d'améliorer l'expérience globale.
                </p>
                <p class="mt-4 text-[10px] font-black text-daba-orange uppercase tracking-widest">Optionnels</p>
              </div>

              <div class="p-8 bg-daba-cream-alt rounded-3xl border border-daba-cream-alt">
                <div class="flex items-center gap-3 mb-4">
                  <Icon icon="solar:target-bold" class="text-daba-orange w-6 h-6" />
                  <h4 class="font-bold text-daba-navy">Cookies Marketing</h4>
                </div>
                <p class="text-sm text-daba-slate leading-relaxed">
                  Utilisés pour vous proposer des publicités pertinentes sur d'autres sites ou réseaux sociaux en fonction de votre navigation sur Daba.
                </p>
                <p class="mt-4 text-[10px] font-black text-daba-orange uppercase tracking-widest">Optionnels</p>
              </div>
            </div>
          </section>

          <section>
            <h2 class="text-2xl font-black text-daba-navy mb-6 flex items-center gap-4">
              <div class="w-10 h-10 bg-daba-cream-alt text-daba-green rounded-xl flex items-center justify-center text-lg">
                <Icon icon="solar:checklist-bold" />
              </div>
              Vos Choix
            </h2>
            <p class="text-daba-slate leading-relaxed mb-6">
              Vous avez le contrôle total sur les cookies non-essentiels. Vous pouvez modifier vos préférences à tout moment via le lien <strong>"Gérer mes cookies"</strong> présent dans le pied de page du site.
            </p>
            <div class="bg-daba-navy text-white p-8 rounded-3xl relative overflow-hidden">
               <Icon icon="solar:lock-bold" class="absolute -right-10 -bottom-10 w-48 h-48 opacity-10" />
               <p class="relative z-10 text-sm leading-relaxed opacity-90">
                 Notez que le blocage complet des cookies via les paramètres de votre navigateur peut empêcher le bon fonctionnement de certaines parties de notre boutique, notamment le processus de commande.
               </p>
            </div>
          </section>

          <div class="flex justify-center pt-8">
            <router-link to="/" class="px-8 py-4 bg-daba-navy text-sm font-black text-white uppercase tracking-widest rounded-2xl hover:bg-daba-green transition-all active:scale-95 shadow-lg shadow-daba-cream-alt">
              Retour Boutique
            </router-link>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Icon } from '@iconify/vue'
</script>
