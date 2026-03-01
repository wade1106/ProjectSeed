<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[9999] flex items-center justify-center p-4"
        @click="close"
      >
        <div
          class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden relative"
          @click.stop
        >
          <div class="bg-slate-50 px-8 py-6 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">
              {{ modalTitle }}
            </h2>
            <button
              class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200 transition flex items-center justify-center"
              @click="close"
            >
              <i class="fa-solid fa-xmark" />
            </button>
          </div>

          <div
            v-if="isLoading"
            class="flex items-center justify-center h-96"
          >
            <div class="text-center">
              <i class="fa-solid fa-spinner spinning text-4xl text-indigo-600 mb-4" />
              <p class="text-slate-600">
                載入客戶資料中...
              </p>
            </div>
          </div>

          <form
            v-else
            class="p-8 max-h-[70vh] overflow-y-auto custom-scrollbar space-y-6 pb-24"
            @submit.prevent="handleSubmit"
          >
            <div class="bg-slate-50 p-6 rounded-xl border border-slate-200">
              <h3 class="text-lg font-bold text-slate-800 mb-4">
                公司基本資訊
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-2">公司名稱 *</label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                    placeholder="請輸入公司名稱"
                  >
                  <p
                    v-if="errors.name"
                    class="text-red-500 text-sm mt-1"
                  >
                    {{ errors.name }}
                  </p>
                </div>
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-2">統一編號</label>
                  <input
                    v-model="form.taxId"
                    type="text"
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                    placeholder="請輸入統一編號"
                  >
                </div>
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-2">公司代碼</label>
                  <input
                    v-model="form.code"
                    type="text"
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                    placeholder="請輸入公司代碼"
                  >
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-semibold text-slate-700 mb-2">公司地址</label>
                  <input
                    v-model="form.address"
                    type="text"
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                    placeholder="請輸入公司完整地址"
                  >
                </div>
              </div>
            </div>

            <div class="bg-slate-50 p-6 rounded-xl border border-slate-200">
              <h3 class="text-lg font-bold text-slate-800 mb-4">
                主要聯絡人資訊
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-2">聯絡人姓名</label>
                  <input
                    v-model="form.contact"
                    type="text"
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                    placeholder="請輸入主要聯絡人姓名"
                  >
                </div>
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-2">聯絡人電話</label>
                  <input
                    v-model="form.tel"
                    type="tel"
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                    placeholder="請輸入聯絡電話"
                  >
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-semibold text-slate-700 mb-2">聯絡人Email</label>
                  <input
                    v-model="form.email"
                    type="email"
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                    placeholder="請輸入聯絡人Email"
                  >
                </div>
              </div>
            </div>

            <div class="bg-slate-50 p-6 rounded-xl border border-slate-200">
              <h3 class="text-lg font-bold text-slate-800 mb-4">
                合約授權資訊
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-2">合約開始日期</label>
                  <input
                    v-model="form.start_date"
                    type="date"
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                  >
                </div>
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-2">合約結束日期</label>
                  <input
                    v-model="form.end_date"
                    type="date"
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                  >
                </div>
                <div class="flex items-center">
                  <input
                    v-model="form.enable"
                    type="checkbox"
                    class="w-4 h-4 text-indigo-600 rounded focus:ring-2 focus:ring-indigo-500/20 border-slate-300"
                  >
                  <label class="ml-2 text-sm font-semibold text-slate-700">啟用此客戶</label>
                </div>
              </div>
            </div>

            <div class="bg-slate-50 p-6 rounded-xl border border-slate-200">
              <h3 class="text-lg font-bold text-slate-800 mb-4">
                備註
              </h3>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">客戶備註</label>
                <textarea
                  v-model="form.notes"
                  rows="3"
                  placeholder="請輸入客戶備註..."
                  class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all resize-none"
                />
              </div>
            </div>

            <div
              v-if="errorMessage"
              class="bg-red-50 border border-red-200 rounded-lg p-4"
            >
              <div class="flex items-center gap-2 text-red-800">
                <i class="fa-solid fa-triangle-exclamation" />
                <p class="font-medium">
                  {{ errorMessage }}
                </p>
              </div>
            </div>

            <div class="absolute bottom-0 left-0 right-0 p-6 bg-white border-t border-slate-100 flex justify-end gap-4">
              <button
                type="button"
                class="px-8 py-3 bg-white text-slate-700 border border-slate-300 rounded-lg font-bold hover:bg-slate-50 transition-all"
                :disabled="isSubmitting"
                @click="close"
              >
                取消
              </button>
              <button
                type="submit"
                class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold transition-all flex items-center gap-2"
                :disabled="isSubmitting"
              >
                <i
                  v-if="!isSubmitting"
                  class="fa-solid fa-check"
                />
                <i
                  v-else
                  class="fa-solid fa-spinner spinning"
                />
                {{ submitButtonText }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
import { customerService } from '@/services/customerService.js'

export default {
  name: 'CustomerModal',

  props: {
    isOpen: {
      type: Boolean,
      default: false
    },
    mode: {
      type: String,
      default: 'add',
      validator: (value) => ['add', 'edit'].includes(value)
    },
    customer: {
      type: Object,
      default: () => ({})
    }
  },

  emits: ['close', 'saved'],

  data () {
    return {
      form: {
        id: '',
        name: '',
        code: '',
        taxId: '',
        contact: '',
        email: '',
        tel: '',
        address: '',
        start_date: '',
        end_date: '',
        enable: true,
        notes: ''
      },
      errors: {},
      isSubmitting: false,
      errorMessage: '',
      isLoading: false
    }
  },

  computed: {
    modalTitle () {
      return this.mode === 'add' ? '新增客戶公司' : '編輯客戶公司'
    },
    submitButtonText () {
      return this.mode === 'add'
        ? (this.isSubmitting ? '儲存中...' : '新增客戶')
        : (this.isSubmitting ? '更新中...' : '更新客戶')
    },
    isEditMode () {
      return this.mode === 'edit'
    }
  },

  watch: {
    isOpen (newVal) {
      if (newVal) {
        this.initForm()
        // 當 Modal 開啟時防止背景捲動
        document.body.style.overflow = 'hidden'
      } else {
        // 當 Modal 關閉時恢復背景捲動
        document.body.style.overflow = ''
      }
    },

    mode () {
      if (this.isOpen) {
        this.initForm()
      }
    },

    customer: {
      immediate: true,
      handler (newVal) {
        if (this.isOpen && this.isEditMode && newVal.id) {
          this.loadCustomerData()
        }
      }
    }
  },

  beforeUnmount () {
    // 確保組件銷毀時恢復 body 狀態
    document.body.style.overflow = ''
  },

  methods: {
    close () {
      this.$emit('close')
      this.resetForm()
    },

    initForm () {
      if (this.isEditMode && this.customer.id) {
        this.loadCustomerData()
      } else {
        this.resetForm()
      }
    },

    async loadCustomerData () {
      if (!this.customer.id) return

      this.isLoading = true
      try {
        const result = await customerService.getCustomerById(this.customer.id)

        if (result.success) {
          this.populateForm(result.data)
        } else {
          this.errorMessage = result.message || '載入客戶資料失敗'
        }
      } catch (error) {
        this.errorMessage = '載入客戶資料失敗'
        console.error('Load customer error:', error)
      } finally {
        this.isLoading = false
      }
    },

    populateForm (customerData) {
      this.form = {
        id: customerData.id || '',
        name: customerData.name || '',
        code: customerData.code || '',
        taxId: customerData.taxId || '',
        contact: customerData.contact || '',
        email: customerData.email || '',
        tel: customerData.tel || '',
        address: customerData.address || '',
        start_date: this.formatDate(customerData.start_date),
        end_date: this.formatDate(customerData.end_date),
        enable: !!customerData.enable,
        notes: customerData.notes || ''
      }
      this.errors = {}
      this.errorMessage = ''
    },

    formatDate (dateString) {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toISOString().split('T')[0]
    },

    async handleSubmit () {
      this.isSubmitting = true
      this.errorMessage = ''
      this.errors = {}

      try {
        let result
        if (this.isEditMode) {
          result = await customerService.updateCustomer(this.form.id, this.form)
        } else {
          result = await customerService.createCustomer(this.form)
        }

        if (result.success) {
          this.$emit('saved', result.data)
          this.close()
          this.$toast?.success(this.mode === 'add' ? '客戶新增成功' : '客戶更新成功')
        } else {
          this.errorMessage = result.message || (this.mode === 'add' ? '新增失敗，請稍後再試' : '更新失敗，請稍後再試')
          if (result.data) {
            this.errors = result.data
          }
        }
      } catch (error) {
        this.errorMessage = this.isEditMode ? '更新失敗，請檢查網路連線' : '新增失敗，請檢查網路連線'
        console.error(`${this.mode} customer error:`, error)
      } finally {
        this.isSubmitting = false
      }
    },

    resetForm () {
      this.form = {
        id: '',
        name: '',
        code: '',
        taxId: '',
        contact: '',
        email: '',
        tel: '',
        address: '',
        start_date: '',
        end_date: '',
        enable: true,
        notes: ''
      }
      this.errors = {}
      this.errorMessage = ''
      this.isLoading = false
    }
  }
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.spinning {
  animation: spin 1s linear infinite;
}
</style>
