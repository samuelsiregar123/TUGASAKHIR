<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'

defineProps({ title: String })

const page  = usePage()
const user  = computed(() => page.props.auth?.user)
const role  = computed(() => user.value?.role)
const flash = computed(() => page.props.flash)

// Auto-dismiss flash
const flashVisible = ref(false)
let flashTimer = null
watch(flash, (val) => {
    if (val?.success || val?.error) {
        flashVisible.value = true
        clearTimeout(flashTimer)
        flashTimer = setTimeout(() => { flashVisible.value = false }, 4000)
    }
}, { immediate: true })

// Mobile sidebar toggle
const sidebarOpen = ref(false)
function toggleSidebar() { sidebarOpen.value = !sidebarOpen.value }
function closeSidebar()  { sidebarOpen.value = false }

const menus = {
  admin: [
    { label: 'Dashboard',       href: '/admin/dashboard'   },
    { label: 'Kelola pengguna', href: '/admin/pengguna'    },
    { label: 'Audit Log',       href: '/admin/audit-log'   },
  ],
  auditee: [
    { label: 'Dashboard',       href: '/auditee/dashboard'       },
    { label: 'Pengajuan audit', href: '/auditee/pengajuan'       },
    { label: 'Kuesioner',       href: '/auditee/kuesioner'       },
    { label: 'Unduh LHAK',      href: '/auditee/lhak'            },
    { label: 'Tindak lanjut',   href: '/auditee/tindak-lanjut'   },
  ],
  ketua_tim: [
    { label: 'Dashboard',         href: '/ketua-tim/dashboard'       },
    { label: 'Pengajuan',         href: '/ketua-tim/pengajuan'       },
    { label: 'Audit plan',        href: '/ketua-tim/audit-plan'      },
    { label: 'Penilaian',         href: '/ketua-tim/penilaian'       },
    { label: 'Temuan',            href: '/ketua-tim/temuan'          },
    { label: 'Konklusi & LHAK',   href: '/ketua-tim/konklusi-lhak'  },
    { label: 'Tindak lanjut',     href: '/ketua-tim/tindak-lanjut'  },
  ],
  auditor: [
    { label: 'Dashboard',       href: '/auditor/dashboard'        },
    { label: 'Pemindaian',      href: '/auditor/pemindaian'       },
    { label: 'Penilaian',       href: '/auditor/penilaian'        },
    { label: 'Temuan',          href: '/auditor/temuan'           },
    { label: 'Tindak lanjut',   href: '/auditor/tindak-lanjut'   },
  ],
  supervisor: [
    { label: 'Dashboard',       href: '/supervisor/dashboard'     },
    { label: 'Daftar Audit',    href: '/supervisor/audit'         },
    { label: 'Persetujuan LHAK', href: '/supervisor/persetujuan' },
  ],
}

const navItems = computed(() => menus[role.value] || [])

const isActive = (href) => page.url.startsWith(href)

const logout = () => router.post(route('logout'))
</script>

<template>
  <div class="spbe-screen">
    <Head :title="title" />

    <!-- Mobile top bar -->
    <div class="mobile-topbar">
      <button class="hamburger" @click="toggleSidebar" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
      <span class="mobile-logo">SIASKA</span>
    </div>

    <!-- Overlay backdrop for mobile -->
    <div v-if="sidebarOpen" class="sidebar-overlay" @click="closeSidebar"></div>

    <!-- Sidebar -->
    <aside :class="['spbe-sidebar', { 'sidebar-open': sidebarOpen }]">
      <div class="spbe-logo">SIASKA</div>

      <nav>
        <Link
          v-for="item in navItems"
          :key="item.href"
          :href="item.href"
          :class="['spbe-nav-item', { active: isActive(item.href) }]"
          @click="closeSidebar"
        >
          {{ item.label }}
        </Link>
      </nav>

      <div class="spbe-nav-divider" style="margin-top:auto" />
      <Link
        href="/user/profile"
        :class="['spbe-nav-item', 'nav-profile', { active: isActive('/user/profile') }]"
        @click="closeSidebar"
      >
        Profil &amp; Keamanan
      </Link>
      <div style="padding:8px 14px;font-size:10px;color:#bbb;line-height:1.4">
        {{ user?.name }}<br>
        <span style="text-transform:capitalize">{{ role?.replace('_',' ') }}</span>
      </div>
      <button class="spbe-nav-logout" @click="logout">Keluar</button>
    </aside>

    <!-- Main content -->
    <main class="spbe-main">
      <!-- Flash toast -->
      <transition name="flash-fade">
        <div
          v-if="flashVisible && (flash?.success || flash?.error)"
          :class="['flash-toast', flash?.success ? 'flash-success' : 'flash-error']"
        >
          <span class="flash-icon">{{ flash?.success ? '✓' : '✕' }}</span>
          <span>{{ flash?.success || flash?.error }}</span>
          <button class="flash-close" @click="flashVisible = false">×</button>
        </div>
      </transition>

      <slot />
    </main>
  </div>
</template>

<style>
/* Global reset / base already provided by Jetstream — only add overrides here */
</style>

<style scoped>
/* ── Layout ── */
.spbe-screen {
  display: flex;
  min-height: 100vh;
  background: #f3f4f6;
}

/* ── Sidebar ── */
.spbe-sidebar {
  width: 200px;
  min-width: 200px;
  background: #1F4E79;
  display: flex;
  flex-direction: column;
  position: sticky;
  top: 0;
  height: 100vh;
  overflow-y: auto;
  z-index: 100;
  transition: transform 0.25s ease;
}
.spbe-logo {
  font-size: .95rem;
  font-weight: 800;
  color: #fff;
  padding: 18px 16px 14px;
  letter-spacing: .5px;
  border-bottom: 1px solid rgba(255,255,255,.12);
}
.spbe-nav-item {
  display: block;
  padding: 9px 16px;
  font-size: .82rem;
  color: rgba(255,255,255,.75);
  text-decoration: none;
  border-left: 3px solid transparent;
  transition: background .15s, color .15s;
  cursor: pointer;
}
.spbe-nav-item:hover { background: rgba(255,255,255,.08); color: #fff; }
.spbe-nav-item.active { background: rgba(255,255,255,.15); color: #fff; border-left-color: #fff; font-weight: 600; }
.nav-profile { font-size: .78rem; color: rgba(255,255,255,.55); }
.nav-profile.active { color: #fff; }
.spbe-nav-divider { border-top: 1px solid rgba(255,255,255,.12); margin: 6px 0; }
.spbe-nav-logout {
  margin: 6px 12px 14px;
  background: rgba(220,38,38,.25);
  color: #fca5a5;
  border: 1px solid rgba(220,38,38,.35);
  border-radius: 6px;
  padding: 7px 0;
  font-size: .8rem;
  font-weight: 600;
  cursor: pointer;
  width: calc(100% - 24px);
  transition: background .15s;
}
.spbe-nav-logout:hover { background: rgba(220,38,38,.45); }

/* ── Main ── */
.spbe-main {
  flex: 1;
  padding: 24px;
  overflow-x: hidden;
  min-width: 0;
}

/* ── Flash toast ── */
.flash-toast {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 11px 16px;
  border-radius: 8px;
  font-size: .85rem;
  font-weight: 500;
  margin-bottom: 16px;
  box-shadow: 0 2px 8px rgba(0,0,0,.12);
  position: relative;
}
.flash-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.flash-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.flash-icon    { font-size: 1rem; font-weight: 700; flex-shrink: 0; }
.flash-close   { margin-left: auto; background: none; border: none; font-size: 1.1rem; cursor: pointer; opacity: .6; padding: 0 2px; color: inherit; }
.flash-close:hover { opacity: 1; }

.flash-fade-enter-active { transition: opacity .3s, transform .3s; }
.flash-fade-leave-active { transition: opacity .3s, transform .3s; }
.flash-fade-enter-from  { opacity: 0; transform: translateY(-8px); }
.flash-fade-leave-to    { opacity: 0; transform: translateY(-8px); }

/* ── Mobile topbar (hidden on desktop) ── */
.mobile-topbar {
  display: none;
  position: fixed;
  top: 0; left: 0; right: 0;
  height: 52px;
  background: #1F4E79;
  align-items: center;
  padding: 0 16px;
  gap: 14px;
  z-index: 200;
  box-shadow: 0 1px 4px rgba(0,0,0,.2);
}
.mobile-logo { color: #fff; font-size: .9rem; font-weight: 800; letter-spacing: .4px; }
.hamburger {
  background: none; border: none; cursor: pointer;
  display: flex; flex-direction: column; gap: 4px; padding: 4px;
}
.hamburger span {
  display: block; width: 22px; height: 2px;
  background: #fff; border-radius: 2px;
}

/* ── Sidebar overlay (mobile) ── */
.sidebar-overlay {
  display: none;
  position: fixed; inset: 0;
  background: rgba(0,0,0,.5);
  z-index: 150;
}

/* ── Responsive breakpoints ── */
@media (max-width: 768px) {
  .mobile-topbar  { display: flex; }
  .sidebar-overlay { display: block; }

  .spbe-screen  { display: block; }
  .spbe-sidebar {
    position: fixed;
    top: 0; left: 0;
    height: 100vh;
    z-index: 160;
    transform: translateX(-100%);
  }
  .spbe-sidebar.sidebar-open { transform: translateX(0); }

  .spbe-main {
    padding: 72px 14px 24px; /* room for topbar */
  }
}

@media (min-width: 769px) and (max-width: 1024px) {
  .spbe-sidebar { width: 170px; min-width: 170px; }
  .spbe-main    { padding: 18px; }
}
</style>
