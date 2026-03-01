<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-[10000] flex items-center justify-center p-4">
      <div
        class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
        @click="!isSubmitting && $emit('cancel')"
      ></div>

      <div class="relative bg-white w-full max-w-sm shadow-2xl rounded-3xl overflow-hidden scale-in">
        <div class="p-8 text-center">
          <div class="mx-auto w-20 h-20 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mb-6 ring-8 ring-rose-50/50">
            <i :class="['fa-solid', isSubmitting ? 'fa-spinner spinning' : 'fa-trash-can', 'text-3xl']"></i>
          </div>

          <h3 class="text-xl font-bold text-slate-800 mb-2">{{ title }}</h3>
          <p class="text-slate-500 leading-relaxed">
            確定要刪除「<span class="text-slate-900 font-bold">{{ message }}</span>」嗎？<br>
            此操作將無法復原。
          </p>
        </div>

        <div class="flex border-t border-slate-100">
          <button
            type="button"
            :disabled="isSubmitting"
            @click="$emit('cancel')"
            class="flex-1 px-6 py-4 text-sm font-bold text-slate-500 hover:bg-slate-50 transition-colors disabled:opacity-50"
          >
            取消
          </button>
          <button
            type="button"
            :disabled="isSubmitting"
            @click="handleConfirm"
            class="flex-1 px-6 py-4 text-sm font-bold text-white bg-rose-500 hover:bg-rose-600 transition-colors shadow-inner flex items-center justify-center gap-2 disabled:bg-rose-400"
          >
            <i v-if="isSubmitting" class="fa-solid fa-spinner spinning text-xs"></i>
            <span>{{ isSubmitting ? '處理中...' : '確認刪除' }}</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script>
export default {
  name: 'ConfirmDeleteModal',
  props: {
    show: Boolean,
    title: { type: String, default: '確認刪除' },
    message: String,
    // 增加一個 prop 讓父組件控制讀取狀態
    loading: { type: Boolean, default: false }
  },
  emits: ['confirm', 'cancel', 'notification'],
  data () {
    return {
      // 內部狀態，如果父組件沒傳 loading prop，也可以在內部控制
      internalLoading: false
    }
  },
  computed: {
    isSubmitting () {
      return this.loading || this.internalLoading
    }
  },
  watch: {
    show (newVal) {
      if (!newVal) {
        this.internalLoading = false
      }
    }
  },
  methods: {
    handleConfirm () {
      if (this.isSubmitting) return
      this.internalLoading = true
      this.$emit('confirm')
    }
  }
}
</script>

<style scoped>
.scale-in { animation: scaleIn 0.2s ease-out forwards; }
@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

/* 旋轉動畫 */
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.spinning {
  animation: spin 1s linear infinite;
  display: inline-block;
}
</style>
