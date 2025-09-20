@extends('layout.master')

@section('title', 'Dosen - Detail Logbook Mahasiswa')
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
                            <th style="width: 160px;">Aksi</th>
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
                                        'approved' => 'badge-light-success', // pengaman jika ada variasi label
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
                                        @php $isPending = strtolower($apDos) === 'pending'; @endphp
                                        @php $isReject = strtolower($apDos) === 'reject'; @endphp
                                        <a href="#"
                                           class="btn btn-sm btn-action btnViewLogbook {{ $isPending ? 'btn-warning' : ($isReject ? 'btn-danger' : 'btn-primary') }}"
                                           data-user-id="{{ $student->id }}"
                                           data-logbook-id="{{ $log->id }}"
                                           data-status-dosen="{{ strtolower($apDos) }}">
                                            {{ $isPending ? 'Verifikasi Data' : ($isReject ? 'Revisi Respon' : 'Detail Logbook') }}
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
            const csrfToken = '{{ csrf_token() }}';

            if (window.jQuery && $.fn && $.fn.DataTable) {
                const dt = $(tableEl).DataTable({
                    responsive: true,
                    pageLength: 10,
                    lengthChange: true,
                    lengthMenu: [[10, 25, 50, 100],[10, 25, 50, 100]],
                    ordering: true,
                    language: {
                        emptyTable: 'Belum ada data logbook.'
                    },
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
                // Fallback simple filter jika DataTables tidak tersedia
                // Fallback sederhana tanpa DataTables
                // Tampilkan pesan jika kosong
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

            // ========== Modal Review Logbook ==========
            // Build modal once
            const modalHtml = `
            <div class="modal fade" id="logbookReviewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Review Logbook</h5>
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
                                <textarea id="rv_description" class="form-control" rows="8" readonly></textarea>
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
                                <label class="form-label">Remark Dosen</label>
                                <textarea id="rv_remark_dosen" class="form-control" rows="6"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label d-block">Keputusan</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="rv_decision" id="rv_approve" value="approve">
                                    <label class="form-check-label" for="rv_approve">Approve</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="rv_decision" id="rv_reject" value="reject">
                                    <label class="form-check-label" for="rv_reject">Reject</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                            <button type="button" id="btnSubmitDecision" class="btn btn-primary">Simpan Keputusan</button>
                        </div>
                    </div>
                </div>
            </div>`;

            const container = document.createElement('div');
            container.innerHTML = modalHtml;
            document.body.appendChild(container);
            const reviewModalEl = document.getElementById('logbookReviewModal');
            const reviewModal = new bootstrap.Modal(reviewModalEl);

            // TinyMCE init for readonly description and editable remark
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
                                // Paksa readonly mode pada editor deskripsi
                                if (editor.mode && editor.mode.set) {
                                    editor.mode.set('readonly');
                                } else {
                                    // Fallback untuk versi yang berbeda
                                    editor.setMode && editor.setMode('readonly');
                                }
                            });
                        }
                    });
                }
                if (!tinymce.get('rv_remark_dosen')) {
                    tinymce.init({
                        selector: '#rv_remark_dosen',
                        menubar: false,
                        height: 220,
                        plugins: 'advlist autolink lists link charmap preview searchreplace visualblocks fullscreen insertdatetime table help wordcount',
                        toolbar: 'undo redo | bold italic underline | bullist numlist | alignleft aligncenter alignright | removeformat',
                        content_style: 'body { font-family: Inter, Helvetica, Arial, sans-serif; font-size: 14px; }'
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

            let currentLogbookId = null;
            let currentUserId = null;

            $(document).on('click', '.btnViewLogbook', function(e){
                e.preventDefault();
                const userId = this.dataset.userId;
                const logbookId = this.dataset.logbookId;
                if (!userId || !logbookId) return;
                currentUserId = userId;
                currentLogbookId = logbookId;

                // Reset form
                document.getElementById('rv_start').value = '';
                document.getElementById('rv_end').value = '';
                document.getElementById('rv_subject').value = '';
                setEditorContent('rv_description', '');
                setEditorContent('rv_remark_dosen', '');
                document.getElementById('rv_approve').checked = false;
                document.getElementById('rv_reject').checked = false;
                const list = document.getElementById('attachmentsList');
                list.innerHTML = '';
                const zipBtn = document.getElementById('btnZipAll');
                zipBtn.href = '#';

                // Fetch detail
                const detailUrl = "{{ route('dosen.logbooks_approval.detail', ['userId' => 'USER_ID', 'logbookId' => 'LOGBOOK_ID']) }}".replace('USER_ID', encodeURIComponent(userId)).replace('LOGBOOK_ID', encodeURIComponent(logbookId));
                $.get(detailUrl, function(resp){
                    const d = resp?.data || {};
                    // Fill basics
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
                    setEditorContent('rv_remark_dosen', d.remark_dosen || '');
                    const status = (d.approval_dosen || '').toLowerCase();
                    if (status === 'approve' || status === 'approved') document.getElementById('rv_approve').checked = true;
                    else if (status === 'reject' || status === 'rejected') document.getElementById('rv_reject').checked = true;

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
                    zipBtn.href = "{{ route('dosen.logbooks_approval.download_zip', ['logbookId' => 'LOGBOOK_ID']) }}".replace('LOGBOOK_ID', encodeURIComponent(logbookId));

                    // Ensure editors are ready then show modal
                    setTimeout(initEditors, 50);
                    setTimeout(() => reviewModal.show(), 120);
                });
            });

            // Submit decision
            document.getElementById('btnSubmitDecision').addEventListener('click', function(){
                if (!currentLogbookId) return;
                const remark = (window.tinymce && tinymce.get('rv_remark_dosen')) ? tinymce.get('rv_remark_dosen').getContent() : (document.getElementById('rv_remark_dosen').value || '');
                const decision = document.getElementById('rv_approve').checked ? 'approve' : (document.getElementById('rv_reject').checked ? 'reject' : '');
                if (!decision) {
                    Swal.fire({ icon: 'warning', title: 'Pilih keputusan', text: 'Silakan pilih Approve atau Reject.' });
                    return;
                }
                const url = "{{ route('dosen.logbooks_approval.decide', ['logbookId' => 'LOGBOOK_ID']) }}".replace('LOGBOOK_ID', encodeURIComponent(currentLogbookId));
                $.ajax({
                    url: url,
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: { decision: decision, remark: remark },
                    success: function(resp){
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: resp?.message || 'Keputusan berhasil disimpan', timer: 1800, showConfirmButton: false });
                        // Refresh halaman agar badge status ter-update
                        setTimeout(() => { window.location.reload(); }, 900);
                    },
                    error: function(xhr){
                        const msg = xhr?.responseJSON?.message || 'Gagal menyimpan keputusan';
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    }
                });
            });
        })();
    </script>
@endsection
