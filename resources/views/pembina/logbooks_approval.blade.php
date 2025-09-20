@extends('layout.master')

@section('title', 'Pembina - Persetujuan Logbook Mahasiswa')
@section('page_heading', 'Persetujuan Logbook Mahasiswa')

@section('extra_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-1.13.6/r-2.5.0/datatables.min.css" />
    <style>
        .table thead th { white-space: nowrap; }
        .search-bar .form-control { max-width: 280px; }
        .btn-action { min-width: 160px; }
        .avatar-sm { width: 40px; height: 40px; object-fit: cover; }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center flex-wrap gap-3">
            <h3 class="card-title m-0">Data Logbook Magang Mahasiswa</h3>
            <div class="ms-auto search-bar">
                <div class="input-group input-group-sm">
                    <input type="text" id="searchInput" class="form-control form-control-sm"
                           placeholder="Cari Data Mahasiswa">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="studentsTable" class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3 w-100">
                    <thead>
                        <tr class="fw-semibold text-muted">
                            <th style="width: 60px;">No</th>
                            <th style="width: 70px;">Foto</th>
                            <th style="width: 160px;">NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $idx => $mhs)
                            @php
                                $profile = optional($mhs->profile);
                                $pending = (int) ($pendingMap[$mhs->id] ?? 0); // jumlah logbook menunggu approval
                                // Siapkan URL foto jika ada, jika tidak gunakan avatar inisial
                                $photo = $profile->photo_url ? asset($profile->photo_url) : null;
                                $displayName = $profile->full_name ?? $mhs->email;
                                // Pilih warna background dan teks secara pseudo-random berbasis nama (konsisten per user)
                                $palette = ['1ABC9C','2ECC71','3498DB','9B59B6','E67E22','E74C3C','16A085','27AE60','2980B9','8E44AD','F39C12','D35400','C0392B','2C3E50','7F8C8D'];
                                $seed = abs(crc32((string) $displayName));
                                $bg = $palette[$seed % count($palette)];
                                // Warna teks dipilih acak dari palet berbeda, berbasis seed juga agar konsisten per nama
                                $fgPalette = ['FFFFFF','000000','F8F9FA','212529','FFD166','073B4C','EF476F'];
                                $fg = $fgPalette[($seed >> 3) % count($fgPalette)];
                                if (strtoupper($fg) === strtoupper($bg)) { $fg = 'FFFFFF'; }
                                $avatarFallback = 'https://ui-avatars.com/api/?name=' . urlencode((string) $displayName) . '&size=64&background=' . $bg . '&color=' . $fg;
                                $avatarUrl = $photo ?: $avatarFallback;
                            @endphp
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>
                                    <img src="{{ $avatarUrl }}" class="rounded-circle avatar-sm" alt="Foto {{ $profile->full_name ?? $mhs->email }}">
                                </td>
                                <td>{{ $profile->nim ?? '-' }}</td>
                                <td>{{ $profile->full_name ?? $mhs->email }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('pembina.logbooks_approval.show', $mhs->id) }}" class="btn btn-sm btn-action {{ $pending > 0 ? 'btn-warning' : 'btn-primary' }}">
                                            {{ $pending > 0 ? ('Verifikasi Logbook (' . $pending . ')') : 'Lihat Logbook' }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada mahasiswa binaan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('extra_js')
    <script>
        (function(){
            const tableEl = document.getElementById('studentsTable');
            const searchInput = document.getElementById('searchInput');

            if (window.jQuery && $.fn && $.fn.DataTable) {
                const dt = $(tableEl).DataTable({
                    responsive: true,
                    pageLength: 10,
                    lengthChange: false,
                    ordering: true,
                    columnDefs: [
                        {
                            targets: 0,
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row, meta){
                                return meta.row + 1;
                            }
                        },
                        {
                            targets: -1,
                            orderable: false,
                            searchable: false
                        }
                    ]
                });

                // Global search input
                searchInput.addEventListener('input', function(){
                    dt.search(this.value).draw();
                });
            } else {
                // Fallback simple filter jika DataTables JS tidak tersedia
                const rows = () => Array.from(tableEl.querySelectorAll('tbody tr'));
                function normalize(s){ return (s || '').toString().toLowerCase(); }
                searchInput.addEventListener('input', function(){
                    const q = normalize(this.value);
                    rows().forEach(tr => {
                        const text = normalize(tr.innerText);
                        tr.style.display = text.includes(q) ? '' : 'none';
                    });
                });
            }
        })();
    </script>
@endsection
