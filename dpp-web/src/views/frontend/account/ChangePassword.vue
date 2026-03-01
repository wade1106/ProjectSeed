<template>
  <div class="bg-slate-50 min-h-screen flex flex-col items-center justify-center p-4 font-inter">
    <AppToast
      :show="toast.show"
      :message="toast.message"
      :type="toast.type"
    />

    <div class="change-password-card w-full max-w-lg bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
      <div class="p-8 pb-0 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 text-white rounded-2xl shadow-lg mb-4">
          <i class="fa-solid fa-key text-3xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Change Password</h1>
        <p class="text-slate-500 text-sm mt-1 font-medium">變更帳戶密碼</p>
      </div>

      <form
        class="p-8 space-y-5"
        @submit.prevent="handleSubmit"
      >
        <div class="grid grid-cols-2 gap-4">
          <div class="flex flex-col items-start">
            <label class="text-[12px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 ml-1">
              公司代碼 / Company
            </label>
            <input
              v-model="form.companyCode"
              type="text"
              class="block w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition"
              placeholder="Company Code"
              required
            >
          </div>
          <div class="flex flex-col items-start">
            <label class="text-[12px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 ml-1">
              帳號 / Account
            </label>
            <input
              v-model="form.username"
              type="text"
              class="block w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition"
              placeholder="Username"
              required
            >
          </div>
        </div>

        <div class="relative py-2">
          <div class="absolute inset-0 flex items-center">
            <span class="w-full border-t border-slate-100" />
          </div>
        </div>

        <div class="flex flex-col items-start">
          <label class="text-[12px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 ml-1">
            目前密碼 / Current Password
          </label>
          <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
              <i class="fa-solid fa-lock-open text-sm"></i>
            </span>
            <input
              v-model="form.oldPassword"
              :type="showOld ? 'text' : 'password'"
              placeholder="Enter current password"
              class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition"
              required
            >
            <button
              type="button"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-indigo-600 transition"
              @click="showOld = !showOld"
            >
              <i class="fa-solid" :class="showOld ? 'fa-eye-slash' : 'fa-eye'"></i>
            </button>
          </div>
        </div>

        <div class="flex flex-col items-start">
          <label class="text-[12px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 ml-1">
            新密碼 / New Password
          </label>
          <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
              <i class="fa-solid fa-shield-halved text-sm"></i>
            </span>
            <input
              v-model="form.password"
              :type="showNew ? 'text' : 'password'"
              placeholder="Uppercase, Lowercase, Number & Symbol(@$!%*?&)"
              class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition"
              required
            >
            <button
              type="button"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-indigo-600 transition"
              @click="showNew = !showNew"
            >
              <i class="fa-solid" :class="showNew ? 'fa-eye-slash' : 'fa-eye'"></i>
            </button>
          </div>
        </div>

        <div class="flex flex-col items-start">
          <label class="text-[12px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 ml-1">
            確認新密碼 / Confirm New Password
          </label>
          <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
              <i class="fa-solid fa-check-double text-sm"></i>
            </span>
            <input
              v-model="form.passwordConfirmation"
              :type="showConfirm ? 'text' : 'password'"
              placeholder="Repeat your new password"
              class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition"
              required
            >
            <button
              type="button"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-indigo-600 transition"
              @click="showConfirm = !showConfirm"
            >
              <i class="fa-solid" :class="showConfirm ? 'fa-eye-slash' : 'fa-eye'"></i>
            </button>
          </div>
        </div>

        <div
          v-if="errorMessage"
          class="text-red-600 text-xs font-medium bg-red-50 border border-red-200 rounded-lg p-3 animate-pulse flex items-start text-left"
        >
          <i class="fa-solid fa-circle-exclamation mr-2 mt-0.5 flex-shrink-0"></i>
          <span v-html="errorMessage" class="leading-normal"></span>
        </div>

        <div class="flex gap-3 pt-2">
          <button
            type="button"
            class="flex-1 px-4 py-3 border border-slate-200 text-slate-600 font-bold rounded-lg hover:bg-slate-50 transition active:scale-[0.98] disabled:opacity-50"
            :disabled="loading"
            @click="handleCancel"
          >
            取消 / Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="flex-[2] bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-bold py-3 rounded-lg shadow-md transition-all active:scale-[0.98] flex items-center justify-center gap-2"
          >
            <i v-if="loading" class="fa-solid fa-circle-notch animate-spin"></i>
            <span>{{ loading ? '更新中...' : '確認變更 / Update' }}</span>
            <i v-if="!loading" class="fa-solid fa-paper-plane text-xs"></i>
          </button>
        </div>
      </form>

      <div class="px-8 py-4 bg-slate-50 border-t border-slate-200 text-center overflow-hidden">
        <p class="text-[12px] text-slate-400 font-medium whitespace-nowrap">
          安全提示：請確保新密碼包含英文字母大小寫、數字及特殊符號，且長度至少 8 位。
        </p>
      </div>
    </div>

    <footer class="mt-8 text-center text-slate-400">
      <p class="text-[14px] tracking-wide">
        © 2026 版權所有 / All Rights Reserved
      </p>
    </footer>
  </div>
</template>

<script>
import { userProfileService } from '@/services/userProfileService'
import AppToast from '@/components/frontend/AppToast.vue'

export default {
  name: 'ChangePasswordView',

  components: {
    AppToast
  },

  data () {
    return {
      loading: false,
      errorMessage: '',
      // 控制密碼顯示狀態
      showOld: false,
      showNew: false,
      showConfirm: false,
      // Toast 狀態管理
      toast: {
        show: false,
        message: '',
        type: 'success'
      },
      form: {
        companyCode: '',
        username: '',
        oldPassword: '',
        password: '',
        passwordConfirmation: ''
      }
    }
  },

  mounted () {
    const { code, user } = this.$route.query
    if (code) this.form.companyCode = code
    if (user) this.form.username = user
  },

  methods: {
    validateForm () {
      const { password, passwordConfirmation } = this.form
      const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/

      if (password !== passwordConfirmation) {
        this.errorMessage = '兩次輸入的新密碼不符'
        return false
      }

      if (!passwordRegex.test(password)) {
        this.errorMessage = '密碼格式不符，密碼長度至少8位且需包含大小寫字母、數字及特殊符號(@$!%*?&)'
        return false
      }

      return true
    },

    async handleSubmit () {
      if (this.loading) return
      this.errorMessage = ''

      if (!this.validateForm()) return

      this.loading = true

      try {
        const payload = {
          companyCode: this.form.companyCode,
          username: this.form.username,
          old_password: this.form.oldPassword,
          password: this.form.password,
          password_confirmation: this.form.passwordConfirmation
        }

        const response = await userProfileService.changePassword(payload)

        if (response.success) {
          // 觸發成功 Toast
          this.toast = {
            show: true,
            message: '密碼變更成功，請使用新密碼重新登入',
            type: 'success'
          }

          localStorage.clear()

          // 延遲跳轉，確保使用者能看到成功提示
          setTimeout(() => {
            this.$router.push('/login')
          }, 1500)
        } else {
          this.errorMessage = response.message || '變更失敗，請檢查輸入資訊'
        }
      } catch (error) {
        this.errorMessage = error.message || '連線伺服器時發生錯誤'
        console.error('Password Change Error:', error)
      } finally {
        // 只有在沒有跳轉的情況下才重置 loading
        if (!this.toast.show) {
          this.loading = false
        }
      }
    },

    handleCancel () {
      this.$router.push('/login')
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

.font-inter {
  font-family: 'Inter', sans-serif;
}

.change-password-card {
  animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

input::placeholder {
  font-size: 0.85rem;
  color: #94a3b8;
}

button:focus {
  outline: none;
}
</style>
