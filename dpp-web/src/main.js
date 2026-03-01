import { createApp } from 'vue'
import { createPinia } from 'pinia' // 1. 引入
import App from './App.vue'
import router from './router'
import './assets/tailwind.css' // <--- 務必確認這行存在

const app = createApp(App)
const pinia = createPinia() // 2. 建立 Pinia 實例

app.use(pinia) // 3. 掛載 Pinia
app.use(router) // 掛載 Router
app.mount('#app')
