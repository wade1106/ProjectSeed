import axios from 'axios'

const API_BASE_URL = process.env.VUE_APP_API_URL || 'http://localhost:8000'

// 建立 axios 實例
const apiClient = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json'
  }
})

// Request 攔截器：自動注入 JWT Token
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

// Response 攔截器：處理驗證錯誤 (401)
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
      // 導向登入頁面
      window.location.href = '/#/login'
    }
    return Promise.reject(error)
  }
)

/**
 * 產品服務模組
 */
const productCategoryService = {
  /**
   * 獲取產品列表
   * @param {Object} params - 篩選參數 (keyword, enable, page, per_page)
   */
  async getProductCategories (params = {}) {
    try {
      const response = await apiClient.get('/api/v1/productCategory', { params })
      return response.data
    } catch (error) {
      return error.response?.data || { success: false, message: '網路連線異常或伺服器無回應' }
    }
  },

  /**
   * 獲取特定產品資料
   * @param {string|number} id - 產品 ID
   */
  async getProductCategoryById (id) {
    try {
      const response = await apiClient.get(`/api/v1/productCategory/${id}`)
      return response.data
    } catch (error) {
      return error.response?.data || { success: false, message: '無法讀取產品資料' }
    }
  },

  /**
   * 建立新產品
   * @param {Object} productCategoryData
   */
  async createProductCategory (productCategoryData) {
    try {
      const response = await apiClient.post('/api/v1/productCategory', productCategoryData)
      return response.data
    } catch (error) {
      return error.response?.data || { success: false, message: '產品類別建立失敗' }
    }
  },

  /**
   * 更新產品資料
   */
  async updateProductCategory (id, productCategoryData) {
    try {
      const response = await apiClient.put(`/api/v1/productCategory/${id}`, productCategoryData)
      return response.data
    } catch (error) {
      return error.response?.data || { success: false, message: '產品類別更新失敗' }
    }
  },

  /**
   * 刪除產品類別
   */
  async deleteProductCategory (id) {
    try {
      const response = await apiClient.delete(`/api/v1/productCategory/${id}`)
      return response.data
    } catch (error) {
      return error.response?.data || { success: false, message: '產品類別刪除失敗' }
    }
  },

  /**
   * 獲取產品的 HS 前綴碼清單
   * @param {string} productId
   */
  async getHsPrefixes (productId) {
    try {
      const response = await apiClient.get(`/api/v1/productCategory/${productId}/prefixes`)
      return response.data
    } catch (error) {
      return error.response?.data || { success: false, message: '載入 HS 前綴失敗' }
    }
  },

  /**
   * 更新產品的 HS 前綴碼 (批量)
   * @param {string} productId
   * @param {Array} prefixes
   */
  async updateHsPrefixes (productId, prefixes) {
    try {
      const response = await apiClient.put(`/api/v1/productCategory/${productId}/prefixes`, {
        prefixes
      })
      return response.data
    } catch (error) {
      return error.response?.data || { success: false, message: '更新 HS 前綴失敗' }
    }
  },

  /**
   * 獲取客戶的產品類別清單
   * @param {string|number} customerId - 客戶 ID
   */
  async getCustomerProductCategories (customerId) {
    try {
      const response = await apiClient.get('/api/v1/productCategory/customerProductCategories', {
        params: {
          customerId
        }
      })
      return response.data
    } catch (error) {
      return error.response?.data || { success: false, message: '載入客戶產品類別失敗' }
    }
  },

  /**
   * 更新客戶的產品類別清單
   * @param {string|number} customerId - 客戶 ID
   * @param {Array} categoryIds - 產品類別 ID 清單
   */
  async updateCustomerProductCategories (customerId, categoryIds) {
    try {
      const response = await apiClient.put('/api/v1/productCategory/customerProductCategories', {
        customerId,
        categoryIds
      })
      return response.data
    } catch (error) {

    }
  }
}

export { productCategoryService }
