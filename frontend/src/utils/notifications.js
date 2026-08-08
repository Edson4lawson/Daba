import Swal from 'sweetalert2'

/**
 * Système de notifications et d'alertes DABA
 * Style haut de gamme, palettes Daba, icônes SVG épurées (sans emojis/stickers).
 */

const dabaSwalClass = {
  container: 'daba-swal-container',
  popup: 'daba-swal-popup',
  toast: 'daba-swal-toast',
  title: 'daba-swal-title',
  htmlContainer: 'daba-swal-text',
  confirmButton: 'daba-swal-confirm-btn',
  cancelButton: 'daba-swal-cancel-btn',
  closeButton: 'daba-swal-close-btn'
}

/**
 * Toast pour l'ajout au panier
 */
export function notifyAddToCart(productTitle, quantity = 1) {
  const quantityText = quantity > 1 ? `${quantity}x ` : ''
  
  return Swal.fire({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2600,
    timerProgressBar: true,
    customClass: dabaSwalClass,
    html: `
      <div class="flex items-center gap-3.5 text-left">
        <div class="w-9 h-9 rounded-full bg-[#ce4600]/10 text-[#ce4600] flex items-center justify-center shrink-0 border border-[#ce4600]/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m15 11-1 9"/>
            <path d="m19 11-4-7"/>
            <path d="M2 11h20"/>
            <path d="m3.5 11 1.6 7.4a2 2 0 0 0 2 1.6h9.8a2 2 0 0 0 2-1.6l1.7-7.4"/>
            <path d="m4.5 11 4-7"/>
            <path d="m9 11 1 9"/>
          </svg>
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-xs font-bold uppercase tracking-wider text-[#ce4600] font-inter">Ajouté au panier</p>
          <p class="text-sm font-semibold text-[#1b2b47] truncate font-karla mt-0.5">${quantityText}${productTitle}</p>
        </div>
      </div>
    `
  })
}

/**
 * Toast pour la liste de favoris
 */
export function notifyWishlist(productTitle, added = true) {
  return Swal.fire({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2200,
    timerProgressBar: true,
    customClass: dabaSwalClass,
    html: `
      <div class="flex items-center gap-3 text-left">
        <div class="w-9 h-9 rounded-full ${added ? 'bg-[#ce4600]/10 text-[#ce4600] border border-[#ce4600]/20' : 'bg-gray-100 text-gray-500'} flex items-center justify-center shrink-0">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="${added ? 'currentColor' : 'none'}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
          </svg>
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-xs font-bold uppercase tracking-wider text-[#1b2b47] font-inter">${added ? 'Ajouté aux favoris' : 'Retiré des favoris'}</p>
          <p class="text-xs font-medium text-[#5a6474] truncate font-karla mt-0.5">${productTitle}</p>
        </div>
      </div>
    `
  })
}

/**
 * Modal de confirmation de commande
 */
export function notifyOrderSuccess(orderNumber) {
  return Swal.fire({
    icon: false,
    showConfirmButton: true,
    confirmButtonText: 'Voir ma commande',
    customClass: dabaSwalClass,
    buttonsStyling: false,
    html: `
      <div class="text-center py-2 px-1">
        <div class="w-16 h-16 rounded-full bg-[#6f8766]/15 text-[#6f8766] flex items-center justify-center mx-auto mb-4 border border-[#6f8766]/30">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 6 9 17l-5-5"/>
          </svg>
        </div>
        <h3 class="text-xl font-bold font-playfair text-[#1b2b47] mb-2">Commande confirmée</h3>
        <p class="text-sm font-karla text-[#5a6474] leading-relaxed max-w-sm mx-auto">
          Votre commande <strong class="text-[#1b2b47]">N° ${orderNumber}</strong> a été enregistrée avec succès. Nos équipes préparent la livraison.
        </p>
      </div>
    `
  })
}

/**
 * Modal ou Toast d'erreur
 */
export function notifyError(title = 'Une erreur est survenue', message = 'Veuillez réessayez dans quelques instants.', isToast = false) {
  if (isToast) {
    return Swal.fire({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3500,
      timerProgressBar: true,
      customClass: dabaSwalClass,
      html: `
        <div class="flex items-center gap-3 text-left">
          <div class="w-9 h-9 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0 border border-red-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" x2="12" y1="8" y2="12"/>
              <line x1="12" x2="12.01" y1="16" y2="16"/>
            </svg>
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-xs font-bold uppercase tracking-wider text-red-700 font-inter">${title}</p>
            <p class="text-xs text-[#5a6474] font-karla mt-0.5">${message}</p>
          </div>
        </div>
      `
    })
  }

  return Swal.fire({
    icon: false,
    showConfirmButton: true,
    confirmButtonText: 'Compris',
    customClass: dabaSwalClass,
    buttonsStyling: false,
    html: `
      <div class="text-center py-2 px-1">
        <div class="w-14 h-14 rounded-full bg-red-50 text-red-600 flex items-center justify-center mx-auto mb-4 border border-red-200">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" x2="12" y1="8" y2="12"/>
            <line x1="12" x2="12.01" y1="16" y2="16"/>
          </svg>
        </div>
        <h3 class="text-lg font-bold font-playfair text-[#1b2b47] mb-2">${title}</h3>
        <p class="text-xs font-karla text-[#5a6474] leading-relaxed max-w-sm mx-auto">
          ${message}
        </p>
      </div>
    `
  })
}

/**
 * Modal de succès générique (B2B, Contact, Profil, etc.)
 */
export function notifySuccess(title = 'Action réussie', message = '', buttonText = 'D\'accord') {
  return Swal.fire({
    icon: false,
    showConfirmButton: true,
    confirmButtonText: buttonText,
    customClass: dabaSwalClass,
    buttonsStyling: false,
    html: `
      <div class="text-center py-2 px-1">
        <div class="w-14 h-14 rounded-full bg-[#6f8766]/15 text-[#6f8766] flex items-center justify-center mx-auto mb-4 border border-[#6f8766]/30">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 6 9 17l-5-5"/>
          </svg>
        </div>
        <h3 class="text-lg font-bold font-playfair text-[#1b2b47] mb-2">${title}</h3>
        ${message ? `<p class="text-xs font-karla text-[#5a6474] leading-relaxed max-w-sm mx-auto">${message}</p>` : ''}
      </div>
    `
  })
}

/**
 * Toast générique d'information
 */
export function notifyInfo(title, message = '') {
  return Swal.fire({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2600,
    timerProgressBar: true,
    customClass: dabaSwalClass,
    html: `
      <div class="flex items-center gap-3 text-left">
        <div class="w-9 h-9 rounded-full bg-[#1b2b47]/10 text-[#1b2b47] flex items-center justify-center shrink-0 border border-[#1b2b47]/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 16v-4"/>
            <path d="M12 8h.01"/>
          </svg>
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-xs font-bold uppercase tracking-wider text-[#1b2b47] font-inter">${title}</p>
          ${message ? `<p class="text-xs text-[#5a6474] font-karla mt-0.5">${message}</p>` : ''}
        </div>
      </div>
    `
  })
}
