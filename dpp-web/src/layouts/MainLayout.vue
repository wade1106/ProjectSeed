<template>
  <div class="bg-slate-50 min-h-screen flex flex-col font-inter text-slate-900">
    <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-6 shrink-0 shadow-sm z-40 relative">
      <div class="flex items-center gap-4">
        <button
          @click="toggleDrawer"
          class="text-slate-600 hover:text-indigo-600 transition-colors p-2 rounded-lg hover:bg-slate-50"
          title="Menu"
        >
          <i class="fa-solid fa-bars text-xl" />
        </button>

        <div class="bg-indigo-600 text-white p-2 rounded-lg shadow-md">
          <i class="fa-solid fa-passport text-xl" />
        </div>
        <div>
          <h1 class="text-lg font-bold tracking-tight text-slate-800">
            <span class="text-indigo-600">Digital Product Passport</span>
          </h1>
          <div class="flex items-center gap-2 text-[10px] text-slate-500 font-mono">
            <span class="bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded border border-indigo-200">V7.0 EU-2027 Ready</span>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <div class="relative mr-2">
          <select
            id="lang-select"
            :value="localeStore.currentLang"
            class="appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold py-1.5 pl-3 pr-8 rounded focus:outline-none focus:border-indigo-500 cursor-pointer"
            @change="handleLangChange"
          >
            <option
              v-for="locale in localeStore.locales"
              :key="locale.code"
              :value="locale.code"
            >
              {{ locale.name }}
            </option>

            <option v-if="localeStore.locales.length === 0" disabled>
              Loading...
            </option>
          </select>

          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
            <i class="fa-solid fa-earth-americas text-xs" />
          </div>
        </div>

        <div class="flex items-center border-r border-slate-200 pr-3 mr-1">
          <div class="role-badge role-internal">
            <i class="fa-solid fa-user-shield" />
            <span data-i18n="val_role_mfg">{{ currentUser.name ? `${currentUser.customerName}-${currentUser.name}(${currentUser.account})` : '' }}</span>
          </div>
        </div>
        <button @click="toggleJsonView" class="text-slate-500 hover:text-slate-800 transition p-2 rounded-full hover:bg-slate-50" title="View Full JSON-LD">
          <i class="fa-solid fa-code text-lg" />
        </button>
        <button onclick="alert('Data validated against EU Battery Regulation 2023/1542 Schema.')" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow flex items-center gap-2">
          <i class="fa-solid fa-cloud-arrow-up" />
          <span data-i18n="btn_sync">EU Gateway Sync</span>
        </button>
        <div class="border-l border-slate-200 ml-2 pl-4 flex items-center">
          <button
            @click="handleLogout"
            class="flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-slate-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200"
          >
            <i class="fa-solid fa-right-from-bracket" />
            <span>登出</span>
          </button>
        </div>
      </div>
    </header>

    <transition name="fade">
      <div
        v-if="showDrawer"
        class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 transition-opacity duration-300"
        @click="closeDrawer"
      />
    </transition>

    <aside
      id="admin-drawer"
      class="fixed top-0 left-0 h-full w-72 bg-white shadow-2xl transform transition-transform duration-300 z-50 flex flex-col"
      :class="showDrawer ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="p-6 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-indigo-200 shadow-lg">
            <i class="fa-solid fa-gears text-lg" />
          </div>
          <h2 class="text-lg font-bold text-slate-800 tracking-tight">
            管理控制台
          </h2>
        </div>
        <button
          @click="closeDrawer"
          class="text-slate-400 hover:text-slate-600 transition-colors p-1"
        >
          <i class="fa-solid fa-xmark text-xl" />
        </button>
      </div>

      <nav class="flex-1 overflow-y-auto p-4 space-y-1 custom-scroll">
        <div class="px-3 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-[0.1em] text-left">
          Access Control
        </div>

        <button
          v-if="authStore.hasPermission('account', 'view')"
          :class="getNavItemClass('/access/account')"
          @click="navigateToRoute('/access/account')"
        >
          <div class="w-6 flex justify-start">
            <i class="fa-solid fa-users-gear text-slate-400 group-hover:text-indigo-600 transition-colors" />
          </div>
          <span>帳號管理</span>
        </button>

        <button
          v-if="authStore.hasPermission('role', 'view')"
          :class="getNavItemClass('/access/role')"
          @click="navigateToRoute('/access/role')"
        >
          <div class="w-6 flex justify-start">
            <i class="fa-solid fa-user-shield text-slate-400 group-hover:text-indigo-600 transition-colors" />
          </div>
          <span>角色管理</span>
        </button>

        <div class="pt-6 px-3 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-[0.1em] text-left border-t border-slate-50 mt-2">
          Application
        </div>

        <div v-if="authStore.hasPermission('passport', 'view')" class="space-y-1">
          <button
            :class="getNavItemClass('/application/product-passport', true)"
            @click="isPassportOpen = !isPassportOpen"
          >
            <div class="w-6 flex justify-start">
              <i
                class="fa-solid fa-passport transition-colors"
                :class="$route.path.includes('product-passport') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-600'"
              />
            </div>
            <span class="flex-1">產品護照</span>
            <i
              class="fa-solid fa-chevron-right text-[10px] transition-transform duration-200"
              :class="{ 'rotate-90': isPassportOpen }"
            />
          </button>

          <div v-show="isPassportOpen" class="pl-10 space-y-1 overflow-hidden transition-all duration-300">
            <button
              :class="getSubNavItemClass('/application/product-passport/battery')"
              @click="navigateToRoute('/application/product-passport/battery')"
            >
              <i class="fa-solid fa-car-battery text-xs mr-2" />
              <span>電池 Battery</span>
            </button>
            <button
              :class="getSubNavItemClass('/application/product-passport/footwear')"
              @click="navigateToRoute('/application/product-passport/footwear')"
            >
              <i class="fa-solid fa-shoe-prints text-xs mr-2" />
              <span>鞋業 Footwear</span>
            </button>
          </div>
        </div>

        <button
          v-if="authStore.hasPermission('import', 'view')"
          :class="getNavItemClass('/application/product-batch-import')"
          @click="navigateToRoute('/application/product-batch-import')"
        >
          <div class="w-6 flex justify-start">
            <i
              class="fa-solid fa-file-import transition-colors"
              :class="$route.path === '/application/product-batch-import' ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-600'"
            />
          </div>
          <span>產品批次導入</span>
        </button>
      </nav>

      <div class="p-4 border-t border-slate-100 bg-slate-50/50">
        <div class="flex items-center gap-3 p-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="w-10 h-10 shrink-0 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-inner">
            {{ shortName }}
          </div>

          <div class="flex-1 min-w-0">
            <p class="text-xs font-bold text-slate-800 truncate">
              {{ currentUser.name ? `${currentUser.name}(${currentUser.account})` : '' }}
            </p>
            <p class="text-[10px] text-slate-500 font-mono truncate leading-tight">
              {{ currentUser.email ? `${currentUser.email}` : '' }}
            </p>
          </div>

          <button
            @click="handleLogout"
            class="text-slate-300 hover:text-red-500 transition-colors p-1"
            title="Logout"
          >
            <i class="fa-solid fa-right-from-bracket text-sm" />
          </button>
        </div>
      </div>
    </aside>

    <main class="flex flex-1 overflow-hidden relative">
      <router-view />
    </main>

    <transition name="fade">
      <div v-if="isJsonVisible" class="fixed inset-0 bg-slate-900/95 z-[60] p-8 flex flex-col backdrop-blur-sm text-left font-mono">
        <div class="flex justify-between items-center mb-4 text-white">
          <div class="flex items-center gap-3">
            <div class="bg-indigo-500/20 text-indigo-400 p-2 rounded-lg"><i class="fa-solid fa-code" /></div>
            <h3 class="text-lg font-bold">Annex XIII Data Structure (JSON-LD)</h3>
          </div>
          <button @click="isJsonVisible = false" class="text-slate-400 hover:text-white transition p-2">
            <i class="fa-solid fa-xmark text-2xl" />
          </button>
        </div>
        <div class="flex-1 bg-slate-800/40 rounded-2xl border border-white/10 p-6 overflow-auto custom-scroll shadow-2xl">
          <pre class="text-green-400 text-xs leading-relaxed whitespace-pre-wrap">{{ JSON.stringify(jsonData, null, 2) }}</pre>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import { useAuthStore } from '@/stores/auth'
import { useLocaleStore } from '@/stores/locale'

export default {
  name: 'MainLayout',

  computed: {
    authStore () {
      return useAuthStore()
    },
    localeStore () {
      return useLocaleStore()
    },
    currentUser () {
      return this.authStore.userInfo
    },
    shortName () {
      const name = this.currentUser?.name
      return name ? name.charAt(0).toUpperCase() : '?'
    }
  },
  async created () {
    await this.localeStore.fetchLocales()
  },
  data () {
    return {
      showDrawer: false,
      isJsonVisible: false,
      isPassportOpen: false, // 控制產品護照選單展開
      jsonData: {
        '@context': 'https://eur-lex.europa.eu/eli/reg/2023/1542/oj',
        battery_id: 'urn:uuid:6e8b2d35-1f9e-4a6c-92b1-5e7d4c8f0042',
        public_information: {
          category: 'Industrial',
          product_name: 'Volt-Link Industrial Pack 280Ah',
          carbon_footprint: { total: 72.4, share: { raw_material: 65, manufacturing: 25, distribution: 10 }, class: 'B' },
          circularity: { recycled_content: { cobalt: 12, lithium: 4 }, renewable_content: 5.2 }
        },
        restricted_information: { access_level: 'Level 1', hazardous_substances: ['Lead > 0.004%'], chemistry: 'Li-ion NMC 811' }
      }
    }
  },
  methods: {
    handleLangChange (event) {
      const selectedLang = event.target.value
      this.localeStore.setLanguage(selectedLang)
    },

    getNavItemClass (path, isParent = false) {
      const baseClass = 'w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 text-left group'
      const isActive = isParent ? this.$route.path.includes(path) : this.$route.path === path

      if (isActive) {
        return `${baseClass} bg-indigo-50 text-indigo-700 border border-indigo-100/50 shadow-sm`
      }
      return `${baseClass} text-slate-600 hover:bg-indigo-50 hover:text-indigo-700`
    },

    getSubNavItemClass (path) {
      const baseClass = 'w-full flex items-center px-4 py-2 rounded-lg text-xs font-medium transition-all duration-200 text-left group'
      if (this.$route.path === path) {
        return `${baseClass} text-indigo-700 bg-indigo-50/50`
      }
      return `${baseClass} text-slate-500 hover:text-indigo-600 hover:bg-slate-50`
    },

    toggleDrawer () {
      this.showDrawer = !this.showDrawer
    },

    closeDrawer () {
      this.showDrawer = false
    },

    navigateToRoute (path) {
      this.closeDrawer()

      // 定義路徑與路由 Name 的映射
      const pathToName = {
        '/access/account': 'account',
        '/access/role': 'role',
        '/access/permission': 'permission',
        '/application/product-passport/battery': 'product-passport-battery', // 補上電池
        '/application/product-passport/footwear': 'product-passport-footwear', // 補上鞋業
        '/application/product-batch-import': 'product-batch-import'
      }

      const routeName = pathToName[path]

      if (routeName) {
        // 如果在映射表中有定義，則按 Name 跳轉
        this.$router.push({ name: routeName }).catch((err) => {
          if (err.name !== 'NavigationDuplicated') console.error(err)
        })
      } else {
        // 如果映射表中沒有定義（防呆），則直接按 Path 跳轉
        this.$router.push(path).catch((err) => {
          if (err.name !== 'NavigationDuplicated') console.error(err)
        })
      }
    },

    toggleJsonView () {
      this.isJsonVisible = !this.isJsonVisible
    },

    handleLogout () {
      this.authStore.logout()
      this.$router.push('/login')
    },

    syncGateway () {
      alert('Data validated against EU Battery Regulation Schema.')
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.font-inter {
  font-family: 'Inter', sans-serif;
}

.custom-scroll::-webkit-scrollbar {
  width: 4px;
}

.custom-scroll::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scroll::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

button {
  outline: none;
  cursor: pointer;
}
</style>
