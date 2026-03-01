import { defineStore } from 'pinia'
import { authService } from '@/services/authService'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('user_token') || null,
    userInfo: JSON.parse(localStorage.getItem('user_info') || 'null'),
    permissions: JSON.parse(localStorage.getItem('permissions') || '[]'),
    userType: localStorage.getItem('user_type') || null
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,

    // 修正：getter 命名不要與 state 重複，且應從 userInfo 獲取最新狀態
    currentUserRole: (state) => state.userInfo?.role || state.userType,

    /**
     * 檢查是否有特定模組的特定權限
     * 用法: authStore.hasPermission('import', 'view')
     */
    hasPermission: (state) => (moduleName, action) => {
      // 1. 基本安全檢查：確保 permissions 存在且是個物件
      if (!state.permissions || typeof state.permissions !== 'object') {
        return false
      }

      // 2. 取得該模組的權限列表
      const modulePermissions = state.permissions[moduleName]

      // 3. 檢查模組是否存在，且該動作是否在陣列中
      return Array.isArray(modulePermissions) && modulePermissions.includes(action)
    },
    customerId: (state) => state.userInfo?.customerId || null,
    customerName: (state) => state.userInfo?.customerName || ''
  },

  actions: {
    setLoginData (response) {
      if (response.success && response.token) {
        this.token = response.token
        this.userInfo = response.data || null

        // --- 核心修改開始 ---
        const role = response.data.role

        if (role === 'SysAdmin' || role === 'Admin') {
          // 統一將管理端角色標記為 'Admin'，供路由守衛 (router.beforeEach) 判斷
          this.userType = 'Admin'
          this.permissions = response.data.permissions || []
        } else if (role === 'User') {
          this.userType = 'User'
          // 修正：確保一致性，若後端 user 回傳是 permissions 就用 permissions
          this.permissions = response.data.permissions || response.data.features || []
        }
        // --- 核心修改結束 ---

        localStorage.setItem('user_token', this.token)
        localStorage.setItem('user_type', this.userType)
        localStorage.setItem('user_info', JSON.stringify(this.userInfo))
        localStorage.setItem('permissions', JSON.stringify(this.permissions))
      }
    },

    async logout () {
      try {
        await authService.logout()
      } catch (error) {
        console.error('Server logout failed:', error)
      }

      this.token = null
      this.userInfo = null
      this.permissions = []
      this.userType = null

      localStorage.removeItem('user_token')
      localStorage.removeItem('user_type')
      localStorage.removeItem('user_info')
      localStorage.removeItem('app_locales')
      localStorage.removeItem('user_lang')
    },

    async adminLogin (credentials) {
      const response = await authService.adminLogin(credentials)
      if (response.success) {
        this.setLoginData(response)
      }
      return response
    },

    async userLogin (credentials) {
      const response = await authService.userLogin(credentials)
      if (response.success) {
        this.setLoginData(response)
      }
      return response
    }
  }
})
