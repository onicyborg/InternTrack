@extends('layout.master')

@section('title', 'Dosen - Persetujuan Logbook Mahasiswa')
@section('page_heading', 'Persetujuan Logbook Mahasiswa')

@section('extra_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-1.13.6/r-2.5.0/datatables.min.css" />
    <style>
        .table thead th { white-space: nowrap; }
        .search-bar .form-control { max-width: 280px; }
        .btn-action { min-width: 160px; }
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
                            @endphp
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>{{ $profile->nim ?? '-' }}</td>
                                <td>{{ $profile->full_name ?? $mhs->email }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('dosen.logbooks_approval.show', $mhs->id) }}" class="btn btn-sm btn-action {{ $pending > 0 ? 'btn-warning' : 'btn-primary' }}">
                                            {{ $pending > 0 ? ('Verifikasi Logbook (' . $pending . ')') : 'Lihat Logbook' }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Belum ada mahasiswa binaan.</td>
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
