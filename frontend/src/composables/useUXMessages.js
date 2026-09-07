import { ref } from 'vue'

/**
 * Composable pour les messages UX élégants et accessibles
 * Fournit des messages d'erreur, validation et confirmation clairs
 */
export function useUXMessages() {
  const messages = ref([])
  
  /**
   * Types de messages avec styles prédéfinis
   */
  const messageTypes = {
    success: {
      icon: '✓',
      color: 'green',
      title: 'Succès'
    },
    error: {
      icon: '✕',
      color: 'red',
      title: 'Erreur'
    },
    warning: {
      icon: '⚠',
      color: 'yellow',
      title: 'Attention'
    },
    info: {
      icon: 'ℹ',
      color: 'blue',
      title: 'Information'
    }
  }
  
  /**
   * Messages d'erreur élégants et explicites
   */
  const errorMessages = {
    // Authentification
    login_failed: 'Nous n\'avons pas pu vous connecter. Vérifiez votre email et votre mot de passe, puis réessayez.',
    register_failed: 'L\'inscription a échoué. Veuillez vérifier vos informations et réessayer.',
    email_exists: 'Cette adresse email est déjà utilisée. Connectez-vous ou utilisez une autre adresse.',
    weak_password: 'Votre mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.',
    session_expired: 'Votre session a expiré. Veuillez vous reconnecter pour continuer.',
    
    // Panier
    cart_error: 'Une erreur est survenue avec votre panier. Veuillez réessayer.',
    stock_insufficient: 'Ce produit n\'est plus disponible en quantité suffisante. La quantité a été ajustée au stock disponible.',
    product_unavailable: 'Ce produit est temporairement indisponible. Nous vous informerons dès son retour.',
    
    // Commande
    order_creation_failed: 'Nous n\'avons pas pu créer votre commande. Veuillez vérifier vos informations et réessayer.',
    payment_failed: 'Le paiement a échoué. Vérifiez vos informations de paiement ou essayez une autre méthode.',
    order_not_found: 'Cette commande n\'existe pas ou n\'est pas associée à votre compte.',
    
    // Produits
    product_not_found: 'Ce produit n\'existe pas ou a été retiré de notre catalogue.',
    category_not_found: 'Cette catégorie n\'existe pas.',
    
    // Général
    network_error: 'Problème de connexion. Vérifiez votre internet et réessayez.',
    server_error: 'Un problème technique est survenu. Nos équipes en sont informées. Réessayez dans quelques minutes.',
    permission_denied: 'Vous n\'avez pas la permission d\'effectuer cette action.',
    invalid_input: 'Les informations fournies sont invalides. Veuillez les vérifier.'
  }
  
  /**
   * Messages de validation clairs
   */
  const validationMessages = {
    required: 'Ce champ est obligatoire',
    email: 'Veuillez entrer une adresse email valide',
    phone: 'Veuillez entrer un numéro de téléphone valide',
    min_length: (min) => `Ce champ doit contenir au moins ${min} caractères`,
    max_length: (max) => `Ce champ ne peut pas dépasser ${max} caractères`,
    password_match: 'Les mots de passe ne correspondent pas',
    age_required: 'Vous devez avoir au moins 18 ans',
    terms_required: 'Vous devez accepter les conditions générales'
  }
  
  /**
   * Messages de confirmation positifs
   */
  const successMessages = {
    login: 'Connexion réussie ! Bienvenue chez Daba.',
    register: 'Compte créé avec succès ! Un email de confirmation vous a été envoyé.',
    logout: 'Vous avez été déconnecté avec succès.',
    profile_updated: 'Votre profil a été mis à jour avec succès.',
    password_changed: 'Votre mot de passe a été changé avec succès.',
    email_verified: 'Votre email a été vérifié avec succès.',
    
    // Panier
    added_to_cart: 'Produit ajouté à votre panier !',
    cart_updated: 'Votre panier a été mis à jour.',
    item_removed: 'Article retiré de votre panier.',
    
    // Commande
    order_created: 'Votre commande a été créée avec succès !',
    payment_success: 'Paiement accepté ! Votre commande est confirmée.',
    order_cancelled: 'Votre commande a été annulée.',
    
    // Favoris
    added_to_wishlist: 'Produit ajouté à votre liste de favoris !',
    removed_from_wishlist: 'Produit retiré de vos favoris.',
    
    // Contact
    message_sent: 'Votre message a été envoyé avec succès. Nous vous répondrons rapidement.',
    
    // Général
    saved: 'Modifications enregistrées avec succès.',
    deleted: 'Élément supprimé avec succès.'
  }
  
  /**
   * Ajoute un message à la liste
   */
  const addMessage = (type, content, options = {}) => {
    const id = Date.now()
    const defaultOptions = {
      duration: 5000,
      persistent: false,
      action: null
    }
    
    messages.value.push({
      id,
      type,
      content,
      ...messageTypes[type],
      ...defaultOptions,
      ...options
    })
    
    // Auto-suppression si non persistant
    if (!options.persistent) {
      setTimeout(() => {
        removeMessage(id)
      }, options.duration || 5000)
    }
    
    return id
  }
  
  /**
   * Supprime un message
   */
  const removeMessage = (id) => {
    const index = messages.value.findIndex(m => m.id === id)
    if (index !== -1) {
      messages.value.splice(index, 1)
    }
  }
  
  /**
   * Raccourcis pour les types de messages courants
   */
  const success = (content, options = {}) => addMessage('success', content, options)
  const error = (content, options = {}) => addMessage('error', content, options)
  const warning = (content, options = {}) => addMessage('warning', content, options)
  const info = (content, options = {}) => addMessage('info', content, options)
  
  /**
   * Raccourcis pour les messages prédéfinis
   */
  const showError = (key, options = {}) => {
    const message = errorMessages[key] || errorMessages.server_error
    return error(message, { ...options, persistent: true })
  }
  
  const showSuccess = (key, options = {}) => {
    const message = successMessages[key] || successMessages.saved
    return success(message, options)
  }
  
  /**
   * Validation d'un champ avec message d'erreur
   */
  const validateField = (value, rules) => {
    for (const rule of rules) {
      if (rule.required && !value) {
        return { valid: false, message: validationMessages.required }
      }
      
      if (rule.email && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        return { valid: false, message: validationMessages.email }
      }
      
      if (rule.minLength && value && value.length < rule.minLength) {
        return { valid: false, message: validationMessages.min_length(rule.minLength) }
      }
      
      if (rule.maxLength && value && value.length > rule.maxLength) {
        return { valid: false, message: validationMessages.max_length(rule.maxLength) }
      }
      
      if (rule.match && value !== rule.match) {
        return { valid: false, message: validationMessages.password_match }
      }
    }
    
    return { valid: true }
  }
  
  /**
   * Efface tous les messages
   */
  const clearMessages = () => {
    messages.value = []
  }
  
  return {
    messages,
    addMessage,
    removeMessage,
    success,
    error,
    warning,
    info,
    showError,
    showSuccess,
    validateField,
    clearMessages,
    errorMessages,
    successMessages,
    validationMessages
  }
}
