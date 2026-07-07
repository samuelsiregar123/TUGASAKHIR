<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
    logs     : { type: Object, default: () => ({ data: [] }) },
    users    : { type: Array,  default: () => [] },
    logNames : { type: Array,  default: () => [] },
    filters  : { type: Object, default: () => ({}) },
})

const LOG_LABEL = {
    pengguna      : 'Pengguna',
    pengajuan     : 'Pengajuan',
    penilaian     : 'Penilaian',
    konklusi      : 'Konklusi',
    lhak          : 'LHAK',
    tindak_lanjut : 'Tindak Lanjut',
}

const LOG_COLOR = {
    pengguna      : 'chip-blue',
    pengajuan     : 'chip-yellow',
    penilaian     : 'chip-green',
    konklusi      : 'chip-purple',
    lhak          : 'chip-navy',
    tindak_lanjut : 'chip-teal',
}

const form = reactive({
    user_id   : props.filters.user_id   ?? '',
    log_name  : props.filters.log_name  ?? '',
    date_from : props.filters.date_from ?? '',
    date_to   : props.filters.date_to   ?? '',
})

function applyFilter() {
    router.get('/admin/audit-log', form, { preserveState: true, replace: true })
}

function resetFilter() {
    form.user_id   = ''
    form.log_name  = ''
    form.date_from = ''
    form.date_to   = ''
    router.get('/admin/audit-log', {}, { preserveState: true, replace: true })
}

const expandedId = ref(null)
function toggleProps(id) {
    expandedId.value = expandedId.value === id ? null : id
}
</script>

<template>
    <SidebarLayout title="Audit Log">
        <div class="page-topbar">
            <div>
                <p class="page-header">Audit Log Sistem</p>
                <p class="page-sub">Riwayat aksi penting yang dilakukan pengguna</p>
            </div>
        </div>

        <!-- Filter -->
        <div class="filter-card">
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">Pengguna</label>
                    <select v-model="form.user_id" class="filter-input">
                        <option value="">Semua pengguna</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">
                            {{ u.name }} ({{ u.role?.replace('_', ' ') }})
                        </option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Kategori</label>
                    <select v-model="form.log_name" class="filter-input">
                        <option value="">Semua kategori</option>
                        <option v-for="name in logNames" :key="name" :value="name">
                            {{ LOG_LABEL[name] ?? name }}
                        </option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Dari tanggal</label>
                    <input v-model="form.date_from" type="date" class="filter-input">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Sampai tanggal</label>
                    <input v-model="form.date_to" type="date" class="filter-input">
                </div>
                <div class="filter-actions">
                    <button class="btn-filter" @click="applyFilter">Filter</button>
                    <button class="btn-reset" @click="resetFilter">Reset</button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:160px">Waktu</th>
                        <th style="width:90px">Kategori</th>
                        <th>Aksi</th>
                        <th style="width:160px">Oleh</th>
                        <th style="width:80px">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-if="logs.data.length === 0">
                        <tr><td colspan="5" class="empty-row">Belum ada log aktivitas.</td></tr>
                    </template>
                    <template v-for="log in logs.data" :key="log.id">
                        <tr>
                            <td class="text-xs mono">{{ log.created_at }}</td>
                            <td>
                                <span :class="['chip', LOG_COLOR[log.log_name] ?? 'chip-gray']">
                                    {{ LOG_LABEL[log.log_name] ?? log.log_name }}
                                </span>
                            </td>
                            <td class="log-desc">{{ log.description }}</td>
                            <td>
                                <span class="causer-name">{{ log.causer_name }}</span><br>
                                <span class="causer-role">{{ log.causer_role?.replace('_', ' ') }}</span>
                            </td>
                            <td>
                                <button
                                    v-if="Object.keys(log.properties).length > 0"
                                    class="btn-detail"
                                    @click="toggleProps(log.id)"
                                >
                                    {{ expandedId === log.id ? '▲ Tutup' : '▼ Lihat' }}
                                </button>
                                <span v-else class="text-muted">—</span>
                            </td>
                        </tr>
                        <tr v-if="expandedId === log.id" class="props-row">
                            <td colspan="5">
                                <pre class="props-pre">{{ JSON.stringify(log.properties, null, 2) }}</pre>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="logs.last_page > 1" class="pagination">
            <button
                v-for="page in logs.last_page"
                :key="page"
                :class="['page-btn', { active: page === logs.current_page }]"
                @click="router.get(logs.path, { ...filters, page }, { preserveState: true })"
            >{{ page }}</button>
        </div>
    </SidebarLayout>
</template>

<style scoped>
.page-topbar  { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; }
.page-header  { font-size:1.1rem; font-weight:700; color:#1F4E79; margin-bottom:2px; }
.page-sub     { font-size:.8rem; color:#888; }

.filter-card   { background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:14px 16px; margin-bottom:16px; }
.filter-row    { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; }
.filter-group  { display:flex; flex-direction:column; gap:3px; min-width:140px; }
.filter-label  { font-size:.72rem; font-weight:600; color:#374151; }
.filter-input  { height:32px; border:1px solid #d1d5db; border-radius:6px; padding:0 8px; font-size:.8rem; }
.filter-actions{ display:flex; gap:6px; }
.btn-filter    { background:#1F4E79; color:#fff; border:none; border-radius:6px; padding:6px 14px; font-size:.8rem; font-weight:600; cursor:pointer; }
.btn-filter:hover { background:#16396a; }
.btn-reset     { background:#fff; border:1px solid #d1d5db; border-radius:6px; padding:6px 12px; font-size:.8rem; cursor:pointer; color:#374151; }
.btn-reset:hover { background:#f3f4f6; }

.table-wrap   { overflow-x:auto; }
.data-table   { width:100%; border-collapse:collapse; font-size:.8rem; }
.data-table th { background:#1F4E79; color:#fff; padding:9px 12px; text-align:left; font-size:.75rem; }
.data-table td { padding:9px 12px; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
.data-table tr:hover td { background:#f8fbfe; }
.empty-row     { text-align:center; color:#aaa; padding:32px !important; }

.text-xs  { font-size:.72rem; color:#666; }
.mono     { font-family:monospace; }
.text-muted { color:#bbb; font-size:.75rem; }

.log-desc   { color:#1f2937; line-height:1.4; }
.causer-name { font-size:.8rem; font-weight:600; color:#1F4E79; }
.causer-role { font-size:.7rem; color:#888; text-transform:capitalize; }

.chip         { font-size:.7rem; padding:2px 8px; border-radius:8px; font-weight:600; }
.chip-blue    { background:#dbeafe; color:#1d4ed8; }
.chip-yellow  { background:#fef9c3; color:#854d0e; }
.chip-green   { background:#dcfce7; color:#166534; }
.chip-purple  { background:#ede9fe; color:#6d28d9; }
.chip-navy    { background:#e0f2fe; color:#0369a1; }
.chip-teal    { background:#ccfbf1; color:#0f766e; }
.chip-gray    { background:#f3f4f6; color:#6b7280; }

.btn-detail  { background:none; border:none; color:#1F4E79; font-size:.75rem; cursor:pointer; text-decoration:underline; }

.props-row td { background:#f8fafc; padding:8px 12px !important; }
.props-pre    { font-size:.72rem; font-family:monospace; color:#374151; margin:0; white-space:pre-wrap; word-break:break-all; }

.pagination  { display:flex; gap:4px; margin-top:14px; flex-wrap:wrap; }
.page-btn    { padding:5px 10px; border:1px solid #d1d5db; border-radius:5px; background:#fff; font-size:.78rem; cursor:pointer; }
.page-btn.active { background:#1F4E79; color:#fff; border-color:#1F4E79; }
.page-btn:hover:not(.active) { background:#f3f4f6; }
</style>
