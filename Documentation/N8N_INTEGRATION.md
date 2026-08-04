# Documentation Intégration n8n

## Overview

Cette documentation décrit l'intégration entre n8n et le backend PHP pour permettre la création de commandes via API externe (ex: WhatsApp).

---

## Endpoint de Création de Commande Externe

### Informations Générales

- **URL**: `/api/orders/create_external.php`
- **Méthode HTTP**: `POST`
- **Content-Type**: `application/json`
- **Authentification**: Via header `X-API-Key`

### Headers Requis

```http
X-API-Key: <votre_clé_api>
Content-Type: application/json
```

### Configuration de la Clé API

La clé API doit être configurée dans le fichier `.env` :

```env
EXTERNAL_API_KEY=votre_clé_secrète_ici
```

**⚠️ Important**: Ne jamais exposer cette clé en clair dans le code frontend ou dans les logs.

---

## Format de la Requête

### Corps JSON

```json
{
  "phone": "+1234567890",
  "name": "Client Name",
  "address": "Delivery Address",
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 2,
      "quantity": 1
    }
  ],
  "notes": "Optional delivery notes",
  "delivery_date": "YYYY-MM-DD",
  "delivery_time": "HH:MM"
}
```

### Champs Obligatoires

| Champ | Type | Description | Validation |
|-------|------|-------------|------------|
| `phone` | string | Numéro de téléphone du client | Format: 8-15 chiffres, peut inclure `+` |
| `name` | string | Nom complet du client | Non vide |
| `address` | string | Adresse de livraison | Non vide |
| `items` | array | Liste des articles de la commande | Au moins 1 article |

### Champs Optionnels

| Champ | Type | Description |
|-------|------|-------------|
| `notes` | string | Notes additionnelles pour la commande |
| `delivery_date` | string | Date de livraison souhaitée (format YYYY-MM-DD) |
| `delivery_time` | string | Heure de livraison souhaitée (format HH:MM) |

### Structure des Items

Chaque item dans le tableau `items` doit contenir :

| Champ | Type | Description | Validation |
|-------|------|-------------|------------|
| `product_id` | integer | ID du produit dans la base de données | Doit exister et être publié |
| `quantity` | integer | Quantité demandée | Minimum 1, doit être ≤ stock disponible |

---

## Réponses Possibles

### Succès (201 Created)

```json
{
  "success": true,
  "order_id": 125,
  "customer_id": 18,
  "status": "pending",
  "total_amount": 8500,
  "shipping_fee": 2000,
  "message": "Commande créée avec succès"
}
```

### Erreur de Validation (400 Bad Request)

```json
{
  "success": false,
  "message": "Champ 'phone' manquant"
}
```

### Erreur de Stock Insuffisant (400 Bad Request)

```json
{
  "success": false,
  "message": "Stock insuffisant pour le produit 'Product Name'",
  "product_id": 1,
  "available_quantity": 2,
  "requested_quantity": 5
}
```

### Erreur de Clé API (401 Unauthorized)

```json
{
  "error": "Clé API invalide"
}
```

### Produit Introuvable (404 Not Found)

```json
{
  "success": false,
  "message": "Produit ID 999 introuvable ou indisponible"
}
```

### Erreur Serveur (500 Internal Server Error)

```json
{
  "success": false,
  "message": "Erreur lors de la création de la commande"
}
```

---

## Codes HTTP

| Code | Signification |
|------|---------------|
| `201` | Commande créée avec succès |
| `400` | Erreur de validation des données |
| `401` | Clé API manquante ou invalide |
| `404` | Produit introuvable |
| `405` | Méthode HTTP non autorisée (doit être POST) |
| `429` | Trop de requêtes (rate limiting) |
| `500` | Erreur serveur interne |

---

## Comportement de l'Endpoint

### Gestion des Clients

1. **Si le numéro de téléphone existe déjà**:
   - L'utilisateur existant est utilisé
   - L'adresse est mise à jour si une nouvelle est fournie

2. **Si le numéro de téléphone n'existe pas**:
   - Un nouveau compte client est créé automatiquement
   - Un mot de passe temporaire est généré
   - L'email temporaire est généré automatiquement basé sur le numéro de téléphone

### Gestion du Stock

- Le stock est vérifié avec `FOR UPDATE` (lock row) pour éviter les conflits
- Si le stock est insuffisant, la commande est rejetée avec un message explicite
- Le stock est déduit immédiatement après création de la commande

### Calcul des Frais de Livraison

- Le champ `shipping_fee` peut être fourni dans la requête
- Si non fourni, les frais sont calculés à 0 par défaut
- TODO: Implémenter la logique de calcul des frais de livraison selon les règles métier

### Statut de la Commande

- `status`: `pending` (en attente de traitement)
- `payment_status`: `pending` (en attente de paiement)
- `payment_method`: `external` (commande via API externe)

---

## Exemple de Requête cURL

```bash
curl -X POST http://your-domain.com/api/orders/create_external.php \
  -H "X-API-Key: your_api_key" \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+1234567890",
    "name": "Client Name",
    "address": "Delivery Address",
    "items": [
      {
        "product_id": 1,
        "quantity": 2
      }
    ],
    "notes": "Optional delivery notes",
    "delivery_date": "YYYY-MM-DD",
    "delivery_time": "HH:MM"
  }'
```

---

## Exemple de Configuration n8n

### Node HTTP Request

Dans n8n, configurez un node **HTTP Request** avec les paramètres suivants :

**Method**: `POST`

**URL**: `http://your-domain.com/api/orders/create_external.php`

**Authentication**: `Generic Credential Type` → `Header Auth`

**Headers**:
```
X-API-Key: {{$credentials.apiKey}}
Content-Type: application/json
```

**Body** (JSON):
```json
{
  "phone": "={{$json.phone}}",
  "name": "={{$json.name}}",
  "address": "={{$json.address}}",
  "items": "={{$json.items}}",
  "notes": "={{$json.notes}}",
  "delivery_date": "={{$json.delivery_date}}",
  "delivery_time": "={{$json.delivery_time}}"
}
```

### Workflow WhatsApp → n8n → Backend

1. **WhatsApp Business API** reçoit un message de commande
2. **n8n** traite le message et extrait les données
3. **Node HTTP Request** envoie les données à l'endpoint create_external.php
4. **Backend** crée la commande et retourne l'order_id
5. **n8n** envoie une confirmation WhatsApp avec l'order_id

---

## Rate Limiting

L'endpoint est protégé par rate limiting :

- **Limite**: 30 requêtes par minute par IP
- **En cas de dépassement**: Code HTTP 429 avec header `Retry-After`

---

## Sécurité

### Protection contre les attaques

1. **Clé API**: Requise et validée via `hash_equals()` (timing-safe)
2. **Rate Limiting**: Protection contre abus
3. **Validation stricte**: Tous les champs sont validés
4. **SQL Injection**: Requêtes préparées PDO
5. **Stock**: Vérification avec lock row (`FOR UPDATE`)

### Logs

Toutes les créations de commandes externes sont loggées dans :
- `backend/logs/api_YYYY-MM-DD.log`

---

## Dépannage

### Erreur "Clé API manquante"

- Vérifiez que le header `X-API-Key` est bien envoyé
- Vérifiez que la variable d'environnement `EXTERNAL_API_KEY` est configurée

### Erreur "Clé API invalide"

- Vérifiez que la clé correspond exactement à celle dans `.env`
- Vérifiez qu'il n'y a pas d'espaces ou caractères invisibles

### Erreur "Produit introuvable"

- Vérifiez que le `product_id` existe dans la base de données
- Vérifiez que le produit a le statut `published`

### Erreur "Stock insuffisant"

- Vérifiez le stock disponible dans la base de données
- Ajustez la quantité demandée

### Erreur "Format de numéro invalide"

- Le numéro doit contenir 8-15 chiffres
- Le caractère `+` est autorisé en préfixe

---

## Mises à jour Futures

### Fonctionnalités envisagées

- [ ] Webhook de notification de changement de statut de commande
- [ ] Endpoint pour annuler une commande externe
- [ ] Endpoint pour vérifier le statut d'une commande
- [ ] Support pour les commandes récurrentes
- [ ] Intégration directe avec les passerelles Mobile Money

---

## Support

Pour toute question ou problème concernant l'intégration n8n, contactez l'équipe technique ou consultez les logs dans `backend/logs/`.
