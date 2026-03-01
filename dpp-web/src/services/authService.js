import axios from 'axios'

const API_BASE_URL = process.env.VUE_APP_API_URL || 'http://localhost:8000'

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json'
  }
})

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

      window.location.href = '/#/login'
    }
    return Promise.reject(error)
  }
)

export const authService = {
  async adminLogin (data) {
    try {
      const response = await apiClient.post('/api/v1/admin/login', data)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  async userLogin (data) {
    try {
      const response = await apiClient.post('/api/v1/user/login', data)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  async logout () {
    try {
      const response = await apiClient.post('/api/v1/logout')
      return response.data
    } catch (error) {
      console.error('Logout API error:', error)
      // 即使 API 失敗，前端仍然要清除本地資料
      return { success: false }
    }
  }
}

export default apiClient
