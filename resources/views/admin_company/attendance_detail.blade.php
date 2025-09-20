@extends('layout.master')

@section('title', 'Admin - Detail Attendance Mahasiswa')
@section('page_heading', 'Detail Attendance Mahasiswa')

@section('extra_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-1.13.6/r-2.5.0/datatables.min.css" />
    <style>
        .table thead th { white-space: nowrap; }
        .filter-bar .form-select, .filter-bar .form-control { max-width: 260px; }
        .proof-img {
            width: 100%;
            max-height: 280px;
            object-fit: cover;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            background: #f8f9fa;
        }
    </style>
@endsection

@section('content')
    @php
        $profile = optional($student->profile);
        $campus = optional($student->campus);
        $dosenName = optional(optional($student->dosen)->profile)->full_name;
        $photo = $profile && $profile->photo_url ? asset($profile->photo_url) : asset('assets/media/avatars/blank.png');
    @endphp

    <div class="card mb-6">
        <div class="card-body">
            <div class="row g-6">
                <div class="col-md-9">
                    <h3 class="mb-4">Menampilkan Data Absensi {{ $profile->full_name ?? $student->email }}</h3>
                    <div class="row g-2">
                        <div class="col-12 col-md-6"><strong>NIM</strong>: {{ $profile->nim ?? '-' }}</div>
                        <div class="col-12 col-md-6"><strong>Dosen Pembimbing</strong>: {{ $dosenName ?? '-' }}</div>
                        <div class="col-12 col-md-6"><strong>Program Studi</strong>: {{ $profile->program_studi ?? '-' }}</div>
                        <div class="col-12 col-md-6"><strong>Lokasi Magang</strong>: {{ $campus->nama_campus ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-3 d-flex justify-content-md-end justify-content-center align-items-start">
                    <div class="border rounded p-2 bg-white" style="width: 180px; height: 200px; display:flex; align-items:center; justify-content:center;">
                        <img src="{{ $photo }}" alt="Foto Mahasiswa" class="img-fluid rounded" style="max-height: 100%; object-fit: cover;">
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('company_admin.attendance.index') }}" class="btn btn-light">Kembali</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center flex-wrap gap-3">
            <h3 class="card-title m-0">Riwayat Absensi</h3>
            <div class="ms-auto d-flex gap-2 filter-bar">
                <select id="statusFilter" class="form-select form-select-sm">
                    <option value="">-- Filter Berdasarkan Keterangan --</option>
                    <option value="hadir">Masuk</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                    <option value="tanpa_keterangan">Tanpa Keterangan</option>
                </select>
                <div class="input-group input-group-sm" style="max-width:280px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="globalSearch" class="form-control form-control-sm" placeholder="Cari Data">
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="attendanceTable" class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3 w-100">
                    <thead>
                        <tr class="fw-semibold text-muted">
                            <th style="width: 50px;">No</th>
                            <th>Tanggal</th>
                            <th>Absen Masuk</th>
                            <th>Jam Absen Pulang</th>
                            <th>Keterangan</th>
                            <th style="width: 100px;">Bukti Absen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td></td>
                                <td>{{ $row['date_label'] ?? '-' }}</td>
                                <td>{{ $row['checkin_at'] ?? '-' }}</td>
                                <td>{{ $row['checkout_at'] ?? '-' }}</td>
                                <td>{{ $row['status_label'] ?? ($row['status'] ?? '-') }}</td>
                                <td>
                                    @php $disabled = ($row['status'] ?? '') === 'tanpa_keterangan' || ($row['status'] ?? '') === 'Belum Absen' ? 'disabled' : ''; @endphp
                                    <button class="btn btn-sm btnView btn-primary" {{ $disabled }}
                                        data-date-label="{{ $row['date_label'] ?? '' }}"
                                        data-checkin-time="{{ $row['checkin_at'] ?? '' }}"
                                        data-checkout-time="{{ $row['checkout_at'] ?? '' }}"
                                        data-checkin-photo="{{ $row['photo_checkin_url'] ?? '' }}"
                                        data-checkin-ttd="{{ $row['ttd_checkin_url'] ?? '' }}"
                                        data-checkout-photo="{{ $row['photo_checkout_url'] ?? '' }}"
                                        data-checkout-ttd="{{ $row['ttd_checkout_url'] ?? '' }}">Lihat Foto</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Bukti Absen (Read-Only) -->
    <div class="modal fade" id="modalBukti" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Bukti Absen</h5>
                    <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-2x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <div id="modalDate" class="fw-semibold"></div>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="mb-2">Absen Masuk</h6>
                            <img id="imgCheckinPhoto" class="proof-img mb-2" alt="Foto Check-in">
                            <img id="imgCheckinTTD" class="proof-img mb-2" alt="TTD Check-in">
                            <div class="text-muted small">Waktu: <span id="lblCheckinTime">-</span></div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-2">Absen Pulang</h6>
                            <img id="imgCheckoutPhoto" class="proof-img mb-2" alt="Foto Check-out">
                            <img id="imgCheckoutTTD" class="proof-img mb-2" alt="TTD Check-out">
                            <div class="text-muted small">Waktu: <span id="lblCheckoutTime">-</span></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('extra_js')
    <script>
        (function(){
            const tableEl = document.getElementById('attendanceTable');
            const statusFilter = document.getElementById('statusFilter');
            const searchInput = document.getElementById('globalSearch');

            const modalEl = document.getElementById('modalBukti');
            const buktiModal = new bootstrap.Modal(modalEl);
            const studentName = @json($profile->full_name ?? $student->email);

            const modalTitle = document.getElementById('modalTitle');
            const modalDate = document.getElementById('modalDate');
            const imgCheckinPhoto = document.getElementById('imgCheckinPhoto');
            const imgCheckinTTD = document.getElementById('imgCheckinTTD');
            const imgCheckoutPhoto = document.getElementById('imgCheckoutPhoto');
            const imgCheckoutTTD = document.getElementById('imgCheckoutTTD');
            const lblCheckinTime = document.getElementById('lblCheckinTime');
            const lblCheckoutTime = document.getElementById('lblCheckoutTime');

            const dt = $(tableEl).DataTable({
                responsive: true,
                pageLength: 10,
                lengthChange: true,
                lengthMenu: [ [10, 25, 50, 100], [10, 25, 50, 100] ],
                ordering: true,
                columnDefs: [
                    { targets: -1, orderable: false, searchable: false },
                    { targets: 0, orderable: false, searchable: false }
                ]
            });

            // Numbering for first column based on current ordering/search
            dt.on('order.dt search.dt', function(){
                let i = 1;
                dt.column(0, { search: 'applied', order: 'applied' })
                  .nodes()
                  .each(function(cell){ cell.innerHTML = i++; });
            }).draw();

            // Global search
            searchInput.addEventListener('input', function(){ dt.search(this.value).draw(); });

            // Status filter (exact)
            statusFilter.addEventListener('change', function(){
                const val = this.value;
                if (!val) {
                    dt.column(4).search('').draw();
                } else {
                    const map = { hadir: 'Masuk', izin: 'Izin', sakit: 'Sakit', tanpa_keterangan: 'Tanpa Keterangan' };
                    dt.column(4).search('^' + (map[val] || val) + '$', true, false).draw();
                }
            });

            // Bukti modal (read-only)
            $(tableEl).on('click', '.btnView', function(){
                const btn = this;
                const photoIn = btn.dataset.checkinPhoto;
                const ttdIn = btn.dataset.checkinTtd;
                const photoOut = btn.dataset.checkoutPhoto;
                const ttdOut = btn.dataset.checkoutTtd;
                const dateLabel = btn.dataset.dateLabel || '';
                const checkinTime = btn.dataset.checkinTime || '-';
                const checkoutTime = btn.dataset.checkoutTime || '-';

                function setImage(img, url){
                    if (url) { img.src = url; img.style.display = 'block'; }
                    else { img.src = ''; img.style.display = 'none'; }
                }

                setImage(imgCheckinPhoto, photoIn);
                setImage(imgCheckinTTD, ttdIn);
                setImage(imgCheckoutPhoto, photoOut);
                setImage(imgCheckoutTTD, ttdOut);

                // Set title and date
                modalTitle.textContent = 'Bukti Absen ' + (studentName || 'Mahasiswa');
                modalDate.textContent = dateLabel ? ('Hari & Tanggal Absen: ' + dateLabel) : '';
                lblCheckinTime.textContent = checkinTime;
                lblCheckoutTime.textContent = checkoutTime;

                buktiModal.show();
            });
        })();
    </script>
@endsection
