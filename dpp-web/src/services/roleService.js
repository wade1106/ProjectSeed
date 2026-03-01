import axios from 'axios'

const API_BASE_URL = process.env.VUE_APP_API_URL || 'http://localhost:8000'

// Create axios instance with common configuration
const apiClient = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json'
  }
})

// Request interceptor to add JWT token
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('user_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor to handle auth errors
apiClient.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('user_token')
      localStorage.removeItem('user_type')
      localStorage.removeItem('user_info')
      localStorage.removeItem('app_locales')
      localStorage.removeItem('user_lang')
      // Redirect to login
      window.location.href = '/#/login'
    }
    return Promise.reject(error)
  }
)

export const roleService = {
  /**
   * 獲取角色列表
   * @param {Object} params - 篩選參數 { page, per_page, keyword, enable, customer_id }
   */
  async getRoles (params = {}) {
    try {
      // 關鍵修改：將傳入的 params 帶入 axios 的設定中
      const response = await apiClient.get('/api/v1/roles', {
        params
      })
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  // Get specific role by ID
  async getRoleById (roleId) {
    try {
      const response = await apiClient.get(`/api/v1/roles/${roleId}`)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  // Create new role
  async createRole (roleData) {
    try {
      const response = await apiClient.post('/api/v1/roles', roleData)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  // Update role
  async updateRole (roleId, roleData) {
    try {
      const response = await apiClient.put(`/api/v1/roles/${roleId}`, roleData)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  // Delete role
  async deleteRole (roleId) {
    try {
      const response = await apiClient.delete(`/api/v1/roles/${roleId}`)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  // Get all available permissions organized by modules
  async getPermissionsByModules () {
    try {
      const response = await apiClient.get('/api/v1/roles/permissions/modules')
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  // Get permission IDs for a specific role
  async getRolePermissions (roleId) {
    try {
      const response = await apiClient.get(`/api/v1/roles/${roleId}/permissions`)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  // Update role permissions
  async updateRolePermissions (roleId, permissionIds) {
    try {
      const response = await apiClient.put(`/api/v1/roles/${roleId}/permissions`, {
        permission_ids: permissionIds
      })
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  }
}

export default roleService
