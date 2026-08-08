# CHECKLIST DE DÉPLOIEMENT SÉCURISÉ - DABA

**Version:** 1.0.0  
**Date:** .. ....... 2026

---

## PRÉ-DÉPLOIEMENT

### 1. Configuration de l'environnement

- [ ] Créer le fichier `.env.production`
- [ ] Générer `JWT_SECRET` (64 caractères minimum)
- [ ] Générer `ENCRYPTION_KEY` (64 caractères hexadécimaux)
- [ ] Configurer `STRIPE_SECRET_KEY` et `STRIPE_WEBHOOK_SECRET`
- [ ] Configurer `RECAPTCHA_SECRET_KEY`
- [ ] Configurer les credentials de base de données
- [ ] Configurer `ALLOWED_ORIGINS` avec les URLs de production

### 2. Base de données

- [ ] Exécuter la migration `001_security_tables.txt`
- [ ] Exécuter la migration `002_2fa_tables.txt`
- [ ] Exécuter la migration `003_rbac_permissions.txt`
- [ ] Créer l'utilisateur admin avec 2FA activé
- [ ] Vérifier que les tables sont créées correctement
- [ ] Tester la connexion à la base de données

### 3. Certificats SSL/TLS

- [ ] Configurer le certificat SSL pour le domaine
- [ ] Vérifier la configuration HSTS
- [ ] Tester la configuration TLS (testssl.sh)

### 4. DNS

- [ ] Configurer les enregistrements DNS A/CNAME
- [ ] Configurer SPF (v=spf1 include:sendgrid.net -all)
- [ ] Configurer DKIM (3 enregistrements CNAME SendGrid)
- [ ] Configurer DMARC (v=DMARC1; p=quarantine; ...)
- [ ] Vérifier la propagation DNS

---

## DÉPLOIEMENT BACKEND (RENDER)

### 1. Configuration Render

- [ ] Configurer `render.yaml`
- [ ] Configurer les variables d'environnement
- [ ] Configurer PostgreSQL
- [ ] Configurer le health check
- [ ] Configurer le webhook Stripe

### 2. Tests

- [ ] Tester l'endpoint `/health.php`
- [ ] Tester l'authentification
- [ ] Tester le 2FA
- [ ] Tester les rate limits
- [ ] Tester les paiements Stripe

---

## DÉPLOIEMENT FRONTEND (VERCEL)

### 1. Configuration Vercel

- [ ] Configurer `vercel.json`
- [ ] Configurer les variables d'environnement
- [ ] Configurer les rewrites
- [ ] Configurer les headers de sécurité
- [ ] Configurer le domaine personnalisé

### 2. Tests

- [ ] Tester le chargement de l'application
- [ ] Tester l'authentification
- [ ] Tester les appels API
- [ ] Tester le SEO (meta tags, sitemap)
- [ ] Tester le responsive design

---

## POST-DÉPLOIEMENT

### 1. Sécurité

- [ ] Vérifier tous les headers de sécurité
- [ ] Tester la configuration CORS
- [ ] Tester le rate limiting
- [ ] Tester le CAPTCHA
- [ ] Vérifier que les erreurs ne révèlent pas d'informations sensibles

### 2. Monitoring

- [ ] Configurer Cloudflare WAF
- [ ] Configurer les rate limits Cloudflare
- [ ] Configurer les alertes Cloudflare
- [ ] Configurer la journalisation centralisée
- [ ] Configurer les sauvegardes automatiques

### 3. Sauvegardes

- [ ] Configurer le script `backup_database.sh`
- [ ] Configurer le cron job pour les sauvegardes
- [ ] Tester la sauvegarde
- [ ] Tester la restauration
- [ ] Vérifier le chiffrement des sauvegardes

### 4. Email

- [ ] Configurer SendGrid
- [ ] Tester l'envoi d'emails
- [ ] Vérifier SPF/DKIM/DMARC
- [ ] Configurer les templates d'emails

### 5. Paiements

- [ ] Configurer Stripe webhooks
- [ ] Tester les paiements en mode test
- [ ] Activer le mode live Stripe
- [ ] Tester les webhooks
- [ ] Configurer les notifications de paiement

### 6. SEO

- [ ] Soumettre le sitemap à Google Search Console
- [ ] Vérifier la configuration robots.txt
- [ ] Vérifier les meta tags
- [ ] Vérifier les schema.org JSON-LD
- [ ] Tester le chargement des pages (Lighthouse)

---

## VÉRIFICATIONS FINALES

### 1. Tests de sécurité

- [ ] Scanner les vulnérabilités (composer audit)
- [ ] Tester les injections SQL
- [ ] Tester les XSS
- [ ] Tester les CSRF
- [ ] Tester l'élévation de privilèges

### 2. Tests de performance

- [ ] Tester le temps de chargement
- [ ] Tester les requêtes API
- [ ] Tester la base de données
- [ ] Tester les images
- [ ] Optimiser si nécessaire

### 3. Documentation

- [ ] Mettre à jour la documentation
- [ ] Documenter les procédures d'urgence
- [ ] Documenter les contacts
- [ ] Créer le plan de reprise après sinistre

---

## MAINTENANCE

### Quotidienne

- [ ] Vérifier les logs d'erreurs
- [ ] Vérifier les métriques de performance
- [ ] Vérifier les alertes de sécurité

### Hebdomadaire

- [ ] Vérifier les sauvegardes
- [ ] Review des logs de sécurité
- [ ] Vérifier les mises à jour de dépendances

### Mensuelle

- [ ] Review des règles WAF
- [ ] Review des rate limits
- [ ] Audit des accès administrateur
- [ ] Test de restauration de sauvegarde

### Trimestrielle

- [ ] Audit de sécurité complet
- [ ] Penetration test
- [ ] Review de la conformité RGPD
- [ ] Mise à jour de la documentation

---

## CONTACTS D'URGENCE

| Rôle | Nom | Email | Téléphone |
|------|-----|-------|-----------|
| Admin Système | [Nom] | [Email] | [Téléphone] |
| Développeur | [Nom] | [Email] | [Téléphone] |
| Sécurité | [Nom] | [Email] | [Téléphone] |

---

## DOCUMENTS DE RÉFÉRENCE

- [Rapport d'Audit de Sécurité](./SECURITY_AUDIT_REPORT.md)
- [Politique de Confidentialité RGPD](./RGPD_COMPLIANCE.md)
- [Configuration Email Sécurisée](./SPF_DKIM_DMARC_CONFIG.md)
- [Configuration WAF/DDoS](./WAF_DDoS_CONFIG.md)

---

**Checklist effective à compter du .. ....... 2026**
