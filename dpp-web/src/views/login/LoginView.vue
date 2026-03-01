<template>
  <div class="bg-slate-50 min-h-screen flex flex-col items-center justify-center p-4 font-inter">
    <div class="login-card w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
      <div class="p-8 pb-0 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 text-white rounded-2xl shadow-lg mb-4">
          <i class="fa-solid fa-passport text-3xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Digital Product Passport</h1>
        <p class="text-slate-500 text-sm mt-1 font-medium">數位產品護照</p>
      </div>

      <form @submit.prevent="handleLogin" class="p-8 space-y-5">
        <div class="flex flex-col items-start">
          <label class="text-[14px] font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">
            租戶代碼 / Customer Code
          </label>
          <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
              <i class="fa-solid fa-building-user text-sm"></i>
            </span>
            <input
              v-model="form.code"
              type="text"
              placeholder="Your Tenant Code"
              class="block w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none"
              required
            >
          </div>
        </div>

        <div class="flex flex-col items-start">
          <label class="text-[14px] font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">
            帳號 / Account
          </label>
          <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
              <i class="fa-solid fa-user text-sm"></i>
            </span>
            <input
              v-model="form.account"
              type="text"
              placeholder="Your Account ID"
              class="block w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 transition outline-none"
              required
            >
          </div>
        </div>

        <div class="flex flex-col items-start">
          <div class="flex justify-between items-end w-full mb-2 px-1">
            <label class="text-[14px] font-bold text-slate-500 uppercase tracking-wider">
              密碼 / Password
            </label>
            <a href="#" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-tighter">
              忘記密碼? / Forgot Password?
            </a>
          </div>
          <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
              <i class="fa-solid fa-lock text-sm"></i>
            </span>
            <input
              v-model="form.password"
              :type="passwordFieldType"
              placeholder="Your Password"
              class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 transition outline-none"
              required
            >
            <button
              type="button"
              @click="togglePasswordVisibility"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-indigo-600 transition"
            >
              <i class="fa-solid" :class="passwordVisible ? 'fa-eye-slash' : 'fa-eye'"></i>
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
          :disabled="loading"
          class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-bold py-3 rounded-lg shadow-md transition-all active:scale-[0.98] flex items-center justify-center gap-2"
        >
          <span>{{ loading ? '登入中... / Logging in...' : '登入系統 / Login' }}</span>
          <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
        </button>
      </form>

      <div class="px-8 py-4 bg-slate-50 border-t border-slate-200 text-center">
        <p class="text-xs text-slate-600">
          系統管理員？ / Administrator?
          <router-link to="/console" class="text-indigo-600 font-bold hover:underline ml-1">
            點此進入後台 / Admin Console
          </router-link>
        </p>
      </div>
    </div>

    <footer class="mt-8 text-center text-slate-400">
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
  code: '',
  account: '',
  password: ''
})

const passwordVisible = ref(false)
const loading = ref(false)
const errorMessage = ref('')

const passwordFieldType = computed(() => passwordVisible.value ? 'text' : 'password')

const togglePasswordVisibility = () => {
  passwordVisible.value = !passwordVisible.value
}

const handleLogin = async () => {
  if (loading.value) return

  loading.value = true
  errorMessage.value = ''

  try {
    const response = await authStore.userLogin(form.value)

    if (response.success) {
      await router.push({ name: 'product-passport-battery' })
    } else {
      errorMessage.value = response.message || '登入失敗，請檢查您的帳號密碼'
    }
  } catch (error) {
    errorMessage.value = '網路連線錯誤，請稍後再試'
    console.error('User login error:', error)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

.font-inter {
  font-family: 'Inter', sans-serif;
}

.login-card {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
