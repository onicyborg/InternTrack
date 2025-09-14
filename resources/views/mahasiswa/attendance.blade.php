@extends('layout.master')

@section('title', 'Mahasiswa - Attendance')
@section('page_heading', 'Attendances')

@section('extra_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-1.13.6/r-2.5.0/datatables.min.css" />
    <style>
        .table thead th {
            white-space: nowrap;
        }

        .filter-bar .form-select,
        .filter-bar .form-control {
            max-width: 260px;
        }

        .proof-img {
            width: 100%;
            max-height: 280px;
            object-fit: cover;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            background: #f8f9fa;
        }

        .icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: .4rem;
        }

        .icon-btn[disabled] {
            opacity: .5;
            pointer-events: none;
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center flex-wrap gap-3">
            <h3 class="card-title m-0">Data Absensi Magang</h3>
            <div class="ms-auto d-flex gap-2 filter-bar">
                <select id="statusFilter" class="form-select form-select-sm">
                    <option value="">-- Filter Berdasarkan Status Absen --</option>
                    <option value="hadir">Hadir</option>
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
                            <th>Hari & Tanggal Absen</th>
                            <th>Absen Masuk</th>
                            <th>Absen Pulang</th>
                            <th>Status Absen</th>
                            <th>Approval Pembina</th>
                            <th>Approval Dosen</th>
                            <th style="width: 70px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Bukti Absen -->
    <div class="modal fade" id="modalBukti" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bukti Absen</h5>
                    <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-2x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="mb-2">Check-in</h6>
                            <img id="imgCheckinPhoto" class="proof-img mb-2" alt="Foto Check-in">
                            <img id="imgCheckinTTD" class="proof-img" alt="TTD Check-in">
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-2">Check-out</h6>
                            <img id="imgCheckoutPhoto" class="proof-img mb-2" alt="Foto Check-out">
                            <img id="imgCheckoutTTD" class="proof-img" alt="TTD Check-out">
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
        (function() {
            const tableEl = document.getElementById('attendanceTable');
            const statusFilter = document.getElementById('statusFilter');
            const searchInput = document.getElementById('globalSearch');

            const modalEl = document.getElementById('modalBukti');
            const buktiModal = new bootstrap.Modal(modalEl);
            const imgCheckinPhoto = document.getElementById('imgCheckinPhoto');
            const imgCheckinTTD = document.getElementById('imgCheckinTTD');
            const imgCheckoutPhoto = document.getElementById('imgCheckoutPhoto');
            const imgCheckoutTTD = document.getElementById('imgCheckoutTTD');

            const routes = {
                data: "{{ route('mahasiswa.attendance.data') }}"
            };

            function ucfirst(s) {
                if (!s) return s;
                return s.charAt(0).toUpperCase() + s.slice(1);
            }

            const dt = $(tableEl).DataTable({
                responsive: true,
                pageLength: 10,
                lengthChange: false,
                ordering: true,
                ajax: {
                    url: routes.data,
                    dataSrc: function(json) {
                        return json.data || [];
                    }
                },
                columns: [{
                        data: null
                    }, // No
                    {
                        data: 'date_label'
                    },
                    {
                        data: 'checkin_at'
                    },
                    {
                        data: 'checkout_at'
                    },
                    {
                        data: 'status',
                        render: function(d) {
                            if (!d) return '-';
                            // pretty label
                            const map = {
                                hadir: 'Masuk',
                                izin: 'Izin',
                                sakit: 'Sakit',
                                tanpa_keterangan: 'Tanpa Keterangan'
                            };
                            return map[d] || ucfirst(d);
                        }
                    },
                    {
                        data: 'approve_pembina'
                    },
                    {
                        data: 'approve_dosen'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            const disabled = row && row.action_enabled ? '' : 'disabled';
                            return `
                                <button class="btn btn-light-primary icon-btn btnView" ${disabled} title="Lihat Bukti">
                                    <center>
                                        <i class="bi bi-eye"></i>
                                    </center>
                                </button>`;
                        }
                    }
                ],
                columnDefs: [{
                    targets: 0,
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                }]
            });

            // Global search
            searchInput.addEventListener('input', function() {
                dt.search(this.value).draw();
            });

            // Status filter (exact)
            statusFilter.addEventListener('change', function() {
                const val = this.value;
                if (!val) {
                    dt.column(4).search('').draw();
                } else {
                    // use an anchored regex to match exact transformed label
                    // But API status column uses raw values; use raw value filter with a function
                    // Simpler approach: re-fetch data is static; use built-in search with ^value$
                    dt.column(4).search(val === 'tanpa_keterangan' ? 'Tanpa Keterangan' : (val === 'hadir' ?
                        'Masuk' : ucfirst(val)), true, false).draw();
                }
            });

            // Open modal when eye clicked
            $(tableEl).on('click', '.btnView', function() {
                const data = dt.row($(this).closest('tr')).data();
                if (!data) return;

                function setImage(img, url) {
                    if (url) {
                        img.src = url;
                        img.style.display = 'block';
                    } else {
                        img.src = '';
                        img.style.display = 'none';
                    }
                }

                setImage(imgCheckinPhoto, data.photo_checkin_url);
                setImage(imgCheckinTTD, data.ttd_checkin_url);
                setImage(imgCheckoutPhoto, data.photo_checkout_url);
                setImage(imgCheckoutTTD, data.ttd_checkout_url);

                buktiModal.show();
            });
        })();
    </script>
@endsection
