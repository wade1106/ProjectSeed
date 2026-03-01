<template>
  <Teleport to="body">
    <div
      v-if="show"
      class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
      @click.self="closeModal"
    >
      <div class="relative bg-white w-full max-w-lg shadow-2xl rounded-2xl overflow-hidden fade-in">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <h3 class="text-xl font-bold text-slate-800">
            {{ isEdit ? '編輯管理者' : '新增管理者' }}
          </h3>
          <button
            type="button"
            class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:bg-white hover:text-slate-600 transition-all shadow-sm border border-transparent hover:border-slate-200"
            @click="closeModal"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form class="p-8 space-y-6" @submit.prevent="handleSubmit">
          <div class="flex items-start">
            <label class="w-24 pt-2 text-sm font-bold text-slate-600 shrink-0">
              姓名 <span class="text-rose-500">*</span>
            </label>
            <div class="flex-1">
              <input
                v-model="formData.name"
                type="text"
                required
                placeholder="請輸入真實姓名"
                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg text-slate-700 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all"
                :class="{ 'border-rose-500 ring-rose-500/10': errors.name }"
              >
              <p v-if="errors.name" class="mt-1.5 text-xs font-bold text-rose-500">{{ errors.name }}</p>
            </div>
          </div>

          <div class="flex items-start">
            <label class="w-24 pt-2 text-sm font-bold text-slate-600 shrink-0">
              電子郵件 <span class="text-rose-500">*</span>
            </label>
            <div class="flex-1">
              <input
                v-model="formData.email"
                type="email"
                required
                placeholder="example@domain.com"
                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg text-slate-700 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all"
                :class="{ 'border-rose-500 ring-rose-500/10': errors.email }"
              >
              <p v-if="errors.email" class="mt-1.5 text-xs font-bold text-rose-500">{{ errors.email }}</p>
            </div>
          </div>

          <div class="flex items-start">
            <label class="w-24 pt-2 text-sm font-bold text-slate-600 shrink-0">
              帳號 <span class="text-rose-500">*</span>
            </label>
            <div class="flex-1">
              <input
                v-model="formData.account"
                type="text"
                required
                placeholder="建議使用英數字組合"
                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg text-slate-700 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all"
                :class="{ 'border-rose-500 ring-rose-500/10': errors.account }"
              >
              <p v-if="errors.account" class="mt-1.5 text-xs font-bold text-rose-500">{{ errors.account }}</p>
            </div>
          </div>

          <div v-if="!isEdit || showPasswordFields" class="flex items-start">
            <label class="w-24 pt-2 text-sm font-bold text-slate-600 shrink-0">
              {{ isEdit ? '新密碼' : '密碼' }} <span v-if="!isEdit" class="text-rose-500">*</span>
            </label>
            <div class="flex-1">
              <div class="relative">
                <input
                  v-model="formData.password"
                  :type="showPassword ? 'text' : 'password'"
                  :required="!isEdit"
                  placeholder="請輸入至少 6 位字元"
                  class="w-full px-4 py-2.5 pr-12 bg-white border border-slate-300 rounded-lg text-slate-700 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all"
                  :class="{ 'border-rose-500 ring-rose-500/10': errors.password }"
                >
                <button
                  type="button"
                  @click="togglePasswordVisibility"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                >
                  <i class="fa-solid" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
              </div>
              <p v-if="errors.password" class="mt-1.5 text-xs font-bold text-rose-500">{{ errors.password }}</p>
              <p v-if="isEdit" class="mt-1.5 text-xs text-slate-400 font-medium">若不更改密碼請留空</p>
            </div>
          </div>

          <div class="flex items-center">
            <label class="w-24 text-sm font-bold text-slate-600 shrink-0">
              啟用
            </label>
            <div class="flex-1 flex items-center">
              <label :class="['inline-flex items-center group', !canToggleEnable ? 'cursor-not-allowed' : 'cursor-pointer']">
                <div class="relative">
                  <input
                    v-model="formData.enable"
                    type="checkbox"
                    :disabled="!canToggleEnable"
                    class="sr-only peer"
                  >
                  <div
                    class="w-11 h-6 rounded-full peer after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-focus:outline-none"
                    :class="!canToggleEnable
                      ? 'bg-slate-300 cursor-not-allowed'
                      : 'bg-slate-200 peer-checked:bg-emerald-500 peer-checked:after:translate-x-full peer-checked:after:border-white'">
                  </div>
                </div>
                <span
                  :class="['ml-3 text-sm font-bold transition-colors', !canToggleEnable ? 'text-slate-400' : 'text-slate-500 group-hover:text-slate-800']"
                >
                  {{ formData.enable ? '已啟用' : '已停用' }}
                </span>
              </label>
              <span v-if="isDefaultAdmin" class="ml-2 text-xs text-slate-400">
                最高權限管理員無法停用
              </span>
              <span v-else-if="isCurrentUser" class="ml-2 text-xs text-slate-400">
                使用中帳號無法停用
              </span>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 mt-8">
            <button
              type="button"
              class="px-6 py-2.5 text-sm font-bold text-slate-500 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-slate-700 transition-all active:scale-95"
              @click="closeModal"
            >
              取消
            </button>
            <button
              type="submit"
              class="px-8 py-2.5 text-sm font-bold text-white bg-slate-900 rounded-lg hover:bg-slate-800 transition-all shadow-lg shadow-slate-200 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="loading"
            >
              <i v-if="loading" class="fa-solid fa-circle-notch animate-spin mr-2"></i>
              {{ loading ? '處理中' : (isEdit ? '儲存變更' : '立即新增') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script>
import { adminService } from '../../services/adminService'

export default {
  name: 'AdminForm',
  props: {
    show: { type: Boolean, default: false },
    editAdmin: { type: Object, default: null },
    currentAdminId: { type: [String, Number], default: null }
  },
  emits: ['close', 'success'],
  data () {
    return {
      loading: false,
      showPasswordFields: true,
      showPassword: false,
      formData: { name: '', email: '', account: '', password: '', enable: true },
      errors: { name: '', email: '', account: '', password: '' }
    }
  },
  computed: {
    isEdit () { return !!this.editAdmin },
    isDefaultAdmin () { return this.editAdmin?.isDefault || false },
    isCurrentUser () {
      // 支援使用 id 或 uid 進行比對的數據結構
      return this.editAdmin && this.currentAdminId && (
        this.editAdmin.id === this.currentAdminId ||
        this.editAdmin.uid === this.currentAdminId
      )
    },
    canToggleEnable () {
      return !this.isDefaultAdmin && !this.isCurrentUser
    }
  },
  watch: {
    show (newVal) {
      if (newVal) {
        this.resetForm()
        if (this.editAdmin) {
          this.formData.name = this.editAdmin.name
          this.formData.email = this.editAdmin.email
          this.formData.account = this.editAdmin.account
          this.formData.enable = this.editAdmin.enable
          this.formData.password = ''
        }
      }
    }
  },
  mounted () {
    document.addEventListener('keydown', this.handleKeydown)
  },
  beforeUnmount () {
    document.removeEventListener('keydown', this.handleKeydown)
  },
  methods: {
    resetForm () {
      this.formData = { name: '', email: '', account: '', password: '', enable: true }
      this.showPassword = false
      this.clearErrors()
    },
    clearErrors () {
      this.errors = { name: '', email: '', account: '', password: '' }
    },
    closeModal () {
      if (!this.loading) {
        this.resetForm()
        this.$emit('close')
      }
    },
    handleKeydown (e) {
      if (e.key === 'Escape' && this.show) this.closeModal()
    },
    validateForm () {
      this.clearErrors()
      let isValid = true
      if (!this.formData.name.trim()) { this.errors.name = '姓名為必填項目'; isValid = false }
      if (!this.formData.email.trim()) { this.errors.email = '電子郵件為必填項目'; isValid = false } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.formData.email)) { this.errors.email = '請輸入有效的電子郵件地址'; isValid = false }
      if (!this.formData.account.trim()) { this.errors.account = '帳號為必填項目'; isValid = false }
      if (!this.isEdit && !this.formData.password) { this.errors.password = '密碼為必填項目'; isValid = false } else if (this.formData.password && this.formData.password.length < 6) { this.errors.password = '密碼至少需要6個字元'; isValid = false }
      return isValid
    },
    togglePasswordVisibility () {
      this.showPassword = !this.showPassword
    },

    // AdminForm.vue 內的 methods 修正
    async handleSubmit () {
      if (this.loading || !this.validateForm()) return
      this.loading = true
      this.clearErrors() // 提交前再次清空錯誤訊息

      try {
        const requestData = {
          name: this.formData.name.trim(),
          email: this.formData.email.trim(),
          account: this.formData.account.trim(),
          enable: this.formData.enable ? 1 : 0
        }

        // 只有在有輸入密碼時才送出密碼欄位
        if (this.formData.password) {
          requestData.password = this.formData.password
        }

        const result = this.isEdit
          ? await adminService.updateAdmin(this.editAdmin.id, requestData)
          : await adminService.createAdmin(requestData)

        // 關鍵修正：確保 result.success 為真時才執行後續
        if (result && (result.success || result.data)) {
          // 先通知父組件成功了（這樣父組件會去刷列表）
          this.$emit('success')
          // 隨即關閉視窗並重置表單
          this.closeModal()

          // 建議增加一個輕量通知（可用你專案內的 Toast 套件替代）
          console.log(this.isEdit ? '更新成功' : '新增成功')
        } else {
          // 處理後端回傳 success: false 的情況
          alert(result.message || '操作失敗，請檢查輸入資料')
        }
      } catch (error) {
        console.error('API Error:', error)
        // 這裡可以根據後端 422 錯誤塞回 errors 物件
        if (error.response && error.response.data.errors) {
          this.errors = { ...this.errors, ...error.response.data.errors }
        } else {
          alert('系統發生錯誤，請稍後再試')
        }
      } finally {
        this.loading = false
      }
    }

  }
}
</script>

<style scoped>
.fade-in { animation: fadeIn 0.3s ease-out forwards; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
