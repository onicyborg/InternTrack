@extends('layout.master')

@section('title', 'Admin - Detail Logbook Mahasiswa')
@section('page_heading', 'Detail Logbook Mahasiswa')

@section('extra_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-1.13.6/r-2.5.0/datatables.min.css" />
    <style>
        .table thead th { white-space: nowrap; }
        .filter-bar .form-select, .filter-bar .form-control { max-width: 260px; }
        .search-bar .form-control { max-width: 280px; }
        .btn-action { min-width: 140px; }
    </style>
@endsection

@section('content')
    @php
        $profile = optional($student->profile ?? null);
    @endphp

    <div class="card">
        <div class="card-header d-flex align-items-center flex-wrap gap-3">
            <h3 class="card-title m-0">Daftar Logbook {{ $profile->full_name ?? ($student->email ?? 'Mahasiswa') }}</h3>
            <div class="ms-auto search-bar">
                <div class="input-group input-group-sm">
                    <input type="text" id="globalSearch" class="form-control form-control-sm" placeholder="Cari Data">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="logbooksTable" class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3 w-100">
                    <thead>
                        <tr class="fw-semibold text-muted">
                            <th style="width: 60px;">No</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Subject Logbook</th>
                            <th>Approval Pembina</th>
                            <th>Approval Dosen</th>
                            <th style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(($logbooks ?? collect())->count())
                            @foreach(($logbooks ?? []) as $log)
                            @php
                                $start = $log->start_date ? \Carbon\Carbon::parse($log->start_date)->locale('id')->translatedFormat('l d F Y') : '-';
                                $end   = $log->end_date ? \Carbon\Carbon::parse($log->end_date)->locale('id')->translatedFormat('l d F Y') : '-';
                                $apPemb = $log->approval_pembina ?? 'pending';
                                $apDos  = $log->approval_dosen ?? 'pending';
                                $badge = function($v){
                                    $map = [
                                        'approve' => 'badge-light-success',
                                        'approved' => 'badge-light-success',
                                        'pending' => 'badge-light-warning',
                                        'reject'  => 'badge-light-danger',
                                    ];
                                    $lbl = ucfirst($v);
                                    return '<span class="badge '.($map[strtolower($v)] ?? 'badge-light').'">'.$lbl.'</span>';
                                };
                            @endphp
                            <tr>
                                <td></td>
                                <td>{{ $start }}</td>
                                <td>{{ $end }}</td>
                                <td>{{ $log->subject }}</td>
                                <td>{!! $badge($apPemb) !!}</td>
                                <td>{!! $badge($apDos) !!}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="#"
                                           class="btn btn-sm btn-action btn-primary btnViewLogbook"
                                           data-user-id="{{ $student->id }}"
                                           data-logbook-id="{{ $log->id }}">
                                            Detail Logbook
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('extra_js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/locale/id.js"></script>
    <script src="https://cdn.tiny.cloud/1/5gorw5nx4zw5j4viyd3rjucdwe1xqqwublsayv3cd879rzso/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        (function(){
            const tableEl = document.getElementById('logbooksTable');
            const searchInput = document.getElementById('globalSearch');

            if (window.jQuery && $.fn && $.fn.DataTable) {
                const dt = $(tableEl).DataTable({
                    responsive: true,
                    pageLength: 10,
                    lengthChange: true,
                    lengthMenu: [[10, 25, 50, 100],[10, 25, 50, 100]],
                    ordering: true,
                    language: { emptyTable: 'Belum ada data logbook.' },
                    columnDefs: [
                        { targets: -1, orderable: false, searchable: false },
                        { targets: 0, orderable: false, searchable: false },
                    ]
                });

                // Numbering first column
                dt.on('order.dt search.dt', function(){
                    let i = 1;
                    dt.column(0, { search: 'applied', order: 'applied' })
                      .nodes()
                      .each(function(cell){ cell.innerHTML = i++; });
                }).draw();

                // Global search
                searchInput.addEventListener('input', function(){ dt.search(this.value).draw(); });
            } else {
                const tbody = tableEl.querySelector('tbody');
                if (tbody && !tbody.querySelector('tr')) {
                    const tr = document.createElement('tr');
                    for (let i=0;i<7;i++) {
                        const td = document.createElement('td');
                        if (i === 0) {
                            td.colSpan = 7;
                            td.className = 'text-center text-muted';
                            td.textContent = 'Belum ada data logbook.';
                            tr.appendChild(td);
                            break;
                        }
                        tr.appendChild(td);
                    }
                    tbody.appendChild(tr);
                }
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

            // ========== Modal Detail Logbook (Read-Only) ==========
            const modalHtml = `
            <div class="modal fade" id="logbookDetailModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Detail Logbook</h5>
                            <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal" aria-label="Close">
                                <i class="ki-duotone ki-cross fs-2x"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-5">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Mulai</label>
                                        <input type="text" id="rv_start" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Selesai</label>
                                        <input type="text" id="rv_end" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subjek</label>
                                <input type="text" id="rv_subject" class="form-control" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea id="rv_description" class="form-control" rows="8"></textarea>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <label class="form-label mb-0">Lampiran</label>
                                    <a id="btnZipAll" href="#" target="_blank" class="btn btn-sm btn-light-primary">
                                        Unduh Semua (.zip)
                                    </a>
                                </div>
                                <ul id="attachmentsList" class="list-group mt-2"></ul>
                            </div>
                            <hr class="my-4" />
                            <div class="mb-3">
                                <label class="form-label">Remark Pembina</label>
                                <textarea id="rv_remark_pembina" class="form-control" rows="6"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Remark Dosen</label>
                                <textarea id="rv_remark_dosen" class="form-control" rows="6"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>`;

            const container = document.createElement('div');
            container.innerHTML = modalHtml;
            document.body.appendChild(container);
            const detailModalEl = document.getElementById('logbookDetailModal');
            const detailModal = new bootstrap.Modal(detailModalEl);

            // TinyMCE init for readonly fields
            function initEditors() {
                if (!window.tinymce) return;
                if (!tinymce.get('rv_description')) {
                    tinymce.init({
                        selector: '#rv_description',
                        menubar: false,
                        toolbar: false,
                        height: 260,
                        plugins: 'autolink lists link paste',
                        content_style: 'body { font-family: Inter, Helvetica, Arial, sans-serif; font-size: 14px; }',
                        setup: function (editor) {
                            editor.on('init', function () {
                                if (editor.mode && editor.mode.set) { editor.mode.set('readonly'); }
                                else { editor.setMode && editor.setMode('readonly'); }
                            });
                        }
                    });
                }
                if (!tinymce.get('rv_remark_pembina')) {
                    tinymce.init({
                        selector: '#rv_remark_pembina',
                        menubar: false,
                        toolbar: false,
                        height: 200,
                        plugins: 'autolink lists link paste',
                        content_style: 'body { font-family: Inter, Helvetica, Arial, sans-serif; font-size: 14px; }',
                        setup: function (editor) {
                            editor.on('init', function () {
                                if (editor.mode && editor.mode.set) { editor.mode.set('readonly'); }
                                else { editor.setMode && editor.setMode('readonly'); }
                            });
                        }
                    });
                }
                if (!tinymce.get('rv_remark_dosen')) {
                    tinymce.init({
                        selector: '#rv_remark_dosen',
                        menubar: false,
                        toolbar: false,
                        height: 200,
                        plugins: 'autolink lists link paste',
                        content_style: 'body { font-family: Inter, Helvetica, Arial, sans-serif; font-size: 14px; }',
                        setup: function (editor) {
                            editor.on('init', function () {
                                if (editor.mode && editor.mode.set) { editor.mode.set('readonly'); }
                                else { editor.setMode && editor.setMode('readonly'); }
                            });
                        }
                    });
                }
            }

            function setEditorContent(id, html) {
                const ed = window.tinymce && tinymce.get(id);
                if (ed) ed.setContent(html || '');
                else {
                    const el = document.getElementById(id);
                    if (el) el.value = html || '';
                }
            }

            $(tableEl).on('click', '.btnViewLogbook', function(e){
                e.preventDefault();
                const userId = this.dataset.userId;
                const logbookId = this.dataset.logbookId;
                if (!userId || !logbookId) return;

                // Reset
                document.getElementById('rv_start').value = '';
                document.getElementById('rv_end').value = '';
                document.getElementById('rv_subject').value = '';
                setEditorContent('rv_description', '');
                setEditorContent('rv_remark_pembina', '');
                setEditorContent('rv_remark_dosen', '');
                const list = document.getElementById('attachmentsList');
                list.innerHTML = '';
                const zipBtn = document.getElementById('btnZipAll');
                zipBtn.href = '#';

                // Fetch detail JSON
                const detailUrl = "{{ route('company_admin.logbooks.detail', ['userId' => 'USER_ID', 'logbookId' => 'LOGBOOK_ID']) }}"
                    .replace('USER_ID', encodeURIComponent(userId))
                    .replace('LOGBOOK_ID', encodeURIComponent(logbookId));
                $.get(detailUrl, function(resp){
                    const d = resp?.data || {};
                    if (d.start_date) {
                        try { document.getElementById('rv_start').value = moment(d.start_date).locale('id').format('dddd, DD MMMM YYYY'); }
                        catch(e){ document.getElementById('rv_start').value = d.start_date; }
                    }
                    if (d.end_date) {
                        try { document.getElementById('rv_end').value = moment(d.end_date).locale('id').format('dddd, DD MMMM YYYY'); }
                        catch(e){ document.getElementById('rv_end').value = d.end_date; }
                    }
                    document.getElementById('rv_subject').value = d.subject || '';
                    setEditorContent('rv_description', d.description || '');
                    setEditorContent('rv_remark_pembina', (d.remark_pembina && d.remark_pembina.trim()) ? d.remark_pembina : '<span class="text-muted fst-italic">belum ada keterangan dari pembina</span>');
                    setEditorContent('rv_remark_dosen', (d.remark_dosen && d.remark_dosen.trim()) ? d.remark_dosen : '<span class="text-muted fst-italic">belum ada keterangan dari dosen</span>');

                    // Attachments
                    (d.attachments || []).forEach(a => {
                        const url = a.filename;
                        const name = (a.original_name || a.name || (url ? url.split('/').pop() : 'lampiran'));
                        const li = document.createElement('li');
                        li.className = 'list-group-item d-flex justify-content-between align-items-center';
                        const left = document.createElement('div');
                        left.textContent = name;
                        const right = document.createElement('div');
                        const aTag = document.createElement('a');
                        aTag.href = url;
                        aTag.target = '_blank';
                        aTag.rel = 'noopener';
                        aTag.className = 'btn btn-sm btn-light-primary';
                        aTag.innerHTML = '<center><i class="bi bi-download"></i></center>';
                        aTag.title = 'Unduh';
                        right.appendChild(aTag);
                        li.appendChild(left);
                        li.appendChild(right);
                        list.appendChild(li);
                    });

                    // ZIP all link
                    zipBtn.href = "{{ route('company_admin.logbooks.download_zip', ['logbookId' => 'LOGBOOK_ID']) }}".replace('LOGBOOK_ID', encodeURIComponent(logbookId));

                    // Ensure editors are initialized, then show modal
                    setTimeout(initEditors, 50);
                    setTimeout(() => detailModal.show(), 120);
                });
            });
        })();
    </script>
@endsection
