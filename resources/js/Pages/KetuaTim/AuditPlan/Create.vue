<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
  pengajuan: Object,
  auditors:  Array,
})

const waktu_mulai   = ref('')
const waktu_selesai = ref('')
const auditorRows   = ref([{ user_id: '', peran: 'anggota', bagian: 'semua' }])
const errors        = ref({})
const submitting    = ref(false)

const usedIds = computed(() => auditorRows.value.map(a => a.user_id).filter(Boolean))
const availableFor = (idx) => props.auditors.filter(a => !usedIds.value.includes(a.id) || auditorRows.value[idx].user_id == a.id)

function addAuditor() {
  auditorRows.value.push({ user_id: '', peran: 'anggota', bagian: 'semua' })
}

function removeAuditor(idx) {
  if (auditorRows.value.length > 1) auditorRows.value.splice(idx, 1)
}

function submit() {
  submitting.value = true
  errors.value = {}
  router.post('/ketua-tim/audit-plan', {
    audit_request_id: props.pengajuan.id,
    waktu_mulai:      waktu_mulai.value,
    waktu_selesai:    waktu_selesai.value,
    auditors:         auditorRows.value,
  }, {
    onError: (e) => { errors.value = e; submitting.value = false },
    onSuccess: () => { submitting.value = false },
  })
}
</script>

<template>
  <SidebarLayout title="Buat Audit Plan">
    <p class="page-header">Audit plan — {{ pengajuan.nama_instansi }}</p>
    <p class="page-sub">{{ pengajuan.url_target }}</p>

    <!-- Auditor cards — wireframe h.12 -->
    <div v-for="(row, idx) in auditorRows" :key="idx" class="spbe-card-white" style="margin-bottom:12px">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
        <p style="font-size:11px;font-weight:600">Auditor {{ idx + 1 }}</p>
        <button v-if="auditorRows.length > 1" class="btn-no" style="font-size:10px;padding:2px 8px" @click="removeAuditor(idx)">Hapus</button>
      </div>

      <!-- Pilih auditor -->
      <label class="lbl">Pilih auditor</label>
      <select v-model="row.user_id" class="spbe-sel" style="margin-bottom:10px">
        <option value="">— Pilih auditor —</option>
        <option v-for="a in availableFor(idx)" :key="a.id" :value="a.id">
          {{ a.name }} ({{ a.role === 'ketua_tim' ? 'Ketua tim' : 'Auditor' }})
        </option>
      </select>
      <p v-if="errors[`auditors.${idx}.user_id`]" class="err-msg">{{ errors[`auditors.${idx}.user_id`] }}</p>

      <!-- Peran -->
      <label class="lbl">Peran</label>
      <div style="display:flex;gap:20px;font-size:11px;margin:4px 0 10px">
        <label style="display:flex;align-items:center;gap:5px;cursor:pointer">
          <input type="radio" v-model="row.peran" value="ketua" />
          Ketua tim
        </label>
        <label style="display:flex;align-items:center;gap:5px;cursor:pointer">
          <input type="radio" v-model="row.peran" value="anggota" />
          Anggota
        </label>
      </div>

      <!-- Bagian penilaian -->
      <label class="lbl">Bagian penilaian</label>
      <div style="display:flex;gap:20px;font-size:11px;margin:4px 0">
        <label style="display:flex;align-items:center;gap:5px;cursor:pointer">
          <input type="radio" v-model="row.bagian" value="semua" />
          Semua bagian (150 butir)
        </label>
        <label style="display:flex;align-items:center;gap:5px;cursor:pointer">
          <input type="radio" v-model="row.bagian" value="tk_mk" />
          TK &amp; MK (75 butir)
        </label>
        <label style="display:flex;align-items:center;gap:5px;cursor:pointer">
          <input type="radio" v-model="row.bagian" value="fk" />
          Fungsionalitas (75 butir)
        </label>
      </div>
    </div>

    <button class="btn-sm" style="margin-bottom:14px" @click="addAuditor">+ Tambah auditor</button>
    <p v-if="errors.auditors" class="err-msg" style="margin-bottom:10px">{{ errors.auditors }}</p>

    <!-- Tanggal -->
    <div class="grid2">
      <div>
        <label class="lbl">Waktu mulai</label>
        <input v-model="waktu_mulai" type="date" class="spbe-inp" :class="{ err: errors.waktu_mulai }" />
        <p v-if="errors.waktu_mulai" class="err-msg">{{ errors.waktu_mulai }}</p>
      </div>
      <div>
        <label class="lbl">Waktu selesai</label>
        <input v-model="waktu_selesai" type="date" class="spbe-inp" :class="{ err: errors.waktu_selesai }" />
        <p v-if="errors.waktu_selesai" class="err-msg">{{ errors.waktu_selesai }}</p>
      </div>
    </div>

    <div style="display:flex;gap:10px;margin-top:14px">
      <a href="/ketua-tim/audit-plan" class="btn-cancel" style="text-decoration:none;display:flex;align-items:center">Batal</a>
      <button class="btn-p" :disabled="submitting" @click="submit">
        {{ submitting ? 'Menyimpan...' : 'Simpan audit plan' }}
      </button>
    </div>
  </SidebarLayout>
</template>
