<template>
  <div class="space-y-6 fade-in p-6 bg-slate-50/50 min-h-screen">
    <!-- Back Button and Header -->
    <div class="flex items-center gap-4 mb-6">
      <button
        type="button"
        class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 transition shadow-md active:scale-95"
        @click="goBackToRoleView"
      >
        <i class="fa-solid fa-arrow-left" />
        返回角色設定
      </button>
    </div>

    <!-- Company and Role Information -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm mb-6">
      <div class="flex items-center gap-4">
        <div class="p-3 bg-indigo-50 rounded-xl">
          <i class="fa-solid fa-building text-indigo-600 text-xl" />
        </div>
        <div>
          <h2 class="text-xl font-bold text-slate-800">{{ customerName }}</h2>
          <p class="text-slate-600">公司名稱</p>
        </div>
        <div class="border-l border-slate-200 mx-4 h-12"></div>
        <div class="p-3 bg-emerald-50 rounded-xl">
          <i class="fa-solid fa-user-shield text-emerald-600 text-xl" />
        </div>
        <div>
          <h2 class="text-xl font-bold text-slate-800">{{ roleName }}</h2>
          <p class="text-slate-600">角色名稱</p>
        </div>
      </div>
    </div>

    <!-- Permissions Loading -->
    <div v-if="loading" class="bg-white rounded-2xl p-8 text-center">
      <div class="flex items-center justify-center gap-3 text-indigo-600 font-bold">
        <i class="fa-solid fa-circle-notch animate-spin text-2xl" />
        載入權限資料中...
      </div>
    </div>

    <!-- Permissions Content -->
    <div v-else-if="modules.length > 0" class="space-y-6">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-bold text-slate-800">權限設定</h3>
          <div class="flex gap-2">
            <button
              type="button"
              class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition"
              @click="uncheckAll"
            >
              全部取消
            </button>
            <button
              type="button"
              class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition"
              @click="checkAll"
            >
              全部選取
            </button>
          </div>
        </div>

        <!-- Permissions by Module -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div
            v-for="module in modules"
            :key="module.id"
            class="bg-slate-50 rounded-xl p-4 border border-slate-200"
          >
            <div class="flex items-center gap-3 mb-4">
              <h4 class="font-bold text-slate-800">{{ module.name }}</h4>
              <label class="flex items-center gap-2 text-sm text-slate-600">
                <input
                  type="checkbox"
                  :checked="isModuleAllSelected(module)"
                  :indeterminate="isModuleSomeSelected(module)"
                  @change="toggleModuleAll(module)"
                  class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                >
                <span>全選</span>
              </label>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <label
                v-for="permission in module.permissions"
                :key="permission.id"
                class="relative flex items-center p-3 rounded-xl border cursor-pointer transition-all duration-200 group
                      hover:bg-indigo-50/50"
                :class="[
                  selectedPermissions.includes(permission.id)
                    ? 'border-indigo-500 bg-indigo-50/30 ring-1 ring-indigo-500'
                    : 'border-slate-200 bg-white hover:border-indigo-200 shadow-sm'
                ]"
              >
                <input
                  v-model="selectedPermissions"
                  type="checkbox"
                  :value="permission.id"
                  class="sr-only"
                >

                <div class="flex items-center justify-between w-full">
                  <div class="flex items-center gap-3">
                    <div
                      class="w-5 h-5 rounded border flex items-center justify-center transition-colors"
                      :class="[
                        selectedPermissions.includes(permission.id)
                          ? 'bg-indigo-600 border-indigo-600'
                          : 'bg-white border-slate-300 group-hover:border-indigo-400'
                      ]"
                    >
                      <i
                        v-if="selectedPermissions.includes(permission.id)"
                        class="fa-solid fa-check text-[10px] text-white"
                      />
                    </div>
                    <span
                      class="text-sm font-medium transition-colors"
                      :class="selectedPermissions.includes(permission.id) ? 'text-indigo-900' : 'text-slate-700'"
                    >
                      {{ permission.name }}
                    </span>
                  </div>
                </div>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div class="flex justify-end gap-3">
        <button
          type="button"
          class="px-6 py-3 border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 rounded-lg font-medium transition"
          @click="goBackToRoleView"
        >
          取消
        </button>
        <button
          type="button"
          class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition shadow-md disabled:opacity-40 disabled:cursor-not-allowed"
          @click="savePermissions"
          :disabled="loading || selectedPermissions.length === originalPermissions.length && JSON.stringify(selectedPermissions.sort()) === JSON.stringify(originalPermissions.sort())"
        >
          <span v-if="loading">
            <i class="fa-solid fa-circle-notch animate-spin mr-2" />
            儲存中...
          </span>
          <span v-else>
            <i class="fa-solid fa-floppy-disk mr-2" />
            儲存變更
          </span>
        </button>
      </div>
    </div>

    <!-- Error or Empty State -->
    <div v-else class="bg-white rounded-2xl p-8 text-center">
      <div class="flex flex-col items-center">
        <i class="fa-solid fa-triangle-exclamation text-4xl mb-4 text-amber-500" />
        <h3 class="text-lg font-bold text-slate-800 mb-2">無法載入權限資料</h3>
        <p class="text-slate-600 mb-4">請稍後再試或聯絡系統管理員</p>
        <button
          type="button"
          class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition"
          @click="loadPermissions"
        >
          重新載入
        </button>
      </div>
    </div>
    <AppToast :message="toast.message" :type="toast.type" :show="toast.show" />
  </div>
</template>

<script>
import { roleService } from '@/services/roleService'
import AppToast from '@/components/backend/AppToast.vue'

export default {
  name: 'FunctionView',
  components: {
    AppToast
  },
  data () {
    return {
      loading: false,
      roleId: null,
      roleName: '',
      customerId: null,
      customerName: '',
      modules: [],
      selectedPermissions: [],
      originalPermissions: [],
      toast: { show: false, message: '', type: 'info' }
    }
  },
  created () {
    this.loadQueryParams()
    this.loadPermissions()
  },
  methods: {
    loadQueryParams () {
      // Get parameters from route query
      this.roleId = this.$route.query.roleId
      this.roleName = this.$route.query.roleName || ''
      this.customerId = this.$route.query.customerId
      this.customerName = this.$route.query.customerName || ''

      // Validate required parameters
      if (!this.roleId) {
        this.showNotification('缺少角色資訊', 'error')
        this.goBackToRoleView()
      }
    },
    async loadPermissions () {
      this.loading = true
      try {
        // 同時載入所有模組權限和該角色已有的權限
        const [modulesResponse, rolePermissionsResponse] = await Promise.all([
          roleService.getPermissionsByModules(),
          roleService.getRolePermissions(this.roleId)
        ])

        // 載入所有可用的模組和權限
        if (modulesResponse.success) {
          this.modules = modulesResponse.data || []
        } else {
          this.showNotification(modulesResponse.message || '載入模組權限失敗', 'error')
          return
        }

        // 載入該角色已有的權限
        if (rolePermissionsResponse.success) {
          // 假設後端返回的是該角色已有的權限 ID 數組
          const rolePermissionIds = rolePermissionsResponse.data || []
          this.selectedPermissions = rolePermissionIds
          this.originalPermissions = [...rolePermissionIds]
        } else {
          // 如果無法載入角色權限，可能是新角色還沒有設定，使用空陣列
          this.selectedPermissions = []
          this.originalPermissions = []
          console.warn('無法載入角色權限資料:', rolePermissionsResponse.message)
        }
      } catch (error) {
        console.error('Load permissions error:', error)
        this.showNotification('系統錯誤，請稍後再試', 'error')
      } finally {
        this.loading = false
      }
    },
    isModuleAllSelected (module) {
      const modulePermissionIds = module.permissions.map(p => p.id)
      return modulePermissionIds.every(id => this.selectedPermissions.includes(id))
    },
    isModuleSomeSelected (module) {
      const modulePermissionIds = module.permissions.map(p => p.id)
      return modulePermissionIds.some(id => this.selectedPermissions.includes(id)) &&
             !this.isModuleAllSelected(module)
    },
    toggleModuleAll (module) {
      const modulePermissionIds = module.permissions.map(p => p.id)
      if (this.isModuleAllSelected(module)) {
        // Uncheck all in this module
        this.selectedPermissions = this.selectedPermissions.filter(id => !modulePermissionIds.includes(id))
      } else {
        // Check all in this module
        this.selectedPermissions = [...new Set([...this.selectedPermissions, ...modulePermissionIds])]
      }
    },
    checkAll () {
      const allPermissionIds = this.modules.flatMap(m => m.permissions.map(p => p.id))
      this.selectedPermissions = [...new Set(allPermissionIds)]
    },
    uncheckAll () {
      this.selectedPermissions = []
    },
    async savePermissions () {
      if (this.loading || !this.roleId) return

      this.loading = true
      try {
        const response = await roleService.updateRolePermissions(this.roleId, this.selectedPermissions)
        if (response.success) {
          this.originalPermissions = [...this.selectedPermissions]
          // 呼叫修正後的方法
          this.showNotification('權限更新成功', 'success')
        } else {
          this.showNotification(response.message || '更新權限失敗', 'error')
        }
      } catch (error) {
        console.error('Update permissions error:', error)
        const errorMsg = error.response?.data?.message || '儲存失敗，請稍後再試'
        this.showNotification(errorMsg, 'error')
      } finally {
        this.loading = false
      }
    },
    goBackToRoleView () {
      this.$router.push({
        name: 'admin-role-permission',
        query: {
          customerId: this.customerId,
          customerName: this.customerName,
          search: 'true' // 標記需要自動搜尋
        }
      })
    },
    showNotification (message, type = 'info') {
      this.toast.message = message
      this.toast.type = type
      this.toast.show = true
      setTimeout(() => { this.toast.show = false }, 3000)
    }
  }
}
</script>

<style scoped>
.fade-in {
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

:deep(table) {
  font-feature-settings: "tnum";
  font-variant-numeric: tabular-nums;
}
</style>
