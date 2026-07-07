<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
  stats:      Object,
  total:      Number,
  auditAktif: Array,
})
</script>

<template>
  <SidebarLayout title="Dashboard Admin">
    <p class="page-header">Dashboard admin</p>

    <!-- Stat cards — wireframe h.3 -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-num">{{ stats.admin }}</div>
        <div class="stat-lbl">Admin</div>
      </div>
      <div class="stat-card">
        <div class="stat-num">{{ stats.ketua_tim }}</div>
        <div class="stat-lbl">Ketua tim</div>
      </div>
      <div class="stat-card">
        <div class="stat-num">{{ stats.auditor }}</div>
        <div class="stat-lbl">Auditor</div>
      </div>
      <div class="stat-card">
        <div class="stat-num">{{ stats.auditee }}</div>
        <div class="stat-lbl">Auditee</div>
      </div>
    </div>
    <p class="page-sub">Total {{ total }} pengguna terdaftar</p>

    <!-- Audit aktif -->
    <div v-if="auditAktif.length" style="margin-top:20px">
      <p style="font-size:12px;font-weight:600;margin-bottom:10px;color:#444">Audit plan terbaru</p>
      <div class="spbe-card-white" style="padding:0;overflow:hidden">
        <table class="spbe-tbl">
          <thead>
            <tr>
              <th>ID</th>
              <th>Instansi</th>
              <th>Mulai</th>
              <th>Selesai</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="a in auditAktif" :key="a.id">
              <td style="color:#888">#{{ a.id }}</td>
              <td>{{ a.instansi }}</td>
              <td>{{ a.waktu_mulai }}</td>
              <td>{{ a.waktu_selesai }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div v-else class="spbe-card" style="color:#aaa;font-size:11px;text-align:center;padding:24px">
      Belum ada audit plan.
    </div>
  </SidebarLayout>
</template>
