<script setup>
import { Link } from '@inertiajs/vue3'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

defineProps({ plans: Array })

const konklusiLabel = {
  memadai:           'Memadai',
  perlu_peningkatan: 'Perlu Peningkatan',
  tidak_memadai:     'Tidak Memadai',
}
const konklusiColor = {
  memadai:           '#166534',
  perlu_peningkatan: '#92400e',
  tidak_memadai:     '#991b1b',
}

function pct(selesai, total) {
  if (!total) return 0
  return Math.round((selesai / total) * 100)
}
</script>

<template>
  <SidebarLayout title="Daftar Audit">
    <p class="page-header">Daftar Audit</p>

    <div class="spbe-card-white" style="padding:0;overflow:hidden">
      <table class="spbe-tbl">
        <thead>
          <tr>
            <th>Instansi</th>
            <th>Ketua Tim</th>
            <th>Auditor</th>
            <th>Progress</th>
            <th>Konklusi</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in plans" :key="p.id">
            <td>
              <div>{{ p.instansi }}</div>
              <div style="font-size:10px;color:#aaa">{{ p.tahun ?? '-' }}</div>
            </td>
            <td>{{ p.ketua_tim }}</td>
            <td style="font-size:11px;color:#666">
              <span v-if="p.auditors.length">{{ p.auditors.join(', ') }}</span>
              <span v-else style="color:#bbb">-</span>
            </td>
            <td style="min-width:110px">
              <div style="font-size:11px;color:#555;margin-bottom:3px">
                {{ p.selesai_butir }}/{{ p.total_butir }} butir
              </div>
              <div style="background:#e5e7eb;border-radius:3px;height:5px;width:100px">
                <div :style="`width:${pct(p.selesai_butir, p.total_butir)}%;background:#1F4E79;height:5px;border-radius:3px`" />
              </div>
              <div style="font-size:10px;color:#aaa;margin-top:2px">{{ pct(p.selesai_butir, p.total_butir) }}%</div>
            </td>
            <td>
              <span v-if="p.konklusi_keseluruhan"
                    :style="`color:${konklusiColor[p.konklusi_keseluruhan]};font-size:11px;font-weight:600`">
                {{ konklusiLabel[p.konklusi_keseluruhan] ?? p.konklusi_keseluruhan }}
              </span>
              <span v-else style="color:#bbb;font-size:11px">Belum dihitung</span>
            </td>
            <td>
              <Link :href="`/supervisor/audit/${p.id}`" class="btn-sm">Detail</Link>
            </td>
          </tr>
          <tr v-if="!plans.length">
            <td colspan="6" style="text-align:center;color:#aaa;padding:20px">Belum ada audit.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </SidebarLayout>
</template>
