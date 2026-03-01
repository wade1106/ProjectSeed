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
                <label class="block text-sm font-bold text-slate-700 mb-2">
                  所屬客戶 <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                  <input
                    v-model="form.customer_name"
                    type="text"
                    readonly
                    class="flex-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 text-sm outline-none"
                    placeholder="請選擇客戶..."
                  >
                  <button
                    type="button"
                    class="px-4 bg-white border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition shadow-sm"
                    @click="$emit('open-selector')"
                  >
                    <i class="fa-solid fa-building text-indigo-500" />
                  </button>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                  <label class="block text-sm font-bold text-slate-700 mb-2">
                    姓名 <span class="text-red-500">*</span>
                  </label>
                  <input v-model="form.name" type="text" class="form-input" placeholder="用戶姓名">
                </div>
                <div class="form-group">
                  <label class="block text-sm font-bold text-slate-700 mb-2">
                    登入帳號 <span class="text-red-500">*</span>
                  </label>
                  <input v-model="form.account" type="text" class="form-input" placeholder="帳號">
                </div>
              </div>

              <div class="form-group">
                <label class="block text-sm font-bold text-slate-700 mb-2">
                  電子郵件 <span class="text-red-500">*</span>
                </label>
                <input v-model="form.email" type="email" class="form-input" placeholder="Email 地址">
              </div>

              <div class="form-group">
                <label class="block text-sm font-bold text-slate-700 mb-2">
                  分配角色 <span class="text-red-500">*</span> <span class="text-xs font-normal text-slate-400 ml-2">(可多選)</span>
                </label>

                <div v-if="fetchingRoles" class="py-6 text-center text-slate-400 text-sm">
                  <i class="fa-solid fa-circle-notch animate-spin mr-2" /> 同步中...
                </div>

                <div v-else-if="availableRoles.length > 0" class="flex flex-wrap gap-3 p-4 bg-slate-50 rounded-xl border border-slate-200" :class="{ 'border-red-300 bg-red-50/30': errors.role_ids }">
                  <label v-for="role in availableRoles" :key="role.id" class="relative cursor-pointer">
                    <input
                      v-model="form.role_ids"
                      type="checkbox"
                      :value="String(role.id)"
                      class="hidden"
                      @change="delete errors.role_ids"
                    />

                    <div
                      class="px-4 h-[42px] rounded-lg border-2 transition-all duration-200 text-sm font-bold flex items-center gap-2 justify-center shadow-sm"
                      :style="isRoleSelected(role.id)
                        ? 'background-color: #4f46e5; border-color: #4f46e5; color: #ffffff;'
                        : 'background-color: #ffffff; border-color: #e2e8f0; color: #64748b;'"
                    >
                      <i
                        class="fa-solid fa-check-circle text-lg"
                        :style="isRoleSelected(role.id) ? 'opacity: 1; color: white;' : 'opacity: 0; width: 0; overflow: hidden;'"
                      />
                      <span class="whitespace-nowrap">{{ role.name }}</span>
                    </div>
                  </label>
                </div>
                <span v-if="errors.role_ids" class="text-red-500 text-xs mt-1 block font-bold">
                  <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ errors.role_ids }}
                </span>
              </div>

              <div v-if="!isEdit || showPasswordFields" class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-indigo-50/30 rounded-xl border border-indigo-100">
                <div class="form-group">
                  <label class="block text-sm font-bold text-slate-700 mb-2">
                    密碼 <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <input v-model="form.password" :type="isPasswordVisible ? 'text' : 'password'" class="form-input pr-10" placeholder="至少8位">
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" @click="isPasswordVisible = !isPasswordVisible">
                      <i class="fa-solid" :class="isPasswordVisible ? 'fa-eye-slash' : 'fa-eye'" />
                    </button>
                  </div>
                </div>
                <div class="form-group">
                  <label class="block text-sm font-bold text-slate-700 mb-2">
                    確認密碼 <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <input v-model="form.password_confirmation" :type="isConfirmPasswordVisible ? 'text' : 'password'" class="form-input pr-10" placeholder="再次確認">
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" @click="isConfirmPasswordVisible = !isConfirmPasswordVisible">
                      <i class="fa-solid" :class="isConfirmPasswordVisible ? 'fa-eye-slash' : 'fa-eye'" />
                    </button>
                  </div>
                </div>
              </div>

              <div v-if="isEdit" class="flex justify-start">
                <button type="button" class="text-sm text-indigo-600 font-bold flex items-center gap-1" @click="showPasswordFields = !showPasswordFields">
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
                  <span v-if="!isEdit" class="text-xs font-normal text-slate-400 ml-1">(新增用戶預設停用)</span>
                </span>
              </div>
            </form>
          </div>

          <div class="px-8 py-6 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">
            <button type="button" class="px-6 py-2.5 bg-white border border-slate-300 rounded-lg font-bold text-slate-600 hover:bg-slate-100" @click="close">
              取消
            </button>
            <button
              type="button"
              class="px-8 py-2.5 bg-indigo-600 text-white rounded-lg font-bold flex items-center gap-2 shadow-lg hover:bg-indigo-700 active:scale-95 transition-all"
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
    userData: { type: Object, default: null }
  },
  emits: ['close', 'save', 'open-selector'],
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
        role_ids: []
      },
      errors: {} // 用於儲存驗證錯誤訊息
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

        await this.fetchRolesByCustomer(this.form.customer_id)

        let userRoles = []
        if (this.userData.roles && Array.isArray(this.userData.roles)) {
          userRoles = this.userData.roles.map(r => String(r.id).toLowerCase())
        } else if (this.userData.role_ids && Array.isArray(this.userData.role_ids)) {
          userRoles = this.userData.role_ids.map(r => String(r).toLowerCase())
        }
        this.form.role_ids = userRoles
      } else {
        this.form.enable = false
      }
    },

    async fetchRolesByCustomer (customerId) {
      if (!customerId) return
      this.fetchingRoles = true
      try {
        const response = await roleService.getRoles({ customer_id: customerId, enable: 1 })
        if (response.success) {
          this.availableRoles = response.data.map(r => ({
            ...r,
            id: String(r.id)
          }))
        }
      } catch (e) {
        console.error('Fetch roles error', e)
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
      this.errors = {} // 重置錯誤訊息
    },

    setCustomer (company) {
      this.form.customer_id = String(company.id)
      this.form.customer_name = company.name
      this.fetchRolesByCustomer(this.form.customer_id)
      // 如果選擇了客戶，清除角色相關的錯誤
      if (this.errors.role_ids) delete this.errors.role_ids
    },

    handleSubmit () {
      this.errors = {} // 每次提交前清空錯誤

      // 1. 驗證：分配角色必填
      if (!this.form.role_ids || this.form.role_ids.length === 0) {
        this.errors.role_ids = '請至少為用戶分配一個角色'
      }

      // 2. 驗證其他基礎欄位 (可依需求增加更多驗證)
      // ...

      // 如果有任何驗證錯誤，中斷提交
      if (Object.keys(this.errors).length > 0) {
        return
      }

      this.isSubmitting = true
      const payload = { ...this.form }

      if (this.isEdit && !payload.password) {
        delete payload.password
        delete payload.password_confirmation
      }
      if (!this.isEdit) payload.enable = false

      this.$emit('save', payload)
    },

    close () { this.$emit('close') },

    isRoleSelected (roleId) {
      if (!this.form.role_ids || !Array.isArray(this.form.role_ids)) return false
      const normalizedRoleId = String(roleId).toLowerCase()
      return this.form.role_ids.some(id => String(id).toLowerCase() === normalizedRoleId)
    }
  }
}
</script>

<style scoped>
.form-input {
  @apply w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm;
}
.modal-enter-active, .modal-leave-active { transition: opacity 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
