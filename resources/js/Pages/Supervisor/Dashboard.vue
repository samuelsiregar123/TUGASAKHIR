<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

defineProps({
  totalAudit:        Number,
  totalBerjalan:     Number,
  totalSelesai:      Number,
  totalMenungguLhak: Number,
})
</script>

<template>
  <SidebarLayout title="Dashboard Supervisor">
    <p class="page-header">Dashboard Supervisor</p>

    <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:24px">
      <div class="spbe-card-white" style="flex:1;min-width:140px;text-align:center;padding:20px 16px">
        <div style="font-size:2rem;font-weight:700;color:#1F4E79">{{ totalAudit }}</div>
        <div style="font-size:12px;color:#888;margin-top:4px">Total Audit</div>
      </div>
      <div class="spbe-card-white" style="flex:1;min-width:140px;text-align:center;padding:20px 16px">
        <div style="font-size:2rem;font-weight:700;color:#0369a1">{{ totalBerjalan }}</div>
        <div style="font-size:12px;color:#888;margin-top:4px">Sedang Berjalan</div>
      </div>
      <div class="spbe-card-white" style="flex:1;min-width:140px;text-align:center;padding:20px 16px">
        <div style="font-size:2rem;font-weight:700;color:#166534">{{ totalSelesai }}</div>
        <div style="font-size:12px;color:#888;margin-top:4px">Selesai</div>
      </div>
      <div class="spbe-card-white" style="flex:1;min-width:140px;text-align:center;padding:20px 16px">
        <div style="font-size:2rem;font-weight:700;color:#92400e">{{ totalMenungguLhak }}</div>
        <div style="font-size:12px;color:#888;margin-top:4px">LHAK Menunggu Persetujuan</div>
      </div>
    </div>
  </SidebarLayout>
</template>
