<template>
  <div class="relative overflow-hidden" :class="containerClass">
    <!-- Placeholder avec blur pendant le chargement -->
    <div 
      v-show="!loaded" 
      class="absolute inset-0 bg-gradient-to-br from-daba-cream-alt to-daba-cream animate-pulse"
      :style="placeholderStyle"
    ></div>
    
    <!-- Image avec lazy loading natif -->
    <img 
      ref="imgRef"
      :src="imageSrc"
      :key="imageSrc"
      :alt="alt"
      :loading="eager ? 'eager' : 'lazy'"
      :decoding="eager ? 'sync' : 'async'"
      :class="[
        'transition-opacity duration-500',
        loaded ? 'opacity-100' : 'opacity-0',
        imageClass
      ]"
      :style="imageStyle"
      @load="onLoad"
      @error="onError"
    />
    
    <!-- Fallback si erreur de chargement -->
    <div 
      v-if="error" 
      class="absolute inset-0 flex items-center justify-center bg-daba-cream-alt"
    >
      <Icon icon="solar:image-broken-linear" class="w-12 h-12 text-daba-slate-dark" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
  src: {
    type: String,
    required: true
  },
  alt: {
    type: String,
    default: ''
  },
  // Utiliser le chargement eager pour les images above-the-fold
  eager: {
    type: Boolean,
    default: false
  },
  // Classes pour le container
  containerClass: {
    type: String,
    default: ''
  },
  // Classes pour l'image
  imageClass: {
    type: String,
    default: 'w-full h-full object-cover'
  },
  // Ratio d'aspect pour le placeholder
  aspectRatio: {
    type: String,
    default: '4/5'
  },
  // Largeur pour srcset (optimisation responsive)
  sizes: {
    type: String,
    default: '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw'
  }
});

const imgRef = ref(null);
const loaded = ref(false);
const error = ref(false);

// Reset state when src changes
watch(() => props.src, () => {
  loaded.value = false;
  error.value = false;
});

// Calculer le src de l'image (pourrait être optimisé via CDN)
const imageSrc = computed(() => props.src);

// Style pour le placeholder
const placeholderStyle = computed(() => ({
  aspectRatio: props.aspectRatio
}));

// Style pour l'image
const imageStyle = computed(() => ({
  aspectRatio: props.aspectRatio
}));

// Handlers
const onLoad = () => {
  loaded.value = true;
  error.value = false;
};

const onError = () => {
  error.value = true;
  loaded.value = true;
};

// Observer pour déclencher le chargement quand visible
let observer = null;

onMounted(() => {
  // Si l'image est déjÀ  en cache, déclencher load
  if (imgRef.value?.complete && imgRef.value?.naturalHeight !== 0) {
    onLoad();
  }
});

onBeforeUnmount(() => {
  if (observer) {
    observer.disconnect();
  }
});
</script>



