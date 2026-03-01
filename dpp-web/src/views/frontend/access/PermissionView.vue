<template>
  <div class="w-full p-6 bg-slate-50/50 min-h-screen font-inter text-left fade-in">

    <div class="flex items-center gap-4 mb-6">
      <button
        type="button"
        class="bg-slate-600 hover:bg-slate-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 transition shadow-md active:scale-95 shrink-0"
        @click="goBackToRoleView"
      >
        <i class="fa-solid fa-arrow-left" />
        返回角色設定
      </button>

      <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
            <i class="fa-solid fa-building text-indigo-500 text-xs" />
          </div>
          <div class="flex flex-col">
            <span class="text-[10px] text-slate-400 font-black uppercase leading-none mb-1">公司名稱</span>
            <span class="text-sm font-black text-slate-800 leading-none">{{ customerName }}</span>
          </div>
        </div>
        <div class="h-8 border-l border-slate-100 mx-2"></div>
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
            <i class="fa-solid fa-user-shield text-emerald-500 text-xs" />
          </div>
          <div class="flex flex-col">
            <span class="text-[10px] text-slate-400 font-black uppercase leading-none mb-1">角色名稱</span>
            <span class="text-sm font-black text-slate-800 leading-none">{{ roleName }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="flex gap-6 w-full items-start">

      <div class="w-[320px] shrink-0 sticky top-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col h-[calc(100vh-160px)]">
          <div class="p-5 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-base font-black text-slate-800 flex items-center gap-2 mb-4">
              <i class="fa-solid fa-toggle-on text-indigo-600" /> 功能權限設定
            </h3>
            <div class="flex gap-2">
              <button type="button" class="btn-sub-xs flex-1" @click="uncheckAll" :disabled="loading">全部取消</button>
              <button type="button" class="btn-sub-xs flex-1" @click="checkAll" :disabled="loading">全部選取</button>
            </div>
          </div>

          <div class="flex-1 overflow-y-auto p-5 custom-scrollbar relative">
            <div v-if="loading" class="absolute inset-0 bg-white/80 z-10 flex flex-col items-center justify-center gap-3">
              <i class="fa-solid fa-circle-notch animate-spin text-3xl text-indigo-600" />
              <span class="text-xs font-bold text-indigo-600 tracking-widest uppercase">載入功能權限...</span>
            </div>

            <div v-else-if="modules.length === 0" class="h-full flex flex-col items-center justify-center text-slate-400 gap-2">
              <i class="fa-solid fa-folder-open text-3xl opacity-20" />
              <span class="text-sm font-bold tracking-wider">暫無模組功能資料</span>
            </div>

            <div v-else class="space-y-8">
              <div v-for="module in modules" :key="module.id" class="space-y-4">
                <div class="flex items-center gap-3 pb-2 border-b border-slate-50">
                  <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                  <span class="text-[15px] font-black text-slate-700 uppercase leading-none">{{ module.name }}</span>

                  <label class="flex items-center gap-1.5 ml-auto cursor-pointer group">
                    <span class="text-[12px] font-extrabold text-slate-600 group-hover:text-indigo-600 transition-colors">全選</span>
                    <input
                      type="checkbox"
                      :checked="isModuleAllSelected(module)"
                      :indeterminate="isModuleSomeSelected(module)"
                      @change="toggleModuleAll(module)"
                      class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 transition-all"
                    >
                  </label>
                </div>

                <div class="flex flex-col gap-2">
                  <label
                    v-for="permission in module.permissions" :key="permission.id"
                    class="permission-card-sm"
                    :class="selectedPermissions.includes(permission.id) ? 'active' : ''"
                  >
                    <input v-model="selectedPermissions" type="checkbox" :value="permission.id" class="sr-only">
                    <div class="checkbox-ui">
                      <i v-if="selectedPermissions.includes(permission.id)" class="fa-solid fa-check text-[10px] text-white" />
                    </div>
                    <span class="text-[13px] font-bold">{{ permission.name }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="p-4 border-t border-slate-100 bg-slate-50">
            <button
              type="button"
              class="btn-primary w-full py-3 flex items-center justify-center gap-2"
              @click="savePermissions"
              :disabled="loading || isSavingFunction || isFunctionUnchanged"
            >
              <i v-if="isSavingFunction" class="fa-solid fa-circle-notch animate-spin" />
              <i v-else class="fa-solid fa-floppy-disk" />
              {{ isSavingFunction ? '儲存中...' : '儲存功能權限' }}
            </button>
          </div>
        </div>
      </div>

      <div class="flex-1 min-w-0">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col h-[calc(100vh-160px)]">
          <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-center gap-6">
              <div class="flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-database text-emerald-600 text-xl" />
                <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight whitespace-nowrap">資料欄位權限</h3>
              </div>
              <div class="h-8 border-l border-slate-200 hidden lg:block"></div>
              <div class="flex items-center gap-4 flex-1 max-w-md">
                <span class="text-sm font-black text-slate-500 uppercase tracking-widest whitespace-nowrap">產品類別</span>
                <select v-model="selectedCategory" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-700 outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all cursor-pointer shadow-sm" @change="handleCategoryChange">
                  <option v-for="cat in productCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
              </div>
            </div>

            <button type="button" class="btn-ghost-emerald" @click="toggleAllDataColumns" :disabled="dataLoading">
              <i class="fa-solid fa-check-double mr-2" />
              {{ isAllDataSelected ? '取消全選本類別' : '選取本類別全部' }}
            </button>
          </div>

          <div class="flex-1 overflow-y-auto p-8 custom-scrollbar bg-white relative">
            <div v-if="dataLoading" class="absolute inset-0 bg-white/80 z-10 flex flex-col items-center justify-center h-full text-emerald-600 gap-4">
              <i class="fa-solid fa-circle-notch animate-spin text-5xl" />
              <span class="font-bold tracking-widest text-sm uppercase animate-pulse">讀取欄位架構中...</span>
            </div>

            <div v-else-if="availableDataColumns.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-4">
              <label v-for="col in availableDataColumns" :key="col.field" class="data-column-card-lg group" :class="selectedDataFields.includes(col.field) ? 'active' : ''">
                <input v-model="selectedDataFields" type="checkbox" :value="col.field" class="sr-only">
                <div class="flex items-start gap-4">
                  <div class="checkbox-custom-lg">
                    <i v-if="selectedDataFields.includes(col.field)" class="fa-solid fa-check text-xs text-white" />
                  </div>
                  <div class="flex flex-col min-w-0">
                    <span class="text-sm font-black text-slate-700 truncate group-hover:text-emerald-700 transition-colors">{{ col.label }}</span>
                    <span class="text-[11px] font-mono text-slate-400 mt-1 uppercase tracking-tighter group-hover:text-slate-500">{{ col.field }}</span>
                  </div>
                </div>
              </label>
            </div>

            <div v-else class="flex flex-col items-center justify-center h-full text-slate-300">
              <i class="fa-solid fa-layer-group text-7xl mb-6 opacity-10" />
              <p class="text-base font-bold uppercase tracking-widest">此類別目前未定義資料欄位</p>
            </div>
          </div>

          <div class="p-6 border-t border-slate-100 bg-slate-50 flex justify-between items-center">
            <div class="flex items-center gap-2 text-slate-400">
              <i class="fa-solid fa-circle-info text-xs" />
              <p class="text-xs font-bold italic tracking-wide">系統會自動記錄各產品類別的獨立授權狀態</p>
            </div>
            <button
              type="button"
              class="btn-emerald px-12 py-3.5 shadow-emerald-200/50 flex items-center gap-2"
              @click="saveDataPermissions"
              :disabled="dataLoading || isSavingData || isDataUnchanged"
            >
              <i v-if="isSavingData" class="fa-solid fa-circle-notch animate-spin" />
              <i v-else class="fa-solid fa-shield-halved" />
              {{ isSavingData ? '處理中...' : '儲存本類別資料權限' }}
            </button>
          </div>
        </div>
      </div>

    </div>

    <AppToast :message="toast.message" :type="toast.type" :show="toast.show" />
  </div>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { roleService } from '@/services/roleService'
import AppToast from '@/components/backend/AppToast.vue'

export default {
  name: 'PermissionView',
  components: { AppToast },
  data () {
    return {
      loading: false, // 載入初始功能權限的 loading
      dataLoading: false, // 切換類別載入欄位的 loading
      isSavingFunction: false, // 儲存功能權限時的動畫狀態
      isSavingData: false, // 儲存資料權限時的動畫狀態

      roleId: null,
      roleName: '',
      modules: [],
      selectedPermissions: [],
      originalPermissions: [],
      selectedCategory: 1,
      productCategories: [
        { id: 1, name: '電子產品 (Electronics)' },
        { id: 2, name: '紡織材料 (Textiles)' },
        { id: 3, name: '食品原料 (Food Ingredients)' },
        { id: 4, name: '化學試劑 (Chemicals)' }
      ],
      availableDataColumns: [],
      selectedDataFields: [],
      originalDataFields: [],
      toast: { show: false, message: '', type: 'info' }
    }
  },
  computed: {
    ...mapState(useAuthStore, ['customerId', 'customerName']),
    isFunctionUnchanged () {
      return JSON.stringify([...this.selectedPermissions].sort()) === JSON.stringify([...this.originalPermissions].sort())
    },
    isDataUnchanged () {
      return JSON.stringify([...this.selectedDataFields].sort()) === JSON.stringify([...this.originalDataFields].sort())
    },
    isAllDataSelected () {
      return this.availableDataColumns.length > 0 &&
             this.availableDataColumns.every(col => this.selectedDataFields.includes(col.field))
    }
  },
  created () {
    this.loadQueryParams()
    this.loadPermissions()
    this.handleCategoryChange()
  },
  methods: {
    loadQueryParams () {
      this.roleId = this.$route.query.roleId
      this.roleName = this.$route.query.roleName || '未命名角色'
      if (!this.roleId) {
        this.showNotification('缺少角色資訊', 'error')
        this.goBackToRoleView()
      }
    },
    async loadPermissions () {
      this.loading = true
      try {
        const [modulesResponse, rolePermissionsResponse] = await Promise.all([
          roleService.getPermissionsByModules(),
          roleService.getRolePermissions(this.roleId)
        ])
        if (modulesResponse.success) this.modules = modulesResponse.data || []
        if (rolePermissionsResponse.success) {
          const ids = rolePermissionsResponse.data || []
          this.selectedPermissions = [...ids]
          this.originalPermissions = [...ids]
        }
      } finally {
        // 為了讓動畫有感覺，延遲 400ms 關閉
        setTimeout(() => { this.loading = false }, 400)
      }
    },
    isModuleAllSelected (module) {
      const ids = module.permissions.map(p => p.id)
      return ids.every(id => this.selectedPermissions.includes(id))
    },
    isModuleSomeSelected (module) {
      const ids = module.permissions.map(p => p.id)
      return ids.some(id => this.selectedPermissions.includes(id)) && !this.isModuleAllSelected(module)
    },
    toggleModuleAll (module) {
      const ids = module.permissions.map(p => p.id)
      if (this.isModuleAllSelected(module)) {
        this.selectedPermissions = this.selectedPermissions.filter(id => !ids.includes(id))
      } else {
        this.selectedPermissions = [...new Set([...this.selectedPermissions, ...ids])]
      }
    },
    checkAll () { this.selectedPermissions = [...new Set(this.modules.flatMap(m => m.permissions.map(p => p.id)))] },
    uncheckAll () { this.selectedPermissions = [] },

    async savePermissions () {
      if (this.isSavingFunction) return
      this.isSavingFunction = true
      try {
        const response = await roleService.updateRolePermissions(this.roleId, this.selectedPermissions)
        if (response.success) {
          this.originalPermissions = [...this.selectedPermissions]
          this.showNotification('功能權限更新成功', 'success')
        }
      } finally {
        setTimeout(() => { this.isSavingFunction = false }, 600)
      }
    },

    async handleCategoryChange () {
      this.dataLoading = true
      try {
        await new Promise(resolve => setTimeout(resolve, 500))
        const mockFields = {
          1: [
            { field: 'carbon_footprint', label: '產品碳足跡' },
            { field: 'energy_rating', label: '能效分級' },
            { field: 'battery_capacity', label: '電池額定容量' },
            { field: 'recycle_parts', label: '可回收零件清單' },
            { field: 'serial_number', label: '唯一識別編號' },
            { field: 'material_bom', label: '物料清單' },
            { field: 'voltage_info', label: '額定電壓規範' }
          ],
          2: [
            { field: 'fabric_composition', label: '織物成分比例' },
            { field: 'dye_toxicity', label: '染料毒性檢測' }
          ]
        }
        this.availableDataColumns = mockFields[this.selectedCategory] || mockFields[1]
        this.selectedDataFields = [this.availableDataColumns[0]?.field]
        this.originalDataFields = [...this.selectedDataFields]
      } finally {
        this.dataLoading = false
      }
    },
    toggleAllDataColumns () {
      this.selectedDataFields = this.isAllDataSelected ? [] : this.availableDataColumns.map(c => c.field)
    },
    async saveDataPermissions () {
      if (this.isSavingData) return
      this.isSavingData = true
      try {
        await new Promise(resolve => setTimeout(resolve, 1000)) // 模擬後端儲存時間
        this.originalDataFields = [...this.selectedDataFields]
        this.showNotification('資料權限儲存成功', 'success')
      } finally {
        this.isSavingData = false
      }
    },

    goBackToRoleView () { this.$router.push({ name: 'role', query: { search: 'true' } }) },
    showNotification (message, type = 'info') {
      this.toast = { show: true, message, type }
      setTimeout(() => { this.toast.show = false }, 3000)
    }
  }
}
</script>

<style scoped>
/* 樣式保持不變，已整合您最新的樣式 */
.font-inter { font-family: 'Inter', sans-serif; }
.btn-sub-xs { @apply py-2 px-3 text-[13px] font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:border-indigo-300 hover:text-indigo-600 transition-all uppercase flex items-center justify-center active:scale-95 disabled:opacity-50; }
.btn-primary { @apply bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-black text-sm transition-all shadow-lg shadow-indigo-200 active:scale-95 disabled:opacity-50; }
.btn-emerald { @apply bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black text-sm transition-all shadow-lg active:scale-95 disabled:opacity-50; }
.btn-ghost-emerald { @apply px-5 py-2.5 text-sm font-black text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-xl transition-all flex items-center shadow-sm disabled:opacity-50; }
.permission-card-sm { @apply flex items-center gap-3 p-3 rounded-xl border border-slate-100 bg-white cursor-pointer transition-all hover:bg-indigo-50/50 hover:border-indigo-200 w-full shadow-sm; }
.permission-card-sm.active { @apply border-indigo-500 bg-indigo-50/50 ring-1 ring-indigo-500 shadow-indigo-100; }
.checkbox-ui { @apply w-4 h-4 rounded border border-slate-200 bg-white flex items-center justify-center transition-colors shrink-0; }
.active .checkbox-ui { @apply bg-indigo-600 border-indigo-600; }
.data-column-card-lg { @apply p-5 rounded-2xl border border-slate-100 bg-white cursor-pointer transition-all duration-300 hover:shadow-xl hover:border-emerald-300 hover:-translate-y-1; }
.data-column-card-lg.active { @apply border-emerald-500 bg-emerald-50/20 ring-1 ring-emerald-500 shadow-emerald-100; }
.checkbox-custom-lg { @apply w-6 h-6 rounded-lg border border-slate-200 bg-white flex items-center justify-center transition-all shrink-0; }
.active .checkbox-custom-lg { @apply bg-emerald-600 border-emerald-600; }
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
select { -webkit-appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 1rem center; background-size: 1.2em; }
</style>
