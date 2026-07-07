<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
    plan     : { type: Object, required: true },
    result   : { type: Object, default: null },
    approval : { type: Object, default: null },
})

const result   = reactive(props.result ? { ...props.result } : {})
const loading  = ref(false)
const genLoading = ref(false)
const message  = ref('')
const msgType  = ref('') // 'ok' | 'err'

const KONKLUSI_CLASS = {
    memadai           : 'k-ok',
    perlu_peningkatan : 'k-warn',
    tidak_memadai     : 'k-bad',
}
const KONKLUSI_LABEL = {
    memadai           : 'Memadai',
    perlu_peningkatan : 'Perlu Peningkatan',
    tidak_memadai     : 'Tidak Memadai',
}
function fmt(v) {
    if (v === null || v === undefined) return '—'
    return (v * 100).toFixed(1) + '%'
}

async function hitungUlang() {
    loading.value = true
    message.value = ''
    try {
        const res = await axios.post(`/ketua-tim/konklusi-lhak/${props.plan.id}/hitung`)
        if (res.data.ok) {
            Object.assign(result, res.data.result)
            message.value = 'Konklusi berhasil dihitung.'
            msgType.value  = 'ok'
        } else {
            message.value = res.data.message || 'Gagal menghitung.'
            msgType.value  = 'err'
        }
    } catch (e) {
        message.value = e.response?.data?.message || 'Terjadi kesalahan.'
        msgType.value  = 'err'
    } finally {
        loading.value = false
    }
}

async function generateLhak() {
    genLoading.value = true
    message.value = ''
    try {
        const res = await axios.post(`/ketua-tim/konklusi-lhak/${props.plan.id}/generate`)
        if (res.data.ok) {
            result.lhak_url = res.data.lhak_url
            message.value = 'LHAK berhasil digenerate.'
            msgType.value  = 'ok'
        } else {
            message.value = res.data.message || 'Gagal generate.'
            msgType.value  = 'err'
        }
    } catch (e) {
        message.value = e.response?.data?.message || 'Terjadi kesalahan.'
        msgType.value  = 'err'
    } finally {
        genLoading.value = false
    }
}

function unduhLhak() {
    window.location.href = `/ketua-tim/konklusi-lhak/${props.plan.id}/download`
}

const ajukanLoading = ref(false)
function ajukan() {
    ajukanLoading.value = true
    router.post(`/ketua-tim/konklusi-lhak/${props.plan.id}/ajukan`, {}, {
        onFinish: () => { ajukanLoading.value = false },
    })
}
</script>

<template>
    <SidebarLayout title="Konklusi &amp; LHAK">
        <div class="back-row">
            <button class="btn-back" @click="router.visit('/ketua-tim/konklusi-lhak')">← Kembali</button>
        </div>

        <p class="page-header">Konklusi &amp; Laporan Hasil Audit</p>
        <p class="page-sub">{{ plan.instansi }} — {{ plan.aplikasi }}</p>

        <div v-if="message" :class="['alert', msgType === 'ok' ? 'alert-ok' : 'alert-err']">{{ message }}</div>

        <!-- BAGIAN 1: REKAP KONKLUSI -->
        <div class="section-label">1. Rekap Konklusi per Bagian</div>

        <div class="kgrid">
            <div :class="['kcard', KONKLUSI_CLASS[result.konklusi_tk] ?? '']">
                <div class="kt">Tata Kelola (TK)</div>
                <div class="kv">{{ KONKLUSI_LABEL[result.konklusi_tk] ?? '—' }}</div>
                <div class="kd">EDK {{ fmt(result.nilai_edk_tk) }} · EIK {{ fmt(result.nilai_eik_tk) }} · EFK {{ fmt(result.nilai_efk_tk) }}</div>
            </div>
            <div :class="['kcard', KONKLUSI_CLASS[result.konklusi_mk] ?? '']">
                <div class="kt">Manajemen Keamanan (MK)</div>
                <div class="kv">{{ KONKLUSI_LABEL[result.konklusi_mk] ?? '—' }}</div>
                <div class="kd">EDK {{ fmt(result.nilai_edk_mk) }} · EIK {{ fmt(result.nilai_eik_mk) }} · EFK {{ fmt(result.nilai_efk_mk) }}</div>
            </div>
            <div :class="['kcard', KONKLUSI_CLASS[result.konklusi_fk] ?? '']">
                <div class="kt">Fungsionalitas Keamanan (FK)</div>
                <div class="kv">{{ KONKLUSI_LABEL[result.konklusi_fk] ?? '—' }}</div>
                <div class="kd">EDK {{ fmt(result.nilai_edk_fk) }} · EIK {{ fmt(result.nilai_eik_fk) }} · EFK {{ fmt(result.nilai_efk_fk) }}</div>
            </div>
        </div>

        <div class="overall-box" v-if="result.konklusi_keseluruhan">
            Konklusi Keseluruhan:
            <strong :class="'text-' + (KONKLUSI_CLASS[result.konklusi_keseluruhan] ?? '')">
                {{ KONKLUSI_LABEL[result.konklusi_keseluruhan] }}
            </strong>
            — berdasarkan kombinasi hasil 3 bagian melalui matriks konklusi.
        </div>
        <div class="overall-box overall-empty" v-else>
            Konklusi belum dihitung. Klik tombol di bawah untuk menghitung.
        </div>

        <div class="btn-row">
            <button class="btn-ghost" :disabled="loading" @click="hitungUlang">
                {{ loading ? 'Menghitung...' : 'Hitung Ulang Konklusi' }}
            </button>
        </div>
        <p class="hint">Konklusi otomatis dihitung dari nilai EDK/EIK/EFK seluruh butir yang sudah dinilai auditor.</p>

        <hr class="divider">

        <!-- BAGIAN 2: GENERATE LHAK -->
        <div class="section-label">2. Laporan Hasil Audit Keamanan (LHAK)</div>

        <div class="btn-row">
            <button class="btn-primary" :disabled="genLoading || !result.konklusi_keseluruhan" @click="generateLhak">
                {{ genLoading ? 'Generating...' : 'Generate LHAK (PDF)' }}
            </button>
        </div>
        <p class="hint">Membuat dokumen PDF 7 halaman berisi cover, ringkasan, grafik, detail penilaian, temuan, dan tanda tangan.</p>

        <!-- PDF Preview area -->
        <div v-if="result.lhak_url" class="pdf-ready">
            <div class="pdf-icon">📄</div>
            <div class="pdf-name">LHAK_{{ plan.instansi }}_{{ plan.id }}.pdf</div>
            <div style="font-size:.75rem; color:#888">PDF siap diunduh</div>
        </div>
        <div v-else class="pdf-empty">
            <div class="pdf-icon">📄</div>
            <div>Preview LHAK akan muncul di sini setelah di-generate</div>
        </div>

        <div v-if="result.lhak_url" class="btn-row" style="margin-top:10px">
            <a :href="result.lhak_url" target="_blank" class="btn-ghost">Lihat Preview</a>
            <button class="btn-primary" @click="unduhLhak">Unduh PDF</button>
        </div>

        <hr class="divider">

        <!-- BAGIAN 3: PENGAJUAN KE SUPERVISOR -->
        <div class="section-label">3. Pengajuan ke Supervisor</div>

        <!-- Belum ada LHAK -->
        <div v-if="!result.lhak_url" class="approval-box approval-empty">
            Generate LHAK terlebih dahulu sebelum dapat diajukan ke Supervisor.
        </div>

        <!-- Belum pernah diajukan -->
        <template v-else-if="!approval">
            <p class="hint">LHAK siap diajukan ke Supervisor untuk mendapat persetujuan.</p>
            <div class="btn-row">
                <button class="btn-primary" :disabled="ajukanLoading" @click="ajukan">
                    {{ ajukanLoading ? 'Mengajukan...' : 'Ajukan ke Supervisor' }}
                </button>
            </div>
        </template>

        <!-- Menunggu persetujuan -->
        <div v-else-if="approval.status === 'pending'" class="approval-box approval-pending">
            <span class="approval-icon">⏳</span>
            <div>
                <div class="approval-title">Menunggu Persetujuan Supervisor</div>
                <div class="approval-sub">LHAK sudah diajukan dan sedang ditinjau oleh Supervisor.</div>
            </div>
        </div>

        <!-- Disetujui -->
        <div v-else-if="approval.status === 'disetujui'" class="approval-box approval-ok">
            <span class="approval-icon">✅</span>
            <div>
                <div class="approval-title">LHAK Disetujui oleh Supervisor</div>
                <div class="approval-sub" v-if="approval.reviewer">
                    Ditinjau oleh {{ approval.reviewer }}
                    <span v-if="approval.reviewed_at"> pada {{ approval.reviewed_at }}</span>
                </div>
            </div>
        </div>

        <!-- Ditolak -->
        <template v-else-if="approval.status === 'ditolak'">
            <div class="approval-box approval-err">
                <span class="approval-icon">❌</span>
                <div>
                    <div class="approval-title">LHAK Ditolak oleh Supervisor</div>
                    <div class="approval-sub" v-if="approval.catatan">
                        Catatan: {{ approval.catatan }}
                    </div>
                    <div class="approval-sub" v-if="approval.reviewed_at">
                        {{ approval.reviewed_at }}
                    </div>
                </div>
            </div>
            <p class="hint" style="margin-top:8px">Generate ulang LHAK jika ada perbaikan, lalu ajukan kembali.</p>
            <div class="btn-row">
                <button class="btn-primary" :disabled="ajukanLoading" @click="ajukan">
                    {{ ajukanLoading ? 'Mengajukan...' : 'Ajukan Kembali ke Supervisor' }}
                </button>
            </div>
        </template>
    </SidebarLayout>
</template>

<style scoped>
.back-row    { margin-bottom:12px; }
.btn-back    { background:none; border:1px solid #d1d5db; border-radius:6px; padding:5px 12px; cursor:pointer; font-size:.8rem; color:#374151; }
.btn-back:hover { background:#f9fafb; }
.page-header { font-size:1.1rem; font-weight:700; color:#1F4E79; margin-bottom:2px; }
.page-sub    { font-size:.8rem; color:#888; margin-bottom:16px; }
.section-label { font-size:.7rem; font-weight:700; color:#1F4E79; text-transform:uppercase; letter-spacing:.5px; margin:16px 0 8px; }

.alert      { padding:8px 12px; border-radius:6px; font-size:.8rem; margin-bottom:12px; }
.alert-ok   { background:#EAF3DE; color:#375623; border:1px solid #cfe3b8; }
.alert-err  { background:#FCEBEB; color:#9b1c1c; border:1px solid #f0c0c0; }

.kgrid      { display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:10px; }
.kcard      { border-radius:6px; padding:12px; border:1px solid #e5e5e5; }
.kt         { font-size:.7rem; color:#888; margin-bottom:4px; }
.kv         { font-size:.9rem; font-weight:700; margin-bottom:6px; }
.kd         { font-size:.7rem; color:#666; }
.k-ok       { background:#EAF3DE; border-color:#cfe3b8; }
.k-ok .kv   { color:#375623; }
.k-warn     { background:#FFF2CC; border-color:#f0e0a0; }
.k-warn .kv { color:#7B6000; }
.k-bad      { background:#FCEBEB; border-color:#f0c0c0; }
.k-bad .kv  { color:#9b1c1c; }

.overall-box   { background:#E6F1FB; border:1px solid #c5dcf0; border-radius:6px; padding:10px 12px; margin-bottom:14px; font-size:.8rem; }
.overall-empty { background:#f5f5f4; border-color:#ddd; color:#999; }
.text-k-ok     { color:#375623; }
.text-k-warn   { color:#7B6000; }
.text-k-bad    { color:#9b1c1c; }

.btn-row   { display:flex; gap:8px; margin:6px 0 4px; flex-wrap:wrap; }
.btn-ghost { background:#fff; border:1px solid #1F4E79; color:#1F4E79; border-radius:6px; padding:7px 16px; font-size:.8rem; font-weight:600; cursor:pointer; text-decoration:none; }
.btn-ghost:hover:not(:disabled) { background:#f0f6fc; }
.btn-primary { background:#1F4E79; color:#fff; border:none; border-radius:6px; padding:7px 16px; font-size:.8rem; font-weight:600; cursor:pointer; }
.btn-primary:hover:not(:disabled) { background:#16396a; }
.btn-primary:disabled, .btn-ghost:disabled { opacity:.5; cursor:not-allowed; }

.hint    { font-size:.72rem; color:#999; margin-top:3px; margin-bottom:4px; }
.divider { border:0; border-top:1px solid #eee; margin:16px 0; }

.pdf-empty, .pdf-ready {
    border:1px dashed #bbb; border-radius:6px; background:#fafafa;
    min-height:100px; display:flex; flex-direction:column; align-items:center;
    justify-content:center; color:#999; font-size:.8rem; margin-top:6px; gap:4px; padding:16px;
}
.pdf-ready { background:#f0f9f0; border-color:#cfe3b8; color:#375623; }
.pdf-icon  { font-size:2rem; }
.pdf-name  { font-size:.8rem; font-weight:600; }

.approval-box {
    display:flex; align-items:flex-start; gap:10px;
    border-radius:6px; padding:12px 14px; font-size:.8rem; margin-top:6px;
}
.approval-empty  { background:#f5f5f4; border:1px solid #ddd; color:#999; }
.approval-pending { background:#FFF9E6; border:1px solid #f0e0a0; color:#7B6000; }
.approval-ok      { background:#EAF3DE; border:1px solid #cfe3b8; color:#375623; }
.approval-err     { background:#FCEBEB; border:1px solid #f0c0c0; color:#9b1c1c; }
.approval-icon    { font-size:1.3rem; flex-shrink:0; }
.approval-title   { font-weight:700; margin-bottom:2px; }
.approval-sub     { font-size:.75rem; opacity:.85; }
</style>
