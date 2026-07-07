<script setup>
import { ref, reactive, computed } from 'vue'
import axios from 'axios'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
    plan       : { type: Object, required: true },
    temuan     : { type: Array,  default: () => [] },
    butirList  : { type: Array,  default: () => [] },
    isKetuaTim : { type: Boolean, default: false },
})

const baseUrl = computed(() => props.isKetuaTim ? '/ketua-tim' : '/auditor')
const list    = reactive([...props.temuan])

// 'none' | 'manual' | 'lengkapi'
const mode           = ref('none')
const editId         = ref(null)
const lengkapiTarget = ref(null)
const msg            = ref('')
const msgType        = ref('')

const form = reactive({
    audit_plan_id : props.plan.id,
    butir_id      : '',
    judul         : '',
    deskripsi     : '',
    risiko        : 'sedang',
    rekomendasi   : '',
})

const RISIKO_CLASS = { tinggi: 'badge-tinggi', sedang: 'badge-sedang', rendah: 'badge-rendah' }
const RISIKO_LABEL = { tinggi: 'Tinggi', sedang: 'Sedang', rendah: 'Rendah' }
const STATUS_LABEL  = { proses: 'Proses', selesai: 'Selesai' }
const KELEMAHAN_LABEL = {
    edk_tidak_memadai : 'EDK Tidak Memadai',
    eik_tidak_sesuai  : 'EIK Tidak Sesuai',
    efk_belum_efektif : 'EFK Belum Efektif',
}

const perluDilengkapi = computed(() => list.filter(t => !t.is_lengkap))
const selesai         = computed(() => list.filter(t => t.is_lengkap))

const otomatisButirIds = computed(() =>
    new Set(list.filter(t => t.sumber === 'otomatis').map(t => t.butir_id))
)

const manualButirConflict = computed(() =>
    mode.value === 'manual' && !editId.value &&
    form.butir_id && otomatisButirIds.value.has(Number(form.butir_id))
)

function resetForm() {
    form.butir_id    = ''
    form.judul       = ''
    form.deskripsi   = ''
    form.risiko      = 'sedang'
    form.rekomendasi = ''
    editId.value         = null
    lengkapiTarget.value = null
}

function openManual() {
    resetForm()
    mode.value = 'manual'
}

function openEdit(t) {
    resetForm()
    editId.value     = t.id
    form.butir_id    = t.butir_id
    form.judul       = t.judul === '-' ? '' : t.judul
    form.deskripsi   = t.deskripsi
    form.risiko      = t.risiko
    form.rekomendasi = t.rekomendasi
    mode.value       = 'manual'
}

function openLengkapi(t) {
    resetForm()
    lengkapiTarget.value = t
    form.butir_id        = t.butir_id
    mode.value           = 'lengkapi'
}

function cancel() {
    mode.value = 'none'
    resetForm()
    msg.value = ''
}

async function submitManual() {
    msg.value = ''
    try {
        if (editId.value) {
            await axios.put(`${baseUrl.value}/temuan/${editId.value}`, form)
            const idx = list.findIndex(t => t.id === editId.value)
            if (idx !== -1) {
                const butir = props.butirList.find(b => b.id == form.butir_id)
                Object.assign(list[idx], {
                    butir_id    : form.butir_id,
                    judul       : form.judul,
                    deskripsi   : form.deskripsi,
                    risiko      : form.risiko,
                    rekomendasi : form.rekomendasi,
                    butir_kode  : butir?.kode ?? '-',
                    butir_judul : butir?.judul_butir ?? '-',
                })
            }
            msg.value = 'Temuan diperbarui.'
        } else {
            const res = await axios.post(`${baseUrl.value}/temuan`, form)
            const butir = props.butirList.find(b => b.id == form.butir_id)
            list.unshift({
                id                   : res.data.id,
                judul                : form.judul,
                deskripsi            : form.deskripsi,
                risiko               : form.risiko,
                rekomendasi          : form.rekomendasi,
                deadline             : null,
                status_tindak_lanjut : 'proses',
                butir_id             : form.butir_id,
                butir_kode           : butir?.kode ?? '-',
                butir_judul          : butir?.judul_butir ?? '-',
                auditor_name         : '',
                sumber               : 'manual',
                jenis_kelemahan      : [],
                is_lengkap           : true,
            })
            msg.value = 'Temuan ditambahkan.'
        }
        msgType.value = 'ok'
        mode.value    = 'none'
        resetForm()
    } catch (e) {
        msg.value     = e.response?.data?.message || 'Gagal menyimpan.'
        msgType.value = 'err'
    }
}

async function submitLengkapi() {
    msg.value = ''
    const t = lengkapiTarget.value
    try {
        await axios.put(`${baseUrl.value}/temuan/${t.id}`, {
            butir_id    : t.butir_id,
            judul       : form.judul,
            deskripsi   : form.deskripsi,
            risiko      : form.risiko,
            rekomendasi : form.rekomendasi,
            is_lengkap  : true,
        })
        const idx = list.findIndex(x => x.id === t.id)
        if (idx !== -1) {
            Object.assign(list[idx], {
                judul       : form.judul,
                deskripsi   : form.deskripsi,
                risiko      : form.risiko,
                rekomendasi : form.rekomendasi,
                is_lengkap  : true,
            })
        }
        msgType.value = 'ok'
        msg.value     = 'Temuan berhasil dilengkapi.'
        mode.value    = 'none'
        resetForm()
    } catch (e) {
        msg.value     = e.response?.data?.message || 'Gagal menyimpan.'
        msgType.value = 'err'
    }
}

async function hapus(t) {
    const label = (t.judul && t.judul !== '-') ? t.judul : t.butir_kode
    if (!confirm(`Hapus temuan "${label}"?`)) return
    try {
        await axios.delete(`${baseUrl.value}/temuan/${t.id}`)
        const idx = list.findIndex(x => x.id === t.id)
        if (idx !== -1) list.splice(idx, 1)
    } catch (e) {
        alert(e.response?.data?.message || 'Gagal menghapus.')
    }
}
</script>

<template>
    <SidebarLayout title="Temuan Audit">
        <div class="page-topbar">
            <div>
                <p class="page-header">Temuan Audit</p>
                <p class="page-sub">{{ plan.instansi }}</p>
            </div>
            <button class="btn-primary" @click="openManual">+ Tambah Temuan Manual</button>
        </div>

        <div v-if="msg" :class="['alert', msgType === 'ok' ? 'alert-ok' : 'alert-err']">{{ msg }}</div>

        <!-- ── Form: Tambah / Edit Manual ── -->
        <div v-if="mode === 'manual'" class="form-card">
            <p class="form-title">{{ editId ? 'Edit Temuan' : 'Tambah Temuan Manual' }}</p>

            <label class="field-label">Butir Terkait</label>
            <select v-model="form.butir_id" class="field-input">
                <option value="">— Pilih Butir —</option>
                <optgroup v-for="bagian in ['tk','mk','fk']" :key="bagian" :label="bagian.toUpperCase()">
                    <option
                        v-for="b in butirList.filter(x => x.bagian === bagian)"
                        :key="b.id"
                        :value="b.id"
                    >{{ b.kode }} — {{ b.judul_butir }}</option>
                </optgroup>
            </select>
            <div v-if="manualButirConflict" class="field-warn">
                Butir ini sudah memiliki temuan otomatis. Temuan manual akan ditambahkan terpisah.
            </div>

            <label class="field-label">Judul Temuan</label>
            <input v-model="form.judul" class="field-input" placeholder="Judul singkat temuan...">

            <label class="field-label">Deskripsi</label>
            <textarea v-model="form.deskripsi" class="field-textarea" rows="3" placeholder="Deskripsi detail temuan..."></textarea>

            <label class="field-label">Tingkat Risiko</label>
            <select v-model="form.risiko" class="field-input">
                <option value="tinggi">Tinggi</option>
                <option value="sedang">Sedang</option>
                <option value="rendah">Rendah</option>
            </select>

            <label class="field-label">Rekomendasi</label>
            <textarea v-model="form.rekomendasi" class="field-textarea" rows="3" placeholder="Rekomendasi perbaikan..."></textarea>

            <div class="form-actions">
                <button class="btn-ghost" @click="cancel">Batal</button>
                <button class="btn-primary" @click="submitManual">{{ editId ? 'Simpan Perubahan' : 'Tambah Temuan' }}</button>
            </div>
        </div>

        <!-- ── Form: Lengkapi Temuan Otomatis ── -->
        <div v-if="mode === 'lengkapi' && lengkapiTarget" class="form-card form-card--lengkapi">
            <p class="form-title">Lengkapi Temuan Otomatis</p>

            <label class="field-label">Butir Terkait</label>
            <div class="readonly-field">{{ lengkapiTarget.butir_kode }} — {{ lengkapiTarget.butir_judul }}</div>

            <label class="field-label">Jenis Kelemahan</label>
            <div class="kelemahan-chips">
                <span v-for="k in (lengkapiTarget.jenis_kelemahan || [])" :key="k" class="chip-kelemahan">
                    {{ KELEMAHAN_LABEL[k] || k }}
                </span>
            </div>

            <label class="field-label">Judul Temuan</label>
            <input v-model="form.judul" class="field-input" placeholder="Judul singkat temuan...">

            <label class="field-label">Deskripsi</label>
            <textarea v-model="form.deskripsi" class="field-textarea" rows="3" placeholder="Uraikan detail temuan berdasarkan kelemahan yang terdeteksi..."></textarea>

            <label class="field-label">Tingkat Risiko</label>
            <select v-model="form.risiko" class="field-input">
                <option value="tinggi">Tinggi</option>
                <option value="sedang">Sedang</option>
                <option value="rendah">Rendah</option>
            </select>

            <label class="field-label">Rekomendasi</label>
            <textarea v-model="form.rekomendasi" class="field-textarea" rows="3" placeholder="Rekomendasi perbaikan..."></textarea>

            <div class="form-actions">
                <button class="btn-ghost" @click="cancel">Batal</button>
                <button class="btn-primary" @click="submitLengkapi">Lengkapi &amp; Simpan</button>
            </div>
        </div>

        <!-- ── Seksi: Perlu Dilengkapi ── -->
        <template v-if="perluDilengkapi.length > 0">
            <p class="section-title">
                Perlu Dilengkapi
                <span class="section-count">{{ perluDilengkapi.length }}</span>
            </p>
            <div v-for="t in perluDilengkapi" :key="t.id" class="temuan-card temuan-card--perlu">
                <div class="temuan-header">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <span class="butir-chip">{{ t.butir_kode }}</span>
                        <span class="temuan-title">{{ t.butir_judul }}</span>
                    </div>
                </div>
                <div class="kelemahan-chips" style="margin:6px 0 4px">
                    <span v-for="k in (t.jenis_kelemahan || [])" :key="k" class="chip-kelemahan">
                        {{ KELEMAHAN_LABEL[k] || k }}
                    </span>
                </div>
                <div class="temuan-actions">
                    <button class="btn-lengkapi" @click="openLengkapi(t)">Lengkapi</button>
                    <button class="btn-sm-danger" @click="hapus(t)">Hapus</button>
                </div>
            </div>
        </template>

        <!-- ── Seksi: Temuan Selesai ── -->
        <template v-if="selesai.length > 0">
            <p class="section-title" :style="perluDilengkapi.length > 0 ? 'margin-top:16px' : ''">
                Temuan
                <span class="section-count">{{ selesai.length }}</span>
            </p>
            <div v-for="t in selesai" :key="t.id" class="temuan-card">
                <div class="temuan-header">
                    <div class="temuan-title">{{ t.judul }}</div>
                    <div class="temuan-meta">
                        <span :class="['badge-risiko', RISIKO_CLASS[t.risiko]]">{{ RISIKO_LABEL[t.risiko] }}</span>
                        <span class="badge-status">{{ STATUS_LABEL[t.status_tindak_lanjut] ?? t.status_tindak_lanjut }}</span>
                        <span class="butir-chip">{{ t.butir_kode }}</span>
                        <span :class="['chip-sumber', t.sumber === 'otomatis' ? 'chip-sumber--otomatis' : 'chip-sumber--manual']">
                            {{ t.sumber === 'otomatis' ? 'Otomatis' : 'Manual' }}
                        </span>
                    </div>
                </div>
                <div class="temuan-body">
                    <p><strong>Deskripsi:</strong> {{ t.deskripsi }}</p>
                    <p><strong>Rekomendasi:</strong> {{ t.rekomendasi }}</p>
                    <p v-if="t.auditor_name && t.auditor_name !== '-'" style="font-size:.72rem; color:#888; margin-top:4px">Oleh: {{ t.auditor_name }}</p>
                </div>
                <div class="temuan-actions">
                    <button class="btn-sm-ghost" @click="openEdit(t)">Edit</button>
                    <button class="btn-sm-danger" @click="hapus(t)">Hapus</button>
                </div>
            </div>
        </template>

        <div v-if="list.length === 0 && mode === 'none'" class="empty-state">
            Belum ada temuan. Klik "Tambah Temuan Manual" untuk menambahkan.
        </div>
    </SidebarLayout>
</template>

<style scoped>
.page-topbar  { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; flex-wrap:wrap; gap:8px; }
.page-header  { font-size:1.1rem; font-weight:700; color:#1F4E79; margin-bottom:2px; }
.page-sub     { font-size:.8rem; color:#888; }
.alert        { padding:8px 12px; border-radius:6px; font-size:.8rem; margin-bottom:12px; }
.alert-ok     { background:#EAF3DE; color:#375623; border:1px solid #cfe3b8; }
.alert-err    { background:#FCEBEB; color:#9b1c1c; border:1px solid #f0c0c0; }
.empty-state  { text-align:center; color:#aaa; padding:40px 0; font-size:.85rem; }

.section-title { font-size:.75rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; margin-bottom:8px; display:flex; align-items:center; gap:6px; }
.section-count { background:#e5e7eb; color:#374151; font-size:.7rem; padding:1px 7px; border-radius:8px; font-weight:700; }

.form-card          { background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:16px; margin-bottom:16px; }
.form-card--lengkapi { border-color:#fca5a5; background:#fff9f9; }
.form-title         { font-size:.9rem; font-weight:700; color:#1F4E79; margin-bottom:12px; }
.field-label        { display:block; font-size:.75rem; font-weight:600; color:#374151; margin-bottom:3px; margin-top:10px; }
.field-input        { width:100%; height:34px; border:1px solid #d1d5db; border-radius:6px; padding:0 10px; font-size:.8rem; }
.field-textarea     { width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:.8rem; resize:vertical; }
.field-warn         { background:#FFF2CC; border:1px solid #f5d76e; border-radius:5px; padding:6px 10px; font-size:.75rem; color:#7B6000; margin-top:4px; }
.readonly-field     { background:#f3f4f6; border:1px solid #e5e7eb; border-radius:6px; padding:7px 10px; font-size:.8rem; color:#374151; }
.form-actions       { display:flex; gap:8px; margin-top:14px; justify-content:flex-end; }
.btn-ghost          { background:#fff; border:1px solid #1F4E79; color:#1F4E79; border-radius:6px; padding:6px 14px; font-size:.8rem; font-weight:600; cursor:pointer; }
.btn-primary        { background:#1F4E79; color:#fff; border:none; border-radius:6px; padding:7px 14px; font-size:.8rem; font-weight:600; cursor:pointer; }
.btn-ghost:hover    { background:#f0f6fc; }
.btn-primary:hover  { background:#16396a; }

.temuan-card        { border:1px solid #e5e7eb; border-radius:8px; padding:12px 14px; margin-bottom:10px; background:#fff; }
.temuan-card--perlu { border-color:#fca5a5; background:#fff9f9; }
.temuan-header      { display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:6px; margin-bottom:4px; }
.temuan-title       { font-size:.9rem; font-weight:700; color:#1F4E79; }
.temuan-meta        { display:flex; gap:6px; flex-wrap:wrap; align-items:center; }
.temuan-body        { font-size:.8rem; line-height:1.5; }
.temuan-body p      { margin-bottom:3px; }
.temuan-actions     { display:flex; gap:6px; margin-top:8px; justify-content:flex-end; }

.kelemahan-chips { display:flex; flex-wrap:wrap; gap:4px; }
.chip-kelemahan  { font-size:.68rem; padding:2px 8px; border-radius:8px; background:#FCEBEB; color:#9b1c1c; font-weight:700; border:1px solid #fca5a5; }

.chip-sumber            { font-size:.65rem; padding:2px 7px; border-radius:8px; font-weight:700; }
.chip-sumber--otomatis  { background:#EEF2FF; color:#3730a3; }
.chip-sumber--manual    { background:#f3f4f6; color:#6b7280; }

.badge-risiko  { font-size:.7rem; padding:2px 7px; border-radius:8px; font-weight:700; }
.badge-tinggi  { background:#FCEBEB; color:#9b1c1c; }
.badge-sedang  { background:#FFF2CC; color:#7B6000; }
.badge-rendah  { background:#EAF3DE; color:#375623; }
.badge-status  { font-size:.7rem; padding:2px 7px; border-radius:8px; background:#f3f4f6; color:#6b7280; font-weight:600; }
.butir-chip    { font-size:.7rem; padding:2px 7px; border-radius:8px; background:#E6F1FB; color:#1F4E79; font-weight:600; }

.btn-sm-ghost  { font-size:.75rem; padding:4px 10px; border:1px solid #d1d5db; border-radius:5px; background:#fff; cursor:pointer; color:#374151; }
.btn-sm-ghost:hover  { background:#f9fafb; }
.btn-sm-danger { font-size:.75rem; padding:4px 10px; border:1px solid #fca5a5; border-radius:5px; background:#fff; cursor:pointer; color:#9b1c1c; }
.btn-sm-danger:hover { background:#FCEBEB; }
.btn-lengkapi  { font-size:.75rem; padding:4px 12px; border:1px solid #1F4E79; border-radius:5px; background:#EEF2FF; cursor:pointer; color:#1F4E79; font-weight:600; }
.btn-lengkapi:hover  { background:#dbeafe; }
</style>
