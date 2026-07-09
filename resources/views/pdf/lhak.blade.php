<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'DejaVu Sans', sans-serif; font-size:10pt; color:#222; }

  .page-break { page-break-after:always; }

  /* Cover */
  .cover { text-align:center; padding:80px 60px; }
  .cover .lembaga { font-size:14pt; font-weight:bold; color:#1F4E79; margin-bottom:40px; text-transform:uppercase; }
  .cover .judul { font-size:20pt; font-weight:bold; color:#1F4E79; margin-bottom:10px; text-transform:uppercase; }
  .cover .subjudul { font-size:12pt; color:#333; margin-bottom:50px; }
  .cover .instansi-box { border:2px solid #1F4E79; border-radius:8px; padding:20px 30px; display:inline-block; margin-bottom:40px; }
  .cover .instansi-name { font-size:14pt; font-weight:bold; color:#1F4E79; }
  .cover .info-table td { padding:4px 10px; font-size:10pt; }
  .cover .footer-cover { margin-top:60px; font-size:9pt; color:#666; }

  /* Sections */
  .section-title { font-size:13pt; font-weight:bold; color:#1F4E79; border-bottom:2px solid #1F4E79; padding-bottom:4px; margin-bottom:12px; }
  .section-sub { font-size:11pt; font-weight:bold; color:#1F4E79; margin:14px 0 6px; }
  p { margin-bottom:6px; line-height:1.5; }

  /* Konklusi cards */
  .kgrid { display:table; width:100%; border-collapse:separate; border-spacing:8px; margin-bottom:12px; }
  .krow  { display:table-row; }
  .kcard { display:table-cell; padding:10px; border-radius:6px; border:1px solid #ddd; width:33%; vertical-align:top; }
  .k-ok   { background:#EAF3DE; border-color:#cfe3b8; }
  .k-warn { background:#FFF2CC; border-color:#f0e0a0; }
  .k-bad  { background:#FCEBEB; border-color:#f0c0c0; }
  .kcard .kt { font-size:8pt; color:#888; margin-bottom:3px; }
  .kcard .kv { font-size:12pt; font-weight:bold; margin-bottom:4px; }
  .k-ok .kv   { color:#375623; }
  .k-warn .kv { color:#7B6000; }
  .k-bad .kv  { color:#9b1c1c; }
  .kcard .kd  { font-size:8pt; color:#666; }
  .overall-box { background:#E6F1FB; border:1px solid #c5dcf0; border-radius:6px; padding:8px 12px; margin-bottom:12px; }
  .overall-box b { color:#1F4E79; }

  /* Penilaian table */
  table.ptable { width:100%; border-collapse:collapse; margin-bottom:10px; font-size:8pt; }
  table.ptable th { background:#1F4E79; color:#fff; padding:5px 6px; text-align:left; }
  table.ptable td { padding:5px 6px; border-bottom:1px solid #eee; vertical-align:top; }
  table.ptable tr:nth-child(even) td { background:#f8f9fa; }
  .badge-memadai { background:#EAF3DE; color:#375623; padding:1px 6px; border-radius:4px; font-size:7pt; }
  .badge-perlu   { background:#FFF2CC; color:#7B6000;  padding:1px 6px; border-radius:4px; font-size:7pt; }
  .badge-tidak   { background:#FCEBEB; color:#9b1c1c;  padding:1px 6px; border-radius:4px; font-size:7pt; }
  .badge-efektif { background:#EAF3DE; color:#375623; padding:1px 6px; border-radius:4px; font-size:7pt; }
  .badge-belum   { background:#FCEBEB; color:#9b1c1c;  padding:1px 6px; border-radius:4px; font-size:7pt; }
  .badge-sesuai  { background:#E3F2FD; color:#1565C0;  padding:1px 6px; border-radius:4px; font-size:7pt; }

  /* Temuan */
  .temuan-card { border:1px solid #ddd; border-radius:6px; padding:8px 10px; margin-bottom:8px; }
  .temuan-card .t-risiko-tinggi { color:#9b1c1c; font-weight:bold; }
  .temuan-card .t-risiko-sedang { color:#7B6000; font-weight:bold; }
  .temuan-card .t-risiko-rendah { color:#375623; font-weight:bold; }

  /* TTD */
  .ttd-section { margin-top:40px; }
  .ttd-box { display:inline-block; text-align:center; width:200px; margin:0 20px; }
  .ttd-line { border-top:1px solid #333; margin-top:60px; padding-top:4px; }

  .header-bar { background:#1F4E79; color:#fff; padding:6px 12px; margin-bottom:12px; font-size:9pt; }
  .page-content { padding:30px 40px; }
</style>
</head>
<body>

{{-- Halaman 1: Cover --}}
<div class="cover page-break">
  <div class="lembaga">SPBE-SCAN</div>
  <div class="judul">Laporan Hasil Audit Keamanan</div>
  <div class="subjudul">Sistem Pemerintahan Berbasis Elektronik (SPBE)</div>

  <div class="instansi-box">
    <div class="instansi-name">{{ $instansi }}</div>
    <div style="font-size:10pt; color:#555; margin-top:4px;">{{ $aplikasi }}</div>
  </div>

  <table class="info-table" style="margin:0 auto;">
    <tr><td><b>Nomor Audit</b></td><td>:</td><td>LHAK-{{ str_pad($plan->id, 4, '0', STR_PAD_LEFT) }}/SPBE/{{ now()->year }}</td></tr>
    <tr><td><b>Periode</b></td><td>:</td><td>{{ $waktu_mulai }} s.d. {{ $waktu_selesai }}</td></tr>
    <tr><td><b>Auditee</b></td><td>:</td><td>{{ $auditee }}</td></tr>
    <tr><td><b>Ketua Tim</b></td><td>:</td><td>{{ $ketua_tim }}</td></tr>
    <tr><td><b>Tanggal Generate</b></td><td>:</td><td>{{ $generated_at }}</td></tr>
  </table>

  <div class="footer-cover">
    Dokumen ini dibuat secara otomatis oleh sistem SPBE-SCAN.<br>
    Bersifat rahasia — hanya untuk pihak yang berwenang.
  </div>
</div>

{{-- Halaman 2: Ringkasan Eksekutif --}}
<div class="page-content page-break">
  <div class="header-bar">LAPORAN HASIL AUDIT KEAMANAN — {{ strtoupper($instansi) }}</div>
  <div class="section-title">Ringkasan Eksekutif</div>

  <p>Audit keamanan terhadap <strong>{{ $instansi }}</strong> ({{ $aplikasi }}) telah dilaksanakan
  pada periode <strong>{{ $waktu_mulai }}</strong> sampai dengan <strong>{{ $waktu_selesai }}</strong>.
  Audit ini mencakup tiga domain penilaian: Tata Kelola (TK), Manajemen Keamanan (MK), dan Fungsionalitas Keamanan (FK).</p>

  <p>Audit dilakukan menggunakan pendekatan SPBE yang meliputi evaluasi desain kontrol (EDK),
  evaluasi implementasi kontrol (EIK), dan evaluasi efektivitas kontrol (EFK).</p>

  <div class="section-sub">Konklusi Per Bagian</div>

  @php
    $konklusiClass = fn($k) => match($k) {
      'memadai' => 'k-ok', 'perlu_peningkatan' => 'k-warn', default => 'k-bad'
    };
    $konklusiLabel = fn($k) => match($k) {
      'memadai' => 'Memadai', 'perlu_peningkatan' => 'Perlu Peningkatan', 'tidak_memadai' => 'Tidak Memadai', default => '-'
    };
    $fmt = fn($v) => $v !== null ? number_format($v * 100, 1).'%' : '—';
  @endphp

  <div style="display:table; width:100%; border-collapse:separate; border-spacing:8px; margin-bottom:12px;">
    <div style="display:table-row;">
      <div class="kcard {{ $konklusiClass($result->konklusi_tk) }}" style="display:table-cell; padding:10px; border-radius:6px; border:1px solid #ddd; width:33%; vertical-align:top;">
        <div class="kt">Tata Kelola (TK)</div>
        <div class="kv">{{ $konklusiLabel($result->konklusi_tk) }}</div>
        <div class="kd">EDK {{ $fmt($result->nilai_edk_tk) }} · EIK {{ $fmt($result->nilai_eik_tk) }} · EFK {{ $fmt($result->nilai_efk_tk) }}</div>
      </div>
      <div class="kcard {{ $konklusiClass($result->konklusi_mk) }}" style="display:table-cell; padding:10px; border-radius:6px; border:1px solid #ddd; width:33%; vertical-align:top;">
        <div class="kt">Manajemen Keamanan (MK)</div>
        <div class="kv">{{ $konklusiLabel($result->konklusi_mk) }}</div>
        <div class="kd">EDK {{ $fmt($result->nilai_edk_mk) }} · EIK {{ $fmt($result->nilai_eik_mk) }} · EFK {{ $fmt($result->nilai_efk_mk) }}</div>
      </div>
      <div class="kcard {{ $konklusiClass($result->konklusi_fk) }}" style="display:table-cell; padding:10px; border-radius:6px; border:1px solid #ddd; width:33%; vertical-align:top;">
        <div class="kt">Fungsionalitas Keamanan (FK)</div>
        <div class="kv">{{ $konklusiLabel($result->konklusi_fk) }}</div>
        <div class="kd">EDK {{ $fmt($result->nilai_edk_fk) }} · EIK {{ $fmt($result->nilai_eik_fk) }} · EFK {{ $fmt($result->nilai_efk_fk) }}</div>
      </div>
    </div>
  </div>

  <div class="overall-box">
    Konklusi Keseluruhan: <b>{{ $konklusiLabel($result->konklusi_keseluruhan) }}</b>
  </div>

  <div class="section-sub">Temuan Utama</div>
  <p>Terdapat <strong>{{ $temuan->count() }}</strong> temuan keamanan yang diidentifikasi selama audit,
  dengan rincian:
  <strong>{{ $temuan->where('risiko','tinggi')->count() }}</strong> risiko tinggi,
  <strong>{{ $temuan->where('risiko','sedang')->count() }}</strong> risiko sedang, dan
  <strong>{{ $temuan->where('risiko','rendah')->count() }}</strong> risiko rendah.</p>

  @if($temuan->where('risiko','tinggi')->count() > 0)
  <p>Temuan risiko tinggi memerlukan perhatian dan tindak lanjut segera dari pihak instansi.</p>
  @endif
</div>

{{-- Halaman 3: Detail Penilaian TK --}}
<div class="page-content page-break">
  <div class="header-bar">DETAIL PENILAIAN — TATA KELOLA (TK)</div>
  <div class="section-title">Detail Penilaian Tata Kelola</div>

  <table class="ptable">
    <thead>
      <tr>
        <th style="width:8%">Kode</th>
        <th style="width:30%">Butir</th>
        <th style="width:18%">EDK</th>
        <th style="width:18%">EIK</th>
        <th style="width:18%">EFK</th>
      </tr>
    </thead>
    <tbody>
      @foreach($penilaian_tk as $p)
      <tr>
        <td>{{ $p->butir->kode ?? '-' }}</td>
        <td>{{ $p->butir->judul_butir ?? '-' }}</td>
        <td>
          @if($p->edk === 'memadai') <span class="badge-memadai">Memadai</span>
          @elseif($p->edk === 'perlu_peningkatan') <span class="badge-perlu">Perlu Peningkatan</span>
          @elseif($p->edk === 'tidak_memadai') <span class="badge-tidak">Tidak Memadai</span>
          @else — @endif
        </td>
        <td>
          @if($p->eik === 'sesuai') <span class="badge-sesuai">Sesuai</span>
          @elseif($p->eik === 'tidak_sesuai') <span class="badge-tidak">Tidak Sesuai</span>
          @elseif($p->eik === 'skip') <span style="color:#999">—</span>
          @else — @endif
        </td>
        <td>
          @if($p->efk === 'efektif') <span class="badge-efektif">Efektif</span>
          @elseif($p->efk === 'perlu_peningkatan') <span class="badge-perlu">Perlu Peningkatan</span>
          @elseif($p->efk === 'belum_efektif') <span class="badge-belum">Belum Efektif</span>
          @else — @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

{{-- Halaman 4: Detail Penilaian MK + FK --}}
<div class="page-content page-break">
  <div class="header-bar">DETAIL PENILAIAN — MANAJEMEN KEAMANAN (MK) & FUNGSIONALITAS KEAMANAN (FK)</div>

  <div class="section-title">Detail Penilaian Manajemen Keamanan</div>
  <table class="ptable">
    <thead>
      <tr>
        <th style="width:8%">Kode</th>
        <th style="width:30%">Butir</th>
        <th style="width:18%">EDK</th>
        <th style="width:18%">EIK</th>
        <th style="width:18%">EFK</th>
      </tr>
    </thead>
    <tbody>
      @foreach($penilaian_mk as $p)
      <tr>
        <td>{{ $p->butir->kode ?? '-' }}</td>
        <td>{{ $p->butir->judul_butir ?? '-' }}</td>
        <td>
          @if($p->edk === 'memadai') <span class="badge-memadai">Memadai</span>
          @elseif($p->edk === 'perlu_peningkatan') <span class="badge-perlu">Perlu Peningkatan</span>
          @elseif($p->edk === 'tidak_memadai') <span class="badge-tidak">Tidak Memadai</span>
          @else — @endif
        </td>
        <td>
          @if($p->eik === 'sesuai') <span class="badge-sesuai">Sesuai</span>
          @elseif($p->eik === 'tidak_sesuai') <span class="badge-tidak">Tidak Sesuai</span>
          @elseif($p->eik === 'skip') <span style="color:#999">—</span>
          @else — @endif
        </td>
        <td>
          @if($p->efk === 'efektif') <span class="badge-efektif">Efektif</span>
          @elseif($p->efk === 'perlu_peningkatan') <span class="badge-perlu">Perlu Peningkatan</span>
          @elseif($p->efk === 'belum_efektif') <span class="badge-belum">Belum Efektif</span>
          @else — @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="section-title" style="margin-top:16px;">Detail Penilaian Fungsionalitas Keamanan</div>
  <table class="ptable">
    <thead>
      <tr>
        <th style="width:8%">Kode</th>
        <th style="width:30%">Butir</th>
        <th style="width:18%">EDK</th>
        <th style="width:18%">EIK</th>
        <th style="width:18%">EFK</th>
      </tr>
    </thead>
    <tbody>
      @foreach($penilaian_fk as $p)
      <tr>
        <td>{{ $p->butir->kode ?? '-' }}</td>
        <td>{{ $p->butir->judul_butir ?? '-' }}</td>
        <td>
          @if($p->edk === 'memadai') <span class="badge-memadai">Memadai</span>
          @elseif($p->edk === 'perlu_peningkatan') <span class="badge-perlu">Perlu Peningkatan</span>
          @elseif($p->edk === 'tidak_memadai') <span class="badge-tidak">Tidak Memadai</span>
          @else — @endif
        </td>
        <td>
          @if($p->eik === 'sesuai') <span class="badge-sesuai">Sesuai</span>
          @elseif($p->eik === 'tidak_sesuai') <span class="badge-tidak">Tidak Sesuai</span>
          @elseif($p->eik === 'skip') <span style="color:#999">—</span>
          @else — @endif
        </td>
        <td>
          @if($p->efk === 'efektif') <span class="badge-efektif">Efektif</span>
          @elseif($p->efk === 'perlu_peningkatan') <span class="badge-perlu">Perlu Peningkatan</span>
          @elseif($p->efk === 'belum_efektif') <span class="badge-belum">Belum Efektif</span>
          @else — @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

{{-- Halaman 5: Daftar Temuan --}}
<div class="page-content page-break">
  <div class="header-bar">DAFTAR TEMUAN AUDIT KEAMANAN</div>
  <div class="section-title">Daftar Temuan Keamanan</div>

  @if($temuan->isEmpty())
  <p style="color:#888; font-style:italic;">Tidak ada temuan yang dicatat dalam audit ini.</p>
  @else
  @foreach($temuan as $i => $t)
  <div class="temuan-card">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:4px;">
      <div style="font-weight:bold; font-size:10pt;">{{ $i+1 }}. {{ $t->judul ?: 'Temuan #'.($i+1) }}</div>
      <span class="t-risiko-{{ $t->risiko }}">{{ strtoupper($t->risiko) }}</span>
    </div>
    <div style="font-size:8pt; color:#888; margin-bottom:4px;">Butir: {{ $t->butir?->kode ?? '-' }} · Auditor: {{ $t->auditor?->name ?? '-' }}</div>
    <div style="font-size:9pt; margin-bottom:4px;">{{ $t->deskripsi }}</div>
    <div style="font-size:9pt;"><strong>Rekomendasi:</strong> {{ $t->rekomendasi }}</div>

    @if($t->pesanTindakLanjut && $t->pesanTindakLanjut->count())
    <div style="margin-top:8px;">
      <div style="font-size:9pt; font-weight:bold; color:#1F4E79; margin-bottom:2px;">Tindak Lanjut:</div>
      <ul style="margin-left:12px; font-size:8.5pt; color:#222;">
        @foreach($t->pesanTindakLanjut as $p)
          <li style="margin-bottom:2px;">
            <span style="color:#1F4E79; font-weight:bold;">{{ $p->user->name ?? 'User' }}</span>:
            {{ $p->pesan }}
            @if($p->created_at)
              <span style="color:#888; font-size:7.5pt;">({{ $p->created_at->format('d M Y H:i') }})</span>
            @endif
          </li>
        @endforeach
      </ul>
    </div>
    @endif
  </div>
  @endforeach
  @endif
</div>

{{-- Halaman 6: Grafik / Ringkasan Visual --}}
<div class="page-content page-break">
  <div class="header-bar">RINGKASAN VISUAL HASIL AUDIT</div>
  <div class="section-title">Ringkasan Skor Penilaian</div>

  <table style="width:100%; border-collapse:collapse; margin-bottom:16px; font-size:9pt;">
    <thead>
      <tr style="background:#1F4E79; color:#fff;">
        <th style="padding:6px 8px; text-align:left;">Domain</th>
        <th style="padding:6px 8px; text-align:center;">Skor EDK</th>
        <th style="padding:6px 8px; text-align:center;">Skor EIK</th>
        <th style="padding:6px 8px; text-align:center;">Skor EFK</th>
        <th style="padding:6px 8px; text-align:center;">Konklusi</th>
      </tr>
    </thead>
    <tbody>
      @foreach([
        ['Tata Kelola (TK)', $result->nilai_edk_tk, $result->nilai_eik_tk, $result->nilai_efk_tk, $result->konklusi_tk],
        ['Manajemen Keamanan (MK)', $result->nilai_edk_mk, $result->nilai_eik_mk, $result->nilai_efk_mk, $result->konklusi_mk],
        ['Fungsionalitas Keamanan (FK)', $result->nilai_edk_fk, $result->nilai_eik_fk, $result->nilai_efk_fk, $result->konklusi_fk],
      ] as [$label, $edk, $eik, $efk, $konklusi])
      <tr style="border-bottom:1px solid #eee;">
        <td style="padding:6px 8px; font-weight:bold;">{{ $label }}</td>
        <td style="padding:6px 8px; text-align:center;">{{ $edk !== null ? number_format($edk*100,1).'%' : '—' }}</td>
        <td style="padding:6px 8px; text-align:center;">{{ $eik !== null ? number_format($eik*100,1).'%' : '—' }}</td>
        <td style="padding:6px 8px; text-align:center;">{{ $efk !== null ? number_format($efk*100,1).'%' : '—' }}</td>
        <td style="padding:6px 8px; text-align:center;">
          @if($konklusi === 'memadai') <span class="badge-memadai">Memadai</span>
          @elseif($konklusi === 'perlu_peningkatan') <span class="badge-perlu">Perlu Peningkatan</span>
          @elseif($konklusi === 'tidak_memadai') <span class="badge-tidak">Tidak Memadai</span>
          @else — @endif
        </td>
      </tr>
      @endforeach
      <tr style="background:#E6F1FB; font-weight:bold;">
        <td style="padding:6px 8px;">Keseluruhan</td>
        <td colspan="3" style="padding:6px 8px; text-align:center;"></td>
        <td style="padding:6px 8px; text-align:center;">
          @if($result->konklusi_keseluruhan === 'memadai') <span class="badge-memadai">Memadai</span>
          @elseif($result->konklusi_keseluruhan === 'perlu_peningkatan') <span class="badge-perlu">Perlu Peningkatan</span>
          @elseif($result->konklusi_keseluruhan === 'tidak_memadai') <span class="badge-tidak">Tidak Memadai</span>
          @else — @endif
        </td>
      </tr>
    </tbody>
  </table>

  <div class="section-sub">Distribusi Temuan per Risiko</div>
  <table style="width:100%; border-collapse:collapse; font-size:9pt;">
    <tr>
      <td style="width:33%; padding:10px; text-align:center; background:#FCEBEB; border-radius:6px; margin:4px;">
        <div style="font-size:20pt; font-weight:bold; color:#9b1c1c;">{{ $temuan->where('risiko','tinggi')->count() }}</div>
        <div style="color:#9b1c1c; font-weight:bold;">Risiko Tinggi</div>
      </td>
      <td style="width:33%; padding:10px; text-align:center; background:#FFF2CC; border-radius:6px; margin:4px;">
        <div style="font-size:20pt; font-weight:bold; color:#7B6000;">{{ $temuan->where('risiko','sedang')->count() }}</div>
        <div style="color:#7B6000; font-weight:bold;">Risiko Sedang</div>
      </td>
      <td style="width:33%; padding:10px; text-align:center; background:#EAF3DE; border-radius:6px; margin:4px;">
        <div style="font-size:20pt; font-weight:bold; color:#375623;">{{ $temuan->where('risiko','rendah')->count() }}</div>
        <div style="color:#375623; font-weight:bold;">Risiko Rendah</div>
      </td>
    </tr>
  </table>
</div>

{{-- Halaman 7: Tanda Tangan --}}
<div class="page-content">
  <div class="header-bar">PENGESAHAN LAPORAN</div>
  <div class="section-title">Pengesahan dan Tanda Tangan</div>

  <p>Laporan Hasil Audit Keamanan ini telah disusun berdasarkan pelaksanaan audit yang dilakukan
  sesuai standar dan prosedur yang berlaku. Konklusi dalam laporan ini mencerminkan kondisi
  nyata dari sistem yang diaudit pada periode yang ditetapkan.</p>

  <p style="margin-top:10px;">Laporan ini dinyatakan sah dan dapat digunakan sebagai dasar tindak
  lanjut perbaikan keamanan sistem oleh instansi yang bersangkutan.</p>

  <div style="margin-top:50px;">
    <table style="width:100%;">
      <tr>
        <td style="width:50%; text-align:center; padding:0 20px;">
          <div>Auditee</div>
          <div style="border-top:1px solid #333; margin-top:70px; padding-top:4px;">{{ $auditee }}</div>
          <div style="font-size:8pt; color:#666;">{{ $instansi }}</div>
        </td>
        <td style="width:50%; text-align:center; padding:0 20px;">
          <div>Ketua Tim Auditor</div>
          <div style="border-top:1px solid #333; margin-top:70px; padding-top:4px;">{{ $ketua_tim }}</div>
          <div style="font-size:8pt; color:#666;">Tim Audit SPBE-SCAN</div>
        </td>
      </tr>
    </table>
  </div>

  <div style="margin-top:40px; text-align:center; font-size:8pt; color:#999; border-top:1px solid #eee; padding-top:8px;">
    Dokumen ini dibuat secara otomatis oleh sistem SPBE-SCAN pada {{ $generated_at }}.
  </div>
</div>

</body>
</html>
