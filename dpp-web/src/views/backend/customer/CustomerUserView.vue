<template>
  <div class="space-y-8 fade-in p-6 bg-slate-50/50 min-h-screen">
    <div class="flex justify-end items-center mb-6">
      <button
        type="button"
        class="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3 rounded-lg text-base font-bold flex items-center gap-2 transition shadow-lg active:scale-95"
        @click="openFormModal()"
      >
        <i class="fa-solid fa-user-plus text-sm text-indigo-400" /> 新增用戶
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
                v-model="filters.customer_name"
                type="text"
                readonly
                placeholder="點擊右側圖示選擇客戶..."
                class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 outline-none cursor-default text-sm h-[46px]"
                :class="{ 'border-red-300 ring-4 ring-red-50': !filters.customer_id && hasSearched }"
              >
              <button
                v-if="filters.customer_id"
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
              placeholder="搜尋姓名、帳號或 e-Mail..."
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
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">用戶名稱</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">登入帳號</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">所屬客戶</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-center">狀態</th>
              <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-right">操作</th>
            </tr>
          </thead>

          <tbody v-if="!loading" class="divide-y divide-slate-100 text-base">
            <template v-if="hasSearched">
              <tr v-for="(user, index) in users" :key="user.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="px-6 py-6 text-slate-400 font-mono">{{ calculateNo(index) }}.</td>
                <td class="px-6 py-6">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-100 overflow-hidden border border-slate-200 flex items-center justify-center">
                      <img v-if="user.avatar" :src="user.avatar" class="w-full h-full object-cover">
                      <i v-else class="fa-solid fa-user text-slate-400" />
                    </div>
                    <div>
                      <div class="font-bold text-slate-800 text-lg">{{ user.name }}</div>
                      <div class="text-sm text-slate-500">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-6 font-mono text-slate-600">{{ user.account }}</td>
                <td class="px-6 py-6">
                  <span class="px-2 py-1 bg-indigo-50 text-indigo-700 rounded text-sm font-medium border border-indigo-100">
                    {{ user.customer_name }}
                  </span>
                </td>
                <td class="px-6 py-6 text-center">
                  <span
                    class="inline-flex items-center gap-2 font-bold px-3 py-1.5 rounded-full text-sm"
                    :class="user.enable ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50'"
                  >
                    <span class="w-2 h-2 rounded-full" :class="user.enable ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'" />
                    {{ user.enable ? '啟用' : '停用' }}
                  </span>
                </td>
                <td class="px-6 py-6 text-right">
                  <div class="flex justify-end gap-3">
                    <button type="button" class="action-btn" @click="openFormModal(user)" title="編輯">
                      <i class="fa-solid fa-pen-to-square" />
                    </button>
                    <button type="button" class="action-btn hover:text-red-600 hover:bg-red-50" @click="confirmDelete(user)" title="刪除">
                      <i class="fa-solid fa-trash" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="users.length === 0">
                <td colspan="6" class="px-6 py-20 text-center text-slate-400">
                  <div class="flex flex-col items-center">
                    <i class="fa-solid fa-magnifying-glass-chart text-4xl mb-3 opacity-20" />
                    <p>目前沒有符合條件的用戶資料</p>
                  </div>
                </td>
              </tr>
            </template>

            <tr v-else>
              <td colspan="6" class="px-6 py-20 text-center text-slate-400">
                <div class="flex flex-col items-center">
                  <i class="fa-solid fa-filter text-4xl mb-3 opacity-20" />
                  <p>請選擇所屬客戶並點擊搜尋按鈕開始查詢</p>
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
          <button type="button" class="page-btn" :disabled="pagination.current_page <= 1" @click="goToPage(pagination.current_page - 1)">上一頁</button>
          <div class="flex gap-1">
            <button
              v-for="page in visiblePages"
              :key="page"
              type="button"
              class="w-10 h-10 rounded-lg text-sm font-bold transition-all"
              :class="page === pagination.current_page ? 'bg-slate-900 text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
              @click="goToPage(page)"
            >
              {{ page }}
            </button>
          </div>
          <button type="button" class="page-btn" :disabled="pagination.current_page >= pagination.last_page" @click="goToPage(pagination.current_page + 1)">下一頁</button>
        </div>
      </div>
    </div>

    <CompanySelectorModal
      :is-open="isSelectorOpen"
      @close="isSelectorOpen = false"
      @confirm="handleCompanySelected"
    />

    <UserFormModal
      ref="userForm"
      :show="showModal"
      :userData="selectedUser"
      @close="closeModal"
      @save="handleSaveUser"
      @open-selector="openCompanySelector('form')"
    />

    <ConfirmModal
      :show="showConfirm"
      :message="userToDelete?.name"
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
import { userService } from '@/services/userService.js'
import CompanySelectorModal from '@/components/backend/CompanySelectorModal.vue'
import ConfirmModal from '@/components/backend/ConfirmModal.vue'
import AppToast from '@/components/backend/AppToast.vue'
import UserFormModal from '@/components/backend/UserFormModal.vue'

export default {
  name: 'UserManagement',

  components: {
    CompanySelectorModal,
    ConfirmModal,
    AppToast,
    UserFormModal
  },

  data () {
    return {
      loading: false,
      isSelectorOpen: false,
      selectorTarget: 'form',
      showModal: false,
      selectedUser: null,
      hasSearched: false, // 追蹤是否已執行過查詢
      filters: {
        keyword: '',
        enable: null,
        customer_id: '',
        customer_name: ''
      },
      users: [],
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
        from: 0,
        to: 0
      },
      showConfirm: false,
      userToDelete: null,
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
      for (let i = 1; i <= this.pagination.last_page; i++) {
        pages.push(i)
      }
      return pages
    }
  },

  methods: {
    showNotification (message, type = 'success') {
      this.toast.message = message
      this.toast.type = type
      this.toast.show = true
      setTimeout(() => { this.toast.show = false }, 3000)
    },

    async fetchUsers () {
      if (this.loading) return

      // 核心修正：判斷公司 ID 必填
      if (!this.filters.customer_id) {
        this.showNotification('請選擇所屬公司後再進行查詢', 'warning')
        return
      }

      this.loading = true
      this.hasSearched = true

      try {
        const params = {
          page: this.pagination.current_page,
          per_page: this.pagination.per_page,
          keyword: this.filters.keyword.trim() || undefined,
          enable: this.filters.enable,
          customer_id: this.filters.customer_id
        }

        const result = await userService.getUsers(params)

        if (result.success) {
          this.users = result.data.map(user => ({
            id: user.id,
            customer_id: user.customer_id,
            customer_name: user.customer?.name || '未關聯單位',
            name: user.name,
            email: user.email,
            account: user.account,
            enable: user.enable ? 1 : 0,
            avatar: user.avatar,
            roles: user.roles || []
          }))

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
          this.showNotification(result.message || '載入用戶資料失敗', 'error')
          this.users = []
        }
      } catch (error) {
        this.showNotification('系統連線失敗', 'error')
        this.users = []
      } finally {
        this.loading = false
      }
    },

    handleSearch () {
      // 如果不在第一頁，改頁碼會觸發 watch (如果有)；若無 watch 則手動重置並呼叫
      if (this.pagination.current_page !== 1) {
        this.pagination.current_page = 1
      }
      this.fetchUsers()
    },

    openCompanySelector (target) {
      this.selectorTarget = target
      this.isSelectorOpen = true
    },

    handleCompanySelected (company) {
      if (this.selectorTarget === 'filter') {
        this.filters.customer_id = company.id
        this.filters.customer_name = company.name
      } else {
        this.$refs.userForm.setCustomer(company)
      }
      this.isSelectorOpen = false
    },

    clearCustomerFilter () {
      this.filters.customer_id = ''
      this.filters.customer_name = ''
      this.hasSearched = false // 清除時回到初始引導畫面
      this.users = []
    },

    calculateNo (index) {
      return (this.pagination.current_page - 1) * this.pagination.per_page + index + 1
    },

    goToPage (page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.pagination.current_page = page
        this.fetchUsers()
      }
    },

    openFormModal (user = null) {
      this.selectedUser = user
      this.showModal = true
    },

    closeModal () {
      this.showModal = false
      this.selectedUser = null
    },

    async handleSaveUser (formData) {
      const isEdit = !!this.selectedUser
      try {
        const action = isEdit
          ? userService.updateUser(this.selectedUser.id, formData)
          : userService.createUser(formData)

        const result = await action
        if (result.success) {
          this.showNotification(`用戶「${formData.name}」已成功${isEdit ? '更新' : '新增'}`)
          this.closeModal()
          this.fetchUsers()
        } else {
          this.showNotification(result.message || '儲存失敗', 'error')
        }
      } catch (error) {
        this.showNotification('伺服器錯誤，儲存失敗', 'error')
      }
    },

    confirmDelete (user) {
      this.userToDelete = user
      this.showConfirm = true
    },

    closeConfirm () {
      this.showConfirm = false
      this.userToDelete = null
    },

    async handleDeleteConfirm () {
      if (!this.userToDelete) return
      try {
        const result = await userService.deleteUser(this.userToDelete.id)
        if (result.success) {
          this.showNotification('用戶已成功刪除')
          this.closeConfirm()
          this.fetchUsers()
        } else {
          this.showNotification(result.message || '刪除失敗', 'error')
        }
      } catch (error) {
        this.showNotification('系統錯誤，無法刪除', 'error')
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
