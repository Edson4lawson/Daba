<template>
    <section class="py-20 bg-daba-cream" id="contact" data-aos="fade-up">
        <div class="container px-4 md:px-6 mx-auto">
            <div class="flex flex-col lg:flex-row gap-12 lg:items-stretch">
                <!-- Contact Info Card -->
                <div class="w-full lg:w-5/12 bg-daba-cream-alt rounded-3xl p-8 md:p-12 flex flex-col justify-between" data-aos="fade-right">
                    <div>
                        <h2 class="text-3xl md:text-4xl font-bold text-daba-navy leading-tight">
                            Contactez-nous pour <br class="hidden md:block"> plus d'informations
                        </h2>
                        <p class="mt-4 text-daba-slate justify-center">Vous ne trouvez pas ce que vous cherchez ? Demandez-nous, <br> on a peut-etre en stock.</p>

                        <div class="mt-10 space-y-6">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-daba-cream rounded-2xl shadow-sm flex items-center justify-center text-daba-orange">
                                    <Icon icon="solar:map-point-bold-duotone" class="w-6 h-6" />
                                </div>
                                <div>
                                    <p class="text-xs text-daba-slate-dark font-bold uppercase tracking-wider">Adresse</p>
                                    <p class="text-daba-navy font-medium">Cotonou, Littoral, Benin</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-daba-cream rounded-2xl shadow-sm flex items-center justify-center text-daba-orange">
                                    <Icon icon="solar:phone-calling-bold-duotone" class="w-6 h-6" />
                                </div>
                                <div>
                                    <p class="text-xs text-daba-slate-dark font-bold uppercase tracking-wider">Telephone</p>
                                    <a href="tel:+2290156783770" class="text-daba-navy font-medium">+229 01 56 78 37 70</a>
                                </div>
                            </div>

                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-daba-cream rounded-2xl shadow-sm flex items-center justify-center text-daba-orange">
                                    <Icon icon="solar:letter-bold-duotone" class="w-6 h-6" />
                                </div>
                                <div>
                                    <p class="text-xs text-daba-slate-dark font-bold uppercase tracking-wider">Email</p>
                                    <a href="mailto:egouassangni@mail.com" class="text-daba-navy font-medium">egouassangni@mail.com</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 pt-8 border-t border-daba-cream-alt">
                        <h4 class="text-daba-navy mb-4 font-bold uppercase text-xs tracking-widest">Suivez l'aventure</h4>
                        <div class="flex items-center space-x-4">
                            <a v-for="(social, index) in socialLinks" :href="social.url" :key="index"
                          class="group relative w-10 h-10 flex items-center justify-center rounded-xl bg-daba-cream-alt text-daba-orange hover:bg-daba-orange hover:text-white transition-all duration-300">
                          <Icon :icon="social.icon" class="w-5 h-5 transition-transform group-hover:scale-110" />
                      </a>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="w-full lg:w-7/12" data-aos="fade-left">
                    <div class="bg-daba-cream border border-daba-cream-alt rounded-3xl p-8 md:p-12 shadow-xl shadow-daba-cream-alt">
                        <h3 class="text-2xl font-bold text-daba-navy mb-8">Envoyez-nous un message</h3>

                        <form @submit.prevent="handleSubmit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block mb-2 text-sm font-semibold text-daba-slate">Nom Complet</label>
                                    <input type="text" placeholder="Votre nom" required
                                        class="w-full px-5 py-4 bg-daba-cream-alt border border-daba-cream-alt rounded-2xl focus:bg-daba-cream focus:border-daba-orange focus:ring-4 focus:ring-daba-orange/20 outline-none transition-all" />
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-semibold text-daba-slate">Adresse Email</label>
                                    <input type="email" placeholder="votre@email.com" required
                                        class="w-full px-5 py-4 bg-daba-cream-alt border border-daba-cream-alt rounded-2xl focus:bg-daba-cream focus:border-daba-orange focus:ring-4 focus:ring-daba-orange/20 outline-none transition-all" />
                                </div>
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-semibold text-daba-slate">Sujet</label>
                                <input type="text" placeholder="Comment pouvons-nous vous aider ?"
                                    class="w-full px-5 py-4 bg-daba-cream-alt border border-daba-cream-alt rounded-2xl focus:bg-daba-cream focus:border-daba-orange focus:ring-4 focus:ring-daba-orange/20 outline-none transition-all" />
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-semibold text-daba-slate">Message</label>
                                <textarea required rows="4" placeholder="Votre message..."
                                    class="w-full px-5 py-4 bg-daba-cream-alt border border-daba-cream-alt rounded-2xl focus:bg-daba-cream focus:border-daba-orange focus:ring-4 focus:ring-daba-orange/20 outline-none transition-all resize-none"></textarea>
                            </div>

                            <button type="submit" 
                                class="w-full py-5 bg-daba-orange text-white rounded-2xl font-bold text-lg hover:bg-daba-orange-dark hover:shadow-xl hover:shadow-daba-orange/20 transition-all transform active:scale-[0.98]">
                                Envoyer le message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import AOS from 'aos';
import 'aos/dist/aos.css';

const socialLinks = [
  { icon: 'fa6-brands:facebook-f', url: 'https://www.facebook.com/share/1BhY2JCNV2/?mibextid=LQQJ4d' },
  { icon: 'fa6-brands:instagram', url: 'https://www.instagram.com/jade889148?igsh=Z3UwMWwwc3MwejF3&utm_source=qr' },
  { icon: 'fa6-brands:whatsapp', url: 'https://wa.me/+2290156783770' },
  { icon: 'fa6-brands:snapchat', url: 'https://snapchat.com/t/9pLClwle' }
];


const handleSubmit = () => {
    // Logique d'envoi
    alert('Message envoyé avec succès !');
};

onMounted(() => {
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });
});
</script>


