import { onMounted, watch } from 'vue';

export function useSEO() {
  const baseUrl = 'https://daba.com';

  /**
   * Met à jour les meta tags de base
   */
  const updateMetaTags = (metadata) => {
    const { title, description, keywords, image, url, noIndex } = metadata;

    if (title) {
      document.title = `${title} | Daba`;
      updateOrCreateMetaTag('og:title', title);
      updateOrCreateMetaTag('twitter:title', title);
    }

    if (description) {
      updateOrCreateMetaTag('description', description);
      updateOrCreateMetaTag('og:description', description);
      updateOrCreateMetaTag('twitter:description', description);
    }

    if (keywords) {
      updateOrCreateMetaTag('keywords', keywords);
    }

    if (image) {
      updateOrCreateMetaTag('og:image', image);
      updateOrCreateMetaTag('twitter:image', image);
    }

    if (url) {
      updateOrCreateMetaTag('og:url', url);
      updateOrCreateMetaTag('canonical', url, 'rel');
    }

    // Robots meta tag
    if (noIndex) {
      updateOrCreateMetaTag('robots', 'noindex, nofollow');
    } else {
      updateOrCreateMetaTag('robots', 'index, follow');
    }

    // Géolocalisation et hreflang
    updateHreflangTags();
  };

  /**
   * Ajoute ou met à jour un meta tag
   */
  const updateOrCreateMetaTag = (name, content, attribute = 'name') => {
    let element = document.querySelector(`meta[${attribute}="${name}"]`) || 
                  document.querySelector(`meta[property="${name}"]`) ||
                  document.querySelector(`link[rel="${name}"]`);
    
    if (!element) {
      if (attribute === 'rel') {
        element = document.createElement('link');
        element.setAttribute('rel', name);
      } else {
        element = document.createElement('meta');
        if (name.startsWith('og:') || name.startsWith('twitter:')) {
          element.setAttribute('property', name);
        } else {
          element.setAttribute('name', name);
        }
      }
      document.head.appendChild(element);
    }
    
    element.setAttribute('content', content);
  };

  /**
   * Ajoute les tags hreflang pour le SEO international
   */
  const updateHreflangTags = () => {
    const currentPath = window.location.pathname;
    const locales = [
      { code: 'fr', lang: 'fr-BJ' }, // Français - Bénin (principal)
      { code: 'en', lang: 'en-BJ' }, // Anglais - Bénin
      { code: 'fr', lang: 'fr-FR' }, // Français - France
    ];

    locales.forEach(locale => {
      const href = `${baseUrl}/${locale.code}${currentPath}`;
      let link = document.querySelector(`link[rel="alternate"][hreflang="${locale.lang}"]`);
      
      if (!link) {
        link = document.createElement('link');
        link.setAttribute('rel', 'alternate');
        link.setAttribute('hreflang', locale.lang);
        document.head.appendChild(link);
      }
      
      link.setAttribute('href', href);
    });

    // Canonical pour la page actuelle
    const canonicalUrl = `${baseUrl}${currentPath}`;
    let canonical = document.querySelector('link[rel="canonical"]');
    if (!canonical) {
      canonical = document.createElement('link');
      canonical.setAttribute('rel', 'canonical');
      document.head.appendChild(canonical);
    }
    canonical.setAttribute('href', canonicalUrl);
  };

  /**
   * Ajoute le schema.org JSON-LD pour les produits
   */
  const addProductSchema = (product) => {
    removeExistingSchema('Product');
    
    const schema = {
      '@context': 'https://schema.org/',
      '@type': 'Product',
      name: product.name,
      description: product.description,
      image: [product.image_url],
      brand: {
        '@type': 'Brand',
        name: 'Daba'
      },
      offers: {
        '@type': 'Offer',
        url: `${baseUrl}/produit/${product.slug}`,
        priceCurrency: 'XOF',
        price: product.price,
        availability: product.stock_quantity > 0 
          ? 'https://schema.org/InStock' 
          : 'https://schema.org/OutOfStock',
        seller: {
          '@type': 'Organization',
          name: 'Daba',
          url: baseUrl
        }
      },
      category: product.category_name,
      aggregateRating: product.rating ? {
        '@type': 'AggregateRating',
        ratingValue: product.rating,
        bestRating: '5',
        worstRating: '1'
      } : undefined
    };

    addJsonLdSchema(schema);
  };

  /**
   * Ajoute le schema.org JSON-LD pour l'organisation
   */
  const addOrganizationSchema = () => {
    removeExistingSchema('Organization');
    
    const schema = {
      '@context': 'https://schema.org',
      '@type': 'Organization',
      name: 'Daba',
      url: baseUrl,
      logo: `${baseUrl}/daba-icone.png`,
      description: 'Votre boucherie et charcuterie premium au Bénin',
      address: {
        '@type': 'PostalAddress',
        addressCountry: 'BJ',
        addressRegion: 'Littoral',
        addressLocality: 'Cotonou'
      },
      contactPoint: {
        '@type': 'ContactPoint',
        telephone: '+229XXXXXXXX',
        contactType: 'customer service',
        availableLanguage: ['French', 'English']
      },
      sameAs: [
        'https://facebook.com/daba',
        'https://instagram.com/daba',
        'https://twitter.com/daba'
      ]
    };

    addJsonLdSchema(schema);
  };

  /**
   * Ajoute le schema.org JSON-LD pour le e-commerce
   */
  const addWebSiteSchema = () => {
    removeExistingSchema('WebSite');
    
    const schema = {
      '@context': 'https://schema.org',
      '@type': 'WebSite',
      name: 'Daba',
      url: baseUrl,
      description: 'Boucherie et charcuterie premium au Bénin',
      potentialAction: {
        '@type': 'SearchAction',
        target: `${baseUrl}/recherche?q={search_term_string}`,
        'query-input': 'required name=search_term_string'
      }
    };

    addJsonLdSchema(schema);
  };

  /**
   * Ajoute le schema.org JSON-LD pour le breadcrumb
   */
  const addBreadcrumbSchema = (breadcrumbs) => {
    removeExistingSchema('BreadcrumbList');
    
    const schema = {
      '@context': 'https://schema.org',
      '@type': 'BreadcrumbList',
      itemListElement: breadcrumbs.map((crumb, index) => ({
        '@type': 'ListItem',
        position: index + 1,
        name: crumb.name,
        item: `${baseUrl}${crumb.path}`
      }))
    };

    addJsonLdSchema(schema);
  };

  /**
   * Ajoute un script JSON-LD au head
   */
  const addJsonLdSchema = (schema) => {
    const script = document.createElement('script');
    script.type = 'application/ld+json';
    script.id = `schema-${schema['@type']}`;
    script.textContent = JSON.stringify(schema);
    document.head.appendChild(script);
  };

  /**
   * Supprime un schema existant
   */
  const removeExistingSchema = (type) => {
    const existing = document.getElementById(`schema-${type}`);
    if (existing) {
      existing.remove();
    }
  };

  /**
   * Initialise le SEO pour la page d'accueil
   */
  const initHomepageSEO = () => {
    addOrganizationSchema();
    addWebSiteSchema();
  };

  return { 
    updateMetaTags,
    addProductSchema,
    addOrganizationSchema,
    addWebSiteSchema,
    addBreadcrumbSchema,
    initHomepageSEO
  };
}



