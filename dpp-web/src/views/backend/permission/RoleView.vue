<template>
  <div class="space-y-8 fade-in p-6 bg-slate-50/50 min-h-screen">
    <div class="flex justify-end items-center mb-6">
      <button
        type="button"
        class="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3 rounded-lg text-base font-bold flex items-center gap-2 transition shadow-lg active:scale-95"
        @click="openCreateModal"
      >
        <i class="fa-solid fa-shield-halved text-sm text-indigo-400" /> 新增角色
      </button>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm mb-8 text-left">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end">
        <div class="lg:col-span-4">
          <label class="block text-sm font-bold text-slate-700 mb-2">
            所屬公司 <span class="text-red-500">*</span>
          </label>
          <div class="flex gap-2">
            <div class="relative flex-1">
              <input
                v-model="filters.customerName"
                type="text"
                readonly
                placeholder="點擊右側圖示選擇客戶..."
                class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 outline-none cursor-default text-sm h-[46px]"
                :class="{ 'border-red-300 ring-4 ring-red-50': !filters.customerId && hasSearched }"
              >
              <button
                v-if="filters.customerId"
                type="button"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 transition-colors"
                @click="clearCustomerFilter"
              >
                <i class="fa-solid fa-circle-xmark" />
              </button>
            </div>
            <button
              type="button"
              class="px-4 h-[46px] bg-white border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition shadow-sm flex items-center justify-center"
              @click="openCompanySelector('filter')"
            >
              <i class="fa-solid fa-building text-indigo-500" />
            </button>
          </div>
        </div>

        <div class="lg:col-span-5">
          <label class="block text-sm font-bold text-slate-700 mb-2">關鍵字查詢</label>
          <div class="flex shadow-sm rounded-xl overflow-hidden border border-slate-200 focus-within:border-indigo-500 transition-all h-[46px]">
            <input
              v-model="filters.keyword"
              type="text"
              placeholder="搜尋角色名稱或描述..."
              class="flex-1 px-4 py-3 text-slate-700 placeholder-slate-400 focus:outline-none border-none text-sm h-full"
              @keyup.enter="handleSearch"
            >
          </div>
        </div>

        <div class="lg:col-span-2">
          <label class="block text-sm font-bold text-slate-700 mb-2">狀態</label>
          <select
            v-model="filters.enable"
            class="w-full px-4 h-[46px] rounded-xl border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all appearance-none bg-no-repeat bg-[right_1rem_center] text-sm"
            style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-size: 1.2em;"
          >
            <option :value="null">全部狀態</option>
            <option :value="1">啟用</option>
            <option :value="0">停用</option>
          </select>
        </div>

        <div class="lg:col-span-1">
          <button
            type="button"
            class="w-full h-[46px] bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition shadow-md flex items-center justify-center gap-2 active:scale-95"
            @click="handleSearch"
          >
            <i class="fa-solid fa-magnifying-glass" />
          </button>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider w-20">No.</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">角色名稱</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">描述</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-center">狀態</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-center">權限數量</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-right">操作</th>
            </tr>
          </thead>
          <tbody v-if="!loading" class="divide-y divide-slate-100 text-base">
            <template v-if="hasSearched">
              <tr v-for="(role, index) in roles" :key="role.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="px-6 py-6 text-slate-400 font-mono">{{ calculateNo(index) }}.</td>
                <td class="px-6 py-6 font-bold text-slate-800">{{ role.name }}</td>
                <td class="px-6 py-6 text-slate-500">{{ role.description || '-' }}</td>
                <td class="px-6 py-6 text-center">
                  <span
                    class="inline-flex items-center gap-2 font-bold px-3 py-1.5 rounded-full text-sm"
                    :class="role.enable ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50'"
                  >
                    <span class="w-2 h-2 rounded-full" :class="role.enable ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'" />
                    {{ role.enable ? '啟用' : '停用' }}
                  </span>
                </td>
                <td class="px-6 py-6 text-center">
                  <span class="px-2 py-1 bg-indigo-50 text-indigo-700 rounded text-sm font-medium border border-indigo-100">
                    {{ role.permissions?.length || 0 }} 權限
                  </span>
                </td>
                <td class="px-6 py-6 text-right">
                  <div class="flex justify-end gap-3">
                    <button type="button" class="action-btn" @click="openEditModal(role)" title="編輯">
                      <i class="fa-solid fa-pen-to-square" />
                    </button>
                    <button type="button" class="action-btn" @click="mapsToFunctionPermissions(role)" title="權限設定">
                      <i class="fa-solid fa-key" />
                    </button>
                    <button type="button" class="action-btn hover:text-red-600 hover:bg-red-50" @click="confirmDelete(role)" title="刪除">
                      <i class="fa-solid fa-trash" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="roles.length === 0">
                <td colspan="6" class="px-6 py-20 text-center text-slate-400">
                  <div class="flex flex-col items-center">
                    <i class="fa-solid fa-magnifying-glass-chart text-4xl mb-3 opacity-20" />
                    <p>目前沒有符合條件的角色資料</p>
                  </div>
                </td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="6" class="px-6 py-20 text-center text-slate-400">
                <div class="flex flex-col items-center">
                  <i class="fa-solid fa-filter text-4xl mb-3 opacity-20" />
                  <p>請輸入查詢條件並點擊搜尋按鈕開始查詢</p>
                </div>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="6" class="px-6 py-20 text-center text-indigo-600 font-bold">
                <i class="fa-solid fa-circle-notch animate-spin text-2xl mr-3" /> 資料讀取中...
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="hasSearched && pagination.total > 0" class="px-8 py-6 bg-white border-t border-slate-100 flex items-center justify-between">
        <p class="text-base text-slate-500 font-medium">
          顯示第 <span class="text-slate-900 font-bold">{{ pagination.from }}</span> 至 <span class="text-slate-900 font-bold">{{ pagination.to }}</span> 筆，共 <span class="text-slate-900 font-bold">{{ pagination.total }}</span> 筆
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

    <CompanySelectorModal
      :is-open="isSelectorOpen"
      @close="isSelectorOpen = false"
      @confirm="handleCompanySelected"
    />

    <RoleFormModal
      ref="roleFormModal"
      v-if="showRoleModal"
      :visible="showRoleModal"
      :role="currentRole"
      :is-editing="isEditing"
      @close="closeRoleModal"
      @submit="handleRoleSubmit"
      @open-selector="openCompanySelector('form')"
    />

    <ConfirmModal
      v-if="showDeleteModal"
      :show="showDeleteModal"
      title="確認刪除"
      :message="roleToDelete?.name"
      :loading="loading"
      @confirm="handleDelete"
      @cancel="closeDeleteModal"
    />

    <AppToast :message="toast.message" :type="toast.type" :show="toast.show" />
  </div>
</template>

<script>
import { roleService } from '@/services/roleService'
import CompanySelectorModal from '@/components/backend/CompanySelectorModal.vue'
import RoleFormModal from '@/components/backend/RoleFormModal.vue'
import ConfirmModal from '@/components/backend/ConfirmModal.vue'
import AppToast from '@/components/backend/AppToast.vue'

export default {
  name: 'RoleView',
  components: {
    CompanySelectorModal,
    RoleFormModal,
    ConfirmModal,
    AppToast
  },
  data () {
    return {
      loading: false,
      hasSearched: false,
      isSelectorOpen: false,
      selectorTarget: 'filter',
      showRoleModal: false,
      showDeleteModal: false,
      currentRole: null,
      roleToDelete: null,
      isEditing: false,
      roles: [],
      filters: {
        keyword: '',
        enable: null,
        customerId: '',
        customerName: ''
      },
      pagination: { currentPage: 1, lastPage: 1, perPage: 15, total: 0, from: 0, to: 0 },
      toast: { show: false, message: '', type: 'info' }
    }
  },
  created () {
    this.handleRouteParams()
  },
  computed: {
    visiblePages () {
      const pages = []
      for (let i = 1; i <= this.pagination.lastPage; i++) pages.push(i)
      return pages
    }
  },
  methods: {
    handleRouteParams () {
      // 處理從 FunctionView.vue 返回時帶來的參數
      const customerId = this.$route.query.customerId
      const customerName = this.$route.query.customerName
      const autoSearch = this.$route.query.search

      if (customerId && customerName) {
        // 設定公司資訊
        this.filters.customerId = customerId
        this.filters.customerName = customerName

        // 如果 URL 中有搜尋標記，自動執行搜尋
        if (autoSearch === 'true') {
          // 延遲一點時間讓畫面載入完成
          this.$nextTick(() => {
            this.handleSearch()
          })
        }

        // 清理 URL 參數，避免重複處理
        this.$router.replace({
          name: 'admin-role-permission',
          query: {}
        })
      }
    },
    showNotification (message, type = 'info') {
      this.toast.message = message
      this.toast.type = type
      this.toast.show = true
      setTimeout(() => { this.toast.show = false }, 3000)
    },
    async loadRoles () {
      if (this.loading) return
      if (!this.filters.customerId) {
        this.showNotification('請先選擇所屬公司', 'warning')
        return
      }
      this.loading = true
      this.hasSearched = true
      try {
        const params = {
          page: this.pagination.currentPage,
          per_page: this.pagination.perPage,
          keyword: this.filters.keyword ? this.filters.keyword.trim() : undefined,
          enable: this.filters.enable !== null ? this.filters.enable : undefined,
          customer_id: this.filters.customerId || undefined
        }
        const response = await roleService.getRoles(params)
        if (response.success) {
          this.roles = response.data
          // 更新分頁資訊
          this.pagination.currentPage = response.meta?.current_page || 1
          this.pagination.lastPage = response.meta?.last_page || 1
          this.pagination.total = response.meta?.total || 0
          this.pagination.from = response.meta?.from || 0
          this.pagination.to = response.meta?.to || 0
        }
      } finally {
        this.loading = false
      }
    },
    handleSearch () {
      if (!this.filters.customerId) {
        this.showNotification('請選擇所屬公司後再進行查詢', 'warning')
        return
      }
      if (this.pagination.currentPage === 1) {
        this.loadRoles()
      } else {
        this.pagination.currentPage = 1
      }
    },
    calculateNo (index) {
      return (this.pagination.currentPage - 1) * this.pagination.perPage + index + 1
    },
    goToPage (page) {
      if (page >= 1 && page <= this.pagination.lastPage) {
        this.pagination.currentPage = page
        this.loadRoles()
      }
    },
    openCompanySelector (target) {
      this.selectorTarget = target
      this.isSelectorOpen = true
    },
    handleCompanySelected (company) {
      if (this.selectorTarget === 'filter') {
        this.filters.customerId = company.id
        this.filters.customerName = company.name
      } else if (this.selectorTarget === 'form') {
        if (this.$refs.roleFormModal) {
          this.$refs.roleFormModal.setCustomer(company)
        }
      }
      this.isSelectorOpen = false
    },
    clearCustomerFilter () {
      this.filters.customerId = ''
      this.filters.customerName = ''
      this.hasSearched = false
      this.roles = []
    },
    openCreateModal () {
      this.currentRole = null
      this.isEditing = false
      this.showRoleModal = true
    },
    openEditModal (role) {
      this.currentRole = {
        ...role,
        customer_id: role.customer_id || this.filters.customerId,
        customer_name: role.customer?.name || role.customer_name || this.filters.customerName
      }
      this.isEditing = true
      this.showRoleModal = true
    },
    closeRoleModal () {
      this.showRoleModal = false
      this.currentRole = null
    },
    async handleRoleSubmit (submitData) {
      this.loading = true
      try {
        let response
        if (this.isEditing && this.currentRole?.id) {
          // 編輯模式：呼叫 updateRole
          response = await roleService.updateRole(this.currentRole.id, submitData)
        } else {
          // 新增模式：呼叫 createRole
          response = await roleService.createRole(submitData)
        }

        if (response.success) {
          this.showNotification(
            this.isEditing ? '角色更新成功' : '角色新增成功',
            'success'
          )
          this.closeRoleModal()
          // 操作成功後，重新讀取當前頁面資料以同步狀態
          await this.loadRoles()
        } else {
          // 處理後端回傳的業務邏輯錯誤
          this.showNotification(response.message || '操作失敗，請稍後再試', 'error')
        }
      } catch (error) {
        console.error('Role Submit Error:', error)
        // 取得 Laravel Validation Error (假設後端回傳 422)
        const errorMsg = error.response?.data?.message || '伺服器通訊異常'
        this.showNotification(errorMsg, 'error')
      } finally {
        this.loading = false
        // 確保 Modal 內的按鈕讀取狀態能被正確重置
        if (this.$refs.roleFormModal) {
          this.$refs.roleFormModal.isSubmitting = false
        }
      }
    },
    // 前往功能權限
    mapsToFunctionPermissions (role) {
      // 導航到權限頁面，帶入公司和角色功能權限資訊
      this.$router.push({
        name: 'admin-function-definition',
        query: {
          roleId: role.id,
          roleName: role.name,
          customerId: role.customer_id || this.filters.customerId,
          customerName: role.customer?.name || role.customer_name || this.filters.customerName
        }
      })
    },

    // 點擊列表刪除按鈕
    confirmDelete (role) {
      // 增加檢查：如果是預設角色，直接攔截並提示
      if (role.isDefault) {
        this.showNotification('「' + role.name + '」為系統預設角色，禁止刪除', 'warning')
        return
      }
      this.roleToDelete = role
      this.showDeleteModal = true
    },

    // 關閉刪除確認視窗
    closeDeleteModal () {
      this.showDeleteModal = false
      this.roleToDelete = null
    },
    // 正式執行刪除調用 API
    async handleDelete () {
      if (!this.roleToDelete) return

      this.loading = true
      try {
        const response = await roleService.deleteRole(this.roleToDelete.id)
        if (response.success) {
          this.showNotification('角色已成功刪除', 'success')
          this.closeDeleteModal()
          await this.loadRoles() // 刷新列表
        }
      } catch (error) {
        console.error('Delete Error:', error)
        const errorMsg = error.response?.data?.message || '刪除失敗，該角色可能尚有權限配置'
        this.showNotification(errorMsg, 'error')
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.action-btn { @apply w-10 h-10 rounded-xl border border-slate-200 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 flex items-center justify-center shadow-sm transition-all; }
.page-btn { @apply px-4 py-2 border border-slate-200 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition-all; }
.fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
:deep(table) { font-feature-settings: "tnum"; font-variant-numeric: tabular-nums; }
</style>
