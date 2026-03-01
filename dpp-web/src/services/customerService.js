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

export const customerService = {
  // Get all customers with optional filters
  async getCustomers (params = {}) {
    try {
      const response = await apiClient.get('/api/v1/customers', { params })
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  // Get single customer by ID
  async getCustomerById (id) {
    try {
      const response = await apiClient.get(`/api/v1/customers/${id}`)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  // Create new customer
  async createCustomer (data) {
    try {
      const response = await apiClient.post('/api/v1/customers', data)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  // Update customer
  async updateCustomer (id, data) {
    try {
      const response = await apiClient.put(`/api/v1/customers/${id}`, data)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  // Delete customer (soft delete)
  async deleteCustomer (id) {
    try {
      const response = await apiClient.delete(`/api/v1/customers/${id}`)
      return response.data
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data
      }
      return { success: false, message: 'Network error or server unavailable' }
    }
  },

  // Search customers
  async searchCustomers (searchParams) {
    try {
      const response = await apiClient.get('/api/v1/customers', {
        params: {
          keyword: searchParams.keyword,
          status: searchParams.status,
          per_page: searchParams.per_page || 15
        }
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

export default customerService
