<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
    plans: { type: Array, default: () => [] },
})

const search      = ref('')
const filterStatus= ref('')
const filterTahun = ref('')

const tahunList = computed(() => {
    const years = new Set()
    props.plans.forEach(p => { if (p.tahun) years.add(p.tahun) })
    return [...years].sort().reverse()
})

const STATUS_OPTIONS = [
    { value: 'pengisian',  label: 'Pengisian' },
    { value: 'penilaian',  label: 'Penilaian' },
    { value: 'siap_lhak',  label: 'Siap LHAK' },
    { value: 'selesai',    label: 'Selesai' },
]

const filtered = computed(() => {
    return props.plans.filter(p => {
        const q = search.value.toLowerCase()
        const matchSearch = !q || p.instansi.toLowerCase().includes(q) || (p.aplikasi || '').toLowerCase().includes(q)
        const matchStatus = !filterStatus.value || p.status_pengisian === filterStatus.value
        const matchTahun  = !filterTahun.value  || p.tahun === filterTahun.value
        return matchSearch && matchStatus && matchTahun
    })
})

const statusChip = {
    pengisian : { cls: 'chip-blue',   label: 'Pengisian' },
    penilaian : { cls: 'chip-yellow', label: 'Penilaian' },
    siap_lhak : { cls: 'chip-green',  label: 'Siap LHAK' },
    selesai   : { cls: 'chip-gray',   label: 'Selesai' },
}
const konklusiChip = {
    memadai           : { cls: 'kchip-ok',   label: 'Memadai' },
    perlu_peningkatan : { cls: 'kchip-warn', label: 'Perlu Peningkatan' },
    tidak_memadai     : { cls: 'kchip-bad',  label: 'Tidak Memadai' },
}

function canBuka(plan) {
    return ['siap_lhak', 'selesai'].includes(plan.status_pengisian) ||
           plan.konklusi_keseluruhan !== null
}

function buka(planId) {
    router.visit(`/ketua-tim/konklusi-lhak/${planId}`)
}
</script>

<template>
    <SidebarLayout title="Konklusi &amp; LHAK">
        <p class="page-header">Konklusi &amp; Laporan Hasil Audit</p>
        <p class="page-sub">Daftar instansi yang sedang dan telah diaudit</p>

        <!-- Toolbar -->
        <div class="toolbar">
            <input v-model="search" class="search-input" placeholder="🔍 Cari instansi atau aplikasi...">
            <select v-model="filterStatus" class="filter-select">
                <option value="">Status: Semua</option>
                <option v-for="s in STATUS_OPTIONS" :key="s.value" :value="s.value">{{ s.label }}</option>
            </select>
            <select v-model="filterTahun" class="filter-select">
                <option value="">Tahun: Semua</option>
                <option v-for="y in tahunList" :key="y" :value="y">{{ y }}</option>
            </select>
        </div>

        <!-- Table -->
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:5%">No</th>
                        <th style="width:28%">Instansi</th>
                        <th style="width:24%">Aplikasi</th>
                        <th style="width:14%; text-align:center">Status</th>
                        <th style="width:18%; text-align:center">Konklusi</th>
                        <th style="width:11%; text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(plan, i) in filtered" :key="plan.id">
                        <td>{{ i + 1 }}</td>
                        <td class="td-instansi">{{ plan.instansi }}</td>
                        <td class="td-app">{{ plan.aplikasi }}</td>
                        <td style="text-align:center">
                            <span :class="['chip', statusChip[plan.status_pengisian]?.cls ?? 'chip-gray']">
                                {{ statusChip[plan.status_pengisian]?.label ?? plan.status_pengisian }}
                            </span>
                        </td>
                        <td style="text-align:center">
                            <span v-if="plan.konklusi_keseluruhan" :class="['kchip', konklusiChip[plan.konklusi_keseluruhan]?.cls]">
                                {{ konklusiChip[plan.konklusi_keseluruhan]?.label }}
                            </span>
                            <span v-else class="kchip-none">— belum</span>
                        </td>
                        <td style="text-align:center">
                            <button v-if="canBuka(plan)" class="btn-link" @click="buka(plan.id)">Buka</button>
                            <span v-else class="btn-link-off">Belum siap</span>
                        </td>
                    </tr>
                    <tr v-if="filtered.length === 0">
                        <td colspan="6" style="text-align:center; color:#aaa; padding:24px">Tidak ada data</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </SidebarLayout>
</template>

<style scoped>
.page-header { font-size:1.1rem; font-weight:700; color:#1F4E79; margin-bottom:2px; }
.page-sub    { font-size:.8rem; color:#888; margin-bottom:16px; }
.toolbar     { display:flex; gap:8px; margin-bottom:12px; flex-wrap:wrap; }
.search-input{ flex:1; min-width:180px; height:32px; border:1px solid #ddd; border-radius:6px; padding:0 10px; font-size:.8rem; }
.filter-select{ height:32px; border:1px solid #ddd; border-radius:6px; padding:0 8px; font-size:.8rem; background:#fff; }
.table-wrap  { overflow-x:auto; }
.data-table  { width:100%; border-collapse:collapse; font-size:.8rem; }
.data-table th { background:#1F4E79; color:#fff; padding:8px; text-align:left; font-size:.75rem; }
.data-table td { padding:9px 8px; border-bottom:1px solid #eee; vertical-align:middle; }
.data-table tr:hover td { background:#f8fbfe; }
.td-instansi { font-weight:600; color:#1F4E79; }
.td-app      { font-size:.75rem; color:#666; }

.chip        { font-size:.7rem; padding:2px 8px; border-radius:10px; font-weight:600; display:inline-block; }
.chip-blue   { background:#E6F1FB; color:#1F4E79; }
.chip-yellow { background:#FFF2CC; color:#7B6000; }
.chip-green  { background:#EAF3DE; color:#375623; }
.chip-gray   { background:#eee;    color:#666; }

.kchip       { font-size:.7rem; padding:2px 7px; border-radius:10px; font-weight:600; display:inline-block; }
.kchip-ok    { background:#EAF3DE; color:#375623; }
.kchip-warn  { background:#FFF2CC; color:#7B6000; }
.kchip-bad   { background:#FCEBEB; color:#9b1c1c; }
.kchip-none  { color:#bbb; font-size:.75rem; }

.btn-link    { background:none; border:none; color:#1F4E79; font-weight:600; font-size:.75rem; text-decoration:underline; cursor:pointer; }
.btn-link:hover { color:#16396a; }
.btn-link-off{ color:#bbb; font-size:.75rem; }
</style>
