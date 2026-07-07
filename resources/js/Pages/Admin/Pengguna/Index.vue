<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
  users:  Object,
  filter: String,
})

const page = usePage()
const generatedPw = computed(() => page.props.flash?.generatedPassword)

/* ——— Filter ——— */
const applyFilter = (role) => router.get('/admin/pengguna', { role: role || undefined }, { preserveState: true })

/* ——— Modal state ——— */
const showModal   = ref(false)
const isEdit      = ref(false)
const showConfirm = ref(false)
const targetUser  = ref(null)

const form = ref({ id: null, name: '', email: '', role: 'auditee', nama_instansi: '', password: '' })
const errors = ref({})

const roleLabel = { admin: 'Admin', ketua_tim: 'Ketua tim', auditor: 'Auditor', auditee: 'Auditee', supervisor: 'Supervisor' }
const badgeClass = { admin: 'b-admin', ketua_tim: 'b-ketua', auditor: 'b-auditor', auditee: 'b-auditee', supervisor: 'b-ketua' }

function openAdd() {
  isEdit.value = false
  form.value = { id: null, name: '', email: '', role: 'auditee', nama_instansi: '', password: '' }
  errors.value = {}
  showModal.value = true
}

function openEdit(u) {
  isEdit.value = true
  form.value = { id: u.id, name: u.name, email: u.email, role: u.role, nama_instansi: u.nama_instansi ?? '', password: '' }
  errors.value = {}
  targetUser.value = u
  showModal.value = true
}

function closeModal() { showModal.value = false }

function submitForm() {
  errors.value = {}
  if (isEdit.value) {
    router.put(`/admin/pengguna/${form.value.id}`, form.value, {
      onError: (e) => { errors.value = e },
      onSuccess: () => { showModal.value = false },
    })
  } else {
    router.post('/admin/pengguna', form.value, {
      onError: (e) => { errors.value = e },
      onSuccess: () => { showModal.value = false },
    })
  }
}

function confirmDelete(u) {
  targetUser.value = u
  showConfirm.value = true
}

function doDelete() {
  router.delete(`/admin/pengguna/${targetUser.value.id}`, {
    onSuccess: () => { showConfirm.value = false },
  })
}
</script>

<template>
  <SidebarLayout title="Kelola Pengguna">

    <!-- Generated password banner -->
    <div v-if="generatedPw" class="pw-box">
      <b>Password sementara pengguna baru:</b>&nbsp;
      <span style="font-family:monospace;font-size:13px">{{ generatedPw }}</span>
      &nbsp;— Catat sekarang, tidak akan ditampilkan lagi.
    </div>

    <!-- Header -->
    <div class="section-row">
      <p class="page-header" style="margin:0">Manajemen pengguna</p>
      <button class="btn-p" @click="openAdd">+ Tambah pengguna</button>
    </div>

    <!-- Filter -->
    <div class="filter-bar">
      <span style="font-size:11px;color:#888">Filter role:</span>
      <button
        v-for="r in ['', 'admin', 'ketua_tim', 'auditor', 'auditee', 'supervisor']"
        :key="r"
        :class="['btn-sm', { 'btn-p': filter === (r || undefined) }]"
        @click="applyFilter(r)"
      >{{ r ? roleLabel[r] : 'Semua' }}</button>
    </div>

    <!-- Table -->
    <div class="spbe-card-white" style="padding:0;overflow:hidden">
      <table class="spbe-tbl">
        <thead>
          <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
            <th>Instansi</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in users.data" :key="u.id">
            <td>{{ u.name }}</td>
            <td style="color:#888">{{ u.email }}</td>
            <td><span class="badge" :class="badgeClass[u.role]">{{ roleLabel[u.role] }}</span></td>
            <td style="color:#888">{{ u.nama_instansi || '—' }}</td>
            <td>
              <button class="btn-sm" style="margin-right:4px" @click="openEdit(u)">Edit</button>
              <button class="btn-no" style="padding:3px 8px;font-size:10px" @click="confirmDelete(u)">Hapus</button>
            </td>
          </tr>
          <tr v-if="!users.data.length">
            <td colspan="5" style="text-align:center;color:#aaa;padding:20px">Tidak ada pengguna.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="users.last_page > 1" style="display:flex;gap:6px;margin-top:12px;font-size:11px">
      <a v-for="l in users.links" :key="l.label" v-html="l.label"
        :href="l.url" :class="['btn-sm', { 'btn-p': l.active }]"
        style="text-decoration:none" />
    </div>

    <!-- ===== MODAL TAMBAH / EDIT ===== -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-box">
        <p class="modal-title">
          {{ isEdit ? 'Edit pengguna' : 'Tambah pengguna' }}
          <span class="modal-close" @click="closeModal">✕</span>
        </p>

        <label class="lbl">Nama lengkap</label>
        <input v-model="form.name" class="spbe-inp" :class="{ err: errors.name }" style="margin-bottom:4px" placeholder="Nama lengkap" />
        <p v-if="errors.name" class="err-msg">{{ errors.name }}</p>

        <label class="lbl" style="margin-top:8px">Email</label>
        <input v-model="form.email" type="email" class="spbe-inp" :class="{ err: errors.email }" style="margin-bottom:4px" placeholder="email@instansi.go.id" />
        <p v-if="errors.email" class="err-msg">{{ errors.email }}</p>

        <label class="lbl" style="margin-top:8px">Role</label>
        <select v-model="form.role" class="spbe-sel" style="margin-bottom:4px">
          <option value="admin">Admin</option>
          <option value="ketua_tim">Ketua tim</option>
          <option value="auditor">Auditor</option>
          <option value="auditee">Auditee</option>
          <option value="supervisor">Supervisor</option>
        </select>
        <p v-if="errors.role" class="err-msg">{{ errors.role }}</p>

        <label class="lbl" style="margin-top:8px">Nama instansi <span style="color:#bbb">(opsional)</span></label>
        <input v-model="form.nama_instansi" class="spbe-inp" style="margin-bottom:4px" placeholder="Diskominfo Kota Bogor" />

        <label class="lbl" style="margin-top:8px">
          {{ isEdit ? 'Reset password (kosongkan jika tidak diubah)' : 'Password akan di-generate otomatis' }}
        </label>
        <input v-if="isEdit" v-model="form.password" type="password" class="spbe-inp" :class="{ err: errors.password }" placeholder="Isi untuk mengubah password" />
        <p v-if="errors.password" class="err-msg">{{ errors.password }}</p>

        <div class="modal-footer">
          <button style="background:#fff;color:#888;border:1px solid #ddd" @click="closeModal">Batal</button>
          <button style="background:var(--navy-light);color:var(--navy);border:none" @click="submitForm">Simpan</button>
        </div>

        <button v-if="isEdit" class="btn-danger" style="margin-top:8px" @click="confirmDelete(targetUser); closeModal()">
          Hapus pengguna ini
        </button>
      </div>
    </div>

    <!-- ===== MODAL KONFIRMASI HAPUS ===== -->
    <div v-if="showConfirm" class="modal-overlay" @click.self="showConfirm=false">
      <div class="modal-box" style="width:280px">
        <p class="modal-title">Konfirmasi hapus</p>
        <p style="font-size:12px;margin-bottom:16px">
          Hapus pengguna <b>{{ targetUser?.name }}</b>? Tindakan ini tidak dapat dibatalkan.
        </p>
        <div class="modal-footer">
          <button style="background:#fff;color:#888;border:1px solid #ddd" @click="showConfirm=false">Batal</button>
          <button style="background:var(--red-light);color:var(--red-dark);border:none" @click="doDelete">Ya, hapus</button>
        </div>
      </div>
    </div>

  </SidebarLayout>
</template>
