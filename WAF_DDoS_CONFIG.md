# CONFIGURATION WAF ET PROTECTION DDoS - DABA

**Date:** 12 Juillet 2026  
**Infrastructure:** Cloudflare + Kubernetes

---

## 1. CLOUDFLARE WAF CONFIGURATION

### 1.1 Configuration de base

**Paramètres DNS:**
- **Type:** CNAME
- **Name:** api (pour backend)
- **Name:** www (pour frontend)
- **Target:** daba.vercel.app (frontend)
- **Target:** daba-api.onrender.com (backend)

**SSL/TLS:**
- **Mode:** Full (strict)
- **Minimum TLS Version:** TLS 1.2
- **Cipher Suites:** Modern

### 1.2 Règles WAF

#### Règles de protection automatiques (Managed Rules)

Activer les rule sets suivants :

**Cloudflare Managed Ruleset:**
- Level: Medium
- Anomaly Score: 5

**OWASP ModSecurity Core Rule Set:**
- Level: Medium
- Paranoia Level: 1

#### Règles personnalisées

**1. Protection contre les attaques SQL Injection:**
```
(http.request.uri contains "SELECT" or 
 http.request.uri contains "UNION" or 
 http.request.uri contains "DROP" or 
 http.request.uri contains "INSERT" or 
 http.request.uri contains "UPDATE" or 
 http.request.uri contains "DELETE") and 
 http.request.method in {"GET" "POST"}
Action: Block
```

**2. Protection contre les attaques XSS:**
```
(http.request.uri contains "<script" or 
 http.request.uri contains "javascript:" or 
 http.request.uri contains "onerror=" or 
 http.request.uri contains "onload=") and 
 http.request.method in {"GET" "POST"}
Action: Block
```

**3. Protection contre les path traversal:**
```
(http.request.uri contains "../" or 
 http.request.uri contains "..\\" or 
 http.request.uri contains "%2e%2e")
Action: Block
```

**4. Limitation des requêtes par IP:**
```
(http.request.uri.path matches "^/api/.*") and 
 cf.threat_score > 10
Action: Rate Limit (100 req/min)
```

**5. Protection contre les bots malveillants:**
```
(cf.bot_management.score < 30) and 
 http.request.uri.path matches "^/api/.*"
Action: Challenge (JS Challenge)
```

**6. Blocage des pays non autorisés (optionnel):**
```
(ip.geoip.country ne "BJ" and 
 ip.geoip.country ne "FR" and 
 ip.geoip.country ne "US")
Action: Block
```

### 1.3 Rate Limiting

**Configuration des rate limits:**

| Endpoint | Limite | Période | Action |
|----------|--------|---------|--------|
| /api/auth/login | 5 req |5 min | Block |
| /api/auth/register | 3 req | 1 hour | Block |
| /api/payment/* | 10 req | 1 hour | Block |
| /api/* | 100 req | 1 min | Challenge |
| /* | 200 req | 1 min | None |

### 1.4 Bot Fight Mode

Activer **Bot Fight Mode** pour :
- Détection automatique des bots
- Challenge JS pour les bots suspects
- Protection contre le scraping

### 1.5 Security Level

**Configuration:**
- **Security Level:** High
- **Challenge Passage:** High

---

## 2. PROTECTION DDoS

### 2.1 Cloudflare DDoS Protection

**Paramètres:**
- **HTTP DDoS Protection:** On
- **Under Attack Mode:** Off (activer en cas d'attaque)
- **Rate Limiting:** Configuré ci-dessus

### 2.2 Configuration Kubernetes

**Ingress avec rate limiting:**
```yaml
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: daba-ingress
  annotations:
    nginx.ingress.kubernetes.io/limit-connections: "100"
    nginx.ingress.kubernetes.io/limit-rps: "50"
    nginx.ingress.kubernetes.io/limit-burst: "100"
```

### 2.3 Configuration Nginx

**Dans nginx.conf:**
```nginx
# Zone de rate limiting
limit_req_zone $binary_remote_addr zone=api_limit:10m rate=10r/s;
limit_req_zone $binary_remote_addr zone=login_limit:10m rate=1r/s;
limit_conn_zone $binary_remote_addr zone=conn_limit:10m;

# Application
location /api/auth/login {
    limit_req zone=login_limit burst=5 nodelay;
    limit_conn conn_limit 10;
}

location /api/ {
    limit_req zone=api_limit burst=20 nodelay;
    limit_conn conn_limit 50;
}
```

---

## 3. MONITORING ET ALERTES

### 3.1 Cloudflare Analytics

**Métriques à surveiller:**
- Taux de blocage WAF
- Taux de challenge
- Requêtes par pays
- Requêtes par User-Agent
- Bande passante

### 3.2 Alertes

**Configurer les alertes pour:**
- Taux de blocage > 10%
- Taux d'erreur 5xx > 5%
- Latence > 500ms
- Trafic anormal (> 2x normal)

### 3.3 Logs

**Activer les logs:**
- **Log retention:** 7 jours
- **Log fields:** Tous
- **Log sampling:** 100%

---

## 4. INCIDENT RESPONSE

### 4.1 En cas d'attaque DDoS

1. **Activer Under Attack Mode** dans Cloudflare
2. **Augmenter les rate limits**
3. **Activer le challenge CAPTCHA** pour tout le trafic
4. **Mettre en place une page de maintenance**
5. **Notifier l'équipe technique**

### 4.2 En cas de compromission

1. **Isoler le serveur compromis**
2. **Analyser les logs WAF**
3. **Révoquer tous les tokens**
4. **Forcer le changement de mot de passe**
5. **Notifier les utilisateurs concernés**

---

## 5. TESTS DE SÉCURITÉ

### 5.1 Tests WAF

**Outils de test:**
- OWASP ZAP
- Burp Suite
- SQLMap
- Nmap

### 5.2 Tests DDoS

**Outils de simulation (attention: uniquement sur environnement de test):**
- Apache JMeter
- Locust
- k6

### 5.3 Tests de charge

**Configuration:**
- **Normal:** 100 req/s
- **Peak:** 500 req/s
- **Stress:** 1000 req/s

---

## 6. MAINTENANCE

### 6.1 Mises à jour régulières

- **Règles WAF:** Mensuelles
- **Configuration rate limiting:** Trimestrielle
- **Review des logs:** Hebdomadaire

### 6.2 Audit de sécurité

- **Audit WAF:** Trimestriel
- **Penetration test:** Annuel
- **Review des règles:** Mensuel

---

## 7. DOCUMENTATION

### 7.1 Cloudflare Documentation

- https://developers.cloudflare.com/waf/
- https://developers.cloudflare.com/ddos-protection/

### 7.2 OWASP Documentation

- https://owasp.org/www-project-modsecurity-core-rule-set/

---

## 8. CONTACT SUPPORT

**En cas d'incident de sécurité:**

- **Email:** security@daba.com
- **Téléphone:** [Numéro d'urgence]
- **Slack:** #security-alerts

---

**Configuration effective à compter du 12 Juillet 2026**
