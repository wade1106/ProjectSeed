<template>
  <div class="bg-slate-900 min-h-screen flex flex-col items-center justify-center p-4 font-inter">
    <div class="fade-in w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden admin-glow">
      <div class="bg-slate-800 p-8 text-center relative">
        <div class="absolute top-4 right-4 text-slate-700 opacity-30 text-5xl">
          <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="inline-flex items-center justify-center w-14 h-14 bg-slate-700 text-indigo-400 rounded-xl mb-4 border border-slate-600 shadow-inner">
          <i class="fa-solid fa-user-shield text-2xl"></i>
        </div>
        <h1 class="text-xl font-bold text-white uppercase tracking-widest">DPP Admin Console</h1>
        <p class="text-slate-400 text-[12px] mt-1 font-mono tracking-tighter">
          INTERNAL SYSTEM
        </p>
      </div>
      <form @submit.prevent="handleLogin" class="p-8 space-y-6">
        <div class="flex flex-col items-start">
          <label class="text-[14px] font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">
            管理員帳號 / Admin ID
          </label>
          <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
              <i class="fa-solid fa-id-badge text-sm"></i>
            </span>
            <input
              v-model="form.account"
              type="text"
              placeholder="Enter admin ID"
              class="block w-full pl-10 pr-3 py-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-slate-800 transition outline-none font-mono"
              required
            >
          </div>
        </div>
        <div class="flex flex-col items-start">
          <label class="text-[14px] font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">
            管理密碼 / Password
          </label>
          <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
              <i class="fa-solid fa-key text-sm"></i>
            </span>
            <input
              v-model="form.password"
              :type="passwordFieldType"
              placeholder="Enter Password"
              class="block w-full pl-10 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-slate-800 transition outline-none"
              required
            >
            <button
              type="button"
              @click="togglePasswordVisibility"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-800 transition"
            >
              <i class="fa-solid" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
            </button>
          </div>
        </div>
        <div v-if="errorMessage" class="text-red-600 text-sm font-medium bg-red-50 border border-red-200 rounded-lg p-3"
        >
          <i class="fa-solid fa-triangle-exclamation mr-2"></i>
          {{ errorMessage }}
        </div>
        <button
          type="submit"
          :disabled="isSubmitting"
          class="w-full bg-slate-800 hover:bg-slate-700 disabled:bg-slate-500 text-white font-bold py-3 rounded-lg shadow-lg transition-all active:scale-[0.98] border border-slate-600 flex items-center justify-center gap-2"
        >
          <span>{{ loginButtonText }}</span>
          <i class="fa-solid fa-unlock-keyhole text-xs"></i>
        </button>
      </form>
      <div class="px-8 py-5 bg-slate-50 border-t border-slate-200 flex justify-between items-center">
        <router-link to="/login" class="text-xs text-slate-600 hover:text-indigo-600 font-medium flex items-center gap-2 transition">
          <i class="fa-solid fa-circle-arrow-left"></i>
          <span>客戶登入 / Customer Login</span>
        </router-link>
        <span class="text-[12px] text-slate-300 font-mono">AUTH_V7_STABLE</span>
      </div>
    </div>
    <footer class="mt-8 text-center text-slate-500">
      <p class="text-[14px] tracking-wide">
        © 2026 版權所有 / All Rights Reserved by, Ltd.
      </p>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  account: '',
  password: ''
})

const showPassword = ref(false)
const isSubmitting = ref(false)
const errorMessage = ref('')

const passwordFieldType = computed(() => showPassword.value ? 'text' : 'password')
const loginButtonText = computed(() => isSubmitting.value ? '驗證中 / Verifying...' : '驗證並進入後台 / Login')

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}

const handleLogin = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  errorMessage.value = ''

  try {
    const response = await authStore.adminLogin(form.value)

    if (response.success) {
      await router.push('/admin/customer')
    } else {
      errorMessage.value = response.message || '登入失敗，請檢查您的帳號密碼'
    }
  } catch (error) {
    errorMessage.value = '網路連線錯誤，請稍後再試'
    console.error('Admin login error:', error)
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.admin-glow {
  box-shadow: 0 0 40px rgba(0, 0, 0, 0.4);
}
.fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
.font-inter {
  font-family: 'Inter', sans-serif;
}
</style>
