<template>
  <Teleport to="body">
    <div
      v-if="visible"
      class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto"
    >
      <div
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity fade-in"
        @click="closeModal"
      ></div>

      <div
        class="relative z-[10000] bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] flex flex-col overflow-hidden m-4 scale-in-up"
      >
        <div class="flex items-center justify-between p-6 border-b border-slate-100 bg-slate-50/50">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-indigo-50 rounded-lg">
              <i class="fa-solid fa-box text-indigo-600" />
            </div>
            <h3 class="text-lg font-bold text-slate-800">
              {{ isEditing ? '編輯產品類別' : '新增產品類別' }}
            </h3>
          </div>
          <button
            type="button"
            class="p-2 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors"
            @click="closeModal"
            :disabled="loading"
          >
            <i class="fa-solid fa-xmark text-xl" />
          </button>
        </div>

        <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
          <form @submit.prevent="submitForm" class="space-y-6">
            <div class="text-left">
              <label class="block text-sm font-bold text-slate-700 mb-2">
                產品類別編號 <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.code"
                type="text"
                placeholder="P0001（若不填寫將自動產生）"
                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all"
                :class="{ 'border-red-300 bg-red-50/30': errors.code }"
                maxlength="50"
              >
              <p v-if="errors.code" class="mt-1 text-sm text-red-500 font-medium">{{ errors.code }}</p>
            </div>

            <div class="text-left">
              <label class="block text-sm font-bold text-slate-700 mb-2">
                產品類別名稱 <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                placeholder="請輸入產品名稱"
                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all"
                :class="{ 'border-red-300 bg-red-50/30': errors.name }"
                @input="clearError('name')"
                maxlength="50"
              >
              <p v-if="errors.name" class="mt-1 text-sm text-red-500 font-medium">{{ errors.name }}</p>
            </div>

            <div class="text-left">
              <label class="block text-sm font-bold text-slate-700 mb-2">
                路由地址 <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.path"
                type="text"
                placeholder="請輸入路由地址"
                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all"
                :class="{ 'border-red-300 bg-red-50/30': errors.path }"
                @input="clearError('path')"
                maxlength="50"
              >
              <p v-if="errors.name" class="mt-1 text-sm text-red-500 font-medium">{{ errors.name }}</p>
            </div>

            <div class="text-left">
              <label class="block text-sm font-bold text-slate-700 mb-2">產品類別描述</label>
              <textarea
                v-model="form.description"
                rows="4"
                placeholder="請輸入產品類別詳細描述..."
                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none resize-none transition-all"
                maxlength="1000"
              ></textarea>
              <div class="flex justify-end mt-1">
                <span class="text-xs text-slate-400 font-mono">{{ form.description.length }}/1000</span>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="text-left">
                <label class="block text-sm font-bold text-slate-700 mb-2">產品類別狀態</label>
                <div class="flex items-center gap-2 p-1 bg-slate-100 rounded-xl w-fit">
                  <button
                    type="button"
                    class="px-6 py-2 rounded-lg text-sm font-bold transition-all"
                    :class="form.enable ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                    @click="form.enable = true"
                  >
                    <i class="fa-solid fa-check mr-2" />啟用
                  </button>
                  <button
                    type="button"
                    class="px-6 py-2 rounded-lg text-sm font-bold transition-all"
                    :class="!form.enable ? 'bg-white text-red-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                    @click="form.enable = false"
                  >
                    <i class="fa-solid fa-xmark mr-2" />停用
                  </button>
                </div>
              </div>

              <div class="text-left">
                <label class="block text-sm font-bold text-slate-700 mb-2">排序權重</label>
                <input
                  v-model.number="form.idx"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all"
                >
                <p class="mt-1 text-[11px] text-slate-400 italic">數字越小在列表中越靠前</p>
              </div>
            </div>
          </form>
        </div>

        <div class="px-8 py-6 bg-slate-50/80 border-t border-slate-100 flex justify-end gap-3">
          <button
            type="button"
            class="px-6 py-2.5 border border-slate-200 text-slate-600 hover:text-slate-800 hover:bg-white rounded-lg font-bold transition-all active:scale-95"
            @click="closeModal"
            :disabled="loading"
          >
            取消
          </button>
          <button
            type="button"
            class="px-8 py-2.5 bg-slate-900 text-white rounded-lg font-bold transition-all shadow-lg hover:bg-slate-800 active:scale-95 disabled:opacity-50 flex items-center gap-2"
            @click="submitForm"
            :disabled="loading || !isValid"
          >
            <i v-if="loading" class="fa-solid fa-circle-notch animate-spin" />
            {{ loading ? '處理中...' : (isEditing ? '更新產品類別資料' : '確認新增產品類別') }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script>
export default {
  name: 'ProductCategoryFormModal',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    productCategory: {
      type: Object,
      default: () => null
    },
    isEditing: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'submit', 'notification'],
  data () {
    return {
      form: {
        code: '',
        name: '',
        path: '',
        description: '',
        enable: true,
        idx: 0
      },
      errors: {},
      originalForm: {}
    }
  },
  computed: {
    isValid () {
      const hasName = this.form.name && this.form.name.trim().length > 0
      const hasCode = this.form.code && this.form.code.trim().length > 0
      const hasPath = this.form.path && this.form.path.trim().length > 0
      return hasName && hasCode && hasPath
    }
  },
  watch: {
    visible: {
      handler (newVal) {
        if (newVal) {
          this.initializeForm()
          // 開啟彈窗時鎖定 body 捲動
          document.body.style.overflow = 'hidden'
        } else {
          this.resetData()
          // 關閉彈窗時恢復 body 捲動
          document.body.style.overflow = ''
        }
      },
      immediate: true
    }
  },
  // 組件銷毀前確保恢復捲動狀態
  beforeUnmount () {
    document.body.style.overflow = ''
  },
  methods: {
    initializeForm () {
      if (this.isEditing && this.productCategory) {
        this.form = {
          code: this.productCategory.code || '',
          name: this.productCategory.name || '',
          path: this.productCategory.path || '',
          description: this.productCategory.description || '',
          enable: this.productCategory.enable ?? true,
          idx: this.productCategory.idx ?? 0
        }
      } else {
        this.form = {
          code: '',
          name: '',
          path: '',
          description: '',
          enable: true,
          idx: 0
        }
      }
      this.originalForm = JSON.parse(JSON.stringify(this.form))
      this.errors = {}
    },
    resetData () {
      this.form = {
        code: '',
        name: '',
        path: '',
        description: '',
        enable: true,
        idx: 0
      }
      this.errors = {}
    },
    clearError (field) {
      if (this.errors[field]) {
        const newErrors = { ...this.errors }
        delete newErrors[field]
        this.errors = newErrors
      }
    },
    validateForm () {
      this.errors = {}
      let isValid = true

      // 產品類型編號驗證
      if (!this.form.code || !this.form.code.trim()) {
        this.errors.code = '產品類型編號為必填項目'
        isValid = false
      }

      // 產品類型名稱驗證
      if (!this.form.name || !this.form.name.trim()) {
        this.errors.name = '產品類型名稱為必填項目'
        isValid = false
      }

      // 產品類型路由地址驗證
      if (!this.form.path || !this.form.path.trim()) {
        this.errors.path = '產品類型路由地址為必填項目'
        isValid = false
      }

      return isValid
    },
    async submitForm () {
      if (!this.validateForm()) return

      const submitData = {
        ...this.form,
        name: this.form.name.trim(),
        code: this.form.code.trim(),
        path: this.form.path.trim(),
        description: this.form.description.trim()
      }

      // 4. 直接 emit 即可，狀態由父組件 handleProductCategorySubmit 控制
      this.$emit('submit', submitData)
    },
    closeModal () {
      if (this.loading) return
      this.$emit('close')
    },
    showNotification (message, type = 'info') {
      this.$emit('notification', message, type)
    }
  }
}
</script>

<style scoped>
/* 彈窗進入動畫：位移 + 縮放 */
.scale-in-up {
  animation: scaleInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes scaleInUp {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* 遮罩漸顯 */
.fade-in {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* 自定義捲軸 */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}

/* 強制表格數字等寬 */
:deep(table) {
  font-feature-settings: "tnum";
  font-variant-numeric: tabular-nums;
}
</style>
