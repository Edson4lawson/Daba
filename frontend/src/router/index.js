import { createRouter, createWebHistory } from 'vue-router'

// Pages publiques
const Home = () => import('@/views/Home.vue')
const ProduitsPage = () => import('@/views/ProduitsPage.vue')
const NotreHistoire = () => import('@/views/NotreHistoire.vue')
const Tracabilite = () => import('@/views/Tracabilite.vue')
const EspaceB2B = () => import('@/views/EspaceB2B.vue')
const CookiePolicy = () => import('@/views/CookiePolicy.vue')
const FAQ = () => import('@/views/FAQ.vue')
const PrivacyPolicy = () => import('@/views/PrivacyPolicy.vue')
const Returns = () => import('@/views/Returns.vue')
const Shipping = () => import('@/views/Shipping.vue')
const Terms = () => import('@/views/Terms.vue')
const MentionsLegales = () => import('@/views/MentionsLegales.vue')
const ProductDetail = () => import('@/views/ProductDetail.vue')
const OrderConfirmation = () => import('@/views/OrderConfirmation.vue')

// Pages Admin
const AdminLogin = () => import('@/views/admin/AdminLogin.vue')
const AdminLayout = () => import('@/layouts/AdminLayout.vue')
const Dashboard = () => import('@/views/admin/Dashboard.vue')
const AdminProducts = () => import('@/views/admin/AdminProducts.vue')
const AdminStock = () => import('@/views/admin/AdminStock.vue')
const AdminOrders = () => import('@/views/admin/AdminOrders.vue')
const AdminCustomers = () => import('@/views/admin/AdminCustomers.vue')
const AdminInvoices = () => import('@/views/admin/AdminInvoices.vue')
const AdminSettings = () => import('@/views/admin/AdminSettings.vue')
const AdminAnalytics = () => import('@/views/admin/AdminAnalytics.vue')
const CategoryManager = () => import('@/views/admin/Categories/CategoryManager.vue')
const Unauthorized = () => import('@/views/Unauthorized.vue')

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home,
    meta: { title: 'Accueil — Daba' }
  },
  {
    path: '/produits',
    name: 'Produits',
    component: ProduitsPage,
    meta: { title: 'Produits — Daba' }
  },
  {
    path: '/boutique',
    redirect: '/produits'
  },
  {
    path: '/notre-histoire',
    name: 'NotreHistoire',
    component: NotreHistoire,
    meta: { title: 'Notre Histoire — Daba' }
  },
  {
    path: '/tracabilite',
    name: 'Tracabilite',
    component: Tracabilite,
    meta: { title: 'Traçabilité & Contrôle — Daba' }
  },
  {
    path: '/espace-b2b',
    name: 'EspaceB2B',
    component: EspaceB2B,
    meta: { title: 'Espace B2B — Daba' }
  },
  {
    path: '/produit/:slug',
    name: 'ProductDetail',
    component: ProductDetail,
    meta: { title: 'Produit — Daba' }
  },
  // {
  //   path: '/mon-compte',
  //   redirect: '/'
  // }, // Retiré - espace client désactivé
  {
    path: '/commande-confirmee/:orderId',
    name: 'OrderConfirmation',
    component: OrderConfirmation,
    meta: { title: 'Commande confirmée — Daba' }
  },
  {
    path: '/cookie-policy',
    name: 'CookiePolicy',
    component: CookiePolicy,
    meta: { title: 'Politique cookies — Daba' }
  },
  {
    path: '/faq',
    name: 'FAQ',
    component: FAQ,
    meta: { title: 'FAQ — Daba' }
  },
  {
    path: '/privacy',
    name: 'PrivacyPolicy',
    component: PrivacyPolicy,
    meta: { title: 'Confidentialité — Daba' }
  },
  {
    path: '/returns',
    name: 'Returns',
    component: Returns,
    meta: { title: 'Retours — Daba' }
  },
  {
    path: '/shipping',
    name: 'Shipping',
    component: Shipping,
    meta: { title: 'Livraison — Daba' }
  },
  {
    path: '/terms',
    name: 'Terms',
    component: Terms,
    meta: { title: 'CGV — Daba' }
  },
  {
    path: '/mentions-legales',
    name: 'MentionsLegales',
    component: MentionsLegales,
    meta: { title: 'Mentions légales — Daba' }
  },
  // Admin Login — Route séparée
  {
    path: '/admin/login',
    name: 'AdminLogin',
    component: AdminLogin,
    meta: { title: 'Admin Login — Daba' }
  },
  // Admin Dashboard (avec layout sidebar)
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      {
        path: '',
        redirect: { name: 'AdminDashboard' }
      },
      {
        path: 'dashboard',
        name: 'AdminDashboard',
        component: Dashboard,
        meta: { allowedRoles: ['admin', 'commercial', 'magasinier', 'comptable'] }
      },
      {
        path: 'products',
        name: 'AdminProducts',
        component: AdminProducts,
        meta: { allowedRoles: ['admin', 'magasinier'] }
      },
      {
        path: 'stock',
        name: 'AdminStock',
        component: AdminStock,
        meta: { allowedRoles: ['admin', 'magasinier'] }
      },
      {
        path: 'orders',
        name: 'AdminOrders',
        component: AdminOrders,
        meta: { allowedRoles: ['admin', 'commercial', 'comptable'] }
      },
      {
        path: 'users',
        name: 'AdminCustomers',
        component: AdminCustomers,
        meta: { allowedRoles: ['admin'] }
      },
      {
        path: 'invoices',
        name: 'AdminInvoices',
        component: AdminInvoices,
        meta: { allowedRoles: ['admin', 'comptable'] }
      },
      {
        path: 'settings',
        name: 'AdminSettings',
        component: AdminSettings,
        meta: { allowedRoles: ['admin'] }
      },
      {
        path: 'analytics',
        name: 'AdminAnalytics',
        component: AdminAnalytics,
        meta: { allowedRoles: ['admin', 'commercial', 'comptable'] }
      },
      {
        path: 'categories',
        name: 'AdminCategories',
        component: CategoryManager,
        meta: { allowedRoles: ['admin'] }
      }
    ]
  },

  // Unauthorized access
  {
    path: '/unauthorized',
    name: 'Unauthorized',
    component: Unauthorized,
    meta: { title: 'Accès non autorisé — Daba' }
  },
  // Catch-all 404
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('@/views/NotFound.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition
    if (to.hash) return { el: to.hash, behavior: 'smooth' }
    return { top: 0 }
  }
})

// Navigation guard
router.beforeEach((to, from, next) => {
  // Update document title
  if (to.meta.title) {
    document.title = to.meta.title
  }

  // Auth guard
  if (to.meta.requiresAuth) {
    const token = localStorage.getItem('access_token')
    if (!token) {
      if (to.meta.requiresAdmin) {
        next({ name: 'AdminLogin' })
      } else {
        next({ name: 'Home' })
      }
      return
    }
  }

  // RBAC guard - Vérifier les rôles autorisés
  if (to.meta.allowedRoles) {
    const user = JSON.parse(localStorage.getItem('user') || '{}')
    const userRole = user.role || 'customer'

    if (!to.meta.allowedRoles.includes(userRole)) {
      next({ name: 'Unauthorized' })
      return
    }
  }

  // Admin guard (legacy - pour compatibilité)
  if (to.meta.requiresAdmin) {
    const user = JSON.parse(localStorage.getItem('user') || '{}')
    const userRole = user.role || 'customer'
    const staffRoles = ['admin', 'super_admin', 'commercial', 'magasinier', 'comptable']

    if (!staffRoles.includes(userRole)) {
      next({ name: 'AdminLogin' })
      return
    }
  }

  next()
})

export default router


