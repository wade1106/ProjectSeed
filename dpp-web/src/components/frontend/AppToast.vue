<template>
  <Teleport to="body">
    <Transition name="toast-fade">
      <div v-if="show" class="fixed top-8 left-1/2 -translate-x-1/2 z-[10001]">
        <div
          class="flex items-center gap-3 px-6 py-4 rounded-2xl shadow-2xl border backdrop-blur-md"
          :class="type === 'success' ? 'bg-emerald-500/90 border-emerald-400 text-white' : 'bg-rose-500/90 border-rose-400 text-white'"
        >
          <i :class="type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-exclamation'"></i>
          <span class="font-bold tracking-wide">{{ message }}</span>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
export default {
  props: {
    show: Boolean,
    message: String,
    type: { type: String, default: 'success' }
  }
}
</script>

<style scoped>
.toast-fade-enter-active, .toast-fade-leave-active {
  transition: all 0.4s cubic-bezier(0.18, 0.89, 0.32, 1.28);
}
.toast-fade-enter-from, .toast-fade-leave-to {
  opacity: 0;
  transform: translate(-50%, -20px);
}
</style>
