<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({ approval: Object })

const catatanTolak = ref('')
const fileTte      = ref(null)
const errors       = ref({})
const loading      = ref(false)

function onFilePick(e) { fileTte.value = e.target.files[0] ?? null }

function submitSetujui() {
  errors.value = {}
  if (!fileTte.value) { errors.value.file_tte = 'File TTE wajib diunggah.'; return }
  loading.value = true
  const form = new FormData()
  form.append('file_tte', fileTte.value)
  router.post(`/supervisor/persetujuan/${props.approval.id}/setujui`, form, {
    forceFormData: true,
    onError: (e) => { errors.value = e; loading.value = false },
    onFinish: () => { loading.value = false },
  })
}

function submitTolak() {
  errors.value = {}
  loading.value = true
  router.post(`/supervisor/persetujuan/${props.approval.id}/tolak`, { catatan: catatanTolak.value }, {
    onError: (e) => { errors.value = e; loading.value = false },
    onFinish: () => { loading.value = false },
  })
}

const statusLabel = { pending: 'Menunggu', disetujui: 'Disetujui', ditolak: 'Ditolak' }
const statusColor = { pending: '#92400e', disetujui: '#166534', ditolak: '#991b1b' }
</script>

<template>
  <SidebarLayout :title="`Persetujuan — ${approval.instansi}`">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
      <Link href="/supervisor/persetujuan" class="btn-sm">← Kembali</Link>
      <p class="page-header" style="margin:0">{{ approval.instansi }}</p>
    </div>

    <!-- Info -->
    <div class="spbe-card-white" style="margin-bottom:16px">
      <table style="font-size:12px;width:100%;border-collapse:collapse">
        <tr><td style="color:#888;width:160px;padding:3px 0">Aplikasi</td><td>{{ approval.aplikasi }}</td></tr>
        <tr><td style="color:#888;padding:3px 0">Diajukan oleh</td><td>{{ approval.submitted_by }}</td></tr>
        <tr><td style="color:#888;padding:3px 0">Tanggal pengajuan</td><td>{{ approval.submitted_at }}</td></tr>
        <tr><td style="color:#888;padding:3px 0">Status</td>
          <td><b :style="`color:${statusColor[approval.status]}`">{{ statusLabel[approval.status] }}</b></td></tr>
        <tr v-if="approval.reviewed_by"><td style="color:#888;padding:3px 0">Ditinjau oleh</td><td>{{ approval.reviewed_by }}</td></tr>
        <tr v-if="approval.reviewed_at"><td style="color:#888;padding:3px 0">Tanggal tinjauan</td><td>{{ approval.reviewed_at }}</td></tr>
        <tr v-if="approval.catatan"><td style="color:#888;padding:3px 0">Catatan</td><td style="color:#991b1b">{{ approval.catatan }}</td></tr>
      </table>
    </div>

    <!-- File links -->
    <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap">
      <a v-if="approval.file_url" :href="`/supervisor/persetujuan/${approval.id}/download`"
         class="btn-sm" style="text-decoration:none">Unduh LHAK</a>
      <a v-if="approval.file_tte_url" :href="approval.file_tte_url" target="_blank"
         class="btn-sm" style="text-decoration:none">Lihat LHAK ber-TTE</a>
    </div>

    <!-- Actions — hanya saat pending -->
    <template v-if="approval.status === 'pending'">
      <!-- Setujui -->
      <div class="spbe-card-white" style="margin-bottom:16px">
        <p style="font-size:12px;font-weight:600;margin:0 0 10px">Setujui dengan TTE</p>
        <label class="lbl">Upload LHAK ber-TTE (PDF)</label>
        <input type="file" accept=".pdf" @change="onFilePick" class="spbe-inp" style="margin-bottom:4px" />
        <p v-if="errors.file_tte" class="err-msg">{{ errors.file_tte }}</p>
        <button class="btn-p" style="margin-top:8px" :disabled="loading" @click="submitSetujui">
          Setujui &amp; Upload TTE
        </button>
      </div>

      <!-- Tolak -->
      <div class="spbe-card-white">
        <p style="font-size:12px;font-weight:600;margin:0 0 10px">Tolak pengajuan</p>
        <label class="lbl">Catatan penolakan <span style="color:#ef4444">*</span></label>
        <textarea v-model="catatanTolak" class="spbe-inp" rows="3"
                  style="resize:vertical;margin-bottom:4px"
                  placeholder="Tuliskan alasan penolakan..." />
        <p v-if="errors.catatan" class="err-msg">{{ errors.catatan }}</p>
        <button class="btn-no" style="margin-top:8px" :disabled="loading" @click="submitTolak">
          Tolak LHAK
        </button>
      </div>
    </template>
  </SidebarLayout>
</template>
