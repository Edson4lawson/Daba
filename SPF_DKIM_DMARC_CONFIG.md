# CONFIGURATION EMAIL SÉCURISÉE - daba

**Date:** 12 Juillet 2026  
**Domaine:** daba.com

---

## 1. SPF (Sender Policy Framework)

### Configuration DNS

Ajoutez un enregistrement TXT pour votre domaine :

```
Type: TXT
Name: @
Value: v=spf1 include:sendgrid.net -all
```

### Explication

- `v=spf1`: Version SPF
- `include:sendgrid.net`: Autorise SendGrid à envoyer des emails pour votre domaine
- `-all`: Rejette tous les emails non autorisés

### Test de validation

```bash
dig txt daba.com
```

Ou utiliser : https://mxtoolbox.com/spf.aspx

---

## 2. DKIM (DomainKeys Identified Mail)

### Configuration avec SendGrid

1. **Générer les clés DKIM** dans le dashboard SendGrid
2. **Ajouter les enregistrements DNS** fournis par SendGrid

Exemple typique :

```
Type: CNAME
Name: sendgrid._domainkey
Value: u1234567.wl123.sendgrid.net
```

### Configuration DNS

SendGrid fournira 3 enregistrements CNAME à ajouter :

```
Type: CNAME
Name: s1._domainkey
Value: s1.domainkey.u1234567.wl123.sendgrid.net

Type: CNAME
Name: s2._domainkey
Value: s2.domainkey.u1234567.wl123.sendgrid.net

Type: CNAME
Name: sendgrid._domainkey
Value: u1234567.wl123.sendgrid.net
```

### Test de validation

Envoyer un email à : check-auth@verifier.port25.com

Ou utiliser : https://dkimvalidator.com

---

## 3. DMARC (Domain-based Message Authentication, Reporting, and Conformance)

### Configuration DNS

Ajoutez un enregistrement TXT pour votre domaine :

```
Type: TXT
Name: _dmarc
Value: v=DMARC1; p=quarantine; rua=mailto:dmarc@daba.com; ruf=mailto:dmarc-fail@daba.com; fo=1
```

### Explication des paramètres

- `v=DMARC1`: Version DMARC
- `p=quarantine`: Policy (none, quarantine, reject)
  - `none`: Surveillance uniquement
  - `quarantine`: Mettre en spam (recommandé pour début)
  - `reject`: Rejeter (recommandé après validation)
- `rua`: Rapports agrégés (envoyés quotidiennement)
- `ruf`: Rapports d'échec (envoyés en temps réel)
- `fo=1`: Options de rapport (1 = envoyer tous les rapports)

### Politique progressive

**Phase 1 (Semaine 1-2):**
```
v=DMARC1; p=none; rua=mailto:dmarc@daba.com; ruf=mailto:dmarc-fail@daba.com; fo=1
```

**Phase 2 (Semaine 3-4):**
```
v=DMARC1; p=quarantine; rua=mailto:dmarc@daba.com; ruf=mailto:dmarc-fail@daba.com; fo=1; pct=50
```

**Phase 3 (Après validation):**
```
v=DMARC1; p=quarantine; rua=mailto:dmarc@daba.com; ruf=mailto:dmarc-fail@daba.com; fo=1
```

**Phase 4 (Production stable):**
```
v=DMARC1; p=reject; rua=mailto:dmarc@daba.com; ruf=mailto:dmarc-fail@daba.com; fo=1
```

### Test de validation

```bash
dig txt _dmarc.daba.com
```

Ou utiliser : https://dmarcian.com/dmarc-check

---

## 4. CONFIGURATION COMPLÈTE

### Enregistrements DNS finaux

```
# SPF
Type: TXT
Name: @
Value: v=spf1 include:sendgrid.net -all

# DKIM (3 enregistrements fournis par SendGrid)
Type: CNAME
Name: s1._domainkey
Value: s1.domainkey.u1234567.wl123.sendgrid.net

Type: CNAME
Name: s2._domainkey
Value: s2.domainkey.u1234567.wl123.sendgrid.net

Type: CNAME
Name: sendgrid._domainkey
Value: u1234567.wl123.sendgrid.net

# DMARC
Type: TXT
Name: _dmarc
Value: v=DMARC1; p=quarantine; rua=mailto:dmarc@daba.com; ruf=mailto:dmarc-fail@daba.com; fo=1
```

---

## 5. OUTILS DE VALIDATION

### SPF
- https://mxtoolbox.com/spf.aspx
- https://www.kitterman.com/spf/validate.html

### DKIM
- https://dkimvalidator.com
- https://www.mail-tester.com

### DMARC
- https://dmarcian.com/dmarc-check
- https://mxtoolbox.com/dmarc.aspx

### Complet
- https://www.mail-tester.com
- https://www.glockapps.com

---

## 6. SURVEILLANCE

### Rapports DMARC

Les rapports DMARC seront envoyés à :
- `dmarc@daba.com` (rapports agrégés quotidiens)
- `dmarc-fail@daba.com` (rapports d'échec en temps réel)

### Analyse des rapports

Utiliser des outils comme :
- DMARC Analyzer
- Valimail
- EasyDMARC

---

## 7. BONNES PRATIQUES

1. **Commencer avec p=none** pour surveiller sans impact
2. **Analyser les rapports** pendant 1-2 semaines
3. **Passer à p=quarantine** avec pct=50
4. **Augmenter progressivement** à 100%
5. **Passer à p=reject** après validation complète
6. **Surveiller régulièrement** les rapports DMARC

---

## 8. DÉPANNAGE

### Emails marqués comme spam

- Vérifier la réputation du domaine
- Vérifier les enregistrements SPF/DKIM/DMARC
- Vérifier le contenu des emails (éviter le spam)
- Vérifier la réputation de l'IP d'envoi

### Rapports DMARC vides

- Vérifier que l'adresse email est valide
- Vérifier que les enregistrements DNS sont propagés
- Attendre 24-48h pour la propagation DNS

### DKIM échoue

- Vérifier que les enregistrements CNAME sont corrects
- Vérifier la propagation DNS
- Régénérer les clés DKIM si nécessaire

---

## 9. CONTACT SUPPORT

Pour toute question sur la configuration email :

- **Email:** tech@daba.com
- **Documentation SendGrid:** https://sendgrid.com/docs/for-developers/sending-email/setting-up-spf-and-dkim/

---

**Configuration effective à compter du 12 Juillet 2026**
