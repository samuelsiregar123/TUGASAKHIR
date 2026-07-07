<script setup>
import { Link } from '@inertiajs/vue3'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
  plan:         Object,
  auditors:     Array,
  progressTK:   Object,
  progressMK:   Object,
  progressFK:   Object,
  totalTemuan:  Number,
  tlSelesai:    Number,
  lhakStatus:   String,
})

function pct(selesai, total) {
  if (!total) return 0
  return Math.round((selesai / total) * 100)
}

const konklusiLabel = {
  memadai:           'Memadai',
  perlu_peningkatan: 'Perlu Peningkatan',
  tidak_memadai:     'Tidak Memadai',
}

const lhakStatusConfig = {
  belum_generate: { label: 'Belum Generate',          color: '#888',   bg: '#f5f5f4' },
  sudah_generate: { label: 'LHAK Sudah Digenerate',   color: '#0369a1', bg: '#e0f2fe' },
  diajukan:       { label: 'Diajukan ke Supervisor',  color: '#92400e', bg: '#fef9c3' },
  disetujui:      { label: 'Disetujui oleh Supervisor', color: '#166534', bg: '#dcfce7' },
  ditolak:        { label: 'Ditolak oleh Supervisor', color: '#991b1b', bg: '#fee2e2' },
}
</script>

<template>
  <SidebarLayout :title="`Audit — ${plan.instansi}`">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
      <Link href="/supervisor/audit" class="btn-sm">← Kembali</Link>
      <p class="page-header" style="margin:0">{{ plan.instansi }}</p>
    </div>

    <!-- Info + Tim -->
    <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px">
      <div class="spbe-card-white" style="flex:2;min-width:240px">
        <p style="font-size:11px;color:#888;margin:0 0 10px;font-weight:600">Informasi Audit</p>
        <table style="font-size:12px;width:100%;border-collapse:collapse">
          <tr><td style="color:#888;width:130px;padding:3px 0">Aplikasi</td><td>{{ plan.aplikasi }}</td></tr>
          <tr><td style="color:#888;padding:3px 0">Auditee</td><td>{{ plan.auditee }}</td></tr>
          <tr><td style="color:#888;padding:3px 0">Waktu mulai</td><td>{{ plan.waktu_mulai ?? '-' }}</td></tr>
          <tr><td style="color:#888;padding:3px 0">Waktu selesai</td><td>{{ plan.waktu_selesai ?? '-' }}</td></tr>
          <tr><td style="color:#888;padding:3px 0">Konklusi</td>
            <td>
              <b v-if="plan.konklusi_keseluruhan">{{ konklusiLabel[plan.konklusi_keseluruhan] ?? plan.konklusi_keseluruhan }}</b>
              <span v-else style="color:#bbb">Belum dihitung</span>
            </td>
          </tr>
        </table>
      </div>

      <div class="spbe-card-white" style="flex:1;min-width:180px">
        <p style="font-size:11px;color:#888;margin:0 0 10px;font-weight:600">Tim Auditor</p>
        <div v-for="a in auditors" :key="a.name + a.peran" style="font-size:12px;margin-bottom:4px">
          {{ a.name }}
          <span style="color:#888;font-size:10px">({{ a.peran === 'ketua_tim' ? 'Ketua Tim' : 'Anggota' }})</span>
        </div>
        <div v-if="!auditors.length" style="color:#aaa;font-size:12px">Belum ada tim.</div>
      </div>
    </div>

    <!-- Progress per bagian -->
    <div class="spbe-card-white" style="margin-bottom:16px">
      <p style="font-size:11px;color:#888;margin:0 0 12px;font-weight:600">Progress Penilaian per Bagian</p>
      <div style="display:flex;gap:16px;flex-wrap:wrap">
        <div v-for="(prog, label) in { 'Tata Kelola (TK)': progressTK, 'Manajemen Keamanan (MK)': progressMK, 'Fungsionalitas Keamanan (FK)': progressFK }"
             :key="label" style="flex:1;min-width:160px">
          <div style="font-size:11px;font-weight:600;margin-bottom:4px">{{ label }}</div>
          <div style="font-size:12px;color:#555;margin-bottom:4px">{{ prog.selesai }}/{{ prog.total }} butir</div>
          <div style="background:#e5e7eb;border-radius:4px;height:6px">
            <div :style="`width:${pct(prog.selesai, prog.total)}%;background:#1F4E79;height:6px;border-radius:4px;transition:width .3s`" />
          </div>
          <div style="font-size:10px;color:#888;margin-top:3px">{{ pct(prog.selesai, prog.total) }}%</div>
        </div>
      </div>
    </div>

    <!-- Statistik + LHAK -->
    <div style="display:flex;gap:16px;flex-wrap:wrap">
      <div class="spbe-card-white" style="flex:1;min-width:140px;text-align:center;padding:16px">
        <div style="font-size:1.6rem;font-weight:700;color:#991b1b">{{ totalTemuan }}</div>
        <div style="font-size:11px;color:#888;margin-top:2px">Total Temuan Aktif</div>
      </div>
      <div class="spbe-card-white" style="flex:1;min-width:140px;text-align:center;padding:16px">
        <div style="font-size:1.6rem;font-weight:700;color:#166534">{{ tlSelesai }}</div>
        <div style="font-size:11px;color:#888;margin-top:2px">Tindak Lanjut Selesai</div>
      </div>
      <div class="spbe-card-white" style="flex:2;min-width:200px;padding:16px">
        <div style="font-size:11px;color:#888;margin-bottom:8px;font-weight:600">Status LHAK</div>
        <span :style="`display:inline-block;padding:4px 12px;border-radius:12px;font-size:12px;font-weight:600;background:${lhakStatusConfig[lhakStatus]?.bg ?? '#f5f5f4'};color:${lhakStatusConfig[lhakStatus]?.color ?? '#888'}`">
          {{ lhakStatusConfig[lhakStatus]?.label ?? lhakStatus }}
        </span>
      </div>
    </div>
  </SidebarLayout>
</template>
