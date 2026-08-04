import api from './api'

export const adminService = {
  // ═══════════════════════════════════════════
  // DASHBOARD & ANALYTICS
  // ═══════════════════════════════════════════
  
  async getStats(period = '6m') {
    const response = await api.get(`/admin/analytics/summary.php?period=${period}&t=${Date.now()}`)
    return {
      success: true,
      stats: {
        totalProducts: response.data.total_products || 0,
        totalOrders: response.data.orders_count || 0,
        totalClients: response.data.customers_count || 0,
        totalRevenue: response.data.revenue_total || 0,
        recentOrders: response.data.recent_orders || [],
        recentProducts: response.data.recent_products || [],
        topProducts: response.data.top_products || [],
        orderStatusCounts: response.data.order_status_counts || [],
        monthlySales: response.data.monthly_sales || []
      }
    }
  },

  async getAnalytics(period = '30d') {
    try {
      const response = await api.get(`/admin/analytics/detailed.php?period=${period}&t=${Date.now()}`)
      return { success: true, analytics: response.data }
    } catch {
      return { success: false, analytics: {} }
    }
  },

  // ═══════════════════════════════════════════
  // PRODUITS — CRUD complet
  // ═══════════════════════════════════════════

  async getProducts(params = {}) {
    const response = await api.get('/products/get_all.php', { 
      params: { ...params, t: Date.now() } 
    })
    
    // Extraire proprement les données pour éviter le "raw.map is not a function"
    const raw = response.data
    let productList = []
    
    if (Array.isArray(raw)) {
      productList = raw
    } else if (raw && Array.isArray(raw.data)) {
      productList = raw.data
    } else if (raw && Array.isArray(raw.products)) {
      productList = raw.products
    }
    
    return {
      success: true,
      products: productList
    }
  },

  async getProductById(id) {
    const response = await api.get(`/products/get_one.php?id=${id}&t=${Date.now()}`)
    return { success: true, product: response.data }
  },

  async createProduct(productData) {
    const response = await api.post('/admin/products/create.php', productData)
    return {
      success: true,
      product_id: response.data.product_id || response.data.id,
      message: response.data.message || 'Produit créé'
    }
  },

  async updateProduct(productData) {
    const response = await api.post('/admin/products/update.php', productData)
    return {
      success: true,
      message: response.data.message || 'Produit mis à jour'
    }
  },

  async deleteProduct(id) {
    const response = await api.post('/admin/products/delete.php', { id })
    return {
      success: true,
      message: response.data.message || 'Produit supprimé'
    }
  },

  async toggleNewest(productId, value) {
    const response = await api.post('/admin/products/toggle_feature.php', {
      id: productId, feature: 'is_newest', value
    })
    return { success: true, message: response.data.message }
  },

  async toggleBestseller(productId, value) {
    const response = await api.post('/admin/products/toggle_feature.php', {
      id: productId, feature: 'is_bestseller', value
    })
    return { success: true, message: response.data.message }
  },

  async toggleSpecialOffer(productId, value) {
    const response = await api.post('/admin/products/toggle_feature.php', {
      id: productId, feature: 'is_special_offer', value
    })
    return { success: true, message: response.data.message }
  },

  async uploadProductImage(productId, formData) {
    const response = await api.post(`/admin/products/upload_image.php?id=${productId}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    return { success: true, image_url: response.data.image_url }
  },

  // ═══════════════════════════════════════════
  // CATÉGORIES
  // ═══════════════════════════════════════════

  async getCategories() {
    const response = await api.get(`/categories/get_all.php?t=${Date.now()}`)
    const raw = response.data
    let list = []
    if (Array.isArray(raw)) list = raw
    else if (raw && Array.isArray(raw.data)) list = raw.data
    else if (raw && Array.isArray(raw.categories)) list = raw.categories
    
    return {
      success: true,
      categories: list
    }
  },

  async createCategory(data) {
    const response = await api.post('/admin/categories/create.php', data)
    return { success: true, category_id: response.data.id }
  },

  async updateCategory(data) {
    const response = await api.post('/admin/categories/update.php', data)
    return { success: true, message: response.data.message }
  },

  async deleteCategory(id) {
    const response = await api.post('/admin/categories/delete.php', { id })
    return { success: true, message: response.data.message }
  },

  // ═══════════════════════════════════════════
  // COMMANDES
  // ═══════════════════════════════════════════

  async getOrders(params = {}) {
    const response = await api.get('/admin/orders/get_all.php', {
      params: { ...params, t: Date.now() }
    })
    const raw = response.data
    let list = []
    if (Array.isArray(raw)) list = raw
    else if (raw && Array.isArray(raw.data)) list = raw.data
    else if (raw && Array.isArray(raw.orders)) list = raw.orders

    return {
      success: true,
      orders: list
    }
  },

  async getOrderDetail(orderId) {
    const response = await api.get(`/orders/get_one.php?id=${orderId}&t=${Date.now()}`)
    return {
      success: true,
      order: response.data
    }
  },

  async updateOrderStatus(orderId, status) {
    const response = await api.post('/admin/orders/update_status.php', {
      order_id: orderId,
      status
    })
    return { success: true, message: response.data.message }
  },

  async deleteOrder(orderId) {
    const response = await api.post('/admin/orders/delete.php', { id: orderId })
    return { success: true, message: response.data.message }
  },

  // ═══════════════════════════════════════════
  // CLIENTS
  // ═══════════════════════════════════════════

  async getCustomers(params = {}) {
    const response = await api.get('/admin/users/get_all.php', { 
      params: { ...params, t: Date.now() } 
    })
    const raw = response.data
    let list = []
    if (Array.isArray(raw)) list = raw
    else if (raw && Array.isArray(raw.data)) list = raw.data
    else if (raw && Array.isArray(raw.customers)) list = raw.customers
    else if (raw && Array.isArray(raw.users)) list = raw.users

    return {
      success: true,
      customers: list
    }
  },

  async getCustomerDetail(userId) {
    const response = await api.get(`/admin/users/get_one.php?id=${userId}&t=${Date.now()}`)
    return { success: true, customer: response.data }
  },

  async updateCustomerRole(userId, role) {
    const response = await api.post('/admin/users/update_role.php', {
      user_id: userId, role
    })
    return { success: true, message: response.data.message }
  },

  // ═══════════════════════════════════════════
  // PARAMÈTRES BOUTIQUE
  // ═══════════════════════════════════════════

  async getSettings() {
    try {
      const response = await api.get('/admin/settings/get.php')
      return { success: true, settings: response.data }
    } catch {
      return {
        success: true,
        settings: {
          store_name: 'Bloom by Chloé',
          store_email: 'egouassangni@mail.com',
          store_phone: '+229 01 56 78 37 70',
          store_address: 'Cotonou, Littoral, Bénin',
          currency: 'FCFA',
          free_shipping_threshold: 50000,
          shipping_fee: 2000
        }
      }
    }
  },

  async updateSettings(settings) {
    const response = await api.post('/admin/settings/update.php', settings)
    return { success: true, message: response.data.message }
  }
}

export default adminService
