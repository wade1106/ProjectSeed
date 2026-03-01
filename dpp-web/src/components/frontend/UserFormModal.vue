<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[10000] flex items-center justify-center p-4"
        @click="close"
      >
        <div
          class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden relative flex flex-col"
          @click.stop
        >
          <div class="bg-slate-50 px-8 py-6 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">
              {{ isEdit ? '編輯用戶' : '新增用戶' }}
            </h2>
            <button
              type="button"
              class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200 transition flex items-center justify-center"
              @click="close"
            >
              <i class="fa-solid fa-xmark" />
            </button>
          </div>

          <div class="flex-1 overflow-y-auto p-6">
            <form class="space-y-6" @submit.prevent="handleSubmit">
              <div class="form-group">
                <label class="block text-sm font-bold text-slate-700 mb-2">所屬客戶</label>
                <div class="relative">
                  <i class="fa-solid fa-building absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
                  <input
                    v-model="form.customer_name"
                    type="text"
                    readonly
                    class="w-full pl-11 pr-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-slate-500 text-sm outline-none cursor-not-allowed"
                  >
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                  <label class="block text-sm font-bold text-slate-700 mb-2">姓名 <span class="text-red-500">*</span></label>
                  <input
                    v-model="form.name"
                    type="text"
                    class="form-input"
                    :class="{ 'is-invalid': errors.name }"
                    placeholder="用戶姓名"
                  >
                  <span v-if="errors.name" class="text-red-500 text-xs mt-1 font-bold flex items-center gap-1">
                    <i class="fa-solid fa-circle-exclamation" /> {{ errors.name }}
                  </span>
                </div>
                <div class="form-group">
                  <label class="block text-sm font-bold text-slate-700 mb-2">登入帳號 <span class="text-red-500">*</span></label>
                  <input
                    v-model="form.account"
                    type="text"
                    class="form-input"
                    :class="{ 'is-invalid': errors.account }"
                    placeholder="登入帳號"
                  >
                  <span v-if="errors.account" class="text-red-500 text-xs mt-1 font-bold flex items-center gap-1">
                    <i class="fa-solid fa-circle-exclamation" /> {{ errors.account }}
                  </span>
                </div>
              </div>

              <div class="form-group">
                <label class="block text-sm font-bold text-slate-700 mb-2">電子郵件 <span class="text-red-500">*</span></label>
                <input
                  v-model="form.email"
                  type="email"
                  class="form-input"
                  :class="{ 'is-invalid': errors.email }"
                  placeholder="Email 地址"
                >
                <span v-if="errors.email" class="text-red-500 text-xs mt-1 font-bold flex items-center gap-1">
                  <i class="fa-solid fa-circle-exclamation" /> {{ errors.email }}
                </span>
              </div>

              <div class="form-group">
                <label class="block text-sm font-bold text-slate-700 mb-2">
                  分配角色 <span class="text-red-500">*</span> <span class="text-xs font-normal text-slate-400 ml-2">(可多選)</span>
                </label>

                <div v-if="fetchingRoles" class="py-6 text-center text-slate-400 text-sm">
                  <i class="fa-solid fa-circle-notch animate-spin mr-2" /> 同步角色清單...
                </div>

                <div v-else-if="availableRoles.length > 0"
                  class="flex flex-wrap gap-3 p-4 bg-slate-50 rounded-xl border"
                  :class="errors.role_ids ? 'border-red-300 bg-red-50/30' : 'border-slate-200'"
                >
                  <label v-for="role in availableRoles" :key="role.id" class="relative cursor-pointer group">
                    <input
                      v-model="form.role_ids"
                      type="checkbox"
                      :value="String(role.id)"
                      class="hidden"
                      @change="delete errors.role_ids"
                    >
                    <div
                      class="px-4 h-[42px] rounded-lg border-2 transition-all duration-200 text-sm font-bold flex items-center gap-2 justify-center shadow-sm"
                      :class="isRoleSelected(role.id)
                        ? 'bg-indigo-600 border-indigo-600 text-white'
                        : 'bg-white border-slate-200 text-slate-500 group-hover:border-indigo-300'"
                    >
                      <i v-if="isRoleSelected(role.id)" class="fa-solid fa-check-circle" />
                      <span class="whitespace-nowrap">{{ role.name }}</span>
                    </div>
                  </label>
                </div>

                <div v-else class="py-4 px-4 bg-amber-50 border border-amber-100 rounded-xl text-amber-700 text-xs flex items-center gap-2">
                  <i class="fa-solid fa-triangle-exclamation" /> 該客戶目前無可用角色，請先建立角色。
                </div>

                <span v-if="errors.role_ids" class="text-red-500 text-xs mt-1 font-bold flex items-center gap-1">
                  <i class="fa-solid fa-circle-exclamation" /> {{ errors.role_ids }}
                </span>
              </div>

              <div v-if="!isEdit || showPasswordFields" class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-indigo-50/30 rounded-xl border border-indigo-100">
                <div class="form-group">
                  <label class="block text-sm font-bold text-slate-700 mb-2">密碼 <span class="text-red-500">*</span></label>
                  <div class="relative">
                    <input
                      v-model="form.password"
                      :type="isPasswordVisible ? 'text' : 'password'"
                      class="form-input pr-10"
                      :class="{ 'is-invalid': errors.password }"
                      placeholder="至少8位"
                    >
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" @click="isPasswordVisible = !isPasswordVisible">
                      <i class="fa-solid" :class="isPasswordVisible ? 'fa-eye-slash' : 'fa-eye'" />
                    </button>
                  </div>
                  <span v-if="errors.password" class="text-red-500 text-xs mt-1 font-bold">{{ errors.password }}</span>
                </div>
                <div class="form-group">
                  <label class="block text-sm font-bold text-slate-700 mb-2">確認密碼 <span class="text-red-500">*</span></label>
                  <div class="relative">
                    <input
                      v-model="form.password_confirmation"
                      :type="isConfirmPasswordVisible ? 'text' : 'password'"
                      class="form-input pr-10"
                      :class="{ 'is-invalid': errors.password_confirmation }"
                      placeholder="再次確認"
                    >
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" @click="isConfirmPasswordVisible = !isConfirmPasswordVisible">
                      <i class="fa-solid" :class="isConfirmPasswordVisible ? 'fa-eye-slash' : 'fa-eye'" />
                    </button>
                  </div>
                  <span v-if="errors.password_confirmation" class="text-red-500 text-xs mt-1 font-bold">{{ errors.password_confirmation }}</span>
                </div>
              </div>

              <div v-if="isEdit" class="flex justify-start">
                <button type="button" class="text-sm text-indigo-600 font-bold flex items-center gap-1 hover:text-indigo-700 transition-colors" @click="showPasswordFields = !showPasswordFields">
                  <i :class="['fa-solid', showPasswordFields ? 'fa-minus-circle' : 'fa-plus-circle']" />
                  {{ showPasswordFields ? '取消變更密碼' : '我要變更密碼' }}
                </button>
              </div>

              <div class="form-group flex items-center gap-4 py-2">
                <label class="text-sm font-bold text-slate-700">帳號啟用狀態</label>
                <button
                  type="button"
                  class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
                  :class="[
                    form.enable ? 'bg-indigo-600' : 'bg-slate-300',
                    !isEdit ? 'opacity-50 cursor-not-allowed' : 'focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2'
                  ]"
                  :disabled="!isEdit"
                  @click="form.enable = !form.enable"
                >
                  <span
                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                    :class="form.enable ? 'translate-x-6' : 'translate-x-1'"
                  />
                </button>
                <span class="text-sm font-medium" :class="form.enable ? 'text-indigo-600' : 'text-slate-400'">
                  {{ form.enable ? '已啟用' : '已停用' }}
                  <span v-if="!isEdit" class="text-xs font-normal text-slate-400 ml-1">(新增預設停用)</span>
                </span>
              </div>
            </form>
          </div>

          <div class="px-8 py-6 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">
            <button type="button" class="px-6 py-2.5 bg-white border border-slate-300 rounded-lg font-bold text-slate-600 hover:bg-slate-100 transition-colors" @click="close">
              取消
            </button>
            <button
              type="button"
              class="px-8 py-2.5 bg-indigo-600 text-white rounded-lg font-bold flex items-center gap-2 shadow-lg hover:bg-indigo-700 active:scale-95 transition-all disabled:opacity-50"
              :disabled="isSubmitting"
              @click="handleSubmit"
            >
              <i v-if="isSubmitting" class="fa-solid fa-spinner animate-spin" />
              <i v-else class="fa-solid fa-save" />
              {{ isEdit ? '儲存修改' : '立即新增' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
import { roleService } from '@/services/roleService'

export default {
  name: 'UserFormModal',
  props: {
    show: { type: Boolean, default: false },
    userData: { type: Object, default: null },
    defaultCustomerId: { type: [String, Number], default: '' },
    defaultCustomerName: { type: String, default: '' }
  },
  emits: ['close', 'save'],
  data () {
    return {
      isSubmitting: false,
      showPasswordFields: false,
      isPasswordVisible: false,
      isConfirmPasswordVisible: false,
      fetchingRoles: false,
      availableRoles: [],
      form: {
        customer_id: '',
        customer_name: '',
        name: '',
        email: '',
        account: '',
        password: '',
        password_confirmation: '',
        enable: false,
        role_ids: [] // 初始化角色 ID 陣列
      },
      errors: {}
    }
  },
  computed: {
    isEdit () { return !!this.userData }
  },
  watch: {
    async show (val) {
      if (val) {
        await this.initializeForm()
      } else {
        this.resetFormFields()
      }
    }
  },
  methods: {
    async initializeForm () {
      this.resetFormFields()
      if (this.isEdit && this.userData) {
        this.form.customer_id = String(this.userData.customer_id || '')
        this.form.customer_name = this.userData.customer?.name || this.userData.customer_name || ''
        this.form.name = this.userData.name || ''
        this.form.email = this.userData.email || ''
        this.form.account = this.userData.account || ''
        this.form.enable = Boolean(this.userData.enable)

        // 編輯模式處理角色 (相容不同的資料格式)
        if (this.userData.roles && Array.isArray(this.userData.roles)) {
          this.form.role_ids = this.userData.roles.map(r => String(r.id))
        } else if (this.userData.role_ids) {
          this.form.role_ids = this.userData.role_ids.map(id => String(id))
        }
      } else {
        this.form.customer_id = String(this.defaultCustomerId)
        this.form.customer_name = this.defaultCustomerName
        this.form.enable = false
        this.form.role_ids = []
      }

      // 載入該客戶底下的可用角色
      if (this.form.customer_id) {
        await this.fetchRolesByCustomer(this.form.customer_id)
      }
    },
    async fetchRolesByCustomer (customerId) {
      this.fetchingRoles = true
      try {
        const response = await roleService.getRoles({ customer_id: customerId, enable: 1 })
        if (response.success) {
          this.availableRoles = response.data
        }
      } catch (e) {
        console.error('Fetch roles failed:', e)
      } finally {
        this.fetchingRoles = false
      }
    },
    resetFormFields () {
      this.form = {
        customer_id: '',
        customer_name: '',
        name: '',
        email: '',
        account: '',
        password: '',
        password_confirmation: '',
        enable: false,
        role_ids: []
      }
      this.availableRoles = []
      this.showPasswordFields = false
      this.isSubmitting = false
      this.isPasswordVisible = false
      this.isConfirmPasswordVisible = false
      this.errors = {}
    },
    handleSubmit () {
      this.errors = {}

      // 1. 基礎必填驗證
      if (!this.form.name?.trim()) this.errors.name = '請輸入用戶姓名'
      if (!this.form.account?.trim()) this.errors.account = '請輸入登入帳號'
      if (!this.form.email?.trim()) this.errors.email = '請輸入電子郵件'

      // 2. 角色驗證 (必填)
      if (this.form.role_ids.length === 0) {
        this.errors.role_ids = '請至少分配一個角色'
      }

      // 3. 密碼驗證
      if (!this.isEdit || this.showPasswordFields) {
        if (!this.form.password) {
          this.errors.password = '請設定登入密碼'
        } else if (this.form.password.length < 8) {
          this.errors.password = '密碼長度需至少 8 位'
        }
        if (this.form.password !== this.form.password_confirmation) {
          this.errors.password_confirmation = '確認密碼與密碼不一致'
        }
      }

      if (Object.keys(this.errors).length > 0) return

      this.isSubmitting = true
      const payload = { ...this.form }

      // 編輯模式處理
      if (this.isEdit && !this.showPasswordFields) {
        delete payload.password
        delete payload.password_confirmation
      }

      this.$emit('save', payload)
    },
    close () {
      this.$emit('close')
    },
    isRoleSelected (roleId) {
      return this.form.role_ids.includes(String(roleId))
    }
  }
}
</script>

<style scoped>
.form-input {
  @apply w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm bg-white;
}

/* 修正：使用自定義類名避免循環依賴 */
.is-invalid {
  @apply border-red-500 bg-red-50/10 !important;
}

.modal-enter-active, .modal-leave-active { transition: opacity 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
