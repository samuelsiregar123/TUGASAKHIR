<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

defineProps({ penugasan: Array })

const bagianLabel = { semua: 'Semua bagian', tk_mk: 'TK & MK', fk: 'Fungsionalitas' }
const peranLabel  = { ketua: 'Ketua tim', anggota: 'Anggota' }
</script>

<template>
  <SidebarLayout title="Dashboard Auditor">
    <p class="page-header" style="margin-bottom:4px">Dashboard auditor</p>

    <div v-if="penugasan.length">
      <div v-for="p in penugasan" :key="p.plan_id" class="spbe-card" style="margin-bottom:12px">
        <p style="font-size:12px;font-weight:600;margin-bottom:8px">Penugasan aktif</p>

        <div class="grid2" style="font-size:11px">
          <div><span style="color:#888">Audit:</span> {{ p.instansi }}</div>
          <div><span style="color:#888">Peran:</span> {{ peranLabel[p.peran] }}</div>
          <div><span style="color:#888">Bagian:</span> {{ bagianLabel[p.bagian] }}</div>
          <div><span style="color:#888">Periode:</span> {{ p.waktu_mulai }} — {{ p.waktu_selesai }}</div>
        </div>

        <!-- Progress bar -->
        <div style="margin-top:12px">
          <p style="font-size:12px;font-weight:600;margin-bottom:8px">Progres penilaian</p>

          <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:3px">
            <span>{{ bagianLabel[p.bagian] }} ({{ p.total_butir }} butir)</span>
            <span>{{ p.selesai }}/{{ p.total_butir }}</span>
          </div>
          <div class="prog-bar" style="margin-bottom:8px">
            <div class="prog-fill" :style="{ width: p.persen + '%' }" />
          </div>
          <p style="font-size:10px;color:#888">{{ p.persen }}% selesai</p>
        </div>

        <div style="margin-top:10px">
          <a :href="`/auditor/penilaian?plan=${p.plan_id}`" class="btn-p" style="text-decoration:none;display:inline-block">
            Mulai penilaian
          </a>
        </div>
      </div>
    </div>

    <div v-else class="spbe-card" style="text-align:center;color:#aaa;font-size:11px;padding:28px">
      Belum ada penugasan audit.
    </div>
  </SidebarLayout>
</template>
