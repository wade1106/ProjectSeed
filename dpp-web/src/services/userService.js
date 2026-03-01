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

export const userService = {
  // 獲取用戶列表
  async getUsers (params = {}) {
    try {
      const response = await apiClient.get('/api/v1/users', { params })
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  // 獲取單個用戶
  async getUserById (id) {
    try {
      const response = await apiClient.get(`/api/v1/users/${id}`)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  // 新增用戶
  async createUser (data) {
    try {
      const response = await apiClient.post('/api/v1/users', data)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  // 更新用戶
  async updateUser (id, data) {
    try {
      const response = await apiClient.put(`/api/v1/users/${id}`, data)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  // 刪除用戶
  async deleteUser (id) {
    try {
      const response = await apiClient.delete(`/api/v1/users/${id}`)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  }
}

export default userService
