<template>
  <div class="p-6 bg-slate-50/50 min-h-screen font-inter text-left fade-in">
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center gap-4">
        <h2 class="text-2xl font-black text-slate-800 tracking-tight">帳號管理</h2>
        <div class="flex items-center gap-2 px-3 py-1.5 bg-indigo-50 border border-indigo-100 rounded-lg shadow-sm">
          <i class="fa-solid fa-building text-indigo-500 text-xs" />
          <span class="text-base font-bold text-indigo-700 uppercase tracking-wider">{{ customerName }}</span>
        </div>
      </div>
      <button
        type="button"
        @click="openFormModal()"
        class="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3 rounded-lg text-base font-bold flex items-center gap-2 transition shadow-lg active:scale-95"
      >
        <i class="fa-solid fa-user-plus text-sm text-indigo-400" /> 新增用戶
      </button>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm mb-8 text-left">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end">
        <div class="lg:col-span-8">
          <label class="block text-sm font-bold text-slate-700 mb-2">關鍵字查詢</label>
          <div class="flex shadow-sm rounded-xl overflow-hidden border border-slate-200 focus-within:border-indigo-500 transition-all h-[46px]">
            <input
              v-model="filters.keyword"
              type="text"
              placeholder="搜尋姓名、帳號或 Email..."
              class="flex-1 px-4 py-3 text-slate-700 placeholder-slate-400 focus:outline-none border-none text-sm h-full"
              @keyup.enter="handleSearch"
            />
          </div>
        </div>
        <div class="lg:col-span-3">
          <label class="block text-sm font-bold text-slate-700 mb-2">狀態</label>
          <select
            v-model="filters.enable"
            class="w-full px-4 h-[46px] rounded-xl border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all appearance-none bg-no-repeat bg-[right_1rem_center] text-sm"
          >
            <option :value="null">全部狀態</option>
            <option :value="1">啟用</option>
            <option :value="0">停用</option>
          </select>
        </div>
        <div class="lg:col-span-1">
          <button
            type="button"
            class="w-full h-[46px] bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition shadow-md flex items-center justify-center gap-2 active:scale-95 disabled:opacity-50"
            :disabled="loading"
            @click="handleSearch"
          >
            <i v-if="loading" class="fa-solid fa-circle-notch animate-spin" />
            <i v-else class="fa-solid fa-magnifying-glass" />
          </button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-12 gap-6 w-full items-start">
      <div class="col-span-12 xl:col-span-9 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden min-h-[600px] flex flex-col relative">
        <div class="flex-1 overflow-x-auto">
          <table class="w-full text-left border-collapse table-fixed">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr>
                <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider w-20">No.</th>
                <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider w-64">用戶名稱 / 帳號</th>
                <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">電子郵件 (Email)</th>
                <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider w-32 text-center">狀態</th>
                <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider w-32 text-right">操作</th>
              </tr>
            </thead>

            <tbody v-if="!loading" class="divide-y divide-slate-100 text-base relative">
              <template v-if="hasSearched && users.length > 0">
                <tr
                  v-for="(user, index) in users"
                  :key="user.id"
                  @click="selectUser(user)"
                  class="group hover:bg-slate-50/80 cursor-pointer transition-all"
                  :class="{ 'bg-indigo-50/80 ring-2 ring-inset ring-indigo-200': selectedUserForRole?.id === user.id }"
                >
                  <td class="px-6 py-6 text-slate-400 font-mono text-sm">{{ calculateNo(index) }}.</td>
                  <td class="px-6 py-6">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-indigo-600 font-bold shrink-0 shadow-sm uppercase">
                        {{ user.name.charAt(0) }}
                      </div>
                      <div class="truncate">
                        <div class="font-bold text-slate-800 text-lg">{{ user.name }}</div>
                        <div class="text-sm text-slate-500 font-mono tracking-tighter">{{ user.account }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-6 text-slate-600 font-medium font-mono truncate">{{ user.email }}</td>
                  <td class="px-6 py-6 text-center">
                    <span
                      class="inline-flex items-center gap-2 font-bold px-3 py-1.5 rounded-full text-sm whitespace-nowrap"
                      :class="user.enable ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50'"
                    >
                      <span class="w-2 h-2 rounded-full shrink-0" :class="user.enable ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'" />
                      {{ user.enable ? '啟用' : '停用' }}
                    </span>
                  </td>
                  <td class="px-6 py-6 text-right" @click.stop>
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
              </template>

              <tr v-else-if="hasSearched && users.length === 0">
                <td colspan="5" class="px-6 py-32 text-center text-slate-400">
                  <div class="flex flex-col items-center gap-4">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100 shadow-inner">
                      <i class="fa-solid fa-magnifying-glass text-3xl opacity-20" />
                    </div>
                    <p class="font-bold text-lg text-slate-600 tracking-wide">目前沒有符合條件的用戶資料</p>
                    <button type="button" @click="resetFilters" class="text-indigo-600 font-bold text-xs uppercase tracking-widest border-b border-indigo-200">重設搜尋條件</button>
                  </div>
                </td>
              </tr>

              <tr v-else>
                <td colspan="5" class="px-6 py-32 text-center text-slate-400">
                  <div class="flex flex-col items-center gap-2">
                    <i class="fa-solid fa-filter text-4xl opacity-10 mb-2" />
                    <p class="font-medium tracking-widest uppercase text-sm">輸入關鍵字並點擊搜尋按鈕開始查詢</p>
                  </div>
                </td>
              </tr>
            </tbody>

            <tbody v-else>
              <tr>
                <td colspan="5" class="px-6 py-32 text-center text-indigo-600 font-bold">
                  <div class="flex flex-col items-center gap-3">
                    <i class="fa-solid fa-circle-notch animate-spin text-4xl" />
                    <span class="font-bold tracking-widest text-sm uppercase">資料載入中...</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="hasSearched && pagination.total > 0 && !loading" class="px-8 py-6 bg-white border-t border-slate-100 flex items-center justify-between">
          <p class="text-sm text-slate-500 font-medium">
            顯示第 <span class="text-slate-900 font-bold">{{ pagination.from }}</span> 至 <span class="text-slate-900 font-bold">{{ pagination.to }}</span> 筆，共 <span class="text-slate-900 font-bold">{{ pagination.total }}</span> 筆
          </p>
          <div class="flex items-center gap-2">
            <button type="button" class="page-btn" :disabled="pagination.current_page <= 1" @click="goToPage(pagination.current_page - 1)">上一頁</button>
            <div class="flex gap-1">
              <button
                v-for="page in visiblePages" :key="page"
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

      <div class="col-span-12 xl:col-span-3 sticky top-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm h-[600px] flex flex-col overflow-hidden text-left">
          <div class="p-5 border-b border-slate-100 bg-slate-50/50 rounded-t-2xl">
            <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm tracking-widest uppercase">
              <i class="fa-solid fa-shield-halved text-indigo-600" /> 使用者角色
            </h3>
          </div>

          <Transition name="slide-fade" mode="out-in">
            <div v-if="selectedUserForRole" :key="selectedUserForRole.id" class="p-6 flex-1 flex flex-col min-h-0">
              <div class="mb-4 p-3 bg-indigo-600 rounded-xl text-white shadow-lg shadow-indigo-100 transition-all shrink-0">
                <p class="text-[9px] opacity-70 uppercase font-black tracking-widest mb-0.5">Current Selected</p>
                <p class="text-base font-bold leading-tight">{{ selectedUserForRole.name }}</p>
                <p class="text-[10px] font-mono opacity-60">{{ selectedUserForRole.account }}</p>
              </div>

              <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-4 shrink-0">Assigned Roles</p>

              <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar min-h-0">
                <div v-if="selectedUserForRole.roles && selectedUserForRole.roles.length > 0" class="space-y-3">
                  <div
                    v-for="role in selectedUserForRole.roles"
                    :key="role.id"
                    class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-100 bg-slate-50/50 hover:border-indigo-200 hover:bg-white transition-all group"
                  >
                    <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                      <i class="fa-solid fa-user-tag text-[10px]" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-bold text-slate-700 truncate">{{ role.name }}</p>
                      <p class="text-[10px] text-slate-400 mt-0.5 leading-tight">{{ role.description || '此角色尚無描述' }}</p>
                    </div>
                  </div>
                </div>

                <div v-else class="flex flex-col items-center justify-center py-12 px-4 border-2 border-dashed border-slate-100 rounded-2xl">
                  <i class="fa-solid fa-user-slash text-2xl text-slate-200 mb-3" />
                  <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">尚無指派角色</p>
                </div>
              </div>

              <div class="mt-auto pt-4 border-t border-slate-50">
                <p class="mt-auto pt-4 text-[14px] text-slate-600 font-bold text-center whitespace-nowrap shrink-0">
                  * 如需變更角色權限，請點擊編輯按鈕進行修改
                </p>
              </div>
            </div>

            <div v-else class="flex-1 flex flex-col items-center justify-center p-12 text-center text-slate-400">
              <i class="fa-solid fa-fingerprint text-4xl mb-6 opacity-20" />
              <p class="text-slate-400 text-[14px] font-bold uppercase tracking-widest whitespace-nowrap">請先點選左側列表檢視角色資訊</p>
            </div>
          </Transition>
        </div>
      </div>
    </div>

    <UserFormModal
      ref="userForm"
      :show="showModal"
      :user-data="selectedUser"
      :default-customer-id="customerId"
      :default-customer-name="customerName"
      @close="closeModal"
      @save="handleSaveUser"
    />
    <ConfirmModal :show="showConfirm" :message="userToDelete?.name" @confirm="handleDeleteConfirm" @cancel="closeConfirm" />
    <AppToast :show="toast.show" :message="toast.message" :type="toast.type" />
  </div>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { userService } from '@/services/userService.js'
import ConfirmModal from '@/components/frontend/ConfirmModal.vue'
import AppToast from '@/components/frontend/AppToast.vue'
import UserFormModal from '@/components/frontend/UserFormModal.vue'

export default {
  name: 'AccountView',
  components: { ConfirmModal, AppToast, UserFormModal },
  data () {
    return {
      loading: false,
      hasSearched: false,
      showModal: false,
      selectedUser: null,
      selectedUserForRole: null,
      filters: { keyword: '', enable: null, customer_id: '' },
      users: [],
      pagination: { current_page: 1, last_page: 1, per_page: 15, total: 0, from: 0, to: 0 },
      showConfirm: false,
      userToDelete: null,
      toast: { show: false, message: '', type: 'success' }
    }
  },
  computed: {
    ...mapState(useAuthStore, ['customerId', 'customerName']),
    visiblePages () {
      const pages = []
      const current = this.pagination.current_page
      const last = this.pagination.last_page
      let start = Math.max(1, current - 2)
      const end = Math.min(last, start + 4)
      if (end - start < 4) start = Math.max(1, end - 4)
      for (let i = start; i <= end; i++) { if (i >= 1 && i <= last) pages.push(i) }
      return pages
    }
  },
  mounted () {
    this.filters.customer_id = this.customerId
    this.fetchUsers()
  },
  methods: {
    calculateNo (index) {
      return (this.pagination.current_page - 1) * this.pagination.per_page + index + 1
    },
    async fetchUsers () {
      this.loading = true
      this.hasSearched = true
      // 換頁或重新讀取時重設右側選取狀態
      this.selectedUserForRole = null
      try {
        const res = await userService.getUsers({
          page: this.pagination.current_page,
          customer_id: this.filters.customer_id,
          keyword: this.filters.keyword,
          enable: this.filters.enable
        })
        if (res.success) {
          this.users = res.data
          this.pagination = {
            ...res.pagination,
            from: res.data.length ? (res.pagination.current_page - 1) * res.pagination.per_page + 1 : 0,
            to: (res.pagination.current_page - 1) * res.pagination.per_page + res.data.length
          }
        }
      } catch (error) {
        this.showNotification('載入用戶資料失敗', 'error')
      } finally {
        setTimeout(() => { this.loading = false }, 400)
      }
    },
    resetFilters () {
      this.filters.keyword = ''
      this.filters.enable = null
      this.handleSearch()
    },
    selectUser (user) {
      this.selectedUserForRole = user
    },
    handleSearch () {
      this.pagination.current_page = 1
      this.selectedUserForRole = null
      this.fetchUsers()
    },
    goToPage (page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.pagination.current_page = page
        this.fetchUsers()
      }
    },
    showNotification (message, type = 'success') {
      this.toast = { show: true, message, type }
      setTimeout(() => { this.toast.show = false }, 3000)
    },
    openFormModal (user = null) {
      this.selectedUser = user
      this.showModal = true
    },
    closeModal () {
      this.showModal = false
      this.selectedUser = null
    },
    async handleSaveUser (payload) {
      const isEdit = !!this.selectedUser
      try {
        const res = isEdit
          ? await userService.updateUser(this.selectedUser.id, payload)
          : await userService.createUser(payload)

        if (res.success) {
          this.showNotification(`用戶「${payload.name}」已成功${isEdit ? '更新' : '建立'}`)
          this.closeModal()
          this.fetchUsers()
        } else {
          this.showNotification(res.message || '儲存失敗', 'error')
        }
      } catch (error) {
        this.showNotification('系統連線失敗', 'error')
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
        await userService.deleteUser(this.userToDelete.id)
        this.showNotification('用戶已刪除')
        this.selectedUserForRole = null
        this.closeConfirm()
        this.fetchUsers()
      } catch (err) {
        this.showNotification('刪除失敗', 'error')
      }
    }
  }
}
</script>

<style scoped>
.font-inter { font-family: 'Inter', sans-serif; }
.action-btn { @apply w-10 h-10 rounded-xl border border-slate-200 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 flex items-center justify-center shadow-sm transition-all; }
.page-btn { @apply px-4 py-2 border border-slate-200 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition-all cursor-pointer; }

/* 進場動畫與過渡效果 */
.fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}
.slide-fade-leave-active {
  transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter-from {
  transform: translateX(10px);
  opacity: 0;
}
.slide-fade-leave-to {
  transform: translateX(-10px);
  opacity: 0;
}

select {
  -webkit-appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 1.2em;
}

:deep(table) { font-feature-settings: "tnum"; font-variant-numeric: tabular-nums; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
