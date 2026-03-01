<template>
  <div class="space-y-8 fade-in p-6 bg-slate-50/50 min-h-screen">
    <div class="flex justify-end items-center mb-6">
      <button
        type="button"
        class="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3 rounded-lg text-base font-bold flex items-center gap-2 transition shadow-lg active:scale-95 disabled:opacity-50"
        @click="handleSave"
        :disabled="loading || !selectedCustomerId"
      >
        <i v-if="loading" class="fa-solid fa-circle-notch animate-spin" />
        <i v-else class="fa-solid fa-floppy-disk text-indigo-400" />
        {{ loading ? '儲存中...' : '儲存變更' }}
      </button>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm mb-8 text-left">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end">
        <div class="lg:col-span-6">
          <label class="block text-sm font-bold text-slate-700 mb-2">
            選擇客戶公司 <span class="text-red-500">*</span>
          </label>
          <div class="flex gap-2">
            <div class="relative flex-1">
              <input
                v-model="selectedCustomerName"
                type="text"
                readonly
                placeholder="點擊右側圖示選擇客戶..."
                class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 outline-none cursor-default text-sm h-[46px]"
              >
              <button
                v-if="selectedCustomerId"
                type="button"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 transition-colors"
                @click="clearCustomer"
              >
                <i class="fa-solid fa-circle-xmark" />
              </button>
            </div>
            <button
              type="button"
              class="px-4 h-[46px] bg-white border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition shadow-sm flex items-center justify-center"
              @click="isSelectorOpen = true"
            >
              <i class="fa-solid fa-building text-indigo-500" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden min-h-[400px]">
      <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider">
          <i class="fa-solid fa-tags mr-2" /> 可授權產品類別
        </h3>
        <div class="text-xs text-slate-400" v-if="selectedCustomerId">
          已選擇: <span class="font-bold text-indigo-600">{{ checkedCategoryIds.length }}</span> / {{ availableCategories.length }}
        </div>
      </div>

      <div v-if="fetching" class="flex flex-col items-center justify-center py-32 text-indigo-600 font-bold">
        <div class="relative mb-4">
          <div class="w-16 h-16 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
          <i class="fa-solid fa-tags absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-indigo-600/30"></i>
        </div>
        <span>讀取公司設定中...</span>
      </div>

      <div v-else-if="!selectedCustomerId" class="flex flex-col items-center justify-center py-32 text-slate-400">
        <i class="fa-solid fa-hand-pointer text-5xl mb-4 opacity-20" />
        <p>請先選擇左上方的公司以進行設定</p>
      </div>

      <div v-else class="p-8">
        <div v-if="availableCategories.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          <label
            v-for="category in availableCategories"
            :key="category.id"
            class="group cursor-pointer block relative"
          >
            <input
              v-model="checkedCategoryIds"
              type="checkbox"
              :value="category.id"
              class="peer sr-only"
            >

            <div
              class="p-4 rounded-xl border border-slate-200 bg-white transition-all duration-200 min-h-[72px]
                    group-hover:border-indigo-300
                    peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:ring-2 peer-checked:ring-indigo-600/10
                    flex items-center gap-3"
            >
              <div
                class="flex-shrink-0 w-6 h-6 rounded-md border border-slate-300 flex items-center justify-center transition-colors
                      peer-checked:bg-indigo-600 peer-checked:border-indigo-600"
                :class="{ 'bg-indigo-600 border-indigo-600': checkedCategoryIds.includes(category.id) }"
              >
                <i
                  class="fa-solid fa-check text-white text-[10px] transition-opacity"
                  :class="checkedCategoryIds.includes(category.id) ? 'opacity-100' : 'opacity-0'"
                />
              </div>

              <div class="text-left overflow-hidden">
                <p class="font-bold text-slate-800 text-sm truncate">{{ category.name }}</p>
                <p class="text-xs text-slate-500 font-mono">{{ category.code }}</p>
              </div>
            </div>
          </label>
        </div>
      </div>
    </div>

    <CompanySelectorModal
      :is-open="isSelectorOpen"
      @close="isSelectorOpen = false"
      @confirm="handleCompanySelected"
    />

    <AppToast :message="toast.message" :type="toast.type" :show="toast.show" />
  </div>
</template>

<script>
import { productCategoryService } from '@/services/productCategoryService'
import CompanySelectorModal from '@/components/backend/CompanySelectorModal.vue'
import AppToast from '@/components/backend/AppToast.vue'

export default {
  name: 'CustomerProductCategorySetting',
  components: {
    CompanySelectorModal,
    AppToast
  },
  data () {
    return {
      loading: false,
      fetching: false,
      isSelectorOpen: false,
      selectedCustomerId: '',
      selectedCustomerName: '',
      availableCategories: [],
      checkedCategoryIds: [],
      toast: { show: false, message: '', type: 'info' }
    }
  },
  created () {
    this.fetchAvailableCategories()
  },
  methods: {
    showNotification (message, type = 'info') {
      this.toast.message = message
      this.toast.type = type
      this.toast.show = true
      setTimeout(() => { this.toast.show = false }, 3000)
    },
    async fetchAvailableCategories () {
      try {
        const response = await productCategoryService.getProductCategories({ enable: 1 })
        if (response.success) {
          this.availableCategories = response.data
        }
      } catch (error) {
        console.error('Fetch Categories Error:', error)
      }
    },
    async handleCompanySelected (company) {
      this.selectedCustomerId = company.id
      this.selectedCustomerName = company.name
      this.isSelectorOpen = false
      await this.fetchCustomerSettings()
    },
    async fetchCustomerSettings () {
      if (!this.selectedCustomerId) return
      this.fetching = true
      this.checkedCategoryIds = []
      try {
        const response = await productCategoryService.getCustomerProductCategories(this.selectedCustomerId)
        if (response.success && response.data) {
          this.checkedCategoryIds = response.data.map(item => item.category_id)
        }
      } catch (error) {
        console.error('Fetch Customer Settings Error:', error)
        this.showNotification('載入客戶設定失敗', 'error')
      } finally {
        this.fetching = false
      }
    },
    async handleSave () {
      if (!this.selectedCustomerId) return
      this.loading = true
      try {
        // 修正點：Payload Key 必須與後端 $request->validate 一致
        const payload = {
          customerId: this.selectedCustomerId, // 補上 ID
          categoryIds: this.checkedCategoryIds // 修正名稱由 category_ids 改為 categoryIds
        }

        // 這裡我們直接傳入 payload 物件
        const response = await productCategoryService.updateCustomerProductCategories(
          this.selectedCustomerId,
          payload.categoryIds // 根據你 service 的定義
        )

        if (response.success) {
          this.showNotification(`「${this.selectedCustomerName}」設定儲存成功`, 'success')
        } else {
          this.showNotification(response.message || '儲存失敗', 'error')
        }
      } catch (error) {
        console.error('Save Error:', error)
        this.showNotification('伺服器通訊異常', 'error')
      } finally {
        this.loading = false
      }
    },
    clearCustomer () {
      this.selectedCustomerId = ''
      this.selectedCustomerName = ''
      this.checkedCategoryIds = []
    }
  }
}
</script>

<style scoped>
.fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to { opacity: 1; transform: translateY(0); }
}
input[type="checkbox"]:checked + div {
  @apply shadow-md border-indigo-600;
}
</style>
