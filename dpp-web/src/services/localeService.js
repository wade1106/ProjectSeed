import api from '@/services/index'

export const localeService = {
  /**
   * 取得語系列表
   * 符合後端 LocaleService 格式
   */
  async getLocales () {
    try {
      const { data } = await api.get('/api/v1/locales')
      return data.success ? data : {}
    } catch (error) {
      console.error('Fetch locales failed:', error)
      throw error
    }
  }
}
