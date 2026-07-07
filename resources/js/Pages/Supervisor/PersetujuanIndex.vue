<script setup>
import { Link } from '@inertiajs/vue3'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

defineProps({ approvals: Array })

const statusClass = { pending: 'b-ketua', disetujui: 'b-auditor', ditolak: 'b-admin' }
const statusLabel = { pending: 'Menunggu Review', disetujui: 'Disetujui', ditolak: 'Ditolak' }
</script>

<template>
  <SidebarLayout title="Persetujuan LHAK">
    <p class="page-header">Persetujuan LHAK</p>

    <div class="spbe-card-white" style="padding:0;overflow:hidden">
      <table class="spbe-tbl">
        <thead>
          <tr>
            <th>Instansi</th>
            <th>Ketua Tim</th>
            <th>Tanggal Submit</th>
            <th>Status</th>
            <th>Ditinjau oleh</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in approvals" :key="a.id">
            <td>{{ a.instansi }}</td>
            <td>{{ a.submitted_by }}</td>
            <td style="color:#888;font-size:11px">{{ a.submitted_at }}</td>
            <td><span class="badge" :class="statusClass[a.status]">{{ statusLabel[a.status] }}</span></td>
            <td style="color:#888;font-size:11px">{{ a.reviewed_by ?? '-' }}</td>
            <td>
              <Link :href="`/supervisor/persetujuan/${a.id}`" class="btn-sm">
                {{ a.status === 'pending' ? 'Review' : 'Lihat' }}
              </Link>
            </td>
          </tr>
          <tr v-if="!approvals.length">
            <td colspan="6" style="text-align:center;color:#aaa;padding:20px">Belum ada pengajuan LHAK.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </SidebarLayout>
</template>
