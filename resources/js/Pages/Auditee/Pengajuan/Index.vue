<script setup>
import { router } from '@inertiajs/vue3'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

defineProps({ pengajuan: Array })

const statusBadge = {
  menunggu:  { cls: 'b-wait',  label: 'Menunggu review' },
  disetujui: { cls: 'b-ok',    label: 'Disetujui' },
  ditolak:   { cls: 'b-tolak', label: 'Ditolak' },
}

function cancel(id) {
  if (confirm('Batalkan pengajuan ini?')) {
    router.delete(`/auditee/pengajuan/${id}/cancel`)
  }
}
</script>

<template>
  <SidebarLayout title="Pengajuan Audit">
    <div class="section-row">
      <p class="page-header" style="margin:0">Pengajuan audit saya</p>
      <a href="/auditee/pengajuan/create" class="btn-p">+ Pengajuan baru</a>
    </div>

    <div v-if="pengajuan.length">
      <div v-for="p in pengajuan" :key="p.id" class="spbe-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
          <div>
            <p style="font-size:12px;font-weight:600">{{ p.nama_instansi }}</p>
            <p style="font-size:11px;color:#888;margin-top:2px">{{ p.url_target }}</p>
          </div>
          <span class="badge" :class="statusBadge[p.status]?.cls">
            {{ statusBadge[p.status]?.label }}
          </span>
        </div>

        <p v-if="p.status === 'ditolak' && p.alasan_tolak"
           style="font-size:11px;color:var(--red-dark);background:var(--red-light);padding:6px 10px;border-radius:5px;margin-bottom:8px">
          Alasan penolakan: {{ p.alasan_tolak }}
        </p>

        <div style="display:flex;justify-content:space-between;align-items:center">
          <span style="font-size:10px;color:#bbb">{{ p.created_at }}</span>
          <button
            v-if="p.status === 'menunggu'"
            class="btn-no"
            style="font-size:10px;padding:3px 10px"
            @click="cancel(p.id)"
          >Batalkan</button>
        </div>
      </div>
    </div>

    <div v-else class="spbe-card" style="text-align:center;color:#aaa;font-size:11px;padding:28px">
      Belum ada pengajuan. <a href="/auditee/pengajuan/create" style="color:var(--navy);font-weight:600">Ajukan sekarang →</a>
    </div>
  </SidebarLayout>
</template>
