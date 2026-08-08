# Daba - E-commerce Platform

Plateforme e-commerce moderne pour la vente de produits en ligne.

## Stack Technique

- **Frontend** : Vue 3 (Composition API), Vite, Tailwind CSS, Pinia, Axios
- **Backend** : PHP Vanilla, Architecture orientée API, MySQL
- **Sécurité** : JWT (Opaque Tokens), CORS List, Rate Limiting, Headers de sécurité

## Procédure de Lancement

### 1. Base de données
Assurez-vous que MySQL est actif dans Laragon.
```bash
php backend/init_db.php
```

### 2. Backend (API)
Le backend doit tourner sur le port **8080**.
```bash
cd backend
php -S localhost:8080
```

### 3. Frontend (UI)
Démarrez le serveur de développement Vite :
```bash
cd frontend
npm run dev
```
Accès : **http://localhost:5173**

## Documentation

- [Résumé du projet](Documentation/RESUME_PROJET.md)
- [Guide de déploiement](DEPLOYMENT_GUIDE.md)
- [Audit de sécurité](SECURITY_AUDIT_REPORT.md)
- [Politique de confidentialité](RGPD_COMPLIANCE.md)
