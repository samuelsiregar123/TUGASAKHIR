<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({ namaInstansi: String })

const form = ref({
  nama_instansi: props.namaInstansi || '',
  url_target:    '',
  daftar_tim:    '',
  nda:           null,
})
const errors    = ref({})
const ndaName   = ref('')
const submitting = ref(false)

function onFileChange(e) {
  const f = e.target.files[0]
  if (f) { form.value.nda = f; ndaName.value = f.name }
}

function submit() {
  submitting.value = true
  const data = new FormData()
  data.append('nama_instansi', form.value.nama_instansi)
  data.append('url_target',    form.value.url_target)
  data.append('daftar_tim',    form.value.daftar_tim)
  if (form.value.nda) data.append('nda', form.value.nda)

  router.post('/auditee/pengajuan', data, {
    forceFormData: true,
    onError: (e) => { errors.value = e; submitting.value = false },
    onSuccess: () => { submitting.value = false },
  })
}
</script>

<template>
  <SidebarLayout title="Pengajuan Audit Baru">
    <p class="page-header">Pengajuan audit baru</p>

    <div class="spbe-card-white">
      <!-- Row 1 -->
      <div class="grid2">
        <div>
          <label class="lbl">Nama instansi</label>
          <input v-model="form.nama_instansi" class="spbe-inp" :class="{ err: errors.nama_instansi }"
                 placeholder="Diskominfo Kota Bogor" />
          <p v-if="errors.nama_instansi" class="err-msg">{{ errors.nama_instansi }}</p>
        </div>
        <div>
          <label class="lbl">URL target aplikasi web</label>
          <input v-model="form.url_target" class="spbe-inp" :class="{ err: errors.url_target }"
                 placeholder="https://app.instansi.go.id" />
          <p v-if="errors.url_target" class="err-msg">{{ errors.url_target }}</p>
        </div>
      </div>

      <!-- Daftar tim -->
      <div style="margin-bottom:14px">
        <label class="lbl">Daftar tim auditee <span style="color:#bbb">(opsional)</span></label>
        <input v-model="form.daftar_tim" class="spbe-inp"
               placeholder="Nama 1, Nama 2, Nama 3" />
      </div>

      <!-- Upload NDA -->
      <div style="margin-bottom:20px">
        <label class="lbl">Upload dokumen NDA <span style="color:#bbb">(PDF, maks 5 MB)</span></label>
        <div
          style="border:1px dashed #ddd;border-radius:6px;padding:16px;text-align:center;cursor:pointer"
          @click="$refs.ndaInput.click()"
        >
          <p v-if="ndaName" style="font-size:11px;color:#444">
            <span style="background:var(--red-light);color:var(--red-dark);padding:2px 6px;border-radius:3px;font-size:9px;font-weight:700">PDF</span>
            &nbsp;{{ ndaName }}
          </p>
          <p v-else style="font-size:11px;color:#aaa">Klik untuk upload PDF NDA</p>
        </div>
        <input ref="ndaInput" type="file" accept=".pdf" style="display:none" @change="onFileChange" />
        <p v-if="errors.nda" class="err-msg">{{ errors.nda }}</p>
      </div>

      <div style="display:flex;gap:10px">
        <a href="/auditee/pengajuan" class="btn-cancel">Batal</a>
        <button class="btn-p" :disabled="submitting" @click="submit">
          {{ submitting ? 'Mengirim...' : 'Submit pengajuan' }}
        </button>
      </div>
    </div>
  </SidebarLayout>
</template>
