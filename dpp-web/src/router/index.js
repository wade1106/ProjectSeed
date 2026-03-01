import { createRouter, createWebHashHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/login/LoginView.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/console',
    name: 'console',
    component: () => import('@/views/login/AdminLoginView.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/account/change-password',
    name: 'ChangePassword',
    component: () => import('@/views/frontend/account/ChangePassword.vue'),
    meta: {
      title: '變更密碼',
      requiresAuth: false
    }
  },
  // --- 前端使用者功能模組 ---
  {
    path: '/access',
    component: () => import('@/layouts/MainLayout.vue'),
    meta: { requiresAuth: true, role: 'User' },
    children: [
      {
        path: 'account',
        name: 'account',
        component: () => import('@/views/frontend/access/AccountView.vue')
      },
      {
        path: 'role',
        name: 'role',
        component: () => import('@/views/frontend/access/RoleView.vue')
      },
      {
        path: 'permission',
        name: 'permission',
        component: () => import('@/views/frontend/access/PermissionView.vue')
      }
    ]
  },
  // --- 後端管理者功能模組 ---
  {
    path: '/admin',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, roles: ['SysAdmin', 'Admin'] },
    children: [
      {
        path: 'Customer',
        name: 'admin-customer',
        component: () => import('@/views/backend/customer/CustomerView.vue')
      },
      {
        path: 'customer-user',
        name: 'admin-customer-user',
        component: () => import('@/views/backend/customer/CustomerUserView.vue')
      },
      {
        path: 'customer-product',
        name: 'admin-customer-product',
        component: () => import('@/views/backend/customer/CustomerProductCategoryView.vue')
      },
      {
        path: 'product-category',
        name: 'admin-product-category',
        component: () => import('@/views/backend/product/ProductCategoryView.vue')
      },
      {
        path: 'role-permission',
        name: 'admin-role-permission',
        component: () => import('@/views/backend/permission/RoleView.vue')
      },
      {
        path: 'function-definition',
        name: 'admin-function-definition',
        component: () => import('@/views/backend/permission/FunctionView.vue')
      },
      {
        path: 'user-management',
        name: 'admin-user-management',
        component: () => import('@/views/backend/admin/UserView.vue')
      }
    ]
  }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

// Global navigation guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // Skip authentication for non-protected routes
  if (!to.meta.requiresAuth) {
    return next()
  }

  // Check authentication using Pinia store
  if (!authStore.isAuthenticated) {
    return next('/login')
  }

  // 取得當前使用者的角色
  const userRole = authStore.userType // 假設這從 Pinia 取得，內容會是 'SysAdmin', 'Admin' 或 'user'

  // 1. 取得該路由允許的所有角色 (相容舊的 role 與新的 roles 寫法)
  const allowedRoles = to.meta.roles || (to.meta.role ? [to.meta.role] : [])

  // 2. 如果該頁面有定義角色限制，則檢查使用者是否符合其中之一
  if (allowedRoles.length > 0 && !allowedRoles.includes(userRole)) {
    // 權限不符時的導向邏輯
    if (userRole === 'SysAdmin' || userRole === 'Admin') {
      // 如果是管理員跑錯地方（例如去 user 頁面），導回管理後台首頁
      return next('/console')
    } else {
      // 如果是一般用戶跑去管理員頁面，導回用戶登入
      return next('/login')
    }
  }

  // All checks passed, allow navigation
  next()
})

export default router
