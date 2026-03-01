const { defineConfig } = require('@vue/cli-service')

module.exports = defineConfig({
  transpileDependencies: true,
  devServer: {
    // 這裡設定開發伺服器的代理規則
    proxy: {
      '/api': {
        // 這裡填入你 Laravel 後端的完整網址與 Port
        target: 'http://localhost:8000',
        changeOrigin: true, // 允許跨域轉換來源
        pathRewrite: {
          // 如果你的 API 請求本來就帶有 /api，則不需要 rewrite
          // 這裡的設定會保持路徑不變地轉發給後端
          // '^/api': '/api'
        }
      }
    }
  }
})
