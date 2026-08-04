<template>
    <div id="about" class="min-h-screen scroll-mt-20 py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col lg:flex-row gap-12 mb-20">
                <!-- Image Section -->
                <div class="lg:w-1/2">
                    <div class="relative rounded-2xl overflow-hidden aspect-[16/9] bg-daba-cream-alt flex items-center justify-center shadow-2xl">
                        <img :src="product" alt="Parfums de luxe" class="w-full h-full object-cover" />
                        <div class="absolute inset-0 bg-gradient-to-t from-daba-navy/70 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 p-6 text-black">
                            <h3 class="text-2xl font-bold text-daba-orange">Depuis 2021</h3>
                            <p class="mt-2 text-white/90">Creation de parfums avec passion</p>
                        </div>
                    </div>
                </div>

                <div class="lg:w-1/2 flex flex-col justify-center">
                    <h2 class="text-5xl font-bold text-daba-navy mb-8">À€ Propos de <span class="text-daba-orange">Nous</span></h2>
                    <p class="text-xl text-daba-slate mb-6 leading-relaxed">
                        Nous sommes des artisans passionnés dévoués À  la création de fragrances exceptionnelles qui racontent votre histoire unique. Chaque parfum est conçu avec les meilleurs ingrédients et une attention méticuleuse aux détails.
                    </p>

                    <div class="grid grid-cols-2 gap-6 mt-8">
                        <div class="bg-daba-cream p-6 rounded-xl shadow-lg border border-daba-orange/20 hover:shadow-2xl hover:border-daba-orange/40 transition-all duration-300">
                            <div class="text-center">
                                <h4 class="text-3xl font-bold text-daba-orange mb-2">500+</h4>
                                <p class="text-daba-navy font-medium">Fragrances uniques</p>
                            </div>
                        </div>

                        <div class="bg-daba-cream p-6 rounded-xl shadow-lg border border-daba-orange/20 hover:shadow-2xl hover:border-daba-orange/40 transition-all duration-300">
                            <div class="text-center">
                                <h4 class="text-3xl font-bold text-daba-orange mb-2">50+</h4>
                                <p class="text-daba-navy font-medium">Pays À  travers le monde</p>
                            </div>
                        </div>

                        <div class="bg-daba-cream p-6 rounded-xl shadow-lg border border-daba-orange/20 hover:shadow-2xl hover:border-daba-orange/40 transition-all duration-300">
                            <div class="text-center">
                                <h4 class="text-3xl font-bold text-daba-orange mb-2">100%</h4>
                                <p class="text-daba-navy font-medium">Satisfaction client</p>
                            </div>
                        </div>

                        <div class="bg-daba-cream p-6 rounded-xl shadow-lg border border-daba-orange/20 hover:shadow-2xl hover:border-daba-orange/40 transition-all duration-300">
                            <div class="text-center">
                                <h4 class="text-3xl font-bold text-daba-orange mb-2">24/7</h4>
                                <p class="text-daba-navy font-medium">Support client</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import product from '../assets/product13.jpg'
</script>


