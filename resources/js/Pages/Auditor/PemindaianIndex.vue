<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    plans: { type: Array, default: () => [] },
})

const search     = ref('')
const filterScan = ref('')
const filterTahun= ref('')

const tahunList = computed(() => {
    const years = new Set()
    props.plans.forEach(p => {
        if (p.waktu_mulai) years.add(p.waktu_mulai.split(' ').pop())
    })
    return [...years].sort().reverse()
})

const filtered = computed(() => {
    return props.plans.filter(p => {
        const q = search.value.toLowerCase()
        const matchSearch = !q ||
            p.instansi.toLowerCase().includes(q) ||
            p.url_target.toLowerCase().includes(q)
        const matchScan  = !filterScan.value  || p.scan_status === filterScan.value
        const matchTahun = !filterTahun.value || (p.waktu_mulai || '').includes(filterTahun.value)
        return matchSearch && matchScan && matchTahun
    })
})

const scanStatusLabel = {
    belum    : 'Belum dipindai',
    berjalan : 'Sedang berjalan',
    selesai  : 'Selesai',
    sebagian : 'Sebagian selesai',
}
const scanStatusClass = {
    belum    : 'badge-gray',
    berjalan : 'badge-blue',
    selesai  : 'badge-green',
    sebagian : 'badge-yellow',
}

function buka(planId) {
    router.visit(`/auditor/pemindaian/${planId}`)
}
</script>

<template>
    <AppLayout title="Pemindaian Keamanan">
        <template #header>
            <h2 class="page-title">Pemindaian Keamanan</h2>
        </template>

        <div class="content-card">
            <!-- Filter bar -->
            <div class="filter-bar">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari instansi atau URL..."
                    class="input-search"
                />
                <select v-model="filterScan" class="input-select">
                    <option value="">Semua Status Scan</option>
                    <option value="belum">Belum dipindai</option>
                    <option value="berjalan">Sedang berjalan</option>
                    <option value="selesai">Selesai</option>
                    <option value="sebagian">Sebagian selesai</option>
                </select>
                <select v-model="filterTahun" class="input-select">
                    <option value="">Semua Tahun</option>
                    <option v-for="t in tahunList" :key="t" :value="t">{{ t }}</option>
                </select>
            </div>

            <!-- Table -->
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Instansi</th>
                            <th>URL Aplikasi</th>
                            <th>Periode Audit</th>
                            <th>Status Scan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filtered.length === 0">
                            <td colspan="6" class="empty-row">Tidak ada data.</td>
                        </tr>
                        <tr v-for="(plan, i) in filtered" :key="plan.id">
                            <td class="text-center">{{ i + 1 }}</td>
                            <td class="font-medium">{{ plan.instansi }}</td>
                            <td class="text-sm text-gray-600 mono-sm">{{ plan.url_target }}</td>
                            <td class="text-sm">{{ plan.waktu_mulai }} — {{ plan.waktu_selesai }}</td>
                            <td>
                                <span :class="['badge', scanStatusClass[plan.scan_status] ?? 'badge-gray']">
                                    {{ scanStatusLabel[plan.scan_status] ?? plan.scan_status }}
                                </span>
                            </td>
                            <td>
                                <button class="btn-primary btn-sm" @click="buka(plan.id)">
                                    Buka
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.content-card { background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
.page-title   { font-size: 1.25rem; font-weight: 700; color: var(--navy, #1F4E79); }

.filter-bar { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
.input-search { flex: 1; min-width: 200px; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: .875rem; }
.input-select { padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: .875rem; background: #fff; }

.table-wrapper { overflow-x: auto; }
.data-table   { width: 100%; border-collapse: collapse; font-size: .875rem; }
.data-table th { background: #f8fafc; padding: 10px 14px; text-align: left; font-weight: 600; border-bottom: 2px solid #e5e7eb; color: #374151; }
.data-table td { padding: 10px 14px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.data-table tr:hover td { background: #f9fafb; }
.empty-row    { text-align: center; color: #9ca3af; padding: 32px !important; }

.badge        { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: .75rem; font-weight: 600; }
.badge-gray   { background: #f3f4f6; color: #6b7280; }
.badge-blue   { background: #dbeafe; color: #1d4ed8; }
.badge-green  { background: #dcfce7; color: #166534; }
.badge-yellow { background: #fef9c3; color: #854d0e; }

.btn-primary  { background: var(--navy, #1F4E79); color: #fff; border: none; border-radius: 6px; padding: 7px 16px; font-size: .875rem; cursor: pointer; font-weight: 500; }
.btn-primary:hover { opacity: .85; }
.btn-sm { padding: 5px 12px; font-size: .8rem; }

.font-medium { font-weight: 500; }
.text-sm     { font-size: .875rem; }
.text-center { text-align: center; }
.text-gray-600 { color: #4b5563; }
.mono-sm     { font-family: monospace; font-size: .8rem; }
</style>
