<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

defineProps({ pengajuan: Array })

const showTolakModal = ref(false)
const targetId       = ref(null)
const alasan         = ref('')
const alasanErr      = ref('')

const statusBadge = {
  menunggu:  { cls: 'b-wait',  label: 'Menunggu' },
  disetujui: { cls: 'b-ok',    label: 'Disetujui' },
  ditolak:   { cls: 'b-tolak', label: 'Ditolak' },
}

function setujui(id) {
  if (confirm('Setujui pengajuan ini?')) {
    router.post(`/ketua-tim/pengajuan/${id}/setujui`)
  }
}

function openTolak(id) {
  targetId.value = id
  alasan.value   = ''
  alasanErr.value = ''
  showTolakModal.value = true
}

function submitTolak() {
  if (!alasan.value.trim()) { alasanErr.value = 'Alasan wajib diisi.'; return }
  router.post(`/ketua-tim/pengajuan/${targetId.value}/tolak`, { alasan_tolak: alasan.value }, {
    onError: (e) => { alasanErr.value = e.alasan_tolak || 'Terjadi kesalahan.' },
    onSuccess: () => { showTolakModal.value = false },
  })
}
</script>

<template>
  <SidebarLayout title="Review Pengajuan">
    <p class="page-header">Review pengajuan audit</p>

    <div v-if="pengajuan.length">
      <div v-for="p in pengajuan" :key="p.id" class="spbe-card-white" style="margin-bottom:14px">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px">
          <p style="font-size:12px;font-weight:600">{{ p.nama_instansi }}</p>
          <span class="badge" :class="statusBadge[p.status]?.cls">{{ statusBadge[p.status]?.label }}</span>
        </div>

        <!-- Detail grid — wireframe h.11 -->
        <div class="grid2" style="font-size:11px;margin-bottom:12px">
          <div><span style="color:#888">URL:</span> {{ p.url_target }}</div>
          <div>
            <span style="color:#888">NDA:</span>&nbsp;
            <a v-if="p.path_nda" :href="`/storage/${p.path_nda}`" target="_blank" style="color:var(--navy)">Lihat dokumen</a>
            <span v-else style="color:#bbb">Tidak diupload</span>
          </div>
          <div><span style="color:#888">Tim:</span> {{ p.daftar_tim || '—' }}</div>
          <div><span style="color:#888">Diajukan:</span> {{ p.created_at }}</div>
          <div><span style="color:#888">Oleh:</span> {{ p.auditee_name }}</div>
        </div>

        <p v-if="p.status === 'ditolak' && p.alasan_tolak"
           style="font-size:11px;color:var(--red-dark);background:var(--red-light);padding:6px 10px;border-radius:5px;margin-bottom:10px">
          Alasan: {{ p.alasan_tolak }}
        </p>

        <!-- Actions — hanya untuk status menunggu -->
        <div v-if="p.status === 'menunggu'" style="display:flex;gap:8px">
          <button class="btn-ok" @click="setujui(p.id)">✓ Setujui</button>
          <button class="btn-no" @click="openTolak(p.id)">✕ Tolak</button>
        </div>

        <!-- Buat audit plan kalau disetujui -->
        <div v-if="p.status === 'disetujui'">
          <a :href="`/ketua-tim/audit-plan/create/${p.id}`" class="btn-p" style="display:inline-block;text-decoration:none">
            + Buat audit plan
          </a>
        </div>
      </div>
    </div>

    <div v-else class="spbe-card" style="text-align:center;color:#aaa;font-size:11px;padding:28px">
      Tidak ada pengajuan audit masuk.
    </div>

    <!-- Modal tolak -->
    <div v-if="showTolakModal" class="modal-overlay" @click.self="showTolakModal=false">
      <div class="modal-box" style="width:320px">
        <p class="modal-title">
          Tolak pengajuan
          <span class="modal-close" @click="showTolakModal=false">✕</span>
        </p>
        <label class="lbl">Alasan penolakan</label>
        <textarea v-model="alasan" class="spbe-txta" :class="{ err: alasanErr }"
                  rows="3" placeholder="Jelaskan alasan penolakan pengajuan ini..." />
        <p v-if="alasanErr" class="err-msg">{{ alasanErr }}</p>
        <div class="modal-footer">
          <button style="background:#fff;color:#888;border:1px solid #ddd" @click="showTolakModal=false">Batal</button>
          <button style="background:var(--red-light);color:var(--red-dark);border:none" @click="submitTolak">Tolak pengajuan</button>
        </div>
      </div>
    </div>

  </SidebarLayout>
</template>
