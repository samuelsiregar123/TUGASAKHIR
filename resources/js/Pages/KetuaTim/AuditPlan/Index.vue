<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

defineProps({
  plans:               { type: Array, default: () => [] },
  pengajuanDisetujui:  { type: Array, default: () => [] },
  pengajuanDenganPlan: { type: Array, default: () => [] },
})

const bagianLabel = { semua: 'Semua', tk_mk: 'TK & MK', fk: 'FK' }
const peranLabel  = { ketua: 'Ketua tim', anggota: 'Anggota' }
</script>

<template>
  <SidebarLayout title="Audit Plan">
    <p class="page-header" style="margin-bottom:16px">Audit Plan</p>

    <!-- Pengajuan yang BELUM punya plan → Buat Plan -->
    <div v-if="pengajuanDisetujui.length" style="margin-bottom:20px">
      <p class="section-label" style="color:#166534">
        Pengajuan disetujui — belum ada audit plan:
      </p>
      <div v-for="p in pengajuanDisetujui" :key="p.id" class="pengajuan-row">
        <div>
          <p class="pj-instansi">{{ p.nama_instansi }}</p>
          <p class="pj-url">{{ p.url_target }}</p>
        </div>
        <a :href="`/ketua-tim/audit-plan/create/${p.id}`" class="btn-buat">
          + Buat Plan
        </a>
      </div>
    </div>

    <!-- Pengajuan yang SUDAH punya plan → Lihat Plan -->
    <div v-if="pengajuanDenganPlan.length" style="margin-bottom:20px">
      <p class="section-label" style="color:#1d4ed8">
        Pengajuan dengan audit plan aktif:
      </p>
      <div v-for="p in pengajuanDenganPlan" :key="p.id" class="pengajuan-row">
        <div>
          <p class="pj-instansi">{{ p.nama_instansi }}</p>
          <p class="pj-url">{{ p.url_target }}</p>
        </div>
        <span class="btn-sudah-ada">✓ Sudah ada plan</span>
      </div>
    </div>

    <!-- Daftar audit plan aktif -->
    <p class="section-label">Daftar audit plan ({{ plans.length }} total)</p>
    <div v-if="plans.length" class="spbe-card-white" style="padding:0;overflow:hidden">
      <table class="spbe-tbl">
        <thead>
          <tr>
            <th>Instansi</th>
            <th>Mulai</th>
            <th>Selesai</th>
            <th>Tim Audit</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in plans" :key="p.id">
            <td>
              <div style="font-weight:600">{{ p.instansi }}</div>
              <div style="font-size:10px;color:#888">{{ p.url_target }}</div>
            </td>
            <td>{{ p.waktu_mulai }}</td>
            <td>{{ p.waktu_selesai }}</td>
            <td>
              <div v-for="a in p.auditors" :key="a.name + a.peran" style="font-size:10px;margin-bottom:2px">
                {{ a.name }} —
                <span :class="['badge', a.peran === 'ketua' ? 'b-ketua' : 'b-auditor']" style="font-size:9px">
                  {{ peranLabel[a.peran] ?? a.peran }}
                </span>
                <span style="color:#aaa;margin-left:4px">{{ bagianLabel[a.bagian] ?? a.bagian }}</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else class="spbe-card" style="text-align:center;color:#aaa;font-size:11px;padding:24px">
      Belum ada audit plan di sistem.
    </div>
  </SidebarLayout>
</template>

<style scoped>
.page-header   { font-size:1.1rem; font-weight:700; color:#1F4E79; }
.section-label { font-size:.75rem; font-weight:600; margin-bottom:8px; color:#374151; }

.pengajuan-row {
  display:flex; justify-content:space-between; align-items:center;
  background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px;
  padding:10px 14px; margin-bottom:8px;
}
.pj-instansi { font-size:.82rem; font-weight:600; color:#1F4E79; margin:0; }
.pj-url      { font-size:.72rem; color:#888; margin:2px 0 0; }

.btn-buat {
  background:#1F4E79; color:#fff; border:none; border-radius:6px;
  padding:6px 14px; font-size:.78rem; font-weight:600;
  text-decoration:none; white-space:nowrap;
}
.btn-buat:hover { background:#16396a; }

.btn-sudah-ada {
  font-size:.75rem; color:#166534; background:#dcfce7;
  border:1px solid #bbf7d0; border-radius:6px;
  padding:5px 12px; white-space:nowrap; font-weight:600;
}
</style>
