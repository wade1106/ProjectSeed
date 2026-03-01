import axios from 'axios'

const API_BASE_URL = process.env.VUE_APP_API_URL || 'http://localhost:8000'

/**
 * 創建一個針對 UserProfile 的 axios 實例
 * 由於變更密碼頁面是免登入的，此實例預設不帶 Authorization Token
 */
const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json'
  }
})

// 錯誤處理函數
const handleError = (error) => {
  let customError

  if (error.response) {
    // 伺服器回應錯誤 (如 400, 404, 422, 500)
    const { status, data } = error.response
    customError = new Error(data.message || '操作失敗')
    customError.status = status
    customError.data = data.data || null
  } else if (error.request) {
    // 請求發送失敗 (網路問題)
    customError = new Error('網路連線失敗，請檢查網路連線')
    customError.status = 0
    customError.data = null
  } else {
    // 其他錯誤
    customError = new Error(error.message || '發生未知錯誤')
    customError.status = 0
    customError.data = null
  }

  throw customError // 丟出 Error 物件以符合 ESLint no-throw-literal 規範
}

// API 響應格式化
const formatResponse = (response) => {
  return {
    success: response.data.success ?? true,
    data: response.data.data,
    message: response.data.message || '',
    timestamp: response.data.timestamp || new Date().toISOString()
  }
}

export const userProfileService = {
  /**
   * 免登入變更密碼
   * 參數對象包含：companyCode, username, old_password, password, password_confirmation
   */
  async changePassword (formData) {
    try {
      const response = await api.post('/api/v1/userProfile/change-password', formData)
      return formatResponse(response)
    } catch (error) {
      // 此處 catch 到的錯誤會經由 handleError 處理並拋出符合規範的 Error 物件
      return handleError(error)
    }
  }
}

export default userProfileService
