<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
    plan       : { type: Object, required: true },
    temuan     : { type: Array,  default: () => [] },
    isKetuaTim : { type: Boolean, default: false },
    baseUrlProp: { type: String,  default: null },
})

const page    = usePage()
const role    = computed(() => page.props.auth?.user?.role)
const baseUrl = computed(() => props.baseUrlProp ?? (props.isKetuaTim ? '/ketua-tim' : '/auditee'))

const temuanList    = reactive([...props.temuan])
const openIds       = reactive({})
const pesanForms    = reactive({})
const filesMap      = reactive({})  // temuan id -> File[]
const loadingKirim  = reactive({})
const loadingTandai = reactive({})
const pollTimer     = ref(null)

// Deadline editing — khusus auditee
const deadlineEditing  = reactive({})
const deadlineForms    = reactive({})
const loadingDeadline  = reactive({})

function toggle(id) {
    openIds[id] = !openIds[id]
}

function initForms() {
    temuanList.forEach(t => {
        if (pesanForms[t.id] === undefined) pesanForms[t.id] = ''
        if (loadingKirim[t.id] === undefined) loadingKirim[t.id] = false
        if (!filesMap[t.id]) filesMap[t.id] = []
        if (deadlineForms[t.id] === undefined) deadlineForms[t.id] = t.deadline ?? ''
        if (deadlineEditing[t.id] === undefined) deadlineEditing[t.id] = false
        if (loadingDeadline[t.id] === undefined) loadingDeadline[t.id] = false
    })
}
initForms()

function addFiles(temuanId, event) {
    const incoming = [...event.target.files]
    filesMap[temuanId] = [...(filesMap[temuanId] ?? []), ...incoming]
    event.target.value = ''
}

function removeFile(temuanId, idx) {
    filesMap[temuanId].splice(idx, 1)
}

function formatSize(bytes) {
    if (!bytes) return ''
    if (bytes < 1024)    return bytes + ' B'
    if (bytes < 1048576) return Math.round(bytes / 1024) + ' KB'
    return (bytes / 1048576).toFixed(1) + ' MB'
}

async function kirim(t) {
    const msg   = pesanForms[t.id]
    const files = filesMap[t.id] ?? []
    if (!msg?.trim()) return
    loadingKirim[t.id] = true
    try {
        let payload
        if (files.length > 0) {
            const fd = new FormData()
            fd.append('pesan', msg)
            files.forEach((f, i) => fd.append(`lampiran[${i}]`, f))
            payload = fd
        } else {
            payload = { pesan: msg }
        }
        const res = await axios.post(`${baseUrl.value}/tindak-lanjut/${t.id}/kirim`, payload)
        if (res.data.ok) {
            t.pesan.push(res.data.pesan)
            pesanForms[t.id]  = ''
            filesMap[t.id]    = []
        }
    } catch (e) {
        alert(e.response?.data?.message || 'Gagal mengirim pesan.')
    } finally {
        loadingKirim[t.id] = false
    }
}

async function tandaiSelesai(t) {
    if (!confirm('Tandai temuan ini sebagai selesai?')) return
    loadingTandai[t.id] = true
    try {
        await axios.post(`/ketua-tim/tindak-lanjut/${t.id}/selesai`)
        t.status_tindak_lanjut = 'selesai'
    } catch (e) {
        alert(e.response?.data?.message || 'Gagal menandai selesai.')
    } finally {
        loadingTandai[t.id] = false
    }
}

async function pollPesan() {
    for (const t of temuanList) {
        if (!openIds[t.id]) continue
        try {
            const res = await axios.get(`${baseUrl.value}/tindak-lanjut/${t.id}/pesan`)
            if (res.data.pesan.length !== t.pesan.length) {
                t.pesan.splice(0, t.pesan.length, ...res.data.pesan)
            }
            t.status_tindak_lanjut = res.data.status
        } catch {}
    }
}

onMounted(() => { pollTimer.value = setInterval(pollPesan, 10000) })
onUnmounted(() => { if (pollTimer.value) clearInterval(pollTimer.value) })

function openDeadlineEdit(t) {
    deadlineForms[t.id]   = t.deadline ?? ''
    deadlineEditing[t.id] = true
}

function cancelDeadlineEdit(t) {
    deadlineEditing[t.id] = false
}

async function saveDeadline(t) {
    loadingDeadline[t.id] = true
    try {
        const res = await axios.post(`/auditee/tindak-lanjut/${t.id}/deadline`, {
            deadline: deadlineForms[t.id],
        })
        if (res.data.ok) {
            t.deadline            = res.data.deadline
            deadlineEditing[t.id] = false
        }
    } catch (e) {
        alert(e.response?.data?.message || 'Gagal menyimpan target.')
    } finally {
        loadingDeadline[t.id] = false
    }
}

const RISIKO_CLASS = { tinggi:'badge-tinggi', sedang:'badge-sedang', rendah:'badge-rendah' }
const RISIKO_LABEL = { tinggi:'Tinggi', sedang:'Sedang', rendah:'Rendah' }

function deadlineInfo(deadline) {
    if (!deadline) return null
    const today = new Date(); today.setHours(0, 0, 0, 0)
    const due   = new Date(deadline)
    const diff  = Math.round((due - today) / 86400000)
    const label = due.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })

    let status, text, barPct
    if (diff > 14)      { status = 'ok';      text = `${diff} hari lagi`;      barPct = Math.max(5, Math.round((30 - diff) / 30 * 100)) }
    else if (diff > 3)  { status = 'warning'; text = `${diff} hari lagi`;      barPct = Math.round((30 - diff) / 30 * 100) }
    else if (diff >= 0) { status = 'urgent';  text = `${diff} hari lagi`;      barPct = 90 }
    else                { status = 'overdue'; text = `${-diff} hari terlewat`; barPct = 100 }

    return { label, text, status, barPct }
}
</script>

<template>
    <SidebarLayout title="Tindak Lanjut">
        <div class="back-row">
            <button class="btn-back" @click="router.visit(`${baseUrl}/tindak-lanjut`)">← Kembali</button>
        </div>
        <p class="page-header">Tindak Lanjut Temuan</p>
        <p class="page-sub">{{ plan.instansi }}</p>

        <div v-if="temuanList.length === 0" class="empty-state">
            Belum ada temuan untuk instansi ini.
        </div>

        <div v-for="t in temuanList" :key="t.id" :class="['temuan-card', t.status_tindak_lanjut === 'selesai' ? 'card-selesai' : '']">
            <!-- Header -->
            <div class="temuan-header" @click="toggle(t.id)" style="cursor:pointer">
                <div class="temuan-left">
                    <span :class="['badge-risiko', RISIKO_CLASS[t.risiko]]">{{ RISIKO_LABEL[t.risiko] }}</span>
                    <span class="temuan-title">{{ t.judul }}</span>
                    <span class="butir-chip">{{ t.butir_kode }}</span>
                </div>
                <div class="temuan-right">
                    <span :class="['badge-status', t.status_tindak_lanjut === 'selesai' ? 'status-selesai' : 'status-proses']">
                        {{ t.status_tindak_lanjut === 'selesai' ? '✓ Selesai' : '● Proses' }}
                    </span>
                    <span class="chevron">{{ openIds[t.id] ? '▲' : '▼' }}</span>
                </div>
            </div>

            <!-- Expanded body -->
            <div v-if="openIds[t.id]" class="temuan-body">
                <!-- Info butir -->
                <div v-if="t.butir_judul || t.butir_sumber" class="butir-detail">
                    <p v-if="t.butir_judul" class="butir-detail-judul">
                        <span class="butir-chip-sm">{{ t.butir_kode }}</span> {{ t.butir_judul }}
                    </p>
                    <p v-if="t.butir_sumber" class="butir-detail-sumber">📄 {{ t.butir_sumber }}</p>
                </div>

                <p class="detail-row"><strong>Deskripsi:</strong> {{ t.deskripsi }}</p>
                <p class="detail-row"><strong>Rekomendasi:</strong> {{ t.rekomendasi }}</p>

                <!-- Deadline: auditee dapat set/ubah; auditor & ketua tim read-only -->
                <div v-if="role === 'auditee' && t.status_tindak_lanjut !== 'selesai' && (!t.deadline || deadlineEditing[t.id])"
                     class="deadline-input-box">
                    <p class="dl-input-label">Target penyelesaian</p>
                    <div class="dl-input-row">
                        <input v-model="deadlineForms[t.id]" type="date" class="dl-input"
                               :min="new Date().toISOString().split('T')[0]">
                        <button class="btn-simpan-target"
                                :disabled="loadingDeadline[t.id] || !deadlineForms[t.id]"
                                @click="saveDeadline(t)">
                            {{ loadingDeadline[t.id] ? '...' : 'Simpan Target' }}
                        </button>
                        <button v-if="t.deadline" class="btn-batal-target" @click="cancelDeadlineEdit(t)">Batal</button>
                    </div>
                </div>
                <div v-else-if="deadlineInfo(t.deadline)" :class="['deadline-box', `dl-${deadlineInfo(t.deadline).status}`]">
                    <div class="dl-header">
                        <span class="dl-icon">📅</span>
                        <span class="dl-label">Target penyelesaian</span>
                        <span v-if="deadlineInfo(t.deadline).status === 'warning'" class="dl-badge dl-badge-warn">Segera berakhir</span>
                        <span v-if="deadlineInfo(t.deadline).status === 'urgent'"  class="dl-badge dl-badge-urgent">Segera berakhir</span>
                        <span v-if="deadlineInfo(t.deadline).status === 'overdue'" class="dl-badge dl-badge-overdue">Terlewat</span>
                        <span class="dl-date">{{ deadlineInfo(t.deadline).label }}</span>
                        <button v-if="role === 'auditee' && t.status_tindak_lanjut !== 'selesai'"
                                class="btn-ubah-target" @click="openDeadlineEdit(t)">Ubah</button>
                    </div>
                    <div class="dl-bar-wrap">
                        <div class="dl-bar" :style="{ width: deadlineInfo(t.deadline).barPct + '%' }"></div>
                    </div>
                    <div class="dl-text">{{ deadlineInfo(t.deadline).text }}</div>
                </div>

                <!-- Thread pesan -->
                <div class="thread-title">Riwayat Pesan</div>
                <div class="thread">
                    <div v-if="t.pesan.length === 0" class="thread-empty">Belum ada pesan.</div>
                    <div v-for="p in t.pesan" :key="p.id"
                        :class="['bubble', p.user_role === 'ketua_tim' ? 'bubble-kt' : 'bubble-ae']">
                        <div class="bubble-meta">
                            <span class="bubble-name">{{ p.user_name }}</span>
                            <span class="bubble-role">{{ p.user_role?.replace('_',' ') }}</span>
                            <span class="bubble-time">{{ p.created_at }}</span>
                        </div>
                        <div class="bubble-text">{{ p.pesan }}</div>
                        <div v-if="p.lampiran?.length" class="bubble-bukti">
                            <a v-for="f in p.lampiran" :key="f.url"
                               :href="f.url" target="_blank" class="bukti-link">
                                <span class="bukti-icon">📎</span>
                                <span class="bukti-name">{{ f.name }}</span>
                                <span v-if="f.size" class="bukti-size">{{ formatSize(f.size) }}</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form kirim pesan (all roles can send) -->
                <div v-if="t.status_tindak_lanjut !== 'selesai'" class="pesan-form">
                    <textarea
                        v-model="pesanForms[t.id]"
                        class="pesan-input"
                        rows="2"
                        placeholder="Tulis pesan klarifikasi atau update..."
                    ></textarea>

                    <!-- Upload lampiran — HANYA auditee -->
                    <div v-if="role === 'auditee'" class="upload-area">
                        <input
                            type="file" multiple
                            :id="`up-${t.id}`"
                            class="upload-input-hidden"
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                            @change="addFiles(t.id, $event)"
                        >
                        <label :for="`up-${t.id}`" class="upload-zone">
                            <span class="upload-zone-icon">📎</span>
                            <span class="upload-zone-text">Lampirkan bukti perbaikan</span>
                            <span class="upload-zone-sub">PDF, JPG, PNG, DOC, DOCX · maks. 10 MB per file</span>
                        </label>
                        <div v-if="filesMap[t.id]?.length" class="file-list">
                            <div v-for="(f, i) in filesMap[t.id]" :key="i" class="file-item">
                                <span class="file-item-icon">📄</span>
                                <span class="file-item-name">{{ f.name }}</span>
                                <span class="file-item-size">{{ formatSize(f.size) }}</span>
                                <button class="file-item-remove" @click="removeFile(t.id, i)">×</button>
                            </div>
                        </div>
                    </div>

                    <div class="pesan-actions">
                        <button class="btn-kirim" :disabled="loadingKirim[t.id] || (!pesanForms[t.id]?.trim() && !filesMap[t.id]?.length)"
                            @click="kirim(t)">
                            {{ loadingKirim[t.id] ? 'Mengirim...' : 'Kirim Pesan' }}
                        </button>
                        <button v-if="isKetuaTim" class="btn-selesai"
                            :disabled="loadingTandai[t.id]"
                            @click="tandaiSelesai(t)">
                            {{ loadingTandai[t.id] ? '...' : '✓ Tandai Selesai' }}
                        </button>
                    </div>
                </div>
                <div v-else class="selesai-notice">Temuan ini telah ditandai selesai oleh ketua tim.</div>
            </div>
        </div>
    </SidebarLayout>
</template>

<style scoped>
.back-row    { margin-bottom:12px; }
.btn-back    { background:none; border:1px solid #d1d5db; border-radius:6px; padding:5px 12px; cursor:pointer; font-size:.8rem; color:#374151; }
.btn-back:hover { background:#f9fafb; }
.page-header { font-size:1.1rem; font-weight:700; color:#1F4E79; margin-bottom:2px; }
.page-sub    { font-size:.8rem; color:#888; margin-bottom:16px; }
.empty-state { text-align:center; color:#aaa; padding:40px 0; font-size:.85rem; }

.temuan-card    { border:1px solid #e5e7eb; border-radius:8px; margin-bottom:10px; overflow:hidden; }
.card-selesai   { border-color:#cfe3b8; background:#fafff6; }
.temuan-header  { display:flex; justify-content:space-between; align-items:center; padding:12px 14px; background:#f9fafb; flex-wrap:wrap; gap:6px; }
.card-selesai .temuan-header { background:#f0f9f0; }
.temuan-left    { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.temuan-right   { display:flex; align-items:center; gap:8px; }
.temuan-title   { font-size:.85rem; font-weight:700; color:#1F4E79; }
.butir-chip     { font-size:.7rem; padding:2px 7px; border-radius:8px; background:#E6F1FB; color:#1F4E79; font-weight:600; }
.chevron        { font-size:.7rem; color:#888; }

.badge-risiko { font-size:.7rem; padding:2px 7px; border-radius:8px; font-weight:700; }
.badge-tinggi { background:#FCEBEB; color:#9b1c1c; }
.badge-sedang { background:#FFF2CC; color:#7B6000; }
.badge-rendah { background:#EAF3DE; color:#375623; }
.badge-status { font-size:.72rem; padding:2px 8px; border-radius:8px; font-weight:600; }
.status-proses  { background:#FFF2CC; color:#7B6000; }
.status-selesai { background:#EAF3DE; color:#375623; }

.temuan-body   { padding:12px 14px; }
.detail-row    { font-size:.8rem; margin-bottom:4px; }

.butir-detail        { background:#f8fafc; border-left:3px solid #1F4E79; border-radius:0 6px 6px 0; padding:8px 10px; margin-bottom:10px; }
.butir-chip-sm       { display:inline-block; font-size:.68rem; padding:1px 6px; border-radius:6px; background:#E6F1FB; color:#1F4E79; font-weight:700; margin-right:4px; }
.butir-detail-judul  { font-size:.8rem; color:#1f2937; margin:0 0 4px; line-height:1.45; }
.butir-detail-sumber { font-size:.72rem; color:#6b7280; margin:0; }

/* Deadline */
.deadline-box   { border-radius:8px; padding:10px 12px; margin:10px 0; }
.dl-ok          { background:#f0fdf4; border:1px solid #bbf7d0; }
.dl-warning     { background:#fffbeb; border:1px solid #fde68a; }
.dl-urgent      { background:#fff7ed; border:1px solid #fdba74; }
.dl-overdue     { background:#fef2f2; border:1px solid #fecaca; }
.dl-header      { display:flex; align-items:center; gap:8px; margin-bottom:6px; flex-wrap:wrap; }
.dl-icon        { font-size:.85rem; }
.dl-label       { font-size:.78rem; font-weight:600; color:#374151; }
.dl-date        { font-size:.78rem; font-weight:700; margin-left:auto; color:#1f2937; }
.dl-badge       { font-size:.68rem; padding:1px 7px; border-radius:8px; font-weight:600; }
.dl-badge-warn    { background:#fef3c7; color:#92400e; }
.dl-badge-urgent  { background:#ffedd5; color:#9a3412; }
.dl-badge-overdue { background:#fee2e2; color:#991b1b; }
.dl-bar-wrap    { height:5px; background:#e5e7eb; border-radius:3px; overflow:hidden; margin-bottom:5px; }
.dl-ok .dl-bar      { height:100%; border-radius:3px; background:#16a34a; transition:width .4s; }
.dl-warning .dl-bar { height:100%; border-radius:3px; background:#d97706; transition:width .4s; }
.dl-urgent .dl-bar  { height:100%; border-radius:3px; background:#ea580c; transition:width .4s; }
.dl-overdue .dl-bar { height:100%; border-radius:3px; background:#dc2626; transition:width .4s; }
.dl-text        { font-size:.72rem; font-weight:600; }
.dl-ok .dl-text     { color:#15803d; }
.dl-warning .dl-text{ color:#b45309; }
.dl-urgent .dl-text { color:#c2410c; }
.dl-overdue .dl-text{ color:#b91c1c; }
.thread-title  { font-size:.75rem; font-weight:700; color:#1F4E79; margin:10px 0 6px; text-transform:uppercase; letter-spacing:.3px; }
.thread        { max-height:280px; overflow-y:auto; margin-bottom:10px; }
.thread-empty  { color:#aaa; font-size:.78rem; }

.bubble     { border-radius:8px; padding:8px 10px; margin-bottom:6px; max-width:90%; font-size:.8rem; }
.bubble-kt  { background:#E6F1FB; margin-left:auto; }
.bubble-ae  { background:#f3f4f6; }
.bubble-meta{ display:flex; gap:6px; align-items:center; margin-bottom:3px; flex-wrap:wrap; }
.bubble-name{ font-weight:700; font-size:.75rem; color:#1F4E79; }
.bubble-role{ font-size:.68rem; color:#888; background:#e5e7eb; padding:1px 5px; border-radius:4px; text-transform:capitalize; }
.bubble-time{ font-size:.68rem; color:#aaa; margin-left:auto; }
.bubble-text{ line-height:1.45; }
.bubble-bukti       { margin-top:6px; display:flex; flex-direction:column; gap:4px; }
.bukti-link         { display:inline-flex; align-items:center; gap:5px; font-size:.75rem; color:#1F4E79; text-decoration:none; background:#f0f6ff; border:1px solid #c3dafe; border-radius:5px; padding:3px 8px; width:fit-content; }
.bukti-link:hover   { background:#dbeafe; }
.bukti-icon         { flex-shrink:0; }
.bukti-name         { font-weight:500; }
.bukti-size         { color:#6b7280; font-size:.68rem; }

/* Upload zone */
.upload-area           { margin-top:6px; }
.upload-input-hidden   { display:none; }
.upload-zone           { display:flex; align-items:center; gap:8px; border:1.5px dashed #93c5fd; border-radius:8px; padding:9px 14px; cursor:pointer; background:#f0f9ff; transition:background .15s; }
.upload-zone:hover     { background:#dbeafe; }
.upload-zone-icon      { font-size:1rem; flex-shrink:0; }
.upload-zone-text      { font-size:.8rem; font-weight:600; color:#1d4ed8; }
.upload-zone-sub       { font-size:.68rem; color:#6b7280; margin-left:auto; }
.file-list             { margin-top:6px; display:flex; flex-direction:column; gap:3px; }
.file-item             { display:flex; align-items:center; gap:6px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:6px; padding:4px 8px; font-size:.78rem; }
.file-item-icon        { flex-shrink:0; }
.file-item-name        { flex:1; color:#374151; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.file-item-size        { color:#6b7280; font-size:.68rem; white-space:nowrap; }
.file-item-remove      { background:none; border:none; font-size:.9rem; color:#9ca3af; cursor:pointer; padding:0 2px; line-height:1; flex-shrink:0; }
.file-item-remove:hover{ color:#dc2626; }

.pesan-form    { margin-top:10px; }
.pesan-input   { width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:.8rem; resize:vertical; }
.pesan-actions { display:flex; gap:8px; margin-top:6px; flex-wrap:wrap; }
.btn-kirim     { background:#1F4E79; color:#fff; border:none; border-radius:6px; padding:6px 14px; font-size:.8rem; font-weight:600; cursor:pointer; }
.btn-kirim:hover:not(:disabled) { background:#16396a; }
.btn-kirim:disabled { opacity:.5; cursor:not-allowed; }
.btn-selesai   { background:#375623; color:#fff; border:none; border-radius:6px; padding:6px 14px; font-size:.8rem; font-weight:600; cursor:pointer; }
.btn-selesai:hover:not(:disabled) { background:#2a4119; }
.btn-selesai:disabled { opacity:.5; cursor:not-allowed; }
.selesai-notice { font-size:.78rem; color:#375623; background:#EAF3DE; padding:8px 10px; border-radius:6px; margin-top:8px; }

/* Deadline input (auditee) */
.deadline-input-box { margin:10px 0; padding:10px 12px; background:#f0f9ff; border:1px solid #bae6fd; border-radius:8px; }
.dl-input-label     { font-size:.75rem; font-weight:600; color:#0369a1; margin-bottom:6px; }
.dl-input-row       { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.dl-input           { height:34px; border:1px solid #d1d5db; border-radius:6px; padding:0 10px; font-size:.8rem; }
.btn-simpan-target  { background:#1F4E79; color:#fff; border:none; border-radius:6px; padding:5px 12px; font-size:.78rem; font-weight:600; cursor:pointer; white-space:nowrap; }
.btn-simpan-target:hover:not(:disabled) { background:#16396a; }
.btn-simpan-target:disabled { opacity:.5; cursor:not-allowed; }
.btn-batal-target   { background:#fff; border:1px solid #d1d5db; border-radius:6px; padding:5px 10px; font-size:.78rem; cursor:pointer; color:#6b7280; }
.btn-batal-target:hover { background:#f9fafb; }
.btn-ubah-target    { background:#fff; border:1px solid #d1d5db; border-radius:5px; padding:2px 9px; font-size:.7rem; cursor:pointer; color:#374151; }
.btn-ubah-target:hover { background:#f9fafb; }
</style>
