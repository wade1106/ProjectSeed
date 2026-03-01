<template>
  <Teleport to="body">
    <div v-if="visible" class="fixed inset-0 z-[9999] overflow-y-auto">
      <div
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
        @click="close"
      ></div>

      <div class="flex min-h-full items-center justify-center p-4">
        <div
          class="relative z-[10000] bg-white w-full max-w-2xl max-h-[90vh] rounded-2xl shadow-2xl border border-slate-200 flex flex-col overflow-hidden fade-in-up"
        >
          <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2 text-left w-full">
              <i class="fa-solid" :class="isEditing ? 'fa-pen-to-square text-indigo-500' : 'fa-plus text-emerald-500'"></i>
              {{ isEditing ? '編輯角色' : '新增角色' }}
            </h3>
            <button @click="close" class="text-slate-400 hover:text-slate-600 transition-colors px-2 focus:outline-none">
              <i class="fa-solid fa-xmark text-xl"></i>
            </button>
          </div>

          <div class="flex-1 overflow-y-auto p-8 text-left">
            <form @submit.prevent="submitForm" class="space-y-6">

              <div class="text-left">
                <label class="block text-sm font-bold text-slate-700 mb-2">
                  所屬公司
                </label>
                <div class="flex gap-2">
                  <div class="relative flex-1">
                    <input
                      v-model="formData.customerName"
                      type="text"
                      readonly
                      class="w-full pl-4 pr-10 py-3 border border-slate-200 rounded-xl text-slate-400 outline-none cursor-not-allowed text-sm h-[46px] bg-slate-100 transition-colors"
                    >
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-300">
                      <i class="fa-solid fa-lock text-xs"></i>
                    </div>
                  </div>
                </div>
                <p class="mt-1 text-[11px] text-slate-400 italic">註：角色歸屬於當前登入之公司，不可變更</p>
              </div>

              <div class="text-left">
                <label class="block text-sm font-bold text-slate-700 mb-2">
                  角色名稱 <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.name"
                  type="text"
                  class="w-full px-4 py-3 rounded-lg border border-slate-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all"
                  :class="{ 'border-red-500 bg-red-50/30': errors.name }"
                  placeholder="例如：系統管理員"
                  :disabled="isNameDisabled"
                >
                <p v-if="errors.name" class="mt-1 text-sm text-red-500 font-medium">{{ errors.name }}</p>
              </div>

              <div class="text-left">
                <label class="block text-sm font-bold text-slate-700 mb-2">描述</label>
                <textarea
                  v-model="formData.description"
                  rows="4"
                  class="w-full px-4 py-3 rounded-lg border border-slate-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all"
                  placeholder="請輸入角色描述內容..."
                ></textarea>
              </div>

              <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 text-left">
                <label class="block text-sm font-bold text-slate-700 mb-2">啟用狀態</label>
                <select
                  v-model="formData.enable"
                  class="w-full px-3 py-2 rounded-md border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none bg-white"
                  :disabled="formData.isDefault"
                >
                  <option :value="true">啟用</option>
                  <option :value="false">停用</option>
                </select>
              </div>
            </form>
          </div>

          <div class="px-8 py-6 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <div class="text-xs text-slate-400 text-left">
              <span v-if="isEditing && formData.updated_at">最後更新日期：{{ formatDate(formData.updated_at) }}</span>
            </div>
            <div class="flex gap-3">
              <button
                type="button"
                @click="close"
                class="px-6 py-2.5 rounded-lg border border-slate-200 text-slate-600 font-bold hover:bg-white hover:shadow-sm transition-all"
              >
                取消
              </button>
              <button
                type="button"
                @click="submitForm"
                :disabled="isSubmitting"
                class="px-8 py-2.5 rounded-lg bg-slate-900 text-white font-bold hover:bg-slate-800 shadow-lg active:scale-95 transition-all flex items-center gap-2 disabled:opacity-50"
              >
                <i v-if="isSubmitting" class="fa-solid fa-circle-notch animate-spin text-sm"></i>
                {{ isSubmitting ? '儲存中...' : (isEditing ? '更新角色' : '確認儲存') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script>
export default {
  name: 'RoleFormModal',

  props: {
    visible: Boolean,
    role: Object, // 現在新增時也會傳入包含公司資訊的物件
    isEditing: Boolean
  },

  emits: ['close', 'submit'],

  data () {
    return {
      isSubmitting: false,
      formData: {
        customerId: '',
        customerName: '',
        name: '',
        description: '',
        enable: true,
        isDefault: false,
        updated_at: null
      },
      errors: {
        name: ''
      }
    }
  },

  computed: {
    isNameDisabled () {
      return this.isEditing && this.formData.isDefault
    }
  },

  watch: {
    visible: {
      immediate: true,
      handler (newVal) {
        if (newVal) {
          this.resetForm()
          if (this.role) {
            this.initFormData(this.role)
          }
        }
      }
    }
  },

  methods: {
    resetForm () {
      this.formData = {
        customerId: '',
        customerName: '',
        name: '',
        description: '',
        enable: true,
        isDefault: false,
        updated_at: null
      }
      this.errors = { name: '' }
      this.isSubmitting = false
    },

    initFormData (role) {
      this.formData = {
        // 無論新增(預設帶入)或編輯(讀取舊資料)，都從 role prop 取得
        customerId: role.customer_id || role.customer?.id || '',
        customerName: role.customer_name || role.customer?.name || '',
        name: role.name || '',
        description: role.description || '',
        enable: role.enable !== undefined ? !!role.enable : true,
        isDefault: !!role.isDefault,
        updated_at: role.updated_at || null
      }
    },

    validate () {
      let isValid = true
      this.errors = { name: '' }

      if (!this.formData.name.trim()) {
        this.errors.name = '角色名稱為必填項目'
        isValid = false
      }

      return isValid
    },

    async submitForm () {
      if (!this.validate()) return
      this.isSubmitting = true

      const submitData = {
        customer_id: this.formData.customerId,
        name: this.formData.name.trim(),
        description: this.formData.description.trim(),
        enable: this.formData.enable
      }
      this.$emit('submit', submitData)
    },

    close () {
      this.$emit('close')
    },

    formatDate (date) {
      if (!date) return '-'
      return new Date(date).toLocaleString('zh-TW', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
  }
}
</script>

<style scoped>
.fade-in-up {
  animation: fadeInUp 0.3s ease-out forwards;
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>
