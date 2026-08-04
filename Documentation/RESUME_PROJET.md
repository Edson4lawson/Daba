# 📝 Résumé du Projet daba

## 🌍 Aperçu
Bloom by Chloé est une boutique e-commerce moderne spécialisée dans les produits de beauté, maison et accessoires bien-être, ciblant principalement le marché béninois.

## 🛠️ Stack Technique
- **Frontend** : Vue 3 (Composition API), Vite, Tailwind CSS, Pinia, Axios.
- **Backend** : PHP Vanilla, Architecture orientée API, MySQL.
- **Sécurité** : JWT (Opaque Tokens), CORS List, Rate Limiting, Headers de sécurité.
- **Déploiement** : Nginx (prêt pour production), configuration Laragon recommandée.

## 🚀 Procédure de Lancement

### 1. Base de données
Assurez-vous que MySQL est actif dans Laragon.
```bash
php backend/init_db.php
```
*Cela crée la table `bloom_chloe` et insère ~114 produits par défaut.*

### 2. Backend (API)
Le backend doit tourner sur le port **8001** pour correspondre à la configuration actuelle du frontend.
```bash
npm run backend
```

### 3. Frontend (UI)
Démarrez le serveur de développement Vite :
```bash
npm run dev
```
Accès : **http://localhost:5173**

---

## 📈 État Actuel (Dernier Audit - 11/04/2026)
*   **Architecture** : ✅ Stable, séparation frontend/backend claire.
*   **Sécurité** : ✅ Rate limiting, headers et signature HMAC (paiements) implémentés.
*   **Base de données** : ✅ Schema.sql synchronisé (122 produits).
*   **Produits** : ✅ Chargement dynamique via l'API.
*   **SEO** : ✅ Meta-tags dynamiques via composable `useSEO`.

## ✅ Étapes Termínées
- [x] Connecter la wishlist au backend.
- [x] Implémenter la signature HMAC pour les paiements.
- [x] SEO : Ajouter les meta-tags dynamiques.
- [x] Configuration Backend sur le port 8001.
