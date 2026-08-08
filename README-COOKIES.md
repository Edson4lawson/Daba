# 🍪 Système de Gestion des Cookies - Daba

Un système complet, moderne et conforme RGPD pour la gestion des cookies sur votre site e-commerce.

## ✨ Fonctionnalités

### 🎯 **Fonctionnalités Principales**
- ✅ Bannière de consentement moderne et responsive
- ✅ 4 catégories de cookies (Essentiels, Statistiques, Marketing, Fonctionnels)
- ✅ Boutons "Tout accepter", "Tout refuser", "Personnaliser"
- ✅ Stockage sécurisé des préférences
- ✅ Blocage conditionnel des scripts de tracking
- ✅ Bouton flottant pour gérer les préférences
- ✅ Page de politique des cookies complète
- ✅ Support multi-langue (FR/EN)
- ✅ Dark mode automatique
- ✅ Animations douces et design premium

### 🔒 **Conformité RGPD**
- ✅ Consentement explicite requis
- ✅ Informations claires et transparentes
- ✅ Possibilité de retirer le consentement
- ✅ Durées de conservation limitées
- ✅ Droits des utilisateurs respectés

## 📦 Structure des Fichiers

```
src/
├── components/
│   └── CookieBanner.vue          # Composant principal de la bannière
├── composables/
│   └── useCookieConsent.js       # Hook Vue pour la gestion du consentement
├── views/
│   └── CookiePolicy.vue          # Page de politique des cookies
├── locales/
│   ├── fr.js                     # Traductions françaises
│   └── en.js                     # Traductions anglaises
└── examples/
    └── cookie-integration.js     # Exemples d'intégration
```

## 🚀 Installation Rapide

### 1. **Installation des dépendances**

```bash
npm install @iconify/vue vue-i18n
```

### 2. **Configuration de Vue I18n**

Créez `src/i18n.js`:

```javascript
import { createI18n } from 'vue-i18n'
import fr from './locales/fr.js'
import en from './locales/en.js'

const i18n = createI18n({
  locale: 'fr', // Langue par défaut
  fallbackLocale: 'fr',
  messages: {
    fr,
    en
  }
})

export default i18n
```

### 3. **Intégration dans main.js**

```javascript
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import i18n from './i18n'
import App from './App.vue'
import router from './router'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.use(i18n)
app.mount('#app')
```

### 4. **Ajout du composant**

Dans `src/App.vue`:

```vue
<template>
  <div>
    <!-- Votre contenu existant -->
    <router-view />
    
    <!-- Ajouter la bannière cookies -->
    <CookieBanner />
  </div>
</template>

<script setup>
import CookieBanner from './components/CookieBanner.vue'
</script>
```

### 5. **Ajout de la route**

Dans `src/router/index.js`:

```javascript
{
  path: '/cookies',
  name: 'CookiePolicy',
  component: () => import('../views/CookiePolicy.vue')
}
```

## 🎛️ Configuration des Services de Tracking

### Google Analytics

```javascript
import { useCookieConsent } from '@/composables/useCookieConsent'

const { loadGoogleAnalytics } = useCookieConsent()

// Charger GA uniquement avec consentement
loadGoogleAnalytics('G-XXXXXXXXXX')
```

### Meta Pixel

```javascript
const { loadMetaPixel } = useCookieConsent()

// Charger Meta Pixel uniquement avec consentement
loadMetaPixel('XXXXXXXXXXXXXXXX')
```

### TikTok Pixel

```javascript
const { loadTikTokPixel } = useCookieConsent()

// Charger TikTok Pixel uniquement avec consentement
loadTikTokPixel('XXXXXXXXXXXXXXXX')
```

## 📊 Exemples d'Utilisation

### Dans un composant Vue

```vue
<template>
  <div>
    <button @click="addToCart">Ajouter au panier</button>
  </div>
</template>

<script setup>
import { useCookieConsent } from '@/composables/useCookieConsent'

const { hasCategoryConsent } = useCookieConsent()

const addToCart = () => {
  // Logique d'ajout au panier
  
  // Tracking conditionnel
  if (hasCategoryConsent('analytics')) {
    // Envoyer l'événement à Google Analytics
    if (typeof gtag !== 'undefined') {
      gtag('event', 'add_to_cart', {
        currency: 'XOF',
        value: product.price
      })
    }
  }
  
  if (hasCategoryConsent('marketing')) {
    // Envoyer l'événement à Meta Pixel
    if (typeof fbq !== 'undefined') {
      fbq('track', 'AddToCart')
    }
  }
}
</script>
```

### Vérification du consentement

```javascript
import { useCookieConsent } from '@/composables/useCookieConsent'

const { hasConsent, hasCategoryConsent } = useCookieConsent()

// Vérifier si l'utilisateur a donné son consentement
if (hasConsent.value) {
  console.log('L\'utilisateur a donné son consentement')
}

// Vérifier une catégorie spécifique
if (hasCategoryConsent('analytics')) {
  console.log('Consentement pour les cookies analytiques')
}
```

## 🎨 Personnalisation

### Modification des couleurs

Dans `CookieBanner.vue`, modifiez les classes Tailwind :

```vue
<!-- Couleur principale -->
<button class="bg-purple-600 hover:bg-purple-700">
  <!-- Remplacez purple par votre couleur -->
</button>
```

### Textes personnalisés

Modifiez les fichiers de traduction dans `src/locales/` :

```javascript
// fr.js
export default {
  cookies: {
    title: 'Votre titre personnalisé',
    description: 'Votre description personnalisée',
    // ...
  }
}
```

### Ajout de nouvelles catégories

1. Mettez à jour `useCookieConsent.js`
2. Ajoutez les traductions dans les fichiers `locales/`
3. Mettez à jour le template `CookieBanner.vue`

## 🔧 Configuration Avancée

### Durée de conservation des cookies

Dans `CookieBanner.vue` :

```javascript
const setCookie = (name, value, days = 365) => {
  // Modifiez la durée par défaut ici
}
```

### Scripts personnalisés

```javascript
const { loadScript } = useCookieConsent()

// Charger un script personnalisé
loadScript('https://example.com/script.js', 'analytics', 'custom-script')
  .then(() => {
    console.log('Script chargé avec succès')
  })
  .catch((error) => {
    console.error('Erreur de chargement:', error)
  })
```

## 📱 Responsive et Accessibilité

- ✅ Design responsive (mobile, tablette, desktop)
- ✅ Support du dark mode automatique
- ✅ Navigation au clavier
- ✅ Lecteurs d'écran compatibles
- ✅ Contrastes WCAG AA

## 🧪 Tests et Validation

### Tests manuels

1. **Test de la bannière** :
   - Videz vos cookies
   - Rechargez la page
   - Vérifiez que la bannière s'affiche

2. **Test des consentements** :
   - Testez chaque bouton
   - Vérifiez que les préférences sont sauvegardées
   - Rechargez la page

3. **Test des scripts** :
   - Ouvrez les outils de développement
   - Vérifiez l'onglet Réseau
   - Validez que les scripts ne se chargent qu'avec consentement

### Tests automatisés

```javascript
// Exemple de test avec Jest
import { useCookieConsent } from '@/composables/useCookieConsent'

describe('useCookieConsent', () => {
  it('should block analytics scripts without consent', () => {
    const { hasCategoryConsent } = useCookieConsent()
    
    expect(hasCategoryConsent('analytics')).toBe(false)
  })
  
  it('should allow essential scripts', () => {
    const { hasCategoryConsent } = useCookieConsent()
    
    expect(hasCategoryConsent('essential')).toBe(true)
  })
})
```

## 📋 Checklist de Déploiement

- [ ] Configurer les IDs de tracking (GA, Meta, TikTok)
- [ ] Adapter les textes à votre marque
- [ ] Personnaliser les couleurs
- [ ] Tester tous les scénarios de consentement
- [ ] Valider la conformité RGPD
- [ ] Tester sur mobile et desktop
- [ ] Vérifier l'accessibilité
- [ ] Documenter pour l'équipe

## 🆘 Support et Dépannage

### Problèmes courants

**Q: La bannière ne s'affiche pas**
- Vérifiez que le composant est bien importé
- Videz vos cookies et rechargez
- Vérifiez la console pour les erreurs

**Q: Les scripts se chargent même sans consentement**
- Assurez-vous d'utiliser le composable `useCookieConsent`
- Vérifiez que vous utilisez `loadScript()` pour les scripts conditionnels

**Q: Les traductions ne fonctionnent pas**
- Vérifiez que Vue I18n est correctement configuré
- Assurez-vous que les fichiers de traduction sont importés

### Contact

Pour toute question ou problème, contactez :
- 📧 Email : privacy@bloombychloe.bj
- 📱 Téléphone : +229 XX XX XX XX

---

**Version** : 1.0.0  
**Dernière mise à jour** : {{ new Date().toLocaleDateString('fr-BJ') }}  
**Licence** : MIT
