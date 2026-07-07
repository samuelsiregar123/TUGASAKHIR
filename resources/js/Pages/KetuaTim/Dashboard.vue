<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

defineProps({
  auditAktif:     Number,
  temuanTerbuka:  Number,
  menungguReview: Number,
  auditTerbaru:   Array,
})
</script>

<template>
  <SidebarLayout title="Dashboard Ketua Tim">
    <p class="page-header">Dashboard ketua tim</p>

    <!-- Stats — wireframe h.10 -->
    <div class="stat-grid-3">
      <div class="stat-card">
        <div class="stat-num">{{ auditAktif }}</div>
        <div class="stat-lbl">Audit aktif</div>
      </div>
      <div class="stat-card">
        <div class="stat-num" style="color:var(--red-dark)">{{ temuanTerbuka }}</div>
        <div class="stat-lbl">Temuan terbuka</div>
      </div>
      <div class="stat-card">
        <div class="stat-num" style="color:var(--yellow-dark)">{{ menungguReview }}</div>
        <div class="stat-lbl">Menunggu review</div>
      </div>
    </div>

    <!-- Audit terbaru -->
    <p style="font-size:12px;font-weight:600;margin-bottom:10px;color:#444">Audit terbaru</p>
    <div v-if="auditTerbaru.length" class="spbe-card-white" style="padding:0;overflow:hidden">
      <table class="spbe-tbl">
        <thead>
          <tr>
            <th>Instansi</th>
            <th>Mulai</th>
            <th>Selesai</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in auditTerbaru" :key="a.id">
            <td>{{ a.instansi }}</td>
            <td style="color:#888">{{ a.waktu_mulai }}</td>
            <td style="color:#888">{{ a.waktu_selesai }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else class="spbe-card" style="text-align:center;color:#aaa;font-size:11px;padding:24px">
      Belum ada audit plan.
    </div>

    <!-- Quick links -->
    <div style="display:flex;gap:10px;margin-top:16px">
      <a href="/ketua-tim/pengajuan" class="btn-p">Lihat pengajuan masuk</a>
      <a href="/ketua-tim/audit-plan" class="btn-ok">Kelola audit plan</a>
    </div>
  </SidebarLayout>
</template>
