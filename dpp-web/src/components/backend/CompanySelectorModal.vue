<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[10010] flex items-center justify-center p-4"
        @click="close">
        <div
          class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden relative flex flex-col"
          @click.stop
        >
          <div class="bg-slate-50 px-8 py-6 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">
              選擇公司單位
            </h2>
            <button
              type="button"
              class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200 transition flex items-center justify-center"
              @click="close"
            >
              <i class="fa-solid fa-xmark" />
            </button>
          </div>

          <div class="p-6 bg-white border-b border-slate-100">
            <div class="relative">
              <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="搜尋公司名稱、代碼或統編..."
                class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
              >
            </div>
          </div>

          <div class="flex-1 overflow-y-auto custom-scrollbar p-6 min-h-[300px]">
            <div
              v-if="isLoading"
              class="flex flex-col items-center justify-center py-20"
            >
              <i class="fa-solid fa-spinner spinning text-4xl text-indigo-600 mb-4" />
              <p class="text-slate-500">
                載入資料中...
              </p>
            </div>

            <div
              v-else-if="filteredCompanies.length === 0"
              class="text-center py-20"
            >
              <i class="fa-solid fa-folder-open text-4xl text-slate-300 mb-4" />
              <p class="text-slate-500">
                找不到符合的公司資料
              </p>
            </div>

            <table
              v-else
              class="w-full text-left border-collapse"
            >
              <thead class="sticky top-0 bg-white shadow-sm z-10">
                <tr class="text-slate-500 text-sm">
                  <th class="pb-4 px-4 w-12" />
                  <th class="pb-4 px-4 font-bold uppercase">
                    公司名稱
                  </th>
                  <th class="pb-4 px-4 font-bold uppercase">
                    公司代碼
                  </th>
                  <th class="pb-4 px-4 font-bold uppercase">
                    統一編號
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="company in filteredCompanies"
                  :key="company.id"
                  class="hover:bg-indigo-50/50 cursor-pointer transition-colors"
                  @click="handleRowClick(company.id)"
                >
                  <td class="py-4 px-4">
                    <input
                      type="checkbox"
                      :checked="selectedId === company.id"
                      class="custom-checkbox"
                      @change="handleRowClick(company.id)"
                    >
                  </td>
                  <td class="py-4 px-4 font-medium text-slate-700">
                    {{ company.name }}
                  </td>
                  <td class="py-4 px-4">
                    <span class="px-2 py-1 bg-slate-100 rounded text-xs font-mono text-slate-600">
                      {{ company.code }}
                    </span>
                  </td>
                  <td class="py-4 px-4 text-slate-600">
                    {{ company.taxId }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- 分頁控制 -->
          <div v-if="pagination.total > 0" class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
            <p class="text-sm text-slate-500 font-medium">
              顯示第 <span class="text-slate-900 font-bold">{{ pagination.from }}</span> 至 <span class="text-slate-900 font-bold">{{ pagination.to }}</span> 筆，共 <span class="text-slate-900 font-bold">{{ pagination.total }}</span> 筆
            </p>
            <div class="flex items-center gap-2">
              <button
                type="button"
                class="px-3 py-1 border border-slate-200 rounded text-sm font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition-all"
                :disabled="pagination.current_page <= 1"
                @click="goToPage(pagination.current_page - 1)"
              >
                上一頁
              </button>
              <div class="flex gap-1">
                <button
                  v-for="page in visiblePages.slice(0, 5)"
                  :key="page"
                  type="button"
                  class="w-8 h-8 rounded text-sm font-bold transition-all"
                  :class="page === pagination.current_page ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                  @click="goToPage(page)"
                >
                  {{ page }}
                </button>
                <span v-if="visiblePages.length > 5" class="text-slate-400 px-1">...</span>
              </div>
              <button
                type="button"
                class="px-3 py-1 border border-slate-200 rounded text-sm font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition-all"
                :disabled="pagination.current_page >= pagination.last_page"
                @click="goToPage(pagination.current_page + 1)"
              >
                下一頁
              </button>
            </div>
          </div>

          <div class="p-6 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">
            <button
              type="button"
              class="px-6 py-2.5 bg-white text-slate-700 border border-slate-300 rounded-lg font-bold hover:bg-slate-50 transition-all"
              @click="close"
            >
              取消
            </button>
            <button
              type="button"
              class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="!selectedId"
              @click="confirmSelection"
            >
              <i class="fa-solid fa-check" />
              確定選取
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
import { customerService } from '@/services/customerService.js'

export default {
  name: 'CompanySelectorModal',

  props: {
    isOpen: {
      type: Boolean,
      default: false
    }
  },

  emits: ['close', 'confirm'],

  data () {
    return {
      isLoading: false,
      searchQuery: '',
      selectedId: null,
      companies: [], // 存放 API 原始資料
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
        from: 0,
        to: 0
      },
      debounceTimer: null
    }
  },

  computed: {
    /**
     * 搜尋過濾邏輯：在客戶端過濾當前頁面的資料
     */
    filteredCompanies () {
      const query = this.searchQuery.trim().toLowerCase()
      if (!query) return this.companies

      return this.companies.filter(c => {
        return (
          c.name.toLowerCase().includes(query) ||
          c.code.toLowerCase().includes(query) ||
          (c.taxId && c.taxId.toString().includes(query))
        )
      })
    },

    visiblePages () {
      const pages = []
      for (let i = 1; i <= this.pagination.last_page; i++) {
        pages.push(i)
      }
      return pages
    }
  },

  watch: {
    /**
     * 當彈窗開啟時，執行初始化動作
     */
    isOpen (newVal) {
      if (newVal) {
        this.fetchCompanies()
        document.body.style.overflow = 'hidden'
      } else {
        this.resetState()
        document.body.style.overflow = ''
      }
    }
  },

  beforeUnmount () {
    document.body.style.overflow = ''
  },

  methods: {
    /**
     * 從 API 獲取客戶資料 (只獲取 active 狀態)
     */
    async fetchCompanies () {
      this.isLoading = true
      try {
        const params = {
          page: this.pagination.current_page,
          per_page: this.pagination.per_page,
          status: 'active' // 只獲取啟用的客戶
        }

        const result = await customerService.getCustomers(params)

        if (result.success) {
          // 更新本地資料結構以符合 API 響應格式
          this.companies = result.data.map(customer => ({
            id: customer.id,
            name: customer.name,
            code: customer.code,
            taxId: customer.tax_id || customer.taxId || ''
          }))

          // 更新分頁資訊
          if (result.pagination) {
            this.pagination = {
              current_page: result.pagination.current_page,
              last_page: result.pagination.last_page,
              per_page: result.pagination.per_page,
              total: result.pagination.total,
              from: (result.pagination.current_page - 1) * result.pagination.per_page + 1,
              to: Math.min(result.pagination.current_page * result.pagination.per_page, result.pagination.total)
            }
          }
        } else {
          this.companies = []
          console.error('Failed to fetch customers:', result.message)
        }
      } catch (error) {
        console.error('Fetch customers failed:', error)
        this.companies = []
      } finally {
        this.isLoading = false
      }
    },

    /**
     * 前往指定頁面
     */
    goToPage (page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.pagination.current_page = page
        this.fetchCompanies()
      }
    },

    /**
     * 處理單選邏輯
     */
    handleRowClick (id) {
      this.selectedId = id
    },

    /**
     * 確定選取並回傳指定欄位
     */
    confirmSelection () {
      const selectedItem = this.companies.find(c => c.id === this.selectedId)
      if (selectedItem) {
        this.$emit('confirm', {
          id: selectedItem.id,
          name: selectedItem.name,
          code: selectedItem.code
        })
        this.close()
      }
    },

    close () {
      this.$emit('close')
    },

    resetState () {
      this.searchQuery = ''
      this.selectedId = null
      this.pagination = {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
        from: 0,
        to: 0
      }
      this.companies = []
    }
  }
}
</script>

<style scoped>
/* 轉場動畫 */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
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

/* 旋轉動畫 */
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.spinning {
  animation: spin 1s linear infinite;
}

/* Checkbox 樣式優化：視覺上更像單選 */
.custom-checkbox {
  appearance: none;
  width: 20px;
  height: 20px;
  border: 2px solid #cbd5e1;
  border-radius: 50%;
  display: grid;
  place-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.custom-checkbox::before {
  content: "";
  width: 10px;
  height: 10px;
  border-radius: 50%;
  transform: scale(0);
  transition: 120ms transform ease-in-out;
  background-color: #4f46e5;
}

.custom-checkbox:checked {
  border-color: #4f46e5;
}

.custom-checkbox:checked::before {
  transform: scale(1);
}
</style>
