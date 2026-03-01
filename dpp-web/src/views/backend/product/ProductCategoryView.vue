<template>
  <div class="space-y-8 fade-in p-6 bg-slate-50/50 min-h-screen">
    <!-- Header -->
    <div class="flex justify-end items-center">
      <button
        type="button"
        class="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3 rounded-lg text-base font-bold flex items-center gap-2 transition shadow-lg active:scale-95"
        @click="openCreateModal">
        <i class="fa-solid fa-plus text-sm text-indigo-400" /> 新增產品類別
      </button>
    </div>

    <!-- Search Filters -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end">
            <div class="lg:col-span-4 text-left"> <label class="block text-sm font-bold text-slate-700 mb-2 text-left">關鍵字搜尋</label>
            <div class="flex shadow-sm rounded-xl overflow-hidden border border-slate-200 focus-within:border-indigo-500 transition-all h-[46px]">
                <input
                v-model="filters.keyword"
                type="text"
                placeholder="輸入產品類別名稱、編號或描述..."
                class="flex-1 px-4 py-3 text-slate-700 placeholder-slate-400 focus:outline-none border-none text-sm h-full"
                @keyup.enter="handleSearch"
                >
            </div>
            </div>

            <div class="lg:col-span-3 text-left"> <label class="block text-sm font-bold text-slate-700 mb-2 text-left">狀態</label>
            <select
                v-model="filters.enable"
                class="w-full px-4 h-[46px] rounded-xl border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all appearance-none bg-no-repeat bg-[right_1rem_center] text-sm"
                style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-size: 1.2em;"
            >
                <option :value="null">全部產品類別</option>
                <option :value="1">啟用產品類別</option>
                <option :value="0">停用產品類別</option>
            </select>
            </div>

            <div class="lg:col-span-3 flex items-end">
            <div class="flex gap-2 w-full">
                <button
                type="button"
                class="px-4 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-lg font-medium transition flex-1"
                @click="clearFilters"
                :disabled="loading"
                >
                <i class="fa-solid fa-eraser mr-2" />
                清除
                </button>
                <button
                type="button"
                class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition shadow-sm flex-1 active:scale-95"
                @click="handleSearch"
                :disabled="loading"
                >
                <i class="fa-solid fa-magnifying-glass mr-2" />
                搜尋
                </button>
            </div>
            </div>
        </div>
    </div>

    <!-- Data Count Summary -->
    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4" v-if="hasSearched">
      <div class="flex items-center gap-4 text-sm">
        <div class="flex items-center gap-2 text-indigo-700">
          <i class="fa-solid fa-box text-lg" />
          <span>總產品類別數:</span>
          <span class="font-bold">{{ pagination.total }}</span>
        </div>
        <div class="text-slate-400">顯示第 <span class="font-bold text-slate-600">{{ pagination.from }}</span> 至 <span class="font-bold text-slate-600">{{ pagination.to }}</span> 筆資料</div>
      </div>
    </div>

    <!-- Product Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider w-20">編號</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">產品類別資訊</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">產品類別編號</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">路由地址</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-center">狀態</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-center">HS Code前綴碼數量</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-right">操作</th>
            </tr>
          </thead>
          <tbody v-if="!loading" class="divide-y divide-slate-100 text-base">
            <template v-if="hasSearched">
              <tr v-for="(productCategory, index) in productCategories" :key="productCategory.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="px-6 py-6 text-slate-400 font-mono">{{ calculateNo(index) }}.</td>
                <td class="px-6 py-6">
                  <div class="font-bold text-slate-800 mb-1">{{ productCategory.name }}</div>
                  <div class="text-slate-500 text-sm max-w-md truncate">{{ productCategory.description || '-' }}</div>
                </td>
                <td class="px-6 py-6">
                  <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-sm font-mono">{{ productCategory.code }}</span>
                </td>
                <td class="px-6 py-6">
                  <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-sm font-mono">{{ productCategory.path }}</span>
                </td>
                <td class="px-6 py-6 text-center">
                  <span
                    class="inline-flex items-center gap-2 font-bold px-3 py-1.5 rounded-full text-sm"
                    :class="productCategory.enable ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50'"
                  >
                    <span class="w-2 h-2 rounded-full" :class="productCategory.enable ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'" />
                    {{ productCategory.enable ? '啟用' : '停用' }}
                  </span>
                </td>
                <td class="px-6 py-6 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <span class="px-2 py-1 bg-indigo-50 text-indigo-700 rounded text-sm font-medium border border-indigo-100"
                      v-if="productCategory.hs_prefixes.length" >
                      {{ productCategory.hs_prefixes.length }} 筆
                    </span>
                    <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-sm border border-slate-200"
                      v-else>
                      未設定
                    </span>
                  </div>
                </td>
                <td class="px-6 py-6 text-right">
                  <div class="flex justify-end gap-2">
                    <button
                      type="button"
                      class="action-btn bg-blue-50 hover:bg-blue-100 text-blue-600 hover:text-blue-700"
                      @click="editProductCategory(productCategory)"
                      title="編輯產品類別"
                    >
                      <i class="fa-solid fa-pen-to-square" />
                    </button>
                    <button
                      type="button"
                      class="action-btn bg-indigo-50 hover:bg-indigo-100 text-indigo-600 hover:text-indigo-700"
                      @click="editHSPrefixes(productCategory)"
                      title="HS Prefix設定"
                    >
                      <i class="fa-solid fa-barcode" />
                    </button>
                    <button
                      type="button"
                      class="action-btn bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-700"
                      @click="confirmDelete(productCategory)"
                      title="刪除產品類別"
                    >
                      <i class="fa-solid fa-trash" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="productCategories.length === 0">
                <td colspan="6" class="px-6 py-20 text-center text-slate-400">
                  <div class="flex flex-col items-center">
                    <i class="fa-solid fa-box-open text-4xl mb-3 opacity-20" />
                    <p>目前沒有符合條件的產品類別資料</p>
                  </div>
                </td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="6" class="px-6 py-20 text-center text-slate-400">
                <div class="flex flex-col items-center">
                  <i class="fa-solid fa-search text-4xl mb-3 opacity-20" />
                  <p>請設定搜尋條件並點擊搜尋按鈕開始查詢</p>
                </div>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="6" class="px-6 py-20 text-center text-indigo-600 font-bold">
                <i class="fa-solid fa-circle-notch animate-spin text-2xl mr-3" /> 載入產品類別資料中...
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="hasSearched && pagination.total > 0" class="px-8 py-6 bg-white border-t border-slate-100 flex items-center justify-between">
        <p class="text-base text-slate-500 font-medium">
          顯示第 <span class="text-slate-900 font-bold">{{ pagination.from }}</span> 至 <span class="text-slate-900 font-bold">{{ pagination.to }}</span> 筆，共 <span class="text-slate-900 font-bold">{{ pagination.total }}</span> 筆產品類別
        </p>
        <div class="flex items-center gap-2">
          <button type="button" class="page-btn" :disabled="pagination.currentPage <= 1" @click="goToPage(pagination.currentPage - 1)">上一頁</button>
          <div class="flex gap-1">
            <button
              v-for="page in visiblePages"
              :key="page"
              type="button"
              class="w-10 h-10 rounded-lg text-sm font-bold transition-all"
              :class="page === pagination.currentPage ? 'bg-slate-900 text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
              @click="goToPage(page)"
            >
              {{ page }}
            </button>
          </div>
          <button type="button" class="page-btn" :disabled="pagination.currentPage >= pagination.lastPage" @click="goToPage(pagination.currentPage + 1)">下一頁</button>
        </div>
      </div>
    </div>

    <!-- Product Category Form Modal -->
    <ProductCategoryFormModal
      v-if="showProductCategoryModal"
      :visible="showProductCategoryModal"
      :productCategory="currentProductCategory"
      :is-editing="isEditing"
      :loading="submitting"
      @close="closeProductCategoryModal"
      @submit="handleProductCategorySubmit"
      @notification="showNotification"
    />

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      v-if="showDeleteModal"
      :show="showDeleteModal"
      title="確認刪除"
      :message="deleteProductCategoryName"
      :loading="submitting"
      @confirm="handleDelete"
      @cancel="closeDeleteModal"
      @notification="showNotification"
    />

    <!-- HS Prefix Modal -->
    <HSPrefixModal
      v-if="showHSPrefixModal"
      :visible="showHSPrefixModal"
      :productCategory="selectedProductCategory"
      @close="closeHSPrefixModal"
      @saved="onHSPrefixesSaved"
      @notification="showNotification"
    />

    <AppToast :message="toast.message" :type="toast.type" :show="toast.show" />
  </div>
</template>

<script>
import { productCategoryService } from '@/services/productCategoryService'
import ProductCategoryFormModal from '@/components/backend/ProductCategoryFormModal.vue'
import HSPrefixModal from '@/components/backend/HSPrefixModal.vue'
import ConfirmModal from '@/components/backend/ConfirmModal.vue'
import AppToast from '@/components/backend/AppToast.vue'

export default {
  name: 'ProductCategoryView',
  components: {
    ProductCategoryFormModal,
    HSPrefixModal,
    ConfirmModal,
    AppToast
  },
  data () {
    return {
      loading: false,
      submitting: false, // 專門給 新增/修改/刪除 使用
      hasSearched: false,
      productCategories: [],
      filters: {
        keyword: '',
        enable: null
      },
      pagination: {
        currentPage: 1,
        lastPage: 1,
        perPage: 15,
        total: 0,
        from: 0,
        to: 0
      },
      showProductCategoryModal: false,
      showDeleteModal: false,
      showHSPrefixModal: false,
      currentProductCategory: null,
      selectedProductCategory: null,
      isEditing: false,
      productCategoryToDelete: null,
      toast: { show: false, message: '', type: 'info' }
    }
  },
  computed: {
    visiblePages () {
      const pages = []
      for (let i = 1; i <= this.pagination.lastPage; i++) {
        if (i === 1 || i === this.pagination.lastPage ||
            (i >= this.pagination.currentPage - 1 && i <= this.pagination.currentPage + 1)) {
          pages.push(i)
        } else if (i === this.pagination.currentPage - 2 || i === this.pagination.currentPage + 2) {
          pages.push('...')
        }
      }
      return pages
    },
    deleteProductCategoryName () {
      return this.productCategoryToDelete ? `${this.productCategoryToDelete.name} (${this.productCategoryToDelete.code})` : ''
    }
  },
  created () {
    // Auto search on initial load
    this.handleSearch()
  },
  methods: {
    showNotification (message, type = 'info') {
      this.toast.message = message
      this.toast.type = type
      this.toast.show = true
      setTimeout(() => { this.toast.show = false }, 3000)
    },

    async loadProductCategories () {
      if (this.loading) return

      this.loading = true
      this.hasSearched = true
      try {
        const params = {
          page: this.pagination.currentPage,
          per_page: this.pagination.perPage,
          keyword: this.filters.keyword ? this.filters.keyword.trim() : undefined,
          enable: this.filters.enable !== null ? this.filters.enable : undefined
        }

        const response = await productCategoryService.getProductCategories(params)
        if (response.success) {
          this.productCategories = response.data
          // Update pagination
          this.pagination.currentPage = response.meta?.current_page || 1
          this.pagination.lastPage = response.meta?.last_page || 1
          this.pagination.total = response.meta?.total || 0
          this.pagination.from = response.meta?.from || 0
          this.pagination.to = response.meta?.to || 0
        } else {
          this.showNotification(response.message || '載入資料失敗', 'error')
        }
      } catch (error) {
        console.error('Load product categories error:', error)
        this.showNotification('系統錯誤，請稍後再試', 'error')
      } finally {
        this.loading = false
      }
    },

    handleSearch () {
      this.pagination.currentPage = 1
      this.loadProductCategories()
    },

    clearFilters () {
      this.filters.keyword = ''
      this.filters.enable = null
      this.handleSearch()
    },

    calculateNo (index) {
      return (this.pagination.currentPage - 1) * this.pagination.perPage + index + 1
    },

    goToPage (page) {
      if (page >= 1 && page <= this.pagination.lastPage && page !== this.pagination.currentPage) {
        this.pagination.currentPage = page
        this.loadProductCategories()
      }
    },

    // Product Management - CRUD Operations
    openCreateModal () {
      this.currentProductCategory = null
      this.isEditing = false
      this.showProductCategoryModal = true
    },

    editProductCategory (productCategory) {
      this.currentProductCategory = { ...productCategory }
      this.isEditing = true
      this.showProductCategoryModal = true
    },

    closeProductCategoryModal () {
      this.showProductCategoryModal = false
      this.currentProductCategory = null
      this.isEditing = false
    },

    async handleProductCategorySubmit (productCategoryData) {
      this.submitting = true // 1. 開始提交，這會觸發 Modal 內的 loading 動畫
      try {
        let response
        if (this.isEditing && this.currentProductCategory?.id) {
          response = await productCategoryService.updateProductCategory(this.currentProductCategory.id, productCategoryData)
        } else {
          response = await productCategoryService.createProductCategory(productCategoryData)
        }

        if (response.success) {
          this.showNotification(this.isEditing ? '產品類別更新成功' : '產品類別新增成功', 'success')
          this.closeProductCategoryModal()
          await this.loadProductCategories()
        } else {
          this.showNotification(response.message || '操作失敗，請稍後再試', 'error')
        }
      } catch (error) {
        console.error('Product Category submit error:', error)
        const errorMsg = error.response?.data?.message || '系統錯誤，請稍後再試'
        this.showNotification(errorMsg, 'error')
      } finally {
        this.submitting = false // 2. 結束提交
      }
    },

    // HS Prefix Management
    editHSPrefixes (productCategory) {
      this.selectedProductCategory = productCategory
      this.showHSPrefixModal = true
    },

    closeHSPrefixModal () {
      this.showHSPrefixModal = false
      this.selectedProductCategory = null
    },

    async onHSPrefixesSaved (updatedData) {
      this.showNotification('HS Prefix 更新成功', 'success')
      // Reload current page to refresh data
      await this.loadProductCategories()
      this.closeHSPrefixModal()
    },

    // Delete Confirmation
    confirmDelete (productCategory) {
      this.productCategoryToDelete = productCategory
      this.showDeleteModal = true
    },

    closeDeleteModal () {
      this.showDeleteModal = false
      this.productCategoryToDelete = null
    },

    async handleDelete () {
      if (!this.productCategoryToDelete || this.submitting) return
      this.submitting = true // 改用提交狀態
      try {
        const response = await productCategoryService.deleteProductCategory(this.productCategoryToDelete.id)
        if (response.success) {
          this.showNotification('產品類別已成功刪除', 'success')

          // 1. 先關閉彈窗
          this.showDeleteModal = false

          // 2. 等待 Vue 完成 DOM 更新（彈窗消失後再抓資料）
          await this.$nextTick()
          // this.closeDeleteModal()
          // Reload current page
          // 3. 執行刷新
          await this.loadProductCategories()

          // 4. 最後才清理資料
          this.productCategoryToDelete = null
        } else {
          this.showNotification(response.message || '刪除失敗', 'error')
        }
      } catch (error) {
        console.error('Delete product error:', error)
        const errorMsg = error.response?.data?.message || '刪除失敗，該產品類別可能尚有關聯資料'
        this.showNotification(errorMsg, 'error')
      } finally {
        this.submitting = false // 結束提交狀態
      }
    }
  }
}
</script>

<style scoped>
.action-btn {
  @apply w-9 h-9 rounded-xl border border-slate-200 text-slate-500 hover:scale-105 flex items-center justify-center shadow-sm transition-all duration-200;
}

.action-btn:hover {
  @apply shadow-md border-slate-300;
}

.page-btn {
  @apply px-4 py-2 border border-slate-200 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition-all;
}

.fade-in {
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); }
}

:deep(table) {
  font-feature-settings: "tnum";
  font-variant-numeric: tabular-nums;
}
</style>
