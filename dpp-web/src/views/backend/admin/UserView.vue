<template>
  <div class="space-y-8 fade-in">
    <div class="flex justify-end mb-4">
      <button
        @click="openFormModal()"
        class="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3 rounded-lg text-base font-bold flex items-center gap-2 transition shadow-lg active:scale-95"
      >
        <i class="fa-solid fa-user-plus text-sm text-indigo-400"></i> 新增管理者
      </button>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm mb-8">
      <div class="flex flex-col md:flex-row gap-6 text-left"> <div class="flex-1">
          <label class="block text-sm font-bold text-slate-700 mb-2 text-left">關鍵字查詢</label>
          <div class="flex shadow-sm rounded-lg overflow-hidden border border-slate-200 focus-within:border-indigo-500 transition-all h-[46px]">
            <input
              v-model="searchKeyword"
              type="text"
              placeholder="搜尋姓名、電子郵件或帳號..."
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
            style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-size: 1.2em;"
            @change="handleFilterChange"
          >
            <option :value="null">全部狀態</option>
            <option :value="true">啟用</option>
            <option :value="false">停用</option>
          </select>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider w-20">No.</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">管理者資訊</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">登入帳號</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-center">狀態</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-center">類型</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">建立日期</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-right">操作</th>
            </tr>
          </thead>
          <tbody v-if="!loading" class="divide-y divide-slate-100 text-base">
            <tr v-for="(admin, index) in admins" :key="admin.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="px-6 py-6 text-slate-400 font-mono">
                {{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}.
              </td>
              <td class="px-6 py-6">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold border border-slate-200">
                    {{ admin.name.charAt(0) }}
                  </div>
                  <div>
                    <div class="font-bold text-slate-800 text-lg">{{ admin.name }}</div>
                    <div class="text-sm text-slate-500">{{ admin.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-6 font-mono text-slate-600">{{ admin.account }}</td>
              <td class="px-6 py-6 text-center">
                <span
                  class="inline-flex items-center gap-2 font-bold px-3 py-1.5 rounded-full text-sm"
                  :class="admin.enable ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50'"
                >
                  <span class="w-2 h-2 rounded-full" :class="admin.enable ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'"></span>
                  {{ admin.enable ? '啟用' : '停用' }}
                </span>
              </td>
              <td class="px-6 py-6 text-center">
                <span v-if="admin.isDefault" class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-md text-xs font-bold border border-indigo-100 uppercase">最高權限管理員</span>
                <span v-else class="text-slate-400 text-sm">一般管理員</span>
              </td>
              <td class="px-6 py-6 text-slate-500 font-mono">{{ formatDate(admin.created_at) }}</td>
              <td class="px-6 py-6 text-right">
                <div class="flex justify-end gap-3">
                  <button @click="openFormModal(admin)" class="w-10 h-10 rounded-xl border border-slate-200 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 flex items-center justify-center shadow-sm transition-all" title="編輯">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </button>
                  <button
                    v-if="!admin.isDefault && admin.id !== currentAdminId"
                    @click="confirmDelete(admin)"
                    class="w-10 h-10 rounded-xl border border-slate-200 text-slate-500 hover:text-red-600 hover:bg-red-50 flex items-center justify-center shadow-sm transition-all"
                    title="刪除"
                  >
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="admins.length === 0">
              <td colspan="7" class="px-6 py-20 text-center">
                <div class="flex flex-col items-center justify-center text-slate-400">
                  <i class="fa-solid fa-inbox text-5xl mb-4 opacity-20"></i>
                  <p class="text-lg">目前沒有符合條件的管理者資料</p>
                </div>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="7" class="px-6 py-20 text-center">
                <div class="flex justify-center items-center gap-3 text-indigo-600 font-bold">
                  <i class="fa-solid fa-circle-notch animate-spin text-2xl"></i>
                  <span>資料讀取中...</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="pagination.last_page >= 1" class="px-8 py-6 bg-white border-t border-slate-100 flex items-center justify-between">
        <p class="text-base text-slate-500 font-medium">
          顯示第 <span class="text-slate-900 font-bold">{{ pagination.from }}</span> 至 <span class="text-slate-900 font-bold">{{ pagination.to }}</span> 筆資料，共 <span class="text-slate-900 font-bold">{{ pagination.total }}</span> 筆
        </p>
        <div class="flex items-center gap-2">
          <button class="px-4 py-2 border border-slate-200 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-50" :disabled="pagination.current_page <= 1" @click="goToPage(pagination.current_page - 1)">上一頁</button>
          <div class="flex gap-1">
            <button v-for="page in visiblePages" :key="page" @click="goToPage(page)" class="w-10 h-10 rounded-lg text-sm font-bold transition-all" :class="page === pagination.current_page ? 'bg-slate-900 text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'">{{ page }}</button>
          </div>
          <button class="px-4 py-2 border border-slate-200 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-50" :disabled="pagination.current_page >= pagination.last_page" @click="goToPage(pagination.current_page + 1)">下一頁</button>
        </div>
      </div>
    </div>

    <AdminForm
      :show="showModal"
      :edit-admin="selectedAdmin"
      :current-admin-id="currentAdminId"
      @close="closeModal"
      @success="handleSuccess"
    />

    <ConfirmModal
      :show="showConfirm"
      :message="adminToDelete?.name"
      @confirm="handleDeleteConfirm"
      @cancel="closeConfirm"
    />

    <AppToast
      :show="toast.show"
      :message="toast.message"
      :type="toast.type"
    />
  </div>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { adminService } from '@/services/adminService'
import AdminForm from '@/components/backend/AdminForm.vue'
import ConfirmModal from '@/components/backend/ConfirmModal.vue'
import AppToast from '@/components/backend/AppToast.vue'

export default {
  name: 'AdminManagement',

  components: {
    AdminForm,
    ConfirmModal,
    AppToast
  },

  data () {
    return {
      loading: false,
      searchKeyword: '',
      filterStatus: null,
      admins: [],
      showModal: false,
      selectedAdmin: null,
      // 刪除相關
      showConfirm: false,
      adminToDelete: null,
      // 通知相關
      toast: {
        show: false,
        message: '',
        type: 'success'
      },
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
        from: 0,
        to: 0
      }
    }
  },

  computed: {
    ...mapState(useAuthStore, ['userInfo', 'userType']),
    visiblePages () {
      const pages = []
      for (let i = 1; i <= this.pagination.last_page; i++) {
        pages.push(i)
      }
      return pages
    },
    currentAdminId () {
      // 返回當前登入者的ID，不論是管理員還是一般使用者
      return this.userInfo?.uid || this.userInfo?.id || null
    }
  },

  mounted () {
    this.fetchAdmins()
  },

  methods: {
    // 顯示通知工具
    showNotification (message, type = 'success') {
      this.toast.message = message
      this.toast.type = type
      this.toast.show = true
      setTimeout(() => { this.toast.show = false }, 3000)
    },

    async fetchAdmins () {
      this.loading = true
      try {
        const params = {
          page: this.pagination.current_page,
          per_page: this.pagination.per_page,
          keyword: this.searchKeyword.trim() || undefined,
          enable: this.filterStatus !== null ? (this.filterStatus ? 1 : 0) : undefined
        }
        const result = await adminService.getAdmins(params)
        if (result.success) {
          this.admins = result.data.data || []
          const meta = result.data.pagination || result.data
          this.pagination = {
            ...this.pagination,
            current_page: meta.current_page || 1,
            last_page: meta.last_page || 1,
            per_page: meta.per_page || 15,
            total: meta.total || 0,
            from: meta.from || 0,
            to: meta.to || 0
          }
        }
      } catch (error) {
        this.admins = []
      } finally {
        this.loading = false
      }
    },

    handleSearch () {
      this.pagination.current_page = 1
      this.fetchAdmins()
    },

    handleFilterChange () {
      this.pagination.current_page = 1
      this.fetchAdmins()
    },

    goToPage (page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.pagination.current_page = page
        this.fetchAdmins()
      }
    },

    formatDate (dateString) {
      if (!dateString) return '-'
      return new Date(dateString).toLocaleDateString('zh-TW', { year: 'numeric', month: '2-digit', day: '2-digit' })
    },

    openFormModal (admin = null) {
      this.selectedAdmin = admin
      this.showModal = true
    },

    closeModal () {
      this.showModal = false
      this.selectedAdmin = null
    },

    // 新增/編輯成功處理
    handleSuccess (result) {
      const isEdit = !!this.selectedAdmin
      this.closeModal()
      this.fetchAdmins()

      // 修正：根據你提供的 API 格式，資料就在 result.data 裡面
      // 使用 Optional Chaining (?.) 確保萬無一失
      const adminName = result?.data?.name || '資料'

      this.showNotification(`管理者「${adminName}」已成功${isEdit ? '更新' : '新增'}`)
    },

    // 刪除邏輯
    confirmDelete (admin) {
      this.adminToDelete = admin
      this.showConfirm = true
    },

    closeConfirm () {
      this.showConfirm = false
      this.adminToDelete = null
    },

    async handleDeleteConfirm () {
      if (!this.adminToDelete) return
      try {
        const result = await adminService.deleteAdmin(this.adminToDelete.id)
        if (result.success) {
          this.fetchAdmins()
          this.showNotification(`管理者「${this.adminToDelete.name}」已成功刪除`)
        }
      } catch (error) {
        this.showNotification('刪除失敗，請稍後再試', 'error')
      } finally {
        this.closeConfirm()
      }
    }
  }
}
</script>

<style scoped>
.fade-in { animation: fadeIn 0.4s ease-out forwards; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
:deep(table) { font-feature-settings: "tnum"; font-variant-numeric: tabular-nums; }
</style>
