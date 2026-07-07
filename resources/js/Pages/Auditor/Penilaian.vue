<script setup>
import { ref, computed, reactive, nextTick } from 'vue'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'
import axios from 'axios'

const props = defineProps({
  plan:           Object,
  butirByBagian:  Object,
  assignedBagian: { type: Array, default: () => ['tk', 'mk', 'fk'] },
  scanFindings:   { type: Object, default: () => ({}) }, // butir_id → findings[]
})

/* ─── Tabs ─── */
const allTabs  = ['tk', 'mk', 'fk']
const tabLabel = {
  tk: 'Tata Kelola',
  mk: 'Manajemen Keamanan',
  fk: 'Fungsionalitas Keamanan',
}

const visibleTabs = computed(() => allTabs.filter(t => props.assignedBagian.includes(t)))
const activeTab   = ref(visibleTabs.value[0] ?? 'tk')

/* ─── Options ─── */
const edkOptions = [
  { value: 'memadai',           label: 'Memadai' },
  { value: 'perlu_peningkatan', label: 'Perlu Peningkatan' },
  { value: 'tidak_memadai',     label: 'Tidak Memadai' },
]
const eikOptions = [
  { value: 'sesuai',       label: 'Sesuai' },
  { value: 'tidak_sesuai', label: 'Tidak Sesuai' },
]
const efkOptions = [
  { value: 'efektif',           label: 'Efektif' },
  { value: 'perlu_peningkatan', label: 'Perlu Peningkatan' },
  { value: 'belum_efektif',     label: 'Belum Efektif' },
]

/* ─── Reactive form state ─── */
const formData   = reactive({})
const saving     = reactive({})
const saveStatus = reactive({})

const allButir = computed(() => Object.values(props.butirByBagian).flat())

allButir.value.forEach(p => {
  formData[p.penilaian_id] = {
    edk:         p.edk         ?? '',
    catatan_edk: p.catatan_edk ?? '',
    eik:         p.eik         ?? '',
    catatan_eik: p.catatan_eik ?? '',
    efk:         p.efk         ?? '',
    catatan_efk: p.catatan_efk ?? '',
  }
})

/* ─── Save ─── */
async function savePenilaian(pid) {
  saving[pid]     = true
  saveStatus[pid] = ''
  try {
    await axios.put(`/auditor/penilaian/${pid}`, formData[pid])
    saveStatus[pid] = 'saved'
    setTimeout(() => { saveStatus[pid] = '' }, 2500)
  } catch (e) {
    saveStatus[pid] = 'error'
    alert(e.response?.data?.message || 'Gagal menyimpan.')
  } finally {
    saving[pid] = false
  }
}

/* ─── Helpers ─── */
const currentButir = computed(() => props.butirByBagian[activeTab.value] ?? [])

function tabCount(tab) { return (props.butirByBagian[tab] ?? []).length }
function tabDone(tab)  { return (props.butirByBagian[tab] ?? []).filter(p => isPenilaianComplete(p)).length }
function buktiOf(p, jenis) { return (p.bukti ?? []).filter(b => b.jenis_acuan === jenis) }
function isEdkTidakMemadai(pid) { return formData[pid]?.edk === 'tidak_memadai' }

function edkColor(val) {
  if (val === 'memadai')           return 'var(--green-dark)'
  if (val === 'perlu_peningkatan') return 'var(--yellow-dark)'
  if (val === 'tidak_memadai')     return 'var(--red-dark)'
  return '#aaa'
}

/* ─── Ringkasan penilaian ─── */
function isPenilaianComplete(p) {
  const fd  = formData[p.penilaian_id] ?? {}
  const edk = fd.edk
  const eik = fd.eik
  const efk = fd.efk
  return !!edk && !!efk && (edk === 'tidak_memadai' || !!eik)
}

const incompleteButir = computed(() =>
  props.assignedBagian.flatMap(bag => (props.butirByBagian[bag] ?? []).filter(p => !isPenilaianComplete(p)))
)

const tabProgress = computed(() =>
  allTabs.reduce((acc, tab) => {
    const group = props.butirByBagian[tab] ?? []
    acc[tab] = { done: group.filter(p => isPenilaianComplete(p)).length, total: group.length }
    return acc
  }, {})
)

const allPenilaianComplete = computed(() => incompleteButir.value.length === 0)
const totalDone = computed(() => allButir.value.filter(p => isPenilaianComplete(p)).length)

function scrollToButir(p) {
  activeTab.value = p.bagian
  nextTick(() => {
    const el = document.getElementById(`butir-${p.penilaian_id}`)
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  })
}

const validatingKonklusi = ref(false)
const konklusiResult     = ref(null)

async function validasiKonklusi() {
  validatingKonklusi.value = true
  konklusiResult.value     = null
  try {
    const { data } = await axios.post(`/auditor/penilaian/${props.plan.id}/validasi-konklusi`)
    konklusiResult.value = { ok: true, message: data.message }
  } catch (e) {
    konklusiResult.value = { ok: false, message: e.response?.data?.message || 'Terjadi kesalahan.' }
  } finally {
    validatingKonklusi.value = false
  }
}

/* Scan findings per butir */
const openScanBoxes = reactive({})
function toggleScanBox(butirId) { openScanBoxes[butirId] = !openScanBoxes[butirId] }
const SCAN_SEV_BG    = { Critical: '#fee2e2', High: '#fee2e2', Medium: '#fef3c7', Low: '#dbeafe', Info: '#f3f4f6' }
const SCAN_SEV_COLOR = { Critical: '#7f1d1d', High: '#991b1b', Medium: '#92400e', Low: '#1e3a5f', Info: '#374151' }
const TOOL_COLOR = {
  curl: '#F9A825', testssl: '#1976D2', nmap: '#78716c', nikto: '#D32F2F', zap: '#B71C1C',
}

/* Chip helpers for ringkasan table */
function edkChip(val) {
  if (!val) return { label: 'Belum', bg: 'var(--red-light)',    color: 'var(--red-dark)' }
  if (val === 'memadai')           return { label: 'Memadai',       bg: 'var(--green-light)',  color: 'var(--green-dark)' }
  if (val === 'perlu_peningkatan') return { label: 'Perlu Tinj.',   bg: 'var(--yellow-light)', color: 'var(--yellow-dark)' }
  if (val === 'tidak_memadai')     return { label: 'Tdk Memadai',   bg: 'var(--red-light)',    color: 'var(--red-dark)' }
  return { label: val, bg: '#eee', color: '#555' }
}
function eikChip(val, edk) {
  if (edk === 'tidak_memadai') return { label: 'Dilewati', bg: '#eee', color: '#777' }
  if (!val)                    return { label: 'Belum',    bg: 'var(--red-light)',   color: 'var(--red-dark)' }
  if (val === 'sesuai')        return { label: 'Sesuai',   bg: 'var(--green-light)', color: 'var(--green-dark)' }
  return { label: 'Tdk Sesuai', bg: 'var(--red-light)', color: 'var(--red-dark)' }
}
function efkChip(val) {
  if (!val) return { label: 'Belum', bg: 'var(--red-light)', color: 'var(--red-dark)' }
  if (val === 'efektif')           return { label: 'Efektif',     bg: 'var(--green-light)',  color: 'var(--green-dark)' }
  if (val === 'perlu_peningkatan') return { label: 'Perlu Tinj.', bg: 'var(--yellow-light)', color: 'var(--yellow-dark)' }
  if (val === 'belum_efektif')     return { label: 'Blm Efektif', bg: 'var(--red-light)',    color: 'var(--red-dark)' }
  return { label: val, bg: '#eee', color: '#555' }
}
</script>

<template>
  <SidebarLayout title="Penilaian Audit">
    <p class="page-header" style="margin-bottom:2px">Penilaian — {{ plan.instansi }}</p>
    <p class="page-sub" style="margin-bottom:14px">
      {{ plan.url_target }} &nbsp;·&nbsp; Periode: {{ plan.waktu_mulai }} — {{ plan.waktu_selesai }}
    </p>

    <!-- Tab nav (hanya tab yang ditugaskan) -->
    <div style="display:flex;border-bottom:2px solid #e5e7eb;margin-bottom:16px">
      <button
        v-for="tab in visibleTabs"
        :key="tab"
        @click="activeTab = tab"
        style="background:none;border:none;padding:6px 16px;font-size:11px;cursor:pointer;margin-bottom:-2px"
        :style="{
          fontWeight:   activeTab === tab ? '700' : '500',
          color:        activeTab === tab ? 'var(--navy)' : '#666',
          borderBottom: activeTab === tab ? '2px solid var(--navy)' : '2px solid transparent',
        }"
      >
        {{ tabLabel[tab] }}
        <span style="font-weight:400;color:#aaa"> {{ tabDone(tab) }}/{{ tabCount(tab) }}</span>
      </button>
    </div>

    <!-- Butir list -->
    <div
      v-for="p in currentButir"
      :key="p.penilaian_id"
      :id="`butir-${p.penilaian_id}`"
      class="spbe-card-white"
      style="margin-bottom:14px"
    >
      <!-- Butir header -->
      <div style="border-bottom:1px solid #f0f0f0;padding-bottom:10px;margin-bottom:10px">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px">
          <span style="font-size:10px;font-weight:700;color:var(--navy);background:var(--navy-light);padding:2px 8px;border-radius:4px">
            {{ p.kode }}
          </span>
          <span v-if="p.domain" style="font-size:10px;color:#888">{{ p.domain }}</span>
          <span
            v-if="isPenilaianComplete(p)"
            style="font-size:9px;font-weight:700;padding:2px 7px;border-radius:10px;background:var(--green-light);color:var(--green-dark);margin-left:auto"
          >Dinilai ✓</span>
        </div>

        <p style="font-size:12px;font-weight:600;line-height:1.5;margin-bottom:3px">
          {{ p.judul_butir }}
        </p>

        <p v-if="p.sumber_acuan" style="font-size:10px;color:#888;font-style:italic">
          Sumber: {{ p.sumber_acuan }}
        </p>
      </div>

      <!-- Jawaban auditee (read-only) -->
      <div style="background:#f8f8f8;border-radius:5px;padding:8px 10px;margin-bottom:12px">
        <p style="font-size:10px;font-weight:600;color:#888;margin-bottom:4px">Tanggapan auditee:</p>
        <p style="font-size:11px;color:#333;white-space:pre-wrap;line-height:1.5">
          {{ p.jawaban_auditee || '(Belum diisi oleh auditee)' }}
        </p>
      </div>

      <!-- Bukti per jenis -->
      <div style="margin-bottom:12px">
        <p style="font-size:10px;font-weight:600;color:#888;margin-bottom:6px">Bukti yang diunggah auditee:</p>

        <div v-for="jenis in ['edk', 'eik', 'efk']" :key="jenis">
          <div v-if="buktiOf(p, jenis).length" style="margin-bottom:5px">
            <span
              style="font-size:9px;font-weight:700;padding:2px 6px;border-radius:3px;margin-right:4px"
              :style="{
                background: jenis === 'edk' ? 'var(--navy-light)'  : jenis === 'eik' ? 'var(--green-light)' : 'var(--yellow-light)',
                color:      jenis === 'edk' ? 'var(--navy)'        : jenis === 'eik' ? 'var(--green-dark)'  : 'var(--yellow-dark)',
              }"
            >{{ jenis.toUpperCase() }}</span>
            <span
              v-for="b in buktiOf(p, jenis)"
              :key="b.id"
              style="font-size:10px;margin-right:10px"
            >
              <a :href="`/storage/${b.path_file}`" target="_blank" style="color:var(--navy)">{{ b.nama_file }}</a>
            </span>
          </div>
        </div>

        <p v-if="!p.bukti?.length" style="font-size:10px;color:#bbb">Belum ada bukti diunggah.</p>
      </div>

      <!-- ── Hasil Pemindaian Otomatis (hanya FK butir dengan findings) ── -->
      <div
        v-if="scanFindings[p.butir_id]?.length"
        style="border:1px solid #d1fae5;border-radius:6px;background:#f0fdf4;padding:10px 12px;margin-bottom:12px"
      >
        <div
          style="display:flex;justify-content:space-between;align-items:center;cursor:pointer"
          @click="toggleScanBox(p.butir_id)"
        >
          <span style="font-size:11px;font-weight:700;color:#065f46">
            🔍 Hasil Pemindaian Otomatis
            <span style="font-size:10px;font-weight:400;color:#047857;margin-left:4px">
              ({{ scanFindings[p.butir_id].length }} temuan)
            </span>
          </span>
          <span style="font-size:10px;color:#047857">{{ openScanBoxes[p.butir_id] ? '▲ Sembunyikan' : '▼ Lihat' }}</span>
        </div>

        <div v-if="openScanBoxes[p.butir_id]" style="margin-top:10px">
          <div
            v-for="(finding, fi) in scanFindings[p.butir_id]"
            :key="fi"
            style="border-left:3px solid;border-radius:4px;background:#fff;padding:8px 10px;margin-bottom:8px"
            :style="{ borderLeftColor: TOOL_COLOR[finding.tool] ?? '#aaa' }"
          >
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;flex-wrap:wrap">
              <span
                style="font-size:9px;font-weight:700;padding:1px 6px;border-radius:3px"
                :style="{ background: SCAN_SEV_BG[finding.severity] ?? '#eee', color: SCAN_SEV_COLOR[finding.severity] ?? '#555' }"
              >{{ finding.severity }}</span>
              <span
                style="font-size:9px;padding:1px 6px;border-radius:3px;font-weight:600"
                :style="{ color: TOOL_COLOR[finding.tool] ?? '#555' }"
              >[{{ finding.tool }}]</span>
              <strong style="font-size:11px">{{ finding.title }}</strong>
            </div>
            <p style="font-size:10px;color:#374151;margin-bottom:4px;line-height:1.4">{{ finding.description }}</p>
            <p v-if="finding.evidence" style="font-size:9px;font-family:monospace;background:#f1f5f9;padding:4px 6px;border-radius:3px;word-break:break-all;color:#1e293b;margin-bottom:4px">{{ finding.evidence }}</p>
            <a
              :href="`/auditor/scan/${finding.scan_id}/result`"
              style="font-size:10px;color:var(--navy);text-decoration:underline"
            >Lihat detail scan →</a>
          </div>
        </div>
      </div>

      <!-- ── EDK form ── -->
      <div style="border:1px solid #c8ddef;border-radius:6px;padding:9px 11px;margin-bottom:8px">
        <p style="font-size:11px;font-weight:700;color:var(--navy);margin-bottom:6px">
          Evaluasi Desain Kontrol (EDK)
        </p>
        <p v-if="p.acuan_edk" style="font-size:10px;color:#666;margin-bottom:8px;font-style:italic">
          Acuan: {{ p.acuan_edk }}
        </p>

        <div style="display:flex;flex-wrap:wrap;gap:14px;font-size:11px;margin-bottom:8px">
          <label
            v-for="opt in edkOptions"
            :key="opt.value"
            style="display:flex;align-items:center;gap:5px;cursor:pointer"
          >
            <input type="radio" v-model="formData[p.penilaian_id].edk" :value="opt.value" />
            <span :style="{
              color:      formData[p.penilaian_id].edk === opt.value ? edkColor(opt.value) : 'inherit',
              fontWeight: formData[p.penilaian_id].edk === opt.value ? '600' : '400',
            }">{{ opt.label }}</span>
          </label>
        </div>

        <textarea
          v-model="formData[p.penilaian_id].catatan_edk"
          class="spbe-txta"
          rows="2"
          placeholder="Catatan EDK (opsional)"
        />
      </div>

      <!-- ── EIK form ── -->
      <div
        v-if="!isEdkTidakMemadai(p.penilaian_id)"
        style="border:1px solid #b8d9ac;border-radius:6px;padding:9px 11px;margin-bottom:8px"
      >
        <p style="font-size:11px;font-weight:700;color:var(--green-dark);margin-bottom:6px">
          Evaluasi Implementasi Kontrol (EIK)
        </p>
        <p v-if="p.acuan_eik" style="font-size:10px;color:#666;margin-bottom:8px;font-style:italic">
          Acuan: {{ p.acuan_eik }}
        </p>

        <div style="display:flex;flex-wrap:wrap;gap:14px;font-size:11px;margin-bottom:8px">
          <label
            v-for="opt in eikOptions"
            :key="opt.value"
            style="display:flex;align-items:center;gap:5px;cursor:pointer"
          >
            <input type="radio" v-model="formData[p.penilaian_id].eik" :value="opt.value" />
            {{ opt.label }}
          </label>
        </div>

        <textarea
          v-model="formData[p.penilaian_id].catatan_eik"
          class="spbe-txta"
          rows="2"
          placeholder="Catatan EIK (opsional)"
        />
      </div>
      <div
        v-else
        style="border:1px solid #eee;border-radius:6px;padding:8px 11px;margin-bottom:8px;background:#fafafa"
      >
        <p style="font-size:10px;color:#aaa;font-style:italic">EIK dilewati — EDK dinilai Tidak Memadai</p>
      </div>

      <!-- ── EFK form ── -->
      <div style="border:1px solid #d6c96a;border-radius:6px;padding:9px 11px;margin-bottom:12px">
        <p style="font-size:11px;font-weight:700;color:var(--yellow-dark);margin-bottom:6px">
          Evaluasi Efektivitas Kontrol (EFK)
        </p>
        <p v-if="p.acuan_efk" style="font-size:10px;color:#666;margin-bottom:8px;font-style:italic">
          Acuan: {{ p.acuan_efk }}
        </p>

        <div style="display:flex;flex-wrap:wrap;gap:14px;font-size:11px;margin-bottom:8px">
          <label
            v-for="opt in efkOptions"
            :key="opt.value"
            style="display:flex;align-items:center;gap:5px;cursor:pointer"
          >
            <input type="radio" v-model="formData[p.penilaian_id].efk" :value="opt.value" />
            {{ opt.label }}
          </label>
        </div>

        <textarea
          v-model="formData[p.penilaian_id].catatan_efk"
          class="spbe-txta"
          rows="2"
          placeholder="Catatan EFK (opsional)"
        />
      </div>

      <!-- Save -->
      <div style="display:flex;align-items:center;gap:10px">
        <button
          class="btn-p"
          :disabled="saving[p.penilaian_id] || !formData[p.penilaian_id]?.edk"
          @click="savePenilaian(p.penilaian_id)"
        >
          {{ saving[p.penilaian_id] ? 'Menyimpan...' : 'Simpan penilaian' }}
        </button>
        <span v-if="saveStatus[p.penilaian_id] === 'saved'" style="font-size:11px;color:var(--green-dark)">Tersimpan ✓</span>
        <span v-if="saveStatus[p.penilaian_id] === 'error'" style="font-size:11px;color:var(--red-dark)">Gagal menyimpan</span>
        <span v-if="!formData[p.penilaian_id]?.edk" style="font-size:10px;color:#bbb">Pilih nilai EDK terlebih dahulu</span>
      </div>
    </div>

    <div
      v-if="currentButir.length === 0"
      class="spbe-card"
      style="text-align:center;color:#aaa;font-size:11px;padding:28px"
    >
      Tidak ada butir di bagian ini.
    </div>

    <!-- ── Ringkasan Kelengkapan Penilaian ── -->
    <div class="spbe-card-white" style="margin-top:24px">
      <p style="font-size:14px;font-weight:700;color:var(--navy);margin-bottom:4px">Ringkasan Kelengkapan Penilaian</p>
      <p style="font-size:11px;color:#888;margin-bottom:14px">
        Bagian: {{ props.assignedBagian.map(b => tabLabel[b]).join(', ') }}
      </p>

      <!-- Banner -->
      <div
        :style="{
          background: allPenilaianComplete ? 'var(--green-light)' : '#FFF8E1',
          border:     `1px solid ${allPenilaianComplete ? '#5a9' : '#F9A825'}`,
          color:      allPenilaianComplete ? 'var(--green-dark)' : '#7B6000',
        }"
        style="border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:12px"
      >
        <span v-if="allPenilaianComplete">
          <b>Penilaian lengkap!</b> Semua {{ allButir.length }} butir sudah dinilai. Konklusi dapat dihitung.
        </span>
        <span v-else>
          <b>Penilaian belum lengkap.</b> Masih ada {{ incompleteButir.length }} butir yang nilai EDK/EIK/EFK-nya belum diisi. Konklusi belum bisa dihitung.
        </span>
      </div>

      <!-- Tab mini progress -->
      <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
        <div
          v-for="tab in visibleTabs"
          :key="tab"
          style="font-size:11px;padding:5px 12px;border-radius:6px;background:#f5f5f4;border:1px solid #e5e5e5;color:#555"
        >
          {{ tabLabel[tab] }}
          <span
            :style="{ color: tabProgress[tab].done === tabProgress[tab].total ? 'var(--green-dark)' : '#F9A825', fontWeight: '700' }"
            style="margin-left:6px;font-size:10px"
          >
            {{ tabProgress[tab].done }}/{{ tabProgress[tab].total }}
          </span>
        </div>
      </div>

      <!-- Incomplete table -->
      <template v-if="incompleteButir.length > 0">
        <p style="font-size:11px;font-weight:600;color:#555;margin-bottom:8px">Butir yang belum selesai dinilai:</p>
        <div style="overflow-x:auto;margin-bottom:16px">
          <table style="width:100%;border-collapse:collapse;font-size:11px">
            <thead>
              <tr>
                <th style="background:var(--navy);color:#fff;font-size:10px;font-weight:600;padding:6px 8px;text-align:left;width:8%">Butir</th>
                <th style="background:var(--navy);color:#fff;font-size:10px;font-weight:600;padding:6px 8px;text-align:left">Kontrol Pemeriksaan</th>
                <th style="background:var(--navy);color:#fff;font-size:10px;font-weight:600;padding:6px 8px;text-align:center;width:12%">Nilai EDK</th>
                <th style="background:var(--navy);color:#fff;font-size:10px;font-weight:600;padding:6px 8px;text-align:center;width:12%">Nilai EIK</th>
                <th style="background:var(--navy);color:#fff;font-size:10px;font-weight:600;padding:6px 8px;text-align:center;width:12%">Nilai EFK</th>
                <th style="background:var(--navy);color:#fff;font-size:10px;font-weight:600;padding:6px 8px;text-align:center;width:8%">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in incompleteButir" :key="p.penilaian_id" style="background:#FFFBF5">
                <td style="padding:6px 8px;border-bottom:1px solid #eee;font-weight:700;color:var(--navy)">{{ p.kode }}</td>
                <td style="padding:6px 8px;border-bottom:1px solid #eee;font-size:10px;color:#333;line-height:1.4">{{ p.judul_butir }}</td>
                <td style="padding:6px 8px;border-bottom:1px solid #eee;text-align:center">
                  <span
                    :style="{ background: edkChip(formData[p.penilaian_id]?.edk).bg, color: edkChip(formData[p.penilaian_id]?.edk).color }"
                    style="font-size:9px;padding:2px 6px;border-radius:10px;font-weight:600;display:inline-block;min-width:52px"
                  >{{ edkChip(formData[p.penilaian_id]?.edk).label }}</span>
                </td>
                <td style="padding:6px 8px;border-bottom:1px solid #eee;text-align:center">
                  <span
                    :style="{ background: eikChip(formData[p.penilaian_id]?.eik, formData[p.penilaian_id]?.edk).bg, color: eikChip(formData[p.penilaian_id]?.eik, formData[p.penilaian_id]?.edk).color }"
                    style="font-size:9px;padding:2px 6px;border-radius:10px;font-weight:600;display:inline-block;min-width:52px"
                  >{{ eikChip(formData[p.penilaian_id]?.eik, formData[p.penilaian_id]?.edk).label }}</span>
                </td>
                <td style="padding:6px 8px;border-bottom:1px solid #eee;text-align:center">
                  <span
                    :style="{ background: efkChip(formData[p.penilaian_id]?.efk).bg, color: efkChip(formData[p.penilaian_id]?.efk).color }"
                    style="font-size:9px;padding:2px 6px;border-radius:10px;font-weight:600;display:inline-block;min-width:52px"
                  >{{ efkChip(formData[p.penilaian_id]?.efk).label }}</span>
                </td>
                <td style="padding:6px 8px;border-bottom:1px solid #eee;text-align:center">
                  <button
                    @click="scrollToButir(p)"
                    style="font-size:10px;color:var(--navy);text-decoration:underline;background:none;border:none;cursor:pointer;padding:0"
                  >Nilai</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>

      <!-- Action bar -->
      <div style="display:flex;justify-content:space-between;align-items:center;padding-top:14px;border-top:1px solid #eee;flex-wrap:wrap;gap:10px">
        <span style="font-size:10px;color:#999">{{ totalDone }} dari {{ allButir.length }} butir sudah dinilai</span>
        <div style="display:flex;align-items:center;gap:10px">
          <span v-if="konklusiResult" :style="{ color: konklusiResult.ok ? 'var(--green-dark)' : 'var(--red-dark)' }" style="font-size:11px">
            {{ konklusiResult.message }}
          </span>
          <button
            :disabled="!allPenilaianComplete || validatingKonklusi"
            @click="validasiKonklusi"
            class="btn-p"
            :style="{
              opacity: !allPenilaianComplete ? 0.5 : 1,
              cursor:  !allPenilaianComplete ? 'not-allowed' : 'pointer',
            }"
          >
            <span v-if="validatingKonklusi">Memvalidasi...</span>
            <span v-else-if="!allPenilaianComplete">Hitung Konklusi (terkunci)</span>
            <span v-else>Hitung Konklusi</span>
          </button>
        </div>
      </div>
    </div>

  </SidebarLayout>
</template>
