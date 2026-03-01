import axios from 'axios'

const API_BASE_URL = process.env.VUE_APP_API_URL || 'http://localhost:8000'

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json'
  }
})

// Add auth token interceptor
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

// Handle token expiration
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

      window.location.href = '/#/console'
    }
    return Promise.reject(error)
  }
)

export const adminService = {
  /**
   * Get admin list with pagination and search
   * @param {Object} params - Search parameters
   * @param {number} params.page - Current page
   * @param {number} params.per_page - Items per page
   * @param {string} params.keyword - Search keyword (optional)
   * @param {boolean} params.enable - Filter by enable status (optional)
   * @returns {Promise<Object>} API response with data and pagination
   */
  async getAdmins (params = {}) {
    try {
      const queryParams = new URLSearchParams()

      if (params.page) queryParams.append('page', params.page)
      if (params.per_page) queryParams.append('per_page', params.per_page)
      if (params.keyword) queryParams.append('keyword', params.keyword)
      if (params.enable !== undefined) queryParams.append('enable', params.enable)

      const response = await apiClient.get(`/api/v1/admin/management?${queryParams.toString()}`)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  /**
   * Get single admin by ID
   * @param {string} id - Admin ID
   * @returns {Promise<Object>} API response
   */
  async getAdminById (id) {
    try {
      const response = await apiClient.get(`/api/v1/admin/management/${id}`)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  /**
   * Create new admin
   * @param {Object} adminData - Admin data
   * @param {string} adminData.name - Admin name
   * @param {string} adminData.email - Admin email
   * @param {string} adminData.account - Admin account
   * @param {string} adminData.password - Admin password
   * @param {boolean} adminData.enable - Enable status
   * @returns {Promise<Object>} API response
   */
  async createAdmin (adminData) {
    try {
      const response = await apiClient.post('/api/v1/admin/management', adminData)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  /**
   * Update existing admin
   * @param {string} id - Admin ID
   * @param {Object} adminData - Admin data to update
   * @param {string} adminData.name - Admin name (optional)
   * @param {string} adminData.email - Admin email (optional)
   * @param {string} adminData.account - Admin account (optional)
   * @param {string} adminData.password - Admin password (optional)
   * @param {boolean} adminData.enable - Enable status (optional)
   * @returns {Promise<Object>} API response
   */
  async updateAdmin (id, adminData) {
    try {
      const response = await apiClient.put(`/api/v1/admin/management/${id}`, adminData)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  /**
   * Delete admin (soft delete)
   * @param {string} id - Admin ID
   * @returns {Promise<Object>} API response
   */
  async deleteAdmin (id) {
    try {
      const response = await apiClient.delete(`/api/v1/admin/management/${id}`)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  }
}

export default apiClient
