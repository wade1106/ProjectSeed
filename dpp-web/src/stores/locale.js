import { defineStore } from 'pinia'
import { localeService } from '@/services/localeService'

export const useLocaleStore = defineStore('locale', {
  state: () => ({
    // 從 LocalStorage 初始化，若無則為空陣列
    locales: JSON.parse(localStorage.getItem('app_locales') || '[]'),
    // 當前語系，優先讀取 user_lang，預設 zh-TW
    currentLang: localStorage.getItem('user_lang') || 'zh-TW',
    loading: false
  }),

  getters: {
    /**
     * 取得當前語系的顯示名稱
     */
    currentLangName: (state) => {
      const active = state.locales.find(l => l.code === state.currentLang)
      return active ? active.name : 'Language'
    }
  },

  actions: {
    /**
     * 獲取語系清單
     */
    async fetchLocales () {
      // 如果已經有資料（無論是 state 還是快取讀取的），就不用重複請求
      if (this.locales.length > 0) return

      this.loading = true
      try {
        // 調用 API (請確保後端路徑正確)
        const response = await localeService.getLocales()
        if (response.data.success) {
          this.locales = response.data.data
          // 存入 LocalStorage 實現持久化快取
          localStorage.setItem('app_locales', JSON.stringify(this.locales))
        }
      } catch (error) {
        console.error('Fetch locales failed:', error)
        // 備援機制：如果 API 失敗，給予基本預設值，避免選單完全空白
        this.locales = [
          { code: 'zh-TW', name: '繁體中文' },
          { code: 'zh-CN', name: '简体中文' },
          { code: 'en-US', name: 'English' }
        ]
      } finally {
        this.loading = false
      }
    },

    /**
     * 切換語系
     * @param {string} langCode 語系代碼
     */
    setLanguage (langCode) {
      this.currentLang = langCode
      localStorage.setItem('user_lang', langCode)

      // 如果有整合 vue-i18n，可以在此處切換
      // i18n.global.locale.value = langCode

      // 某些情境下可能需要重新導向或重整頁面以套用後端語系
      // window.location.reload()
    }
  }
})
