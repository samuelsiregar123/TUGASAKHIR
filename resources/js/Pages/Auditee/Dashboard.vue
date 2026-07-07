<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

defineProps({
  pengajuan:    Array,
  namaInstansi: String,
})

const statusBadge = {
  menunggu:  { cls: 'b-wait', label: 'Menunggu review' },
  disetujui: { cls: 'b-prog', label: 'Disetujui' },
  ditolak:   { cls: 'b-tolak', label: 'Ditolak' },
}
</script>

<template>
  <SidebarLayout title="Dashboard Auditee">
    <p class="page-header">Dashboard auditee</p>
    <p class="page-sub">Instansi: <b>{{ namaInstansi || '(belum diatur)' }}</b></p>

    <!-- Audit aktif -->
    <div v-if="pengajuan.length">
      <div v-for="p in pengajuan" :key="p.id" class="spbe-card" style="margin-bottom:12px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
          <p style="font-size:12px;font-weight:600">{{ p.nama_instansi }} — {{ p.url_target }}</p>
          <span class="badge" :class="statusBadge[p.status]?.cls">
            {{ statusBadge[p.status]?.label }}
          </span>
        </div>

        <div v-if="p.plan" style="display:grid;grid-template-columns:1fr 1fr;gap:6px;font-size:11px">
          <div><span style="color:#888">Mulai:</span> {{ p.plan.waktu_mulai }}</div>
          <div><span style="color:#888">Selesai:</span> {{ p.plan.waktu_selesai }}</div>
        </div>

        <p v-if="p.status === 'ditolak' && p.alasan_tolak" style="font-size:11px;color:var(--red-dark);margin-top:6px">
          Alasan penolakan: {{ p.alasan_tolak }}
        </p>

        <p style="font-size:10px;color:#bbb;margin-top:6px">Diajukan {{ p.created_at }}</p>
      </div>
    </div>

    <div v-else class="spbe-card" style="text-align:center;color:#aaa;font-size:11px;padding:28px">
      Belum ada pengajuan audit.
      <br>
      <a href="/auditee/pengajuan/create" style="color:var(--navy);font-weight:600;margin-top:8px;display:inline-block">
        + Ajukan audit sekarang
      </a>
    </div>
  </SidebarLayout>
</template>
