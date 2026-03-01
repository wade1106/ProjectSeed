<template>
  <Teleport to="body">
    <div
      v-if="visible"
      class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
    >
      <div
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity fade-in"
        @click="closeModal"
      ></div>

      <div class="relative z-[10000] bg-white rounded-2xl shadow-2xl max-w-4xl w-full h-full max-h-[85vh] flex flex-col overflow-hidden scale-in-up">

        <div class="flex items-center justify-between p-6 border-b border-slate-100 bg-slate-50/50 shrink-0">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-indigo-50 rounded-lg">
              <i class="fa-solid fa-barcode text-indigo-600" />
            </div>
            <h3 class="text-lg font-bold text-slate-800 text-left">HS Code 前綴碼維護 - {{ productCategory?.name }}</h3>
          </div>
          <button
            type="button"
            class="p-2 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors"
            @click="closeModal"
            :disabled="loading"
          >
            <i class="fa-solid fa-times" />
          </button>
        </div>

        <div class="p-6 bg-gradient-to-r from-slate-50 to-indigo-50/30 border-b border-slate-100 shrink-0">
          <div class="grid grid-cols-2 gap-4">
            <div class="flex items-center gap-3 text-left">
              <div class="p-2 bg-white rounded-lg shadow-sm">
                <i class="fa-solid fa-box text-slate-600" />
              </div>
              <div>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">產品類別編號</p>
                <p class="font-bold text-slate-800">{{ productCategory?.code }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3 text-left">
              <div class="p-2 bg-white rounded-lg shadow-sm">
                <i class="fa-solid fa-circle text-emerald-500" />
              </div>
              <div>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">狀態</p>
                <p class="font-bold text-slate-800">{{ productCategory?.enable ? '啟用' : '停用' }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="flex-1 overflow-y-auto custom-scrollbar bg-slate-50/30">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4" v-if="!isInitialLoading">
              <h4 class="font-bold text-slate-800 flex items-center gap-2 text-left">
                HS Code 前綴碼清單
                <span class="px-2 py-0.5 bg-slate-200 text-slate-600 text-xs rounded-full">{{ prefixes.length }}</span>
              </h4>
              <div class="flex gap-2">
                <button
                  type="button"
                  class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-bold transition shadow-sm"
                  @click="addPrefix"
                  :disabled="loading"
                >
                  <i class="fa-solid fa-plus mr-1" /> 新增
                </button>
                <button
                  type="button"
                  class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold transition shadow-sm"
                  @click="addMultiple"
                  :disabled="loading"
                >
                  <i class="fa-solid fa-layer-group mr-1" /> 批量+5
                </button>
              </div>
            </div>

            <div v-if="isInitialLoading" class="flex flex-col items-center justify-center py-20">
              <div class="relative">
                <div class="w-16 h-16 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
                <i class="fa-solid fa-barcode absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-indigo-600/50"></i>
              </div>
              <p class="mt-4 text-slate-500 font-bold animate-pulse">正在取得伺服器資料...</p>
            </div>

            <div v-else class="space-y-3">
              <div
                v-for="(prefix, index) in prefixes"
                :key="prefix.id || `new-${index}`"
                class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:border-indigo-200 transition-colors"
              >
                <div class="flex items-center gap-4">
                  <div class="w-6 shrink-0 text-center">
                    <span class="text-sm font-mono text-slate-400 font-bold">{{ index + 1 }}.</span>
                  </div>

                  <div class="flex-1 grid grid-cols-12 gap-4 items-start">
                    <div class="col-span-3 text-left relative pb-5">
                      <label class="block text-xs font-bold text-slate-500 mb-1">HS 前綴碼</label>
                      <input
                        v-model="prefix.prefix"
                        type="text"
                        placeholder="例：85"
                        maxlength="2"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm font-mono"
                        :class="{ 'border-red-400 bg-red-50/30': prefix.errors?.prefix }"
                        @input="validatePrefix(index)"
                      >
                      <p v-if="prefix.errors?.prefix" class="absolute left-0 bottom-0 text-[11px] text-red-500 font-medium whitespace-nowrap">{{ prefix.errors.prefix }}</p>
                    </div>

                    <div class="col-span-8 text-left">
                      <label class="block text-xs font-bold text-slate-500 mb-1">描述說明</label>
                      <input
                        v-model="prefix.description"
                        type="text"
                        placeholder="輸入詳細描述..."
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm"
                      >
                    </div>

                    <div class="col-span-1 flex justify-end pt-4">
                      <button
                        type="button"
                        class="w-9 h-9 rounded-lg hover:bg-red-50 text-slate-300 hover:text-red-500 transition-colors flex items-center justify-center shrink-0"
                        @click="removePrefix(index)"
                        :disabled="loading"
                        title="移除"
                      >
                        <i class="fa-solid fa-trash-can text-sm" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="prefixes.length === 0" class="text-center py-12 bg-white border-2 border-dashed border-slate-200 rounded-2xl">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                  <i class="fa-solid fa-barcode text-slate-300 text-2xl" />
                </div>
                <p class="text-slate-500 font-medium">尚未設定任何 HS Code 前置詞</p>
                <p class="text-slate-400 text-xs mt-1">點擊上方按鈕開始新增資料</p>
              </div>
            </div>
          </div>
        </div>

        <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 shrink-0">
          <button
            type="button"
            class="px-6 py-2.5 border border-slate-200 text-slate-600 hover:bg-white hover:text-slate-800 rounded-lg font-bold transition active:scale-95"
            @click="closeModal"
            :disabled="loading"
          >
            取消
          </button>
          <button
            type="button"
            class="px-8 py-2.5 bg-slate-900 text-white rounded-lg font-bold transition shadow-lg hover:bg-slate-800 active:scale-95 disabled:opacity-50 flex items-center gap-2"
            @click="savePrefixes"
            :disabled="loading || !isValid || isInitialLoading"
          >
            <i v-if="loading" class="fa-solid fa-circle-notch animate-spin" />
            {{ loading ? '儲存中...' : '保存變更' }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script>
export default {
  name: 'HSPrefixModal',
  props: {
    visible: { type: Boolean, default: false },
    productCategory: { type: Object, default: () => ({}) }
  },
  emits: ['close', 'saved', 'notification'],
  data () {
    return {
      loading: false,
      isInitialLoading: false, // 專門給初次開啟彈窗時使用
      prefixes: [],
      originalPrefixes: []
    }
  },
  computed: {
    isValid () {
      // 1. 檢查清單是否為空：若空則無效，禁用按鈕
      if (this.prefixes.length === 0) return false

      // 2. 檢查所有項目的格式是否正確（必須有值且符合 2 碼數字正則）
      return this.prefixes.every(p => {
        const value = p.prefix ? p.prefix.trim() : ''
        return value !== '' && /^\d{2}$/.test(value)
      })
    },
    // 專業建議：增加一個判斷是否有變更的邏輯，避免無謂的 API 請求
    isDirty () {
      return JSON.stringify(this.prefixes) !== JSON.stringify(this.originalPrefixes)
    }
  },
  watch: {
    visible: {
      handler (newVal) {
        if (newVal) {
          this.loadPrefixes()
          document.body.style.overflow = 'hidden'
        } else {
          this.resetData()
          document.body.style.overflow = ''
        }
      },
      immediate: true
    }
  },
  methods: {
    async loadPrefixes () {
      if (!this.productCategory?.id) return

      this.isInitialLoading = true // 開始初次載入動畫
      this.loading = true

      try {
        const { productCategoryService } = await import('@/services/productCategoryService')
        const response = await productCategoryService.getHsPrefixes(this.productCategory.id)
        if (response.success) {
          this.prefixes = response.data.map(p => ({ ...p, errors: {} }))
          this.originalPrefixes = JSON.parse(JSON.stringify(response.data))
        }
      } catch (error) {
        console.error('Load error:', error)
        this.showNotification('載入資料失敗', 'error')
      } finally {
        this.loading = false
        this.isInitialLoading = false // 結束初次載入動畫
      }
    },
    addPrefix () {
      this.prefixes.unshift({ id: null, prefix: '', description: '', enable: true, errors: {} })
    },
    addMultiple () {
      for (let i = 0; i < 5; i++) {
        this.prefixes.push({ id: null, prefix: '', description: '', enable: true, errors: {} })
      }
    },
    removePrefix (index) {
      this.prefixes.splice(index, 1)
    },
    validatePrefix (index) {
      const prefix = this.prefixes[index]
      prefix.errors = {}
      if (!prefix.prefix) {
        prefix.errors.prefix = '必填'
      } else if (!/^\d{2}$/.test(prefix.prefix)) {
        prefix.errors.prefix = '需為2位數字'
      }
    },
    async savePrefixes () {
      if (!this.isValid) return
      this.loading = true
      const dataToSave = this.prefixes
        .filter(p => p.prefix.trim() !== '')
        .map(p => ({
          id: p.id,
          prefix: p.prefix.trim(),
          description: p.description.trim(),
          enable: p.enable
        }))
      try {
        const { productCategoryService } = await import('@/services/productCategoryService')
        const response = await productCategoryService.updateHsPrefixes(this.productCategory.id, dataToSave)
        if (response.success) {
          this.showNotification('HS Prefix 已儲存', 'success')
          this.$emit('saved', response.data)
        }
      } catch (error) {
        console.error('Save error:', error)
        this.showNotification('儲存失敗', 'error')
      } finally {
        this.loading = false
      }
    },
    closeModal () {
      if (this.loading) return
      this.$emit('close')
    },
    resetData () {
      this.prefixes = []
      this.originalPrefixes = []
      this.isInitialLoading = false
    },
    showNotification (message, type = 'info') {
      this.$emit('notification', message, type)
    }
  }
}
</script>

<style scoped>
.fade-in { animation: fadeIn 0.3s ease-out forwards; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.scale-in-up { animation: scaleInUp 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes scaleInUp {
  from { opacity: 0; transform: translateY(10px) scale(0.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>
