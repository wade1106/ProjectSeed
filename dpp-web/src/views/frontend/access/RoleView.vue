<template>
  <div class="p-6 bg-slate-50/50 min-h-screen font-inter text-left fade-in">
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center gap-4">
        <h2 class="text-2xl font-black text-slate-800 tracking-tight">角色管理</h2>
        <div class="flex items-center gap-2 px-3 py-1.5 bg-indigo-50 border border-indigo-100 rounded-lg shadow-sm">
          <i class="fa-solid fa-building text-indigo-500 text-xs" />
          <span class="text-base font-bold text-indigo-700 uppercase tracking-wider">{{ customerName }}</span>
        </div>
      </div>
      <button
        type="button"
        class="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3 rounded-lg text-base font-bold flex items-center gap-2 transition shadow-lg active:scale-95"
        @click="openCreateModal"
      >
        <i class="fa-solid fa-shield-halved text-sm text-indigo-400" /> 新增角色
      </button>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm mb-8">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end">
        <div class="lg:col-span-9 text-left">
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
        <div class="lg:col-span-2 text-left">
          <label class="block text-sm font-bold text-slate-700 mb-2">狀態</label>
          <select
            v-model="filters.enable"
            class="w-full px-4 h-[46px] rounded-xl border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none appearance-none bg-no-repeat bg-[right_1rem_center] text-sm"
          >
            <option :value="null">全部狀態</option>
            <option :value="1">啟用</option>
            <option :value="0">停用</option>
          </select>
        </div>
        <div class="lg:col-span-1">
          <button
            type="button"
            class="w-full h-[46px] bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition shadow-md flex items-center justify-center active:scale-95 disabled:opacity-50"
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
      <div class="col-span-12 xl:col-span-7 flex flex-col min-h-[500px] bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="flex-1 overflow-x-auto">
          <table class="w-full text-left border-collapse table-fixed">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr>
                <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase w-16">No.</th>
                <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase w-40">角色名稱</th>
                <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase">描述</th>
                <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase w-36 text-center">狀態</th>
                <th class="px-6 py-5 text-sm font-bold text-slate-500 uppercase w-52 text-right">操作</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-base relative">
              <tr v-if="loading">
                <td colspan="5" class="px-6 py-32 text-center text-indigo-600">
                  <div class="flex flex-col items-center gap-3">
                    <i class="fa-solid fa-circle-notch animate-spin text-4xl" />
                    <span class="font-bold tracking-widest text-sm uppercase">資料載入中...</span>
                  </div>
                </td>
              </tr>

              <template v-else-if="hasSearched && roles.length > 0">
                <tr
                  v-for="(role, index) in roles"
                  :key="role.id"
                  class="group hover:bg-slate-50/80 cursor-pointer transition-all"
                  :class="{ 'bg-indigo-50 ring-1 ring-inset ring-indigo-200': selectedRoleForDetail?.id === role.id }"
                  @click="previewRolePermissions(role)"
                >
                  <td class="px-6 py-6 text-slate-400 font-mono text-sm">{{ calculateNo(index) }}.</td>
                  <td class="px-6 py-6 font-bold text-slate-800 truncate">{{ role.name }}</td>
                  <td class="px-6 py-6 text-slate-500 truncate text-sm">{{ role.description || '-' }}</td>
                  <td class="px-6 py-6 text-center">
                    <span
                      class="inline-flex items-center gap-2 font-bold px-3 py-1.5 rounded-full text-sm whitespace-nowrap"
                      :class="role.enable ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50'"
                    >
                      <span class="w-2 h-2 rounded-full shrink-0" :class="role.enable ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'" />
                      {{ role.enable ? '啟用' : '停用' }}
                    </span>
                  </td>
                  <td class="px-6 py-6 text-right" @click.stop>
                    <div class="flex justify-end items-center gap-2">
                      <button type="button" class="action-btn" title="編輯" @click="openEditModal(role)"><i class="fa-solid fa-pen-to-square" /></button>
                      <button type="button" class="action-btn" title="權限設定" v-if="authStore.hasPermission('permission', 'create') || authStore.hasPermission('permission', 'edit')" @click="mapsToPermissions(role)"><i class="fa-solid fa-key" /></button>
                      <!--button type="button" class="action-btn hover:text-amber-600 hover:bg-amber-50" title="資料權限設定" v-if="authStore.hasPermission('permission', 'create') || authStore.hasPermission('permission', 'edit')" @click="mapsToDataPermissions(role)"><i class="fa-solid fa-file-shield" /></button-->
                      <button type="button" class="action-btn hover:text-red-600 hover:bg-red-50" title="刪除" @click="confirmDelete(role)"><i class="fa-solid fa-trash" /></button>
                    </div>
                  </td>
                </tr>
              </template>

              <tr v-else-if="hasSearched && roles.length === 0">
                <td colspan="5" class="px-6 py-32 text-center text-slate-400">
                  <div class="flex flex-col items-center gap-4">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100 shadow-inner">
                      <i class="fa-solid fa-magnifying-glass text-3xl opacity-20" />
                    </div>
                    <p class="font-bold text-lg text-slate-600 tracking-wide">目前沒有符合條件的角色資料</p>
                    <button type="button" class="text-indigo-600 font-bold text-xs uppercase tracking-widest border-b border-indigo-200" @click="resetFilters">重設搜尋條件</button>
                  </div>
                </td>
              </tr>

              <tr v-else>
                <td colspan="5" class="px-6 py-32 text-center text-slate-400">
                  <div class="flex flex-col items-center gap-2">
                    <i class="fa-solid fa-filter text-4xl opacity-10 mb-2" />
                    <p class="font-medium tracking-widest uppercase">請點擊搜尋按鈕開始查詢</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="hasSearched && pagination.total > 0 && !loading" class="bg-white border-t border-slate-100 px-8 py-6 flex items-center justify-between">
          <div class="text-sm text-slate-500 font-medium">
            顯示 <span class="text-slate-900 font-bold">{{ pagination.from }}</span> - <span class="text-slate-900 font-bold">{{ pagination.to }}</span>，共 <span class="text-slate-900 font-bold">{{ pagination.total }}</span> 筆
          </div>
          <div class="flex items-center gap-2">
            <button type="button" class="page-btn" :disabled="pagination.currentPage <= 1" @click="goToPage(pagination.currentPage - 1)">上一頁</button>
            <div class="flex gap-1">
              <button
                v-for="page in visiblePages" :key="page"
                type="button"
                class="w-10 h-10 rounded-lg text-sm font-bold transition-all"
                :class="page === pagination.currentPage ? 'bg-slate-900 text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                @click="goToPage(page)"
              >{{ page }}</button>
            </div>
            <button type="button" class="page-btn" :disabled="pagination.currentPage >= pagination.lastPage" @click="goToPage(pagination.currentPage + 1)">下一頁</button>
          </div>
        </div>
      </div>

      <div class="col-span-12 xl:col-span-5 sticky top-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm h-[650px] flex flex-col overflow-hidden text-left">
          <div class="p-5 border-b border-slate-100 bg-slate-50/50">
            <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm tracking-widest uppercase">
              <i class="fa-solid fa-list-check text-indigo-600" /> 功能權限配置
            </h3>
          </div>

          <Transition name="slide-fade" mode="out-in">
            <div v-if="!authStore.hasPermission('permission', 'view')" class="flex-1 flex flex-col items-center justify-center p-12 text-center">
              <div class="relative mb-6">
                <div class="absolute inset-0 bg-red-100 rounded-full scale-150 blur-xl opacity-50"></div>
                <i class="fa-solid fa-file-shield text-5xl text-red-500 relative z-10 animate-pulse" />
              </div>
              <h3 class="text-red-600 font-black text-lg mb-2 uppercase tracking-tight">Access Denied</h3>
              <p class="text-slate-500 text-sm font-bold leading-relaxed max-w-[240px]">
                您沒有權限檢視角色有哪些功能，<br>請聯繫系統管理員申請授權。
              </p>
            </div>

            <div v-else-if="selectedRoleForDetail" :key="selectedRoleForDetail.id" class="flex-1 flex flex-col min-h-0">
              <div class="px-6 pt-6">
                <div class="p-4 bg-indigo-600 rounded-xl text-white shadow-lg text-left">
                  <p class="text-[10px] opacity-70 uppercase font-black mb-1 tracking-widest">Current Selected Role</p>
                  <p class="text-lg font-bold leading-tight truncate">{{ selectedRoleForDetail.name }}</p>
                </div>
              </div>

              <div class="flex-1 overflow-y-auto p-6 custom-scrollbar space-y-6">
                <div v-for="(perms, moduleName) in groupedPermissions" :key="moduleName">
                  <div class="flex items-center gap-2 mb-3 border-l-4 border-indigo-500 pl-3">
                    <h4 class="text-sm font-black text-slate-700 uppercase tracking-wider">{{ moduleName }}</h4>
                  </div>
                  <div class="flex flex-wrap gap-2">
                    <div
                      v-for="perm in perms"
                      :key="perm.id"
                      class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50/50 text-indigo-700 border border-indigo-100 rounded-lg hover:bg-indigo-100 hover:shadow-sm transition-all cursor-default group"
                      :title="perm.name + ' (' + perm.code + ')'"
                    >
                      <i class="fa-solid fa-circle-check text-[10px] text-indigo-400 group-hover:text-indigo-600 transition-colors" />
                      <span class="text-sm font-bold leading-none">{{ perm.name }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="p-4 border-t border-slate-100 bg-slate-50/30 text-center">
                <p class="mt-auto pt-4 text-[14px] text-slate-600 font-bold text-center whitespace-nowrap shrink-0">
                    * 詳細設定請點擊資料列中功能權限圖示
                </p>
              </div>
            </div>

            <div v-else class="flex-1 flex flex-col items-center justify-center p-12 text-center text-slate-400">
                <i class="fa-solid fa-fingerprint text-4xl mb-4 opacity-20" />
                <p class="text-slate-400 text-[14px] font-bold uppercase tracking-widest whitespace-nowrap">請先點選左側列表檢視角色資訊</p>
            </div>
          </Transition>
        </div>
      </div>
    </div>

    <RoleFormModal v-if="showRoleModal" ref="roleFormModal" :visible="showRoleModal" :role="currentRole" :is-editing="isEditing" @close="closeRoleModal" @submit="handleRoleSubmit" />
    <ConfirmModal v-if="showDeleteModal" :show="showDeleteModal" :message="roleToDelete?.name" :loading="loading" @confirm="handleDelete" @cancel="closeDeleteModal" />
    <AppToast :message="toast.message" :type="toast.type" :show="toast.show" />
  </div>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { roleService } from '@/services/roleService'
import RoleFormModal from '@/components/frontend/RoleFormModal.vue'
import ConfirmModal from '@/components/frontend/ConfirmModal.vue'
import AppToast from '@/components/frontend/AppToast.vue'

export default {
  name: 'RoleView',
  components: { RoleFormModal, ConfirmModal, AppToast },
  data () {
    return {
      loading: false,
      hasSearched: false,
      showRoleModal: false,
      showDeleteModal: false,
      currentRole: null,
      roleToDelete: null,
      selectedRoleForDetail: null,
      isEditing: false,
      roles: [],
      filters: { keyword: '', enable: null },
      pagination: { currentPage: 1, lastPage: 1, perPage: 15, total: 0, from: 0, to: 0 },
      toast: { show: false, message: '', type: 'info' }
    }
  },
  computed: {
    ...mapState(useAuthStore, ['customerId', 'customerName']),
    authStore () {
      return useAuthStore()
    },
    groupedPermissions () {
      if (!this.selectedRoleForDetail?.permissions) return {}
      return this.selectedRoleForDetail.permissions.reduce((groups, item) => {
        const moduleName = item.module?.name || '其他'
        if (!groups[moduleName]) groups[moduleName] = []
        groups[moduleName].push(item)
        return groups
      }, {})
    },
    visiblePages () {
      const pages = []
      const current = this.pagination.currentPage
      const last = this.pagination.lastPage
      let start = Math.max(1, current - 2)
      const end = Math.min(last, start + 4)
      if (end - start < 4) start = Math.max(1, end - 4)
      for (let i = start; i <= end; i++) { if (i >= 1 && i <= last) pages.push(i) }
      return pages
    }
  },
  mounted () {
    if (this.customerId) this.handleSearch()
  },
  methods: {
    showNotification (message, type = 'info') {
      this.toast = { show: true, message, type }
      setTimeout(() => { this.toast.show = false }, 3000)
    },
    async loadRoles () {
      if (this.loading) return
      this.loading = true
      this.hasSearched = true
      try {
        const response = await roleService.getRoles({
          page: this.pagination.currentPage,
          per_page: this.pagination.perPage,
          keyword: this.filters.keyword?.trim() || undefined,
          enable: this.filters.enable !== null ? this.filters.enable : undefined,
          customer_id: this.customerId
        })
        if (response?.success) {
          this.roles = response.data || []
          const meta = response.meta || response.pagination || {}
          this.pagination.currentPage = meta.current_page || 1
          this.pagination.lastPage = meta.last_page || 1
          this.pagination.total = meta.total || 0
          this.pagination.from = meta.from || (this.roles.length > 0 ? (this.pagination.currentPage - 1) * this.pagination.perPage + 1 : 0)
          this.pagination.to = meta.to || (this.roles.length > 0 ? this.pagination.from + this.roles.length - 1 : 0)
        }
      } catch (error) {
        this.showNotification('載入資料異常', 'error')
      } finally {
        setTimeout(() => { this.loading = false }, 400)
      }
    },
    handleSearch () {
      this.pagination.currentPage = 1
      this.selectedRoleForDetail = null // 重要：搜尋時重設右側預覽
      this.loadRoles()
    },
    goToPage (p) {
      if (p >= 1 && p <= this.pagination.lastPage) {
        this.pagination.currentPage = p
        this.selectedRoleForDetail = null // 重要：換頁時重設右側預覽
        this.loadRoles()
      }
    },
    calculateNo (i) {
      return (this.pagination.currentPage - 1) * this.pagination.perPage + i + 1
    },
    previewRolePermissions (role) {
      // 權限檢查
      if (!this.authStore.hasPermission('permission', 'view')) {
        this.showNotification('您沒有查看權限', 'warning')
        return
      }
      this.selectedRoleForDetail = role
    },
    openCreateModal () {
      this.currentRole = { customer_id: this.customerId, customer_name: this.customerName }
      this.isEditing = false
      this.showRoleModal = true
    },
    openEditModal (r) {
      this.currentRole = { ...r, customer_id: this.customerId, customer_name: this.customerName }
      this.isEditing = true
      this.showRoleModal = true
    },
    closeRoleModal () {
      this.showRoleModal = false
    },
    async handleRoleSubmit (formData) {
      this.loading = true
      try {
        const payload = { ...formData, customer_id: this.customerId }
        const res = this.isEditing ? await roleService.updateRole(this.currentRole.id, payload) : await roleService.createRole(payload)
        if (res.success) {
          this.showNotification('儲存成功', 'success')
          this.closeRoleModal()
          this.selectedRoleForDetail = null // 操作後重設
          this.loadRoles()
        }
      } finally {
        this.loading = false
      }
    },
    mapsToPermissions (role) {
      // 導航到權限頁面，帶入公司和角色功能權限資訊
      this.$router.push({
        name: 'permission',
        query: {
          roleId: role.id,
          roleName: role.name
        }
      })
    },
    /*
    mapsToDataPermissions (r) {
      this.$router.push({ name: 'dataPermission', query: { roleId: r.id } })
    },
    */
    confirmDelete (r) {
      if (r.isDefault) {
        this.showNotification('系統預設不可刪除', 'warning')
        return
      }
      this.roleToDelete = r
      this.showDeleteModal = true
    },
    closeDeleteModal () {
      this.showDeleteModal = false
    },
    async handleDelete () {
      this.loading = true
      try {
        const res = await roleService.deleteRole(this.roleToDelete.id)
        if (res.success) {
          this.showNotification('已成功刪除', 'success')
          this.selectedRoleForDetail = null // 重要：刪除後重設右側預覽
          this.loadRoles()
          this.closeDeleteModal()
        }
      } finally {
        this.loading = false
      }
    },
    resetFilters () {
      this.filters = { keyword: '', enable: null }
      this.selectedRoleForDetail = null // 重設過濾時也重設預覽
      this.handleSearch()
    }
  }
}
</script>

<style scoped>
.font-inter { font-family: 'Inter', sans-serif; }
.action-btn { @apply w-10 h-10 rounded-xl border border-slate-200 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 flex items-center justify-center transition-all shadow-sm; }
.page-btn { @apply px-4 py-2 border border-slate-200 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition-all cursor-pointer; }

/* 基礎進場動畫 */
.fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

/* 切換角色的過渡動畫 */
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

select { -webkit-appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 1rem center; background-size: 1.2em; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
:deep(table) { font-feature-settings: "tnum"; font-variant-numeric: tabular-nums; }
</style>
