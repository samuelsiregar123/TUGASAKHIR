<script setup>
import { computed } from 'vue'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
    plans: { type: Array, default: () => [] },
})

const KONKLUSI_CLASS = {
    memadai           : 'kchip-ok',
    perlu_peningkatan : 'kchip-warn',
    tidak_memadai     : 'kchip-bad',
}
const KONKLUSI_LABEL = {
    memadai           : 'Memadai',
    perlu_peningkatan : 'Perlu Peningkatan',
    tidak_memadai     : 'Tidak Memadai',
}

function unduh(planId) {
    window.location.href = `/auditee/lhak/${planId}/download`
}
</script>

<template>
    <SidebarLayout title="Unduh LHAK">
        <p class="page-header">Laporan Hasil Audit Keamanan</p>
        <p class="page-sub">Unduh LHAK yang telah digenerate oleh ketua tim</p>

        <div v-if="plans.length === 0" class="empty-state">Belum ada LHAK yang tersedia.</div>

        <div v-for="plan in plans" :key="plan.id" class="lhak-card">
            <div class="lhak-info">
                <div class="lhak-instansi">{{ plan.instansi }}</div>
                <div class="lhak-app">{{ plan.aplikasi }}</div>
                <div class="lhak-meta">{{ plan.waktu_mulai }}</div>
            </div>
            <div class="lhak-right">
                <span v-if="plan.konklusi_keseluruhan"
                    :class="['kchip', KONKLUSI_CLASS[plan.konklusi_keseluruhan]]">
                    {{ KONKLUSI_LABEL[plan.konklusi_keseluruhan] }}
                </span>
                <span v-else class="kchip-none">Belum ada konklusi</span>
                <button v-if="plan.has_lhak" class="btn-unduh" @click="unduh(plan.id)">
                    ⬇ Unduh PDF
                </button>
                <span v-else class="badge-pending">LHAK belum tersedia</span>
            </div>
        </div>
    </SidebarLayout>
</template>

<style scoped>
.page-header  { font-size:1.1rem; font-weight:700; color:#1F4E79; margin-bottom:2px; }
.page-sub     { font-size:.8rem; color:#888; margin-bottom:16px; }
.empty-state  { text-align:center; color:#aaa; padding:40px; }
.lhak-card    { display:flex; justify-content:space-between; align-items:center; border:1px solid #e5e7eb; border-radius:8px; padding:12px 16px; margin-bottom:10px; flex-wrap:wrap; gap:8px; }
.lhak-info    {}
.lhak-instansi{ font-weight:700; color:#1F4E79; font-size:.9rem; }
.lhak-app     { font-size:.75rem; color:#666; }
.lhak-meta    { font-size:.72rem; color:#aaa; }
.lhak-right   { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.kchip        { font-size:.7rem; padding:2px 8px; border-radius:10px; font-weight:600; }
.kchip-ok     { background:#EAF3DE; color:#375623; }
.kchip-warn   { background:#FFF2CC; color:#7B6000; }
.kchip-bad    { background:#FCEBEB; color:#9b1c1c; }
.kchip-none   { color:#aaa; font-size:.75rem; }
.btn-unduh    { background:#1F4E79; color:#fff; border:none; border-radius:6px; padding:6px 14px; font-size:.8rem; font-weight:600; cursor:pointer; }
.btn-unduh:hover { background:#16396a; }
.badge-pending { font-size:.72rem; color:#aaa; }
</style>
