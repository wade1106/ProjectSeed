<template>
  <div class="space-y-8 fade-in">
    <div class="flex justify-end mb-8">
      <button
        @click="handleAddCustomer"
        class="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3 rounded-lg text-base font-bold flex items-center gap-2 transition shadow-lg active:scale-95"
      >
        <i class="fa-solid fa-plus text-sm text-indigo-400"></i> 新增客戶公司
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
        <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">有效合約公司</p>
        <p class="text-4xl font-bold text-slate-900">{{ stats.activeCustomers }} <span class="text-lg text-emerald-500 ml-2 font-medium">↑ 4</span></p>
      </div>
      <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
        <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">待續約客戶</p>
        <p class="text-4xl font-bold text-slate-900">{{ stats.pendingCustomers }}</p>
      </div>
      <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
        <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">本月新增</p>
        <p class="text-4xl font-bold text-indigo-600">{{ stats.newThisMonth }}</p>
      </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm mb-8">
      <div class="flex flex-col md:flex-row gap-6 text-left"> <div class="flex-1">
          <label class="block text-sm font-bold text-slate-700 mb-2 text-left">關鍵字查詢</label>
          <div class="flex shadow-sm rounded-lg overflow-hidden border border-slate-200 focus-within:border-indigo-500 transition-all h-[46px]">
            <input
              v-model="searchKeyword"
              type="text"
              placeholder="搜尋公司名稱、統一編號或聯絡人..."
              class="flex-1 px-4 py-3 text-slate-700 placeholder-slate-400 focus:outline-none border-none text-sm h-full"
              @keyup.enter="handleSearch"
            >
            <button
              @click="handleSearch"
              class="px-6 py-3 bg-slate-50 border-l border-slate-200 text-slate-600 font-bold hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
            >
              <i class="fa-solid fa-magnifying-glass"></i>
            </button>
          </div>
        </div>

        <div class="w-full md:w-64">
          <label class="block text-sm font-bold text-slate-700 mb-2 text-left">狀態</label>
          <select
            v-model="filterStatus"
            class="w-full px-4 h-[46px] rounded-lg border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all appearance-none bg-no-repeat bg-[right_1rem_center] text-sm"
            style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%20%3D%20%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-size: 1.2em;"
            @change="handleFilterChange"
          >
            <option value="all">全部狀態</option>
            <option value="active">有效授權中</option>
            <option value="disable">已停用</option>
            <option value="expired">已過期</option>
          </select>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <table class="w-full text-left">
        <thead class="bg-slate-50 border-b border-slate-200">
          <tr>
            <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase">No.</th>
            <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase">公司資訊 / 統編</th>
            <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase">主要聯絡人</th>
            <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase">授權類別</th>
            <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase">合約狀態</th>
            <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase text-right">操作</th>
          </tr>
        </thead>
        <tbody v-if="!loading" class="divide-y divide-slate-100 text-base">
          <tr
            v-for="(customer, index) in customers"
            :key="customer.id"
            class="hover:bg-slate-50/80 transition-colors"
          >
            <td class="px-6 py-6 text-slate-400 font-mono text-base">
              {{ (currentPage - 1) * perPage + index + 1 }}.
            </td>
            <td class="px-6 py-6">
              <div class="font-bold text-slate-800 text-lg">{{ customer.name }}</div>
              <div class="text-base font-mono text-slate-500 mt-1 uppercase">TAX_ID: {{ customer.taxId }}</div>
            </td>
            <td class="px-6 py-6">
              <div class="text-slate-700 font-semibold text-base">{{ customer.contactName }}</div>
              <div class="text-base text-slate-500 mt-1">{{ customer.contactEmail }}</div>
            </td>
            <td class="px-6 py-6">
              <span
                class="px-3 py-1 rounded-md text-xs font-bold border uppercase"
                :class="getLicenseClass(customer.licenseType)"
              >
                {{ customer.licenseType }}
              </span>
            </td>
            <td class="px-6 py-6">
              <span
                class="inline-flex items-center gap-2 font-bold px-3 py-1.5 rounded-full text-base"
                :class="getStatusClass(customer.contractStatus)"
              >
                <span
                  class="w-2.5 h-2.5 rounded-full"
                  :class="getStatusDotClass(customer.contractStatus)"
                ></span>
                {{ getStatusText(customer.contractStatus) }}
              </span>
            </td>
            <td class="px-6 py-6 text-right">
              <div class="flex justify-end gap-3">
                <button
                  @click="handleEdit(customer)"
                  class="w-10 h-10 rounded-xl border border-slate-200 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-all flex items-center justify-center shadow-sm"
                  title="編輯"
                >
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <button
                  @click="handleDelete(customer)"
                  class="w-10 h-10 rounded-xl border border-slate-200 text-slate-500 hover:text-red-600 hover:bg-red-50 transition-all flex items-center justify-center shadow-sm"
                  title="刪除"
                >
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
          <!-- 空數據提示 -->
          <tr v-if="!loading && customers.length === 0">
            <td colspan="6" class="px-6 py-20 text-center text-slate-400">
              <i class="fa-solid fa-folder-open text-5xl mb-4 opacity-20"></i>
              <p class="text-lg font-medium">找不到相關公司資料</p>
            </td>
          </tr>
        </tbody>
        <!-- 加載狀態顯示 -->
        <tbody v-else-if="loading">
          <tr>
            <td colspan="6" class="px-6 py-20 text-center">
              <div class="flex justify-center items-center gap-3 text-indigo-600 font-bold">
                <i class="fa-solid fa-circle-notch animate-spin text-2xl"></i>
                <span>資料讀取中...</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- 分頁元件 -->
      <div v-if="!loading && customers.length > 0" class="px-8 py-5 bg-white border-t border-slate-100 flex items-center justify-between">
        <p class="text-base text-slate-500 font-medium">
          顯示第 <span class="text-slate-900 font-bold">{{ (currentPage - 1) * perPage + 1 }}</span> 至 <span class="text-slate-900 font-bold">{{ Math.min(currentPage * perPage, totalItems) }}</span> 筆資料，共 <span class="text-slate-900 font-bold">{{ totalItems }}</span> 筆
        </p>
        <div class="flex items-center gap-2">
          <button
            @click="changePage(currentPage - 1)"
            class="px-4 py-2 border border-slate-200 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition disabled:opacity-40"
            :disabled="currentPage === 1"
          >
            上一頁
          </button>
          <div class="flex gap-1">
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="changePage(page)"
              class="w-10 h-10 rounded-lg text-sm font-bold transition"
              :class="{ 'bg-slate-900 text-white': currentPage === page, 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50': currentPage !== page }"
            >
              {{ page }}
            </button>
          </div>
          <button
            @click="changePage(currentPage + 1)"
            class="px-4 py-2 border border-slate-200 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition disabled:opacity-40"
            :disabled="currentPage === totalPages"
          >
            下一頁
          </button>
        </div>
      </div>
    </div>
  <CustomerModal
    :is-open="isModalOpen"
    :mode="modalMode"
    :customer="selectedCustomer"
    @close="closeModal"
    @saved="handleCustomerSaved"
  />

  <!-- 刪除確認對話框 -->
  <ConfirmModal
    :show="showConfirm"
    :message="customerToDelete?.name"
    @confirm="handleDeleteConfirm"
    @cancel="closeConfirm"
  />

  <!-- 提示訊息 -->
  <AppToast
    :show="toast.show"
    :message="toast.message"
    :type="toast.type"
  />
  </div>
</template>

<script>
import CustomerModal from '@/components/backend/CustomerModal.vue'
import { customerService } from '@/services/customerService.js'
import ConfirmModal from '@/components/backend/ConfirmModal.vue'
import AppToast from '@/components/backend/AppToast.vue'

export default {
  name: 'CustomerView',

  components: {
    CustomerModal,
    ConfirmModal,
    AppToast
  },

  data () {
    return {
      searchKeyword: '',
      filterStatus: 'all',
      currentPage: 1,
      totalPages: 1,
      perPage: 10,
      totalItems: 0,
      loading: false,
      stats: {
        activeCustomers: 0,
        pendingCustomers: 0,
        newThisMonth: 0
      },
      customers: [],
      isModalOpen: false,
      modalMode: 'add', // 'add' or 'edit'
      selectedCustomer: {},
      // 刪除相關
      showConfirm: false,
      customerToDelete: null,
      // 通知相關
      toast: {
        show: false,
        message: '',
        type: 'success'
      }
    }
  },

  computed: {
    visiblePages () {
      const pages = []
      const start = Math.max(1, this.currentPage - 2)
      const end = Math.min(this.totalPages, this.currentPage + 2)

      for (let i = start; i <= end; i++) {
        pages.push(i)
      }

      return pages
    }
  },

  mounted () {
    this.loadCustomers()
    this.loadStats()
  },

  methods: {

    // 顯示通知工具
    showNotification (message, type = 'success') {
      this.toast.message = message
      this.toast.type = type
      this.toast.show = true
      setTimeout(() => { this.toast.show = false }, 3000)
    },
    async loadCustomers (resetPage = false) {
      if (resetPage) {
        this.currentPage = 1
      }

      this.loading = true
      try {
        const params = {
          page: this.currentPage,
          per_page: this.perPage,
          status: this.filterStatus,
          keyword: this.searchKeyword
        }

        const result = await customerService.getCustomers(params)

        if (result.success) {
          // Transform backend data to frontend format
          this.customers = result.data.map(customer => ({
            id: customer.id,
            name: customer.name,
            taxId: customer.taxId || '',
            contactName: customer.contact || '',
            contactEmail: customer.email || '',
            licenseType: this.getLicenseType(customer.start_date, customer.end_date),
            contractStatus: this.getContractStatus(customer.start_date, customer.end_date, customer.enable)
          }))

          // Update pagination info if available
          if (result.pagination) {
            this.totalItems = result.pagination.total
            this.totalPages = result.pagination.last_page
            this.currentPage = result.pagination.current_page
          } else {
            this.totalItems = this.customers.length
            this.totalPages = Math.ceil(this.customers.length / this.perPage)
          }
        } else {
          this.showNotification('載入客戶資料失敗', 'error')
          this.customers = []
        }
      } catch (error) {
        this.showNotification('載入客戶資料失敗', 'error')
        console.error('Load customers error', error)
      } finally {
        this.loading = false
      }
    },

    async loadStats () {
      try {
        // Load active customers
        const activeResult = await customerService.getCustomers({ status: 'active', per_page: 1 })
        const disableResult = await customerService.getCustomers({ status: 'disable', per_page: 1 })

        // Calculate new this month data
        const startDate = new Date()
        startDate.setDate(1)
        const newCustomersResult = await customerService.getCustomers({
          start_date_after: startDate.toISOString().split('T')[0],
          per_page: 1
        })

        if (activeResult.success && disableResult.success) {
          this.stats.activeCustomers = activeResult.data.length
          this.stats.pendingCustomers = disableResult.data.length
          this.stats.newThisMonth = newCustomersResult.success ? newCustomersResult.data.length : 0
        }
      } catch (error) {
        console.error('Load stats error:', error)
        // Use default values if stats loading fails
        this.stats = {
          activeCustomers: 128,
          pendingCustomers: 12,
          newThisMonth: 7
        }
      }
    },

    async handleSearch () {
      await this.loadCustomers(true)
    },

    async handleFilterChange () {
      await this.loadCustomers(true)
    },

    async changePage (page) {
      if (page < 1 || page > this.totalPages) return
      this.currentPage = page
      await this.loadCustomers()
    },

    getLicenseType (startDate, endDate) {
      // Mock license type based on contract dates
      if (!startDate || !endDate) return 'Basic'

      const start = new Date(startDate)
      const end = new Date(endDate)
      const duration = end - start
      const yearDuration = duration / (1000 * 60 * 60 * 24 * 365)

      if (yearDuration >= 2) return 'Enterprise'
      return 'Cloud Service'
    },

    getContractStatus (startDate, endDate, enable) {
      const now = new Date()

      if (!startDate || !endDate) return 'unknown'

      const start = new Date(startDate)
      const end = new Date(endDate)

      if (now >= end) return 'expired'
      if (!enable) return 'disable'
      if (now >= start && now <= end) return 'active'

      return 'unknown'
    },

    getLicenseClass (licenseType) {
      switch (licenseType) {
        case 'Cloud Service':
          return 'bg-indigo-50 text-indigo-600 border-indigo-100'
        case 'Enterprise':
          return 'bg-emerald-50 text-emerald-600 border-emerald-100'
        case 'Basic':
          return 'bg-slate-50 text-slate-600 border-slate-200'
        default:
          return 'bg-slate-50 text-slate-600 border-slate-200'
      }
    },

    getStatusClass (status) {
      switch (status) {
        case 'active':
          return 'text-emerald-600 bg-emerald-50'
        case 'disable':
          return 'text-orange-600 bg-orange-50'
        case 'expired':
          return 'text-red-600 bg-red-50'
        default:
          return 'text-slate-600 bg-slate-50'
      }
    },

    getStatusDotClass (status) {
      switch (status) {
        case 'active':
          return 'bg-emerald-500 animate-pulse'
        case 'disable':
          return 'bg-orange-500'
        case 'expired':
          return 'bg-red-500'
        default:
          return 'bg-slate-500'
      }
    },

    getStatusText (status) {
      switch (status) {
        case 'active':
          return '有效授權中'
        case 'disable':
          return '已停用'
        case 'expired':
          return '已過期'
        default:
          return '未知'
      }
    },

    handleAddCustomer () {
      this.modalMode = 'add'
      this.selectedCustomer = {}
      this.isModalOpen = true
    },

    handleEdit (customer) {
      this.modalMode = 'edit'
      this.selectedCustomer = customer
      this.isModalOpen = true
    },

    closeModal () {
      this.isModalOpen = false
      this.selectedCustomer = {}
    },

    handleCustomerSaved (savedCustomer) {
      // Reload customers and stats after add/update
      this.loadCustomers()
      this.loadStats()
      this.closeModal()

      // 顯示成功訊息
      const action = this.modalMode === 'add' ? '新增' : '更新'
      if (savedCustomer && savedCustomer.name) {
        this.showNotification(`客戶「${savedCustomer.name}」已成功${action}`)
      } else {
        this.showNotification(`客戶已成功${action}`)
      }
    },

    confirmDelete (customer) {
      this.customerToDelete = customer
      this.showConfirm = true
    },

    closeConfirm () {
      this.showConfirm = false
      this.customerToDelete = null
    },

    async handleDeleteConfirm () {
      if (!this.customerToDelete || !this.customerToDelete.id) return

      // 先保存客戶名稱，因為 closeConfirm() 會清空它
      const customerName = this.customerToDelete.name

      try {
        const result = await customerService.deleteCustomer(this.customerToDelete.id)

        if (result.success) {
          // 立即關閉確認對話框
          this.closeConfirm()
          // 顯示成功訊息
          this.showNotification(`客戶「${customerName}」已成功刪除`)
          // 重新載入資料
          await this.loadCustomers()
          await this.loadStats()
        } else {
          // 先關閉對話框再顯示錯誤
          this.closeConfirm()
          this.showNotification(result.message || '刪除失敗', 'error')
        }
      } catch (error) {
        // 關閉對話框再顯示錯誤
        this.closeConfirm()
        this.showNotification('刪除失敗，請稍後再試', 'error')
        console.error('Delete customer error:', error)
      }
    },

    async handleDelete (customer) {
      this.confirmDelete(customer)
    },

    handleCustomerUpdate (updatedCustomer) {
      // Reload customers and stats after add/update
      this.loadCustomers()
      this.loadStats()
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.fade-in {
  animation: fadeIn 0.4s ease-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* 確保表格中的序號與文字對齊 */
:deep(table) {
  font-feature-settings: "tnum";
  font-variant-numeric: tabular-nums;
}
</style>
