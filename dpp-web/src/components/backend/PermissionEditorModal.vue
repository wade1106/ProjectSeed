<template>
  <Teleport to="body">
    <div v-if="visible || showSelectedModal" class="permission-editor-wrapper">

      <div class="modal fade show d-block" v-if="visible" tabindex="-1" @click.self="close">
        <div class="modal-dialog modal-xl">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">權限設定 - {{ role?.name }}</h5>
              <button type="button" class="btn-close" @click="close"></button>
            </div>

            <div class="modal-body">
              <div class="role-info card mb-3">
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-8 text-left">
                      <strong>角色說明：</strong> <span class="text-muted">{{ role?.description || '無描述' }}</span>
                    </div>
                    <div class="col-md-4 text-end">
                      <span class="badge" :class="role?.enable ? 'bg-success' : 'bg-danger'">
                        {{ role?.enable ? '啟用' : '停用' }}
                      </span>
                      <span v-if="role?.isDefault" class="badge bg-secondary ms-2">預設角色</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="alert alert-info d-flex align-items-center" v-if="role?.isDefault">
                <i class="fas fa-info-circle me-2"></i>
                <span class="text-left">
                  <strong>注意：</strong>這是系統預設角色，修改權限可能會影響所有使用此角色的使用者。
                </span>
              </div>

              <div class="toolbar mb-3">
                <div class="row align-items-center">
                  <div class="col-md-6 text-left">
                    <div class="btn-group" role="group">
                      <button type="button" class="btn btn-outline-primary btn-sm" @click="searchPermissions">
                        <i class="fas fa-search"></i> 搜尋權限
                      </button>
                      <button type="button" class="btn btn-outline-success btn-sm" @click="expandAllModules">
                        <i class="fas fa-chevron-down"></i> 展開全部
                      </button>
                      <button type="button" class="btn btn-outline-warning btn-sm" @click="collapseAllModules">
                        <i class="fas fa-chevron-up"></i> 收起全部
                      </button>
                    </div>
                  </div>
                  <div class="col-md-6 text-end">
                    <div class="btn-group" role="group">
                      <button type="button" class="btn btn-outline-info btn-sm" @click="selectAllPermissions">
                        <i class="fas fa-check-square"></i> 全選
                      </button>
                      <button type="button" class="btn btn-outline-secondary btn-sm" @click="clearAllPermissions">
                        <i class="fas fa-square"></i> 清空
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="search-section mb-3" v-if="showSearch">
                <div class="input-group">
                  <input
                    type="text"
                    class="form-control"
                    placeholder="搜尋權限名稱或代碼..."
                    v-model="searchQuery"
                  />
                  <button class="btn btn-outline-secondary" type="button" @click="closeSearch">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>

              <div class="permissions-container custom-scrollbar">
                <div v-if="loadingPermissions" class="text-center py-5">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">載入權限資訊...</span>
                  </div>
                </div>

                <div v-else-if="!filteredModules.length" class="text-center py-5 text-muted">
                  <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                  <p>找不到符合條件的權限</p>
                  <button type="button" class="btn btn-secondary" @click="clearSearch">清除搜尋</button>
                </div>

                <div v-else class="permissions-panel">
                  <div v-for="module in filteredModules" :key="module.id" class="module-section">
                    <div class="module-header" @click="toggleModuleExpansion(module.id)">
                      <input
                        type="checkbox"
                        class="form-check-input me-2"
                        :id="`module-${module.id}`"
                        @click.stop
                        @change="toggleModulePermissions(module)"
                        :checked="isModuleSelected(module)"
                      />
                      <h6 class="module-title">
                        {{ module.name }}
                        <i class="fas" :class="expandedModules.has(module.id) ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                      </h6>
                      <small class="text-muted ms-auto">已選擇 {{ getModuleSelectedCount(module) }}/{{ getModuleTotalCount(module) }}</small>
                    </div>
                    <div v-show="expandedModules.has(module.id)" class="permissions-grid">
                      <div v-for="permission in filteredPermissions(module.permissions)" :key="permission.id" class="permission-item text-left">
                        <div class="form-check">
                          <input
                            type="checkbox"
                            class="form-check-input"
                            :id="`permission-${permission.id}`"
                            :value="permission.id"
                            v-model="selectedPermissionIds"
                            @change="onPermissionChange(permission)"
                          />
                          <label class="form-check-label" :for="`permission-${permission.id}`">
                            <div class="permission-name">{{ permission.name }}</div>
                            <div v-if="permission.description" class="permission-description text-muted small">
                              {{ permission.description }}
                            </div>
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="selectedPermissionIds.length" class="permissions-summary mt-3">
                <div class="alert alert-success mb-0 d-flex justify-content-between align-items-center">
                  <span>
                    <i class="fas fa-check-circle"></i>
                    已選擇 <strong>{{ selectedPermissionIds.length }}</strong> 個權限
                  </span>
                  <button type="button" class="btn btn-outline-success btn-sm" @click="viewSelectedPermissions">
                    <i class="fas fa-list"></i> 檢視已選權限
                  </button>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="close">取消</button>
              <button type="button" class="btn btn-primary" @click="savePermissions" :disabled="!hasChanges || isSaving || !canSelectPermissions">
                <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
                {{ isSaving ? '儲存中...' : '儲存權限' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="showSelectedModal" class="modal fade show d-block" style="z-index: 1060" tabindex="-1" @click.self="closeSelectedModal">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">已選擇的權限 ({{ selectedPermissionIds.length }} 個)</h5>
              <button type="button" class="btn-close" @click="closeSelectedModal"></button>
            </div>
            <div class="modal-body custom-scrollbar">
              <div v-if="!selectedPermissionsDetail.length" class="text-muted text-center py-4">
                <p>目前沒有選擇任何權限</p>
              </div>
              <div v-else class="selected-permissions-list">
                <div v-for="item in selectedPermissionsDetail" :key="item.permission.id" class="permission-item text-left">
                  <div class="permission-info">
                    <strong>{{ item.permission.name }}</strong>
                    <small v-if="item.permission.description" class="text-muted ms-2">{{ item.permission.description }}</small>
                  </div>
                  <div class="module-info">
                    <i class="fas fa-folder me-1"></i> <span class="text-primary">{{ item.module.name }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeSelectedModal">關閉</button>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-backdrop fade show" v-if="visible" style="z-index: 1040"></div>
      <div class="modal-backdrop fade show" v-if="showSelectedModal" style="z-index: 1059"></div>
    </div>
  </Teleport>
</template>

<script>
import { ref, computed, watch } from 'vue'

export default {
  name: 'PermissionEditorModal',
  props: {
    visible: { type: Boolean, required: true },
    role: { type: Object, required: true },
    permissionsByModules: { type: Array, default: () => [] },
    loadingPermissions: { type: Boolean, default: false }
  },
  emits: ['close', 'save'],
  setup (props, { emit }) {
    const selectedPermissionIds = ref([])
    const originalPermissionIds = ref([])
    const expandedModules = ref(new Set())
    const searchQuery = ref('')
    const showSearch = ref(false)
    const isSaving = ref(false)
    const showSelectedModal = ref(false)

    const canSelectPermissions = computed(() => !props.role?.isDefault)

    const hasChanges = computed(() => {
      const original = JSON.stringify([...originalPermissionIds.value].sort())
      const current = JSON.stringify([...selectedPermissionIds.value].sort())
      return original !== current
    })

    const allModules = computed(() => props.permissionsByModules || [])

    const filteredModules = computed(() => {
      if (!searchQuery.value) return allModules.value

      return allModules.value.filter(module => {
        const moduleMatch = module.name.toLowerCase().includes(searchQuery.value.toLowerCase())
        const permissionsMatch = module.permissions?.some(permission =>
          permission.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
          permission.code.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
          (permission.description && permission.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
        )
        return moduleMatch || permissionsMatch
      })
    })

    const selectedPermissionsDetail = computed(() => {
      if (!selectedPermissionIds.value.length) return []
      const result = []
      allModules.value.forEach(module => {
        module.permissions?.forEach(permission => {
          if (selectedPermissionIds.value.includes(permission.id)) {
            result.push({ permission, module })
          }
        })
      })
      return result.sort((a, b) => a.permission.name.localeCompare(b.permission.name))
    })

    const initializePermissions = () => {
      originalPermissionIds.value = props.role.permissions?.map(p => p.id) || []
      selectedPermissionIds.value = [...originalPermissionIds.value]

      const modulesHasSelected = new Set()
      allModules.value.forEach(module => {
        const hasSelected = module.permissions?.some(p => originalPermissionIds.value.includes(p.id))
        if (hasSelected) modulesHasSelected.add(module.id)
      })
      expandedModules.value = modulesHasSelected
      searchQuery.value = ''
      showSearch.value = false
    }

    watch(() => props.visible, (newVal) => {
      if (newVal && props.role) {
        initializePermissions()
        document.body.style.overflow = 'hidden'
      } else {
        document.body.style.overflow = ''
      }
    }, { immediate: true })

    const toggleModuleExpansion = (moduleId) => {
      const newSet = new Set(expandedModules.value)
      if (newSet.has(moduleId)) newSet.delete(moduleId)
      else newSet.add(moduleId)
      expandedModules.value = newSet
    }

    const expandAllModules = () => {
      expandedModules.value = new Set(allModules.value.map(m => m.id))
    }

    const collapseAllModules = () => {
      expandedModules.value = new Set()
    }

    const filteredPermissions = (permissions) => {
      if (!searchQuery.value || !permissions) return permissions
      const query = searchQuery.value.toLowerCase()
      return permissions.filter(p =>
        p.name.toLowerCase().includes(query) ||
        p.code.toLowerCase().includes(query) ||
        (p.description && p.description.toLowerCase().includes(query))
      )
    }

    const isModuleSelected = (module) => {
      const ids = module.permissions?.map(p => p.id) || []
      return ids.length > 0 && ids.every(id => selectedPermissionIds.value.includes(id))
    }

    const toggleModulePermissions = (module) => {
      const ids = module.permissions?.map(p => p.id) || []
      const allSelected = isModuleSelected(module)
      if (allSelected) {
        selectedPermissionIds.value = selectedPermissionIds.value.filter(id => !ids.includes(id))
      } else {
        selectedPermissionIds.value = [...new Set([...selectedPermissionIds.value, ...ids])]
      }
      expandedModules.value.add(module.id)
    }

    const selectAllPermissions = () => {
      const allIds = []
      allModules.value.forEach(m => m.permissions?.forEach(p => allIds.push(p.id)))
      selectedPermissionIds.value = allIds
      expandAllModules()
    }

    const clearAllPermissions = () => {
      selectedPermissionIds.value = []
    }

    const onPermissionChange = (permission) => {
      console.log('Permission changed:', permission.name)
    }

    const getModuleSelectedCount = (module) => {
      const ids = module.permissions?.map(p => p.id) || []
      return selectedPermissionIds.value.filter(id => ids.includes(id)).length
    }

    const getModuleTotalCount = (module) => module.permissions?.length || 0

    const searchPermissions = () => {
      showSearch.value = true
      expandAllModules()
    }

    const closeSearch = () => {
      showSearch.value = false
      searchQuery.value = ''
    }

    const viewSelectedPermissions = () => {
      if (selectedPermissionIds.value.length) showSelectedModal.value = true
    }

    const closeSelectedModal = () => {
      showSelectedModal.value = false
    }

    const savePermissions = async () => {
      if (props.role.isDefault && !confirm('您正在修改預設角色，確定要繼續嗎？')) return
      if (!hasChanges.value) return

      isSaving.value = true
      try {
        await emit('save', selectedPermissionIds.value)
      } finally {
        isSaving.value = false
      }
    }

    const close = () => {
      if (hasChanges.value && !confirm('有未儲存的變更，確定要關閉嗎？')) return
      emit('close')
    }

    return {
      selectedPermissionIds,
      expandedModules,
      searchQuery,
      showSearch,
      isSaving,
      showSelectedModal,
      canSelectPermissions,
      hasChanges,
      filteredModules,
      selectedPermissionsDetail,
      toggleModuleExpansion,
      expandAllModules,
      collapseAllModules,
      filteredPermissions,
      isModuleSelected,
      toggleModulePermissions,
      selectAllPermissions,
      clearAllPermissions,
      onPermissionChange,
      getModuleSelectedCount,
      getModuleTotalCount,
      searchPermissions,
      closeSearch,
      viewSelectedPermissions,
      closeSelectedModal,
      savePermissions,
      close
    }
  }
}
</script>

<style scoped>
.permission-editor-wrapper {
  position: relative;
  z-index: 9999;
}

.role-info { background-color: #f8f9fa; border-radius: 8px; }
.toolbar { background-color: #e9ecef; padding: 10px; border-radius: 6px; }
.permissions-container {
  max-height: 500px;
  overflow-y: auto;
  border: 2px solid #dee2e6;
  border-radius: 8px;
  background-color: #f8f9fa;
}

/* 自定義捲軸 - 全端工程師細節 */
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

.module-section {
  margin-bottom: 20px; padding: 15px;
  background-color: white; border-radius: 6px;
  border: 1px solid #e9ecef; box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.module-header {
  display: flex; align-items: center;
  padding: 10px 0; border-bottom: 2px solid #dee2e6;
  margin-bottom: 15px; cursor: pointer;
}

.module-title { margin: 0; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; flex-grow: 1; }

.permissions-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 12px; padding-left: 10px;
}

.permission-item {
  border: 1px solid #e9ecef; border-radius: 6px;
  padding: 12px; transition: all 0.2s ease;
}

.permission-item:hover {
  background-color: #f8f9fa; border-color: #007bff;
  box-shadow: 0 2px 4px rgba(0,123,255,0.15);
}

.text-left { text-align: left !important; }
.modal-xl { max-width: 1200px; }
.selected-permissions-list { max-height: 400px; overflow-y: auto; }
</style>
