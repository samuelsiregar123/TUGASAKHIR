<script setup>
import { ref, computed, reactive, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'
import axios from 'axios'

const props = defineProps({
  plan:          { type: Object, default: null },
  butirByBagian: { type: Object, default: () => ({}) },
})

/* ─── Tabs ─── */
const tabs     = ['tk', 'mk', 'fk']
const tabLabel = { tk: 'Tata Kelola', mk: 'Manajemen Keamanan', fk: 'Fungsionalitas Keamanan' }
const activeTab = ref('tk')

/* ─── Reactive state (jawaban + bukti per penilaian_id) ─── */
const jawaban        = reactive({})
const buktiList      = reactive({})
const savingJawaban  = reactive({})
const uploadingBukti = reactive({})
const saveStatus     = reactive({})

const allButir = computed(() => Object.values(props.butirByBagian).flat())

allButir.value.forEach(p => {
  jawaban[p.penilaian_id]   = p.jawaban_auditee ?? ''
  buktiList[p.penilaian_id] = [...(p.bukti ?? [])]
})

/* ─── Debounced save ─── */
const saveTimers = {}
function onJawabanInput(pid) {
  clearTimeout(saveTimers[pid])
  saveStatus[pid] = ''
  saveTimers[pid] = setTimeout(() => saveJawaban(pid), 1800)
}

async function saveJawaban(pid) {
  // Simpan nilai apapun termasuk string kosong — agar penghapusan teks juga tersimpan
  savingJawaban[pid] = true
  try {
    await axios.put(`/auditee/penilaian/${pid}/jawaban`, { jawaban: jawaban[pid] })
    saveStatus[pid] = 'saved'
    setTimeout(() => { saveStatus[pid] = '' }, 2500)
  } catch {
    saveStatus[pid] = 'error'
  } finally {
    savingJawaban[pid] = false
  }
}

/* ─── File upload ─── */
async function uploadBukti(pid, jenis, event) {
  const file = event.target.files[0]
  if (!file) return

  const key = `${pid}_${jenis}`
  uploadingBukti[key] = true

  const fd = new FormData()
  fd.append('file', file)
  fd.append('jenis_acuan', jenis)

  try {
    const { data } = await axios.post(`/auditee/penilaian/${pid}/bukti`, fd)
    buktiList[pid] = [...(buktiList[pid] ?? []), data]
  } catch (e) {
    const msg = e.response?.data?.errors?.file?.[0]
            || e.response?.data?.message
            || 'Gagal mengupload file.'
    alert(msg)
  } finally {
    uploadingBukti[key] = false
    event.target.value  = ''
  }
}

async function deleteBukti(pid, buktiId) {
  if (!confirm('Hapus file ini?')) return
  try {
    await axios.delete(`/auditee/bukti/${buktiId}`)
    buktiList[pid] = buktiList[pid].filter(b => b.id !== buktiId)
  } catch {
    alert('Gagal menghapus file.')
  }
}

/* ─── Helpers ─── */
const currentButir = computed(() => props.butirByBagian[activeTab.value] ?? [])

function tabCount(tab) {
  return (props.butirByBagian[tab] ?? []).length
}

function buktiOf(pid, jenis) {
  return (buktiList[pid] ?? []).filter(b => b.jenis_acuan === jenis)
}

/* ─── Ringkasan kelengkapan ─── */
function isButirComplete(p) {
  return !!(jawaban[p.penilaian_id]?.trim()) && (buktiList[p.penilaian_id] ?? []).length > 0
}

const incompleteButir = computed(() =>
  tabs.flatMap(bag => (props.butirByBagian[bag] ?? []).filter(p => !isButirComplete(p)))
)

const tabProgress = computed(() =>
  tabs.reduce((acc, tab) => {
    const group = props.butirByBagian[tab] ?? []
    acc[tab] = { done: group.filter(p => isButirComplete(p)).length, total: group.length }
    return acc
  }, {})
)

const allComplete   = computed(() => incompleteButir.value.length === 0)
const totalComplete = computed(() => allButir.value.filter(p => isButirComplete(p)).length)
const totalAll      = computed(() => allButir.value.length)

function scrollToButir(p) {
  activeTab.value = p.bagian
  nextTick(() => {
    const el = document.getElementById(`butir-${p.penilaian_id}`)
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  })
}

const tandaiLoading = ref(false)
function tandaiSelesai() {
  if (!confirm('Tandai pengisian kuesioner sebagai SELESAI? Anda tidak dapat mengubah jawaban setelah ini.')) return
  tandaiLoading.value = true
  router.post(`/auditee/kuesioner/${props.plan.id}/tandai-selesai`, {}, {
    onFinish: () => { tandaiLoading.value = false },
  })
}
</script>

<template>
  <SidebarLayout title="Kuesioner Audit">

    <!-- No plan state -->
    <template v-if="!plan">
      <p class="page-header">Kuesioner audit</p>
      <div class="spbe-card" style="text-align:center;color:#aaa;font-size:11px;padding:32px">
        Belum ada audit plan aktif untuk instansi Anda.<br>
        Silakan ajukan audit terlebih dahulu melalui menu Pengajuan.
      </div>
    </template>

    <!-- Kuesioner view -->
    <template v-else>
      <p class="page-header" style="margin-bottom:2px">Kuesioner — {{ plan.instansi }}</p>
      <p class="page-sub" style="margin-bottom:14px">
        Periode: {{ plan.waktu_mulai }} — {{ plan.waktu_selesai }}
      </p>

      <!-- Tab nav -->
      <div style="display:flex;border-bottom:2px solid #e5e7eb;margin-bottom:16px">
        <button
          v-for="tab in tabs"
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
          <span style="color:#aaa;font-weight:400"> ({{ tabCount(tab) }})</span>
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
        <!-- Header -->
        <div style="margin-bottom:10px">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
            <span style="font-size:10px;font-weight:700;color:var(--navy);background:var(--navy-light);padding:2px 8px;border-radius:4px">
              {{ p.kode }}
            </span>
            <span v-if="p.domain" style="font-size:10px;color:#888">{{ p.domain }}</span>
          </div>
          <p style="font-size:12px;font-weight:600;margin-bottom:3px;line-height:1.5">{{ p.judul_butir }}</p>
          <p v-if="p.sumber_acuan" style="font-size:10px;color:#888;font-style:italic;margin-bottom:10px">
            Sumber: {{ p.sumber_acuan }}
          </p>
          <div v-else style="margin-bottom:10px" />

          <!-- Jawaban textarea -->
          <label class="lbl">Tanggapan / Jawaban Auditee</label>
          <textarea
            v-model="jawaban[p.penilaian_id]"
            @input="onJawabanInput(p.penilaian_id)"
            class="spbe-txta"
            rows="3"
            placeholder="Jelaskan kondisi yang ada di instansi Anda untuk butir ini..."
          />
          <p style="font-size:10px;min-height:14px;margin-top:2px">
            <span v-if="savingJawaban[p.penilaian_id]" style="color:#aaa">Menyimpan...</span>
            <span v-else-if="saveStatus[p.penilaian_id] === 'saved'" style="color:var(--green-dark)">Tersimpan ✓</span>
            <span v-else-if="saveStatus[p.penilaian_id] === 'error'" style="color:var(--red-dark)">Gagal menyimpan</span>
          </p>
        </div>

        <!-- ── EDK box ── -->
        <div style="border:1px solid #c8ddef;border-radius:6px;padding:9px 11px;margin-bottom:8px">
          <p style="font-size:11px;font-weight:700;color:var(--navy);margin-bottom:5px">
            Evaluasi Desain Kontrol (EDK)
          </p>
          <p v-if="p.acuan_edk" style="font-size:10px;color:#555;background:#f5f9fe;border-radius:4px;padding:5px 8px;margin-bottom:8px;white-space:pre-wrap;line-height:1.5">
            {{ p.acuan_edk }}
          </p>

          <div
            v-for="b in buktiOf(p.penilaian_id, 'edk')"
            :key="b.id"
            style="display:flex;align-items:center;gap:6px;font-size:10px;margin-bottom:4px"
          >
            <span style="color:var(--navy)">📄</span>
            <a :href="`/storage/${b.path_file}`" target="_blank" style="color:var(--navy)">{{ b.nama_file }}</a>
            <button
              @click="deleteBukti(p.penilaian_id, b.id)"
              style="background:none;border:none;color:var(--red-dark);cursor:pointer;font-size:11px;padding:0 2px"
              title="Hapus"
            >✕</button>
          </div>

          <label
            style="display:inline-flex;align-items:center;gap:4px;font-size:10px;color:var(--navy);padding:3px 9px;border:1px solid var(--navy);border-radius:4px;cursor:pointer;margin-top:4px"
            :style="{ opacity: uploadingBukti[`${p.penilaian_id}_edk`] ? 0.6 : 1 }"
          >
            <input
              type="file"
              style="display:none"
              accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
              :disabled="uploadingBukti[`${p.penilaian_id}_edk`]"
              @change="uploadBukti(p.penilaian_id, 'edk', $event)"
            />
            {{ uploadingBukti[`${p.penilaian_id}_edk`] ? 'Mengupload...' : '+ Upload bukti EDK' }}
          </label>
        </div>

        <!-- ── EIK box ── -->
        <div style="border:1px solid #b8d9ac;border-radius:6px;padding:9px 11px;margin-bottom:8px">
          <p style="font-size:11px;font-weight:700;color:var(--green-dark);margin-bottom:5px">
            Evaluasi Implementasi Kontrol (EIK)
          </p>
          <p v-if="p.acuan_eik" style="font-size:10px;color:#555;background:#f4faf0;border-radius:4px;padding:5px 8px;margin-bottom:8px;white-space:pre-wrap;line-height:1.5">
            {{ p.acuan_eik }}
          </p>

          <div
            v-for="b in buktiOf(p.penilaian_id, 'eik')"
            :key="b.id"
            style="display:flex;align-items:center;gap:6px;font-size:10px;margin-bottom:4px"
          >
            <span style="color:var(--green-dark)">📄</span>
            <a :href="`/storage/${b.path_file}`" target="_blank" style="color:var(--green-dark)">{{ b.nama_file }}</a>
            <button
              @click="deleteBukti(p.penilaian_id, b.id)"
              style="background:none;border:none;color:var(--red-dark);cursor:pointer;font-size:11px;padding:0 2px"
            >✕</button>
          </div>

          <label
            style="display:inline-flex;align-items:center;gap:4px;font-size:10px;color:var(--green-dark);padding:3px 9px;border:1px solid var(--green-dark);border-radius:4px;cursor:pointer;margin-top:4px"
            :style="{ opacity: uploadingBukti[`${p.penilaian_id}_eik`] ? 0.6 : 1 }"
          >
            <input
              type="file"
              style="display:none"
              accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
              :disabled="uploadingBukti[`${p.penilaian_id}_eik`]"
              @change="uploadBukti(p.penilaian_id, 'eik', $event)"
            />
            {{ uploadingBukti[`${p.penilaian_id}_eik`] ? 'Mengupload...' : '+ Upload bukti EIK' }}
          </label>
        </div>

        <!-- ── EFK box ── -->
        <div style="border:1px solid #d6c96a;border-radius:6px;padding:9px 11px">
          <p style="font-size:11px;font-weight:700;color:var(--yellow-dark);margin-bottom:5px">
            Evaluasi Efektivitas Kontrol (EFK)
          </p>
          <p v-if="p.acuan_efk" style="font-size:10px;color:#555;background:#fffbee;border-radius:4px;padding:5px 8px;margin-bottom:8px;white-space:pre-wrap;line-height:1.5">
            {{ p.acuan_efk }}
          </p>

          <div
            v-for="b in buktiOf(p.penilaian_id, 'efk')"
            :key="b.id"
            style="display:flex;align-items:center;gap:6px;font-size:10px;margin-bottom:4px"
          >
            <span style="color:var(--yellow-dark)">📄</span>
            <a :href="`/storage/${b.path_file}`" target="_blank" style="color:var(--yellow-dark)">{{ b.nama_file }}</a>
            <button
              @click="deleteBukti(p.penilaian_id, b.id)"
              style="background:none;border:none;color:var(--red-dark);cursor:pointer;font-size:11px;padding:0 2px"
            >✕</button>
          </div>

          <label
            style="display:inline-flex;align-items:center;gap:4px;font-size:10px;color:var(--yellow-dark);padding:3px 9px;border:1px solid var(--yellow-dark);border-radius:4px;cursor:pointer;margin-top:4px"
            :style="{ opacity: uploadingBukti[`${p.penilaian_id}_efk`] ? 0.6 : 1 }"
          >
            <input
              type="file"
              style="display:none"
              accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
              :disabled="uploadingBukti[`${p.penilaian_id}_efk`]"
              @change="uploadBukti(p.penilaian_id, 'efk', $event)"
            />
            {{ uploadingBukti[`${p.penilaian_id}_efk`] ? 'Mengupload...' : '+ Upload bukti EFK' }}
          </label>
        </div>
      </div>

      <div
        v-if="currentButir.length === 0"
        class="spbe-card"
        style="text-align:center;color:#aaa;font-size:11px;padding:28px"
      >
        Tidak ada butir di bagian ini.
      </div>

      <!-- ── Ringkasan Kelengkapan ── -->
      <div class="spbe-card-white" style="margin-top:24px">
        <p style="font-size:14px;font-weight:700;color:var(--navy);margin-bottom:4px">Ringkasan Kelengkapan Pengisian</p>
        <p style="font-size:11px;color:#888;margin-bottom:14px">Audit — {{ plan.instansi }}</p>

        <!-- Banner -->
        <div
          :style="{
            background: allComplete ? 'var(--green-light)' : '#FFF8E1',
            border:     `1px solid ${allComplete ? '#5a9' : '#F9A825'}`,
            color:      allComplete ? 'var(--green-dark)' : '#7B6000',
          }"
          style="border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:12px"
        >
          <span v-if="allComplete">
            <b>Pengisian lengkap!</b> Semua {{ totalAll }} butir sudah terisi dan memiliki bukti.
          </span>
          <span v-else>
            <b>Belum lengkap.</b> Masih ada {{ incompleteButir.length }} butir yang jawaban dan/atau buktinya belum diunggah. Anda bisa menyimpan dan melanjutkan nanti.
          </span>
        </div>

        <!-- Tab mini progress -->
        <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
          <div
            v-for="tab in tabs"
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

        <!-- Incomplete butir table -->
        <template v-if="incompleteButir.length > 0">
          <p style="font-size:11px;font-weight:600;color:#555;margin-bottom:8px">Butir yang belum lengkap:</p>
          <div style="overflow-x:auto;margin-bottom:16px">
            <table style="width:100%;border-collapse:collapse;font-size:11px">
              <thead>
                <tr>
                  <th style="background:var(--navy);color:#fff;font-size:10px;font-weight:600;padding:6px 8px;text-align:left;width:8%">Butir</th>
                  <th style="background:var(--navy);color:#fff;font-size:10px;font-weight:600;padding:6px 8px;text-align:left">Kontrol Pemeriksaan</th>
                  <th style="background:var(--navy);color:#fff;font-size:10px;font-weight:600;padding:6px 8px;text-align:center;width:14%">Tanggapan</th>
                  <th style="background:var(--navy);color:#fff;font-size:10px;font-weight:600;padding:6px 8px;text-align:center;width:12%">Bukti</th>
                  <th style="background:var(--navy);color:#fff;font-size:10px;font-weight:600;padding:6px 8px;text-align:center;width:10%">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in incompleteButir" :key="p.penilaian_id" style="background:#FFFBF5">
                  <td style="padding:6px 8px;border-bottom:1px solid #eee;font-weight:700;color:var(--navy)">{{ p.kode }}</td>
                  <td style="padding:6px 8px;border-bottom:1px solid #eee;font-size:10px;color:#333;line-height:1.4">{{ p.judul_butir }}</td>
                  <td style="padding:6px 8px;border-bottom:1px solid #eee;text-align:center">
                    <span
                      :style="{
                        background: jawaban[p.penilaian_id]?.trim() ? 'var(--green-light)' : 'var(--red-light)',
                        color:      jawaban[p.penilaian_id]?.trim() ? 'var(--green-dark)'  : 'var(--red-dark)',
                      }"
                      style="font-size:9px;padding:2px 7px;border-radius:10px;font-weight:600;display:inline-block;min-width:46px"
                    >{{ jawaban[p.penilaian_id]?.trim() ? 'Terisi' : 'Kosong' }}</span>
                  </td>
                  <td style="padding:6px 8px;border-bottom:1px solid #eee;text-align:center">
                    <span
                      :style="{
                        background: (buktiList[p.penilaian_id] ?? []).length > 0 ? 'var(--green-light)' : 'var(--red-light)',
                        color:      (buktiList[p.penilaian_id] ?? []).length > 0 ? 'var(--green-dark)'  : 'var(--red-dark)',
                      }"
                      style="font-size:9px;padding:2px 7px;border-radius:10px;font-weight:600;display:inline-block;min-width:46px"
                    >{{ (buktiList[p.penilaian_id] ?? []).length > 0 ? 'Ada' : 'Kosong' }}</span>
                  </td>
                  <td style="padding:6px 8px;border-bottom:1px solid #eee;text-align:center">
                    <button
                      @click="scrollToButir(p)"
                      style="font-size:10px;color:var(--navy);text-decoration:underline;background:none;border:none;cursor:pointer;padding:0"
                    >Lengkapi</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>

        <!-- Action bar -->
        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:14px;border-top:1px solid #eee">
          <span style="font-size:10px;color:#999">{{ totalComplete }} dari {{ totalAll }} butir sudah lengkap</span>
          <button
            :disabled="!allComplete || plan.status_pengisian === 'selesai' || tandaiLoading"
            @click="tandaiSelesai"
            class="btn-p"
            :style="{
              opacity:    (!allComplete || plan.status_pengisian === 'selesai') ? 0.5 : 1,
              cursor:     (!allComplete || plan.status_pengisian === 'selesai') ? 'not-allowed' : 'pointer',
            }"
          >
            <span v-if="plan.status_pengisian === 'selesai'">Pengisian Selesai ✓</span>
            <span v-else-if="tandaiLoading">Menyimpan...</span>
            <span v-else>Tandai Pengisian Selesai</span>
          </button>
        </div>
      </div>

    </template>

  </SidebarLayout>
</template>
