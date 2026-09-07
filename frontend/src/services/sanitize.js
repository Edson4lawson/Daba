/**
 * Utilitaires de sanitization pour Daba
 * Protection contre XSS et autres injections côté client
 * 
 * @module services/sanitize
 */

/**
 * À‰chappe les caractères HTML dangereux
 * À€ utiliser avant d'afficher des données utilisateur
 * 
 * @param {string} text - Texte À  échapper
 * @returns {string} Texte échappé
 */
export function escapeHtml(text) {
    if (!text || typeof text !== 'string') {
        return '';
    }
    
    const htmlEscapes = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
        '/': '&#x2F;',
        '`': '&#x60;',
        '=': '&#x3D;'
    };
    
    return text.replace(/[&<>"'`=/]/g, char => htmlEscapes[char]);
}

/**
 * À‰chappe les caractères spéciaux pour une utilisation dans les attributs HTML
 * 
 * @param {string} text - Texte À  échapper
 * @returns {string} Texte échappé pour attribut
 */
export function escapeAttribute(text) {
    if (!text || typeof text !== 'string') {
        return '';
    }
    
    return text
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

/**
 * Nettoie une chaîne pour utilisation sÀ»re dans une URL
 * 
 * @param {string} text - Texte À  nettoyer
 * @returns {string} Texte nettoyé
 */
export function sanitizeForUrl(text) {
    if (!text || typeof text !== 'string') {
        return '';
    }
    
    return encodeURIComponent(text);
}

/**
 * Valide et nettoie une adresse email
 * 
 * @param {string} email - Email À  valider
 * @returns {string|null} Email nettoyé ou null si invalide
 */
export function sanitizeEmail(email) {
    if (!email || typeof email !== 'string') {
        return null;
    }
    
    const cleaned = email.trim().toLowerCase();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    return emailRegex.test(cleaned) ? cleaned : null;
}

/**
 * Nettoie un numéro de téléphone
 * 
 * @param {string} phone - Numéro de téléphone
 * @returns {string} Numéro nettoyé
 */
export function sanitizePhone(phone) {
    if (!phone || typeof phone !== 'string') {
        return '';
    }
    
    // Garder uniquement les chiffres, +, espaces et tirets
    return phone.replace(/[^\d+\s-]/g, '').trim();
}

/**
 * Nettoie un texte en supprimant les balises HTML
 * 
 * @param {string} text - Texte avec potentiellement du HTML
 * @returns {string} Texte sans HTML
 */
export function stripHtml(text) {
    if (!text || typeof text !== 'string') {
        return '';
    }
    
    // Créer un élément temporaire pour parser le HTML
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = text;
    return tempDiv.textContent || tempDiv.innerText || '';
}

/**
 * Valide un mot de passe selon les critères de sécurité
 * 
 * @param {string} password - Mot de passe À  valider
 * @returns {Object} Résultat de la validation
 */
export function validatePassword(password) {
    const result = {
        valid: true,
        errors: [],
        strength: 0 // 0-4
    };
    
    if (!password || typeof password !== 'string') {
        result.valid = false;
        result.errors.push('Le mot de passe est requis');
        return result;
    }
    
    // Critères de validation
    if (password.length < 8) {
        result.valid = false;
        result.errors.push('Au moins 8 caractères requis');
    } else {
        result.strength++;
    }
    
    if (!/[A-Z]/.test(password)) {
        result.valid = false;
        result.errors.push('Au moins une majuscule requise');
    } else {
        result.strength++;
    }
    
    if (!/[a-z]/.test(password)) {
        result.valid = false;
        result.errors.push('Au moins une minuscule requise');
    } else {
        result.strength++;
    }
    
    if (!/[0-9]/.test(password)) {
        result.valid = false;
        result.errors.push('Au moins un chiffre requis');
    } else {
        result.strength++;
    }
    
    if (!/[^A-Za-z0-9]/.test(password)) {
        result.errors.push('Un caractère spécial est recommandé');
        // Ne pas invalider, juste recommander
    } else {
        result.strength++;
    }
    
    // Vérifier les mots de passe communs
    const commonPasswords = [
        'password', '123456', 'password123', 'admin', 'letmein',
        'welcome', 'monkey', 'dragon', 'master', 'qwerty'
    ];
    
    if (commonPasswords.includes(password.toLowerCase())) {
        result.valid = false;
        result.errors.push('Ce mot de passe est trop courant');
    }
    
    return result;
}

/**
 * Génère un nonce pour les CSP inline scripts
 * 
 * @returns {string} Nonce aléatoire
 */
export function generateNonce() {
    const array = new Uint8Array(16);
    crypto.getRandomValues(array);
    return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
}

/**
 * Valide une URL
 * 
 * @param {string} url - URL À  valider
 * @param {string[]} allowedHosts - Hôtes autorisés (optionnel)
 * @returns {boolean} True si l'URL est valide
 */
export function isValidUrl(url, allowedHosts = []) {
    try {
        const parsedUrl = new URL(url);
        
        // Vérifier le protocole
        if (!['http:', 'https:'].includes(parsedUrl.protocol)) {
            return false;
        }
        
        // Vérifier l'hôte si une liste blanche est fournie
        if (allowedHosts.length > 0 && !allowedHosts.includes(parsedUrl.host)) {
            return false;
        }
        
        return true;
    } catch {
        return false;
    }
}

/**
 * Nettoie les données d'un objet récursivement
 * 
 * @param {Object} obj - Objet À  nettoyer
 * @returns {Object} Objet nettoyé
 */
export function sanitizeObject(obj) {
    if (!obj || typeof obj !== 'object') {
        return obj;
    }
    
    if (Array.isArray(obj)) {
        return obj.map(item => sanitizeObject(item));
    }
    
    const sanitized = {};
    
    for (const [key, value] of Object.entries(obj)) {
        if (typeof value === 'string') {
            sanitized[key] = escapeHtml(value);
        } else if (typeof value === 'object' && value !== null) {
            sanitized[key] = sanitizeObject(value);
        } else {
            sanitized[key] = value;
        }
    }
    
    return sanitized;
}

export default {
    escapeHtml,
    escapeAttribute,
    sanitizeForUrl,
    sanitizeEmail,
    sanitizePhone,
    stripHtml,
    validatePassword,
    generateNonce,
    isValidUrl,
    sanitizeObject
};



