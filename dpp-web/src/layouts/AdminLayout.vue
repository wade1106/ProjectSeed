<template>
  <div class="min-h-screen flex overflow-hidden bg-slate-900">
    <aside class="w-72 sidebar-gradient border-r border-slate-800 flex flex-col shrink-0">
      <div class="p-6 border-b border-slate-800/50">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-slate-800 text-indigo-400 rounded-xl flex items-center justify-center border border-slate-700 shadow-inner">
            <i class="fa-solid fa-user-shield text-xl"></i>
          </div>
          <div>
            <h1 class="text-sm font-bold text-white tracking-widest uppercase">DPP Console</h1>
            <p class="text-[9px] text-slate-500 font-mono tracking-tighter">ADMIN_INTERFACE_v1</p>
          </div>
        </div>
      </div>

      <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
        <div class="menu-container">
          <div
            class="menu-group-title text-slate-300 font-bold uppercase tracking-wider cursor-pointer"
            @click="toggleGroup('customer')"
            :class="{ 'collapsed-group': collapsedGroups.customer }"
          >
            <div class="flex items-center gap-3">
              <i class="fa-solid fa-folder-tree text-xs text-indigo-400"></i>
              <span>客戶企業管理</span>
            </div>
            <i class="fa-solid fa-chevron-down text-[10px] chevron-icon transition-transform duration-300"></i>
          </div>
          <ul class="submenu space-y-1 mt-2 mb-4 transition-all duration-300 ease-in-out"
              :class="{ 'collapsed': collapsedGroups.customer, 'max-height-0 opacity-0': collapsedGroups.customer, 'max-height-[500px] opacity-100': !collapsedGroups.customer }"
          >
            <li>
              <router-link
                to="/admin/customer"
                class="submenu-item flex items-center gap-3 py-3 px-4 rounded-xl text-sm text-slate-400 transition-all duration-200"
                :class="{ 'active-menu': $route.path === '/admin/customer' }"
                @click="setActive('客戶公司維護')"
              >
                <span class="w-5 shrink-0"></span>
                <span>客戶公司維護</span>
              </router-link>
            </li>
            <li>
              <router-link
                to="/admin/customer-user"
                class="submenu-item flex items-center gap-3 py-3 px-4 rounded-xl text-sm text-slate-400 transition-all duration-200"
                :class="{ 'active-menu': $route.path === '/admin/customer-user' }"
                @click="setActive('公司帳號維護')"
              >
                <span class="w-5 shrink-0"></span>
                <span>公司帳號維護</span>
              </router-link>
            </li>
            <li>
              <router-link
                to="/admin/role-permission"
                class="submenu-item flex items-center gap-3 py-3 px-4 rounded-xl text-sm text-slate-400 transition-all duration-200"
                :class="{ 'active-menu': $route.path === '/admin/role-permission' }"
                @click="setActive('角色權限配置')"
              >
                <span class="w-5 shrink-0"></span>
                <span>角色權限配置</span>
              </router-link>
            </li>
            <li>
              <router-link
                to="/admin/customer-product"
                class="submenu-item flex items-center gap-3 py-3 px-4 rounded-xl text-sm text-slate-400 transition-all duration-200"
                :class="{ 'active-menu': $route.path === '/admin/customer-product' }"
                @click="setActive('公司產品授權')"
              >
                <span class="w-5 shrink-0"></span>
                <span>公司產品授權</span>
              </router-link>
            </li>
          </ul>
        </div>

        <div class="menu-container">
          <div
            class="menu-group-title text-slate-300 font-bold uppercase tracking-wider cursor-pointer"
            @click="toggleGroup('product')"
            :class="{ 'collapsed-group': collapsedGroups.product }"
          >
            <div class="flex items-center gap-3">
              <i class="fa-solid fa-box-archive text-xs text-indigo-400"></i>
              <span>產品內容管理</span>
            </div>
            <i class="fa-solid fa-chevron-down text-[10px] chevron-icon transition-transform duration-300"></i>
          </div>
          <ul class="submenu space-y-1 mt-2 mb-4 transition-all duration-300 ease-in-out"
              :class="{ 'collapsed': collapsedGroups.product, 'max-height-0 opacity-0': collapsedGroups.product, 'max-height-[500px] opacity-100': !collapsedGroups.product }"
          >
            <li>
              <router-link
                to="/admin/product-category"
                class="submenu-item flex items-center gap-3 py-3 px-4 rounded-xl text-sm text-slate-400 transition-all duration-200"
                :class="{ 'active-menu': $route.path === '/admin/product-category' }"
                @click="setActive('產品類別維護')"
              >
                <span class="w-5 shrink-0"></span>
                <span>產品類別維護</span>
              </router-link>
            </li>
          </ul>
        </div>

        <!--
        <div class="menu-container">
          <div
            class="menu-group-title text-slate-300 font-bold uppercase tracking-wider cursor-pointer"
            @click="toggleGroup('permission')"
            :class="{ 'collapsed-group': collapsedGroups.permission }"
          >
            <div class="flex items-center gap-3">
              <i class="fa-solid fa-fingerprint text-xs text-indigo-400"></i>
              <span>權限體系管理</span>
            </div>
            <i class="fa-solid fa-chevron-down text-[10px] chevron-icon transition-transform duration-300"></i>
          </div>
          <ul class="submenu space-y-1 mt-2 mb-4 transition-all duration-300 ease-in-out"
              :class="{ 'collapsed': collapsedGroups.permission, 'max-height-0 opacity-0': collapsedGroups.permission, 'max-height-[500px] opacity-100': !collapsedGroups.permission }"
          >
            <li>
              <router-link
                to="/admin/function-definition"
                class="submenu-item flex items-center gap-3 py-3 px-4 rounded-xl text-sm text-slate-400 transition-all duration-200"
                :class="{ 'active-menu': $route.path === '/admin/function-definition' }"
                @click="setActive('功能權限定義')"
              >
                <span class="w-5 shrink-0"></span>
                <span>功能權限定義</span>
              </router-link>
            </li>
          </ul>
        </div>
        -->

        <div class="menu-container">
          <div
            class="menu-group-title text-slate-300 font-bold uppercase tracking-wider cursor-pointer"
            @click="toggleGroup('system')"
            :class="{ 'collapsed-group': collapsedGroups.system }"
          >
            <div class="flex items-center gap-3">
              <i class="fa-solid fa-shield-halved text-xs text-indigo-400"></i>
              <span>系統安全管理</span>
            </div>
            <i class="fa-solid fa-chevron-down text-[10px] chevron-icon transition-transform duration-300"></i>
          </div>
          <ul class="submenu space-y-1 mt-2 mb-4 transition-all duration-300 ease-in-out"
              :class="{ 'collapsed': collapsedGroups.system, 'max-height-0 opacity-0': collapsedGroups.system, 'max-height-[500px] opacity-100': !collapsedGroups.system }"
          >
            <li>
              <router-link
                to="/admin/user-management"
                class="submenu-item flex items-center gap-3 py-3 px-4 rounded-xl text-sm text-slate-400 transition-all duration-200"
                :class="{ 'active-menu': $route.path === '/admin/user-management' }"
                @click="setActive('管理員帳號維護')"
              >
                <span class="w-5 shrink-0"></span>
                <span>管理員帳號維護</span>
              </router-link>
            </li>
          </ul>
        </div>
      </nav>
    </aside>

    <main class="flex-1 flex flex-col bg-slate-50 overflow-hidden">
      <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 shrink-0">
        <div class="flex items-center gap-3">
          <span class="text-slate-300">/</span>
          <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wider">{{ currentSection }}</h2>
          <span class="text-slate-300">/</span>
          <h2 class="text-sm font-bold text-slate-900 uppercase tracking-tighter">{{ currentPage }}</h2>
        </div>

        <div class="flex items-center gap-6">
          <div class="flex flex-col items-end">
            <span class="text-sm font-bold text-slate-700">{{ currentAdminDisplay }}</span>
          </div>
          <div class="w-10 h-10 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center text-indigo-400 shadow-lg">
            <i class="fa-solid fa-user-gear text-lg"></i>
          </div>
          <button
            @click="handleLogout"
            class="px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
          >
            <i class="fa-solid fa-right-from-bracket mr-1"></i>登出
          </button>
        </div>
      </header>

      <section class="flex-1 overflow-y-auto p-8 fade-in">
        <div class="max-w-7xl mx-auto">
          <router-view />
        </div>
      </section>

      <footer class="h-12 bg-white border-t border-slate-200 px-8 flex items-center justify-between shrink-0">
        <p class="text-xs text-slate-400 font-medium">
          © 2026 版權所有 / All Rights Reserved by , Ltd.
        </p>
        <div class="flex gap-4">
          <span class="text-xs text-slate-300 font-mono tracking-tighter uppercase">PHP_V8.5.1</span>
          <span class="text-xs text-slate-300 font-mono tracking-tighter uppercase">DB_MySQL_8.4.0</span>
        </div>
      </footer>
    </main>
  </div>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'AdminLayout',

  computed: {
    ...mapState(useAuthStore, ['userInfo']),
    currentAdminDisplay () {
      // 顯示格式: name (account)
      const name = this.userInfo?.name || 'System Administrator'
      const account = this.userInfo?.account || 'admin'
      return `${name} (${account})`
    }
  },

  data () {
    return {
      currentSection: '客戶企業管理',
      currentPage: '客戶公司維護',
      collapsedGroups: {
        customer: false,
        product: false,
        permission: false,
        system: false
      }
    }
  },

  methods: {
    toggleGroup (groupName) {
      this.collapsedGroups[groupName] = !this.collapsedGroups[groupName]
    },

    setActive (pageName) {
      this.currentPage = pageName
      // 更新區域名稱
      if (['客戶公司維護', '公司帳號維護', '公司產品授權'].includes(pageName)) {
        this.currentSection = '客戶企業管理'
      } else if (['產品類別維護'].includes(pageName)) {
        this.currentSection = '產品內容管理'
      } else if (['角色權限配置', '功能權限定義'].includes(pageName)) {
        this.currentSection = '權限體系管理'
      } else if (['管理員帳號維護'].includes(pageName)) {
        this.currentSection = '系統安全管理'
      }
    },

    handleLogout () {
      const authStore = useAuthStore()
      authStore.logout()
      this.$router.push({ name: 'console' })
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

body {
  font-family: 'Inter', sans-serif;
}

.sidebar-gradient {
  background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
}

.active-menu {
  background: #4f46e5 !important;
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
  color: white !important;
}

.active-menu i { color: white !important; }

.menu-group-title {
  font-size: 15px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  border-radius: 12px;
  transition: all 0.2s;
}

.menu-group-title:hover {
  background: rgba(255, 255, 255, 0.08);
  color: white;
}

.submenu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 12px;
  font-size: 14px;
  color: #94a3b8;
  transition: all 0.2s ease;
  text-decoration: none;
}

.submenu-item:hover:not(.active-menu) {
  background: rgba(255, 255, 255, 0.05);
  color: white;
}

.submenu {
  max-height: 500px;
  overflow: hidden;
  transition: max-height 0.3s ease-in-out, opacity 0.3s;
}

.submenu.collapsed {
  max-height: 0;
  opacity: 0;
  margin-bottom: 0 !important;
}

.chevron-icon { transition: transform 0.3s; }
.collapsed-group .chevron-icon { transform: rotate(-90deg); }

.fade-in { animation: fadeIn 0.4s ease-out forwards; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

::-webkit-scrollbar { width: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }

.router-link-active:not(.active-menu) {
  background: rgba(255, 255, 255, 0.05);
  color: white;
}
</style>
