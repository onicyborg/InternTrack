@extends('layout.master')

@section('title', 'Mahasiswa - Logbooks')
@section('page_heading', 'Mahasiswa')

@section('extra_css')
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .toolbar {
            gap: .75rem;
        }

        .table thead th {
            white-space: nowrap;
        }

        .search-input {
            max-width: 300px;
            min-width: 220px;
        }

        .btn-brand {
            background: linear-gradient(135deg, #4f46e5 0%, #14b8a6 100%);
            color: #fff;
            border: none;
        }

        .btn-brand:hover {
            filter: brightness(.95);
            color: #fff;
        }

        .action-btns .btn {
            --bs-btn-padding-y: .35rem;
            --bs-btn-padding-x: .55rem;
            --bs-btn-font-size: .875rem;
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header flex-wrap d-flex align-items-center gap-2">
            <h3 class="card-title mb-0 me-4">Data Logbook</h3>
            <div class="d-flex align-items-center gap-2 flex-wrap my-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="logbooksSearch" class="form-control form-control-sm" placeholder="Cari kata kunci...">
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <a href="#" id="btnAddLogbook" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Tambah Data
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                @php
                    $mapBadge = function($status) {
                        $s = strtolower((string)$status);
                        return match ($s) {
                            'approved', 'approve', 'disetujui' => ['success','Approved'],
                            'pending', 'menunggu' => ['warning','Pending'],
                            'rejected', 'reject', 'ditolak' => ['danger','Rejected'],
                            default => ['secondary', ucfirst($s ?: '-')],
                        };
                    };
                    $isApproved = function($status) {
                        $s = strtolower((string)$status);
                        return in_array($s, ['approved','approve','disetujui']);
                    };
                @endphp
                <table id="logbooksTable" class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-semibold text-muted">
                            <th style="width:60px">No</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Subject Logbook</th>
                            <th>Approval Pembina</th>
                            <th>Approval Dosen</th>
                            <th style="width:160px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($logbooks ?? []) as $log)
                            @php
                                [$bpClass,$bpText] = $mapBadge($log->approval_pembina ?? 'pending');
                                [$bdClass,$bdText] = $mapBadge($log->approval_dosen ?? 'pending');
                            @endphp
                            <tr>
                                <td></td>
                                <td>
                                    @if(!empty($log->start_date))
                                        {{ \Carbon\Carbon::parse($log->start_date)->locale('id')->translatedFormat('l, d F Y') }}
                                    @else - @endif
                                </td>
                                <td>
                                    @if(!empty($log->end_date))
                                        {{ \Carbon\Carbon::parse($log->end_date)->locale('id')->translatedFormat('l, d F Y') }}
                                    @else - @endif
                                </td>
                                <td>{{ $log->subject ?? '-' }}</td>
                                <td><span class="badge bg-{{ $bpClass }}">{{ $bpText }}</span></td>
                                <td><span class="badge bg-{{ $bdClass }}">{{ $bdText }}</span></td>
                                <td class="action-btns">
                                    @php
                                        $approvedPembina = $isApproved($log->approval_pembina ?? 'pending');
                                        $approvedDosen   = $isApproved($log->approval_dosen ?? 'pending');
                                        $bothApproved    = $approvedPembina && $approvedDosen;
                                    @endphp
                                    <div class="d-flex gap-2">
                                        @if(!$bothApproved)
                                            <a href="#" class="btn btn-primary btn-sm btnEditLogbook" data-id="{{ $log->id }}" title="Edit">
                                                <center><i class="bi bi-pencil"></i></center>
                                            </a>
                                            <a href="#" class="btn btn-danger btn-sm btnDeleteLogbook" data-id="{{ $log->id }}" title="Hapus">
                                                <center><i class="bi bi-trash"></i></center>
                                            </a>
                                        @else
                                            <a href="#" class="btn btn-dark btn-sm btnViewLogbookDetail" data-id="{{ $log->id }}">
                                                <center><i class="bi bi-card-text"></i> <span class="d-none d-sm-inline">Detail
                                                    Logbook</span></center>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada data logbook</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modal Tambah Logbook -->
    <div class="modal fade" id="logbookModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="logbookForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Data Logbook</h5>
                        <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ki-duotone ki-cross fs-2x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-5">
                            <div class="col-md-6">
                                <label class="form-label">Mulai <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date" class="form-control" required>
                                <div class="invalid-feedback" data-field="start_date"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Selesai <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" id="end_date" class="form-control" required>
                                <div class="invalid-feedback" data-field="end_date"></div>
                            </div>
                        </div>
                        <div class="mt-5">
                            <label class="form-label">Subjek <span class="text-danger">*</span></label>
                            <input type="text" name="subject" id="subject" class="form-control" placeholder="Judul Informasi" required>
                            <div class="invalid-feedback" data-field="subject"></div>
                        </div>
                        <div class="mt-5">
                            <label class="form-label">Deskripsi Logbook <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" class="form-control" rows="6" placeholder="Tulis deskripsi logbook..."></textarea>
                            <div class="invalid-feedback" data-field="description"></div>
                        </div>
                        <div class="mt-5">
                            <label class="form-label">Unggah Lampiran (opsional)</label>
                            <div id="attachmentsDropzone" class="dropzone border rounded-3 p-5">
                                <div class="dz-message">
                                    <span>Letakkan file di sini atau klik untuk memilih (bisa lebih dari satu).</span>
                                </div>
                            </div>
                            <div class="form-text">Format: jpg, jpeg, png, webp, gif, pdf. Maks 5MB per file.</div>
                            <div class="invalid-feedback d-block" data-field="attachments"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveLogbook">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Edit Logbook -->
    <div class="modal fade" id="logbookEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="logbookEditForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Logbook</h5>
                        <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ki-duotone ki-cross fs-2x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_id">
                        <div class="row g-5">
                            <div class="col-md-6">
                                <label class="form-label">Mulai <span class="text-danger">*</span></label>
                                <input type="date" id="edit_start_date" class="form-control" required>
                                <div class="invalid-feedback" data-field="start_date"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Selesai <span class="text-danger">*</span></label>
                                <input type="date" id="edit_end_date" class="form-control" required>
                                <div class="invalid-feedback" data-field="end_date"></div>
                            </div>
                        </div>
                        <div class="mt-5">
                            <label class="form-label">Subjek <span class="text-danger">*</span></label>
                            <input type="text" id="edit_subject" class="form-control" placeholder="Judul Informasi" required>
                            <div class="invalid-feedback" data-field="subject"></div>
                        </div>
                        <div class="mt-5">
                            <label class="form-label">Deskripsi Logbook <span class="text-danger">*</span></label>
                            <textarea id="edit_description" class="form-control" rows="6" placeholder="Tulis deskripsi logbook..."></textarea>
                            <div class="invalid-feedback" data-field="description"></div>
                        </div>
                        <div class="mt-5">
                            <label class="form-label">Lampiran Saat Ini</label>
                            <div id="edit_attachments_preview" class="d-flex flex-wrap gap-3"></div>
                        </div>
                        <div class="mt-5">
                            <label class="form-label">Tambah Lampiran Baru (opsional)</label>
                            <div id="attachmentsDropzoneEdit" class="dropzone border rounded-3 p-5">
                                <div class="dz-message">
                                    <span>Letakkan file di sini atau klik untuk memilih (bisa lebih dari satu).</span>
                                </div>
                            </div>
                            <div class="form-text">Format: jpg, jpeg, png, webp, gif, pdf. Maks 5MB per file.</div>
                        </div>
                        <hr class="my-5" />
                        <div class="mt-3">
                            <label class="form-label">Remark Pembina</label>
                            <div id="edit_remark_pembina" class="form-control" style="height:auto; min-height: 120px; overflow:auto"></div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Remark Dosen</label>
                            <div id="edit_remark_dosen" class="form-control" style="height:auto; min-height: 120px; overflow:auto"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnUpdateLogbook">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Detail Logbook (Read-Only) -->
    <div class="modal fade" id="logbookDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
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
                            <label class="form-label">Mulai</label>
                            <input type="text" id="detail_start_date" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Selesai</label>
                            <input type="text" id="detail_end_date" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="mt-5">
                        <label class="form-label">Subjek</label>
                        <input type="text" id="detail_subject" class="form-control" readonly>
                    </div>
                    <div class="mt-5">
                        <label class="form-label">Deskripsi Logbook</label>
                        <div id="detail_description" class="form-control" style="height:auto; min-height: 160px; overflow:auto"></div>
                    </div>
                    <div class="mt-5">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <label class="form-label mb-0">Lampiran</label>
                            <a id="btnZipAllDetail" href="#" target="_blank" class="btn btn-sm btn-light-primary">
                                Unduh Semua (.zip)
                            </a>
                        </div>
                        <ul id="detail_attachments" class="list-group mt-2"></ul>
                    </div>
                    <hr class="my-5" />
                    <div class="mt-3">
                        <label class="form-label">Remark Pembina</label>
                        <div id="detail_remark_pembina" class="form-control" style="height:auto; min-height: 120px; overflow:auto"></div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Remark Dosen</label>
                        <div id="detail_remark_dosen" class="form-control" style="height:auto; min-height: 120px; overflow:auto"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('extra_js')
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <!-- SweetAlert2 (fallback) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tiny.cloud/1/5gorw5nx4zw5j4viyd3rjucdwe1xqqwublsayv3cd879rzso/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        (function() {
            const CSRF_TOKEN = '{{ csrf_token() }}';
            const routes = {
                store: '{{ route('mahasiswa.logbooks.store') }}',
            };

            const table = $('#logbooksTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthChange: false,
                ordering: false,
                searching: true
            });

            function renumber() {
                const info = table.page.info();
                table.column(0, { search: 'applied', order: 'applied', page: 'current' })
                    .nodes()
                    .each(function(cell, i) { cell.innerHTML = (info.start + i + 1).toString(); });
            }
            table.on('order.dt search.dt draw.dt', renumber);
            renumber();

            // Pencarian eksternal ala manage-mentors
            const keyword = document.getElementById('logbooksSearch');
            if (keyword) {
                keyword.addEventListener('input', function(){
                    table.search(this.value).draw();
                });
            }

            // Tampilkan toast sukses jika ada flag dari reload sebelumnya
            (function(){
                try {
                    const msg = localStorage.getItem('logbook_saved');
                    if (msg) {
                        toastr?.success?.(msg);
                        localStorage.removeItem('logbook_saved');
                    }
                    const del = localStorage.getItem('logbook_deleted');
                    if (del) {
                        toastr?.success?.(del);
                        localStorage.removeItem('logbook_deleted');
                    }
                } catch (e) {}
            })();

            // Helper: format ukuran file
            function formatBytes(bytes) {
                if (bytes === 0 || bytes === null || typeof bytes === 'undefined') return '';
                const k = 1024;
                const sizes = ['B','KB','MB','GB','TB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            // Utility: init & destroy TinyMCE (untuk form tambah & edit)
            function buildTinyMCEOptions(selector) {
                const opts = {
                    selector: selector,
                    height: 300,
                    menubar: false,
                    toolbar_sticky: true,
                    plugins: [
                        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor',
                        'searchreplace', 'visualblocks', 'code', 'fullscreen',
                        'insertdatetime', 'media', 'table', 'help', 'wordcount'
                    ],
                    toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | code | help',
                    content_style: 'body { font-family: Inter, Helvetica, Arial, sans-serif; font-size: 14px; }'
                };
                try {
                    if (typeof KTThemeMode !== 'undefined' && KTThemeMode.getMode && KTThemeMode.getMode() === 'dark') {
                        opts.skin = 'oxide-dark';
                        opts.content_css = 'dark';
                    }
                } catch (e) {}
                return opts;
            }

            function ensureTinyMCEInitialized() {
                if (!window.tinymce) return;
                if (!tinymce.get('description')) {
                    tinymce.init(buildTinyMCEOptions('#description'));
                }
                if (!tinymce.get('edit_description')) {
                    tinymce.init(buildTinyMCEOptions('#edit_description'));
                }
            }

            function destroyTinyMCE() {
                if (!window.tinymce) return;
                const ed1 = tinymce.get('description');
                if (ed1) ed1.remove();
                const ed2 = tinymce.get('edit_description');
                if (ed2) ed2.remove();
            }

            // Modal Tambah
            const logbookModalEl = document.getElementById('logbookModal');
            const logbookModal = new bootstrap.Modal(logbookModalEl);
            const btnAdd = document.getElementById('btnAddLogbook');
            const form = document.getElementById('logbookForm');

            function clearValidation() {
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            }

            btnAdd?.addEventListener('click', (e) => {
                e.preventDefault();
                clearValidation();
                form.reset();
                // Bersihkan file di dropzone
                if (attachmentsDz) attachmentsDz.removeAllFiles(true);
                logbookModal.show();
            });

            // Init editor saat modal ditampilkan, destroy saat ditutup
            logbookModalEl.addEventListener('shown.bs.modal', function(){
                ensureTinyMCEInitialized();
                const ed = window.tinymce && tinymce.get('description');
                if (ed) ed.setContent('');
            });
            logbookModalEl.addEventListener('hidden.bs.modal', function(){
                destroyTinyMCE();
            });

            // Inisialisasi Dropzone multiple attachments
            let attachmentsDz;
            if (window.Dropzone) {
                Dropzone.autoDiscover = false;
                attachmentsDz = new Dropzone('#attachmentsDropzone', {
                    url: routes.store, // tidak digunakan (kita submit manual)
                    autoProcessQueue: false,
                    uploadMultiple: true,
                    parallelUploads: 10,
                    maxFilesize: 5, // MB
                    addRemoveLinks: true,
                    acceptedFiles: '.jpg,.jpeg,.png,.webp,.gif,.pdf',
                });
            }

            form.addEventListener('submit', function(e){
                e.preventDefault();
                clearValidation();

                const fd = new FormData();
                fd.append('start_date', document.getElementById('start_date').value);
                fd.append('end_date', document.getElementById('end_date').value);
                fd.append('subject', document.getElementById('subject').value);
                let descVal = document.getElementById('description').value;
                if (window.tinymce) {
                    const ed = tinymce.get('description');
                    if (ed) descVal = ed.getContent();
                }
                fd.append('description', descVal);
                if (attachmentsDz) {
                    attachmentsDz.getAcceptedFiles().forEach(f => fd.append('attachments[]', f, f.name));
                }

                $.ajax({
                    url: routes.store,
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function(resp){
                        // Simpan pesan ke localStorage dan reload halaman agar data dari DB termuat ulang di urutan paling atas
                        try {
                            localStorage.setItem('logbook_saved', resp?.message || 'Logbook berhasil ditambahkan');
                        } catch (e) {}
                        window.location.reload();
                    },
                    error: function(xhr){
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            const errs = xhr.responseJSON.errors;
                            Object.keys(errs).forEach(k => {
                                const input = form.querySelector(`[name="${k}"]`);
                                if (input) input.classList.add('is-invalid');
                                const fb = form.querySelector(`.invalid-feedback[data-field="${k}"]`);
                                if (fb) fb.textContent = errs[k][0];
                            });
                            toastr?.error?.('Periksa kembali isian Anda.');
                            return;
                        }
                        toastr?.error?.('Terjadi kesalahan. Coba lagi.');
                    }
                });
            });

            // ========== Edit ==========
            const editModalEl = document.getElementById('logbookEditModal');
            const editModal = new bootstrap.Modal(editModalEl);
            const editForm = document.getElementById('logbookEditForm');
            let attachmentsDzEdit;
            let removedExisting = [];
            let pendingEditDescription = null;

            // Init editor saat modal edit tampil / tutup
            editModalEl.addEventListener('shown.bs.modal', function(){
                ensureTinyMCEInitialized();
                // Pastikan konten TinyMCE diisi setelah editor siap
                const ed = window.tinymce && tinymce.get('edit_description');
                if (ed) {
                    const val = pendingEditDescription ?? document.getElementById('edit_description').value;
                    ed.setContent(val || '');
                    pendingEditDescription = null;
                }
            });
            editModalEl.addEventListener('hidden.bs.modal', function(){
                destroyTinyMCE();
                if (attachmentsDzEdit) attachmentsDzEdit.removeAllFiles(true);
                removedExisting = [];
                const previewWrap = document.getElementById('edit_attachments_preview');
                if (previewWrap) previewWrap.innerHTML = '';
            });

            // Klik tombol edit
            $(document).on('click', '.btnEditLogbook', function(e){
                e.preventDefault();
                const id = $(this).data('id');
                if (!id) return;
                // Fetch data
                $.get(routes.store.replace('/logbooks', `/logbooks/${id}`), function(resp){
                    const d = resp.data || {};
                    $('#edit_id').val(d.id);
                    $('#edit_start_date').val(d.start_date ? d.start_date.substring(0,10) : '');
                    $('#edit_end_date').val(d.end_date ? d.end_date.substring(0,10) : '');
                    $('#edit_subject').val(d.subject || '');
                    // set content tinymce
                    pendingEditDescription = d.description || '';
                    $('#edit_description').val(pendingEditDescription);
                    // set remarks read-only
                    document.getElementById('edit_remark_pembina').innerHTML = (d.remark_pembina && d.remark_pembina.trim()) ? d.remark_pembina : '<span class="text-muted fst-italic">belum ada keterangan dari pembina</span>';
                    document.getElementById('edit_remark_dosen').innerHTML = (d.remark_dosen && d.remark_dosen.trim()) ? d.remark_dosen : '<span class="text-muted fst-italic">belum ada keterangan dari dosen</span>';
                    // Init Dropzone Edit
                    if (window.Dropzone) {
                        // Bersihkan instance & preview sebelumnya untuk mencegah duplikasi
                        const dzEl = document.getElementById('attachmentsDropzoneEdit');
                        if (attachmentsDzEdit) {
                            try { attachmentsDzEdit.removeAllFiles(true); } catch (e) {}
                            if (attachmentsDzEdit.destroy) attachmentsDzEdit.destroy();
                        }
                        // Hapus elemen preview yang tersisa di DOM
                        if (dzEl) {
                            dzEl.querySelectorAll('.dz-preview').forEach(n => n.remove());
                        }
                        Dropzone.autoDiscover = false;
                        attachmentsDzEdit = new Dropzone('#attachmentsDropzoneEdit', {
                            url: routes.store, // tidak digunakan (submit manual)
                            autoProcessQueue: false,
                            uploadMultiple: true,
                            parallelUploads: 10,
                            maxFilesize: 5,
                            addRemoveLinks: true,
                            acceptedFiles: '.jpg,.jpeg,.png,.webp,.gif,.pdf',
                        });

                        // Tampilkan lampiran existing di area Dropzone agar bisa dihapus/ditambah sinkron
                        removedExisting = [];
                        (d.attachments || []).forEach(a => {
                            const url = a.filename;
                            const name = (a.original_name || a.name || url.split('/').pop());
                            const size = a.size || 0;
                            const mockFile = { name: name, size: size, existing: true, existingId: a.id || null, existingPath: url };
                            attachmentsDzEdit.emit('addedfile', mockFile);
                            const isImg = /(jpg|jpeg|png|webp|gif)$/i.test(url);
                            if (isImg) {
                                attachmentsDzEdit.emit('thumbnail', mockFile, url);
                            } else {
                                // non-image: biarkan default icon
                            }
                            attachmentsDzEdit.emit('complete', mockFile);

                            // Tambahkan tombol unduh dan tampilkan ukuran di preview
                            const previewEl = mockFile.previewElement;
                            if (previewEl) {
                                // Perbarui size text jika tersedia elemen data-dz-size
                                const sizeEl = previewEl.querySelector('[data-dz-size]');
                                if (sizeEl && size > 0) {
                                    sizeEl.textContent = formatBytes(size);
                                } else if (size > 0) {
                                    const meta = document.createElement('small');
                                    meta.className = 'text-muted d-block';
                                    meta.textContent = formatBytes(size);
                                    previewEl.appendChild(meta);
                                }

                                // Tambah link unduh
                                const actionWrap = document.createElement('div');
                                actionWrap.className = 'mt-2';
                                const aTag = document.createElement('a');
                                aTag.href = url;
                                aTag.target = '_blank';
                                aTag.rel = 'noopener';
                                aTag.className = 'btn btn-sm btn-light-primary';
                                aTag.textContent = 'Unduh';
                                // atribut download opsional (beberapa browser abaikan jika cross-origin)
                                aTag.setAttribute('download', name);
                                actionWrap.appendChild(aTag);
                                previewEl.appendChild(actionWrap);
                            }
                        });

                        // Saat user menghapus file, jika itu existing -> catat untuk dihapus di server
                        attachmentsDzEdit.on('removedfile', function(file){
                            if (file.existing) {
                                const idOrPath = file.existingId || file.existingPath;
                                if (idOrPath && !removedExisting.includes(idOrPath)) {
                                    removedExisting.push(idOrPath);
                                }
                            }
                        });
                    }
                    editModal.show();
                });
            });

            // Submit update
            editForm.addEventListener('submit', function(e){
                e.preventDefault();
                // clear validation
                editForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                editForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

                const id = document.getElementById('edit_id').value;
                const fd = new FormData();
                fd.append('start_date', document.getElementById('edit_start_date').value);
                fd.append('end_date', document.getElementById('edit_end_date').value);
                fd.append('subject', document.getElementById('edit_subject').value);
                let descVal = document.getElementById('edit_description').value;
                const ed2 = window.tinymce && tinymce.get('edit_description');
                if (ed2) descVal = ed2.getContent();
                fd.append('description', descVal);
                if (attachmentsDzEdit) {
                    attachmentsDzEdit.getAcceptedFiles().forEach(f => fd.append('attachments[]', f, f.name));
                }
                // Kirim list lampiran existing yang dihapus (by id jika ada, fallback by path)
                if (removedExisting && removedExisting.length) {
                    removedExisting.forEach(val => fd.append('removed_attachments[]', val));
                }
                fd.append('_method', 'PUT');

                $.ajax({
                    url: routes.store.replace('/logbooks', `/logbooks/${id}`),
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function(resp){
                        try {
                            localStorage.setItem('logbook_saved', resp?.message || 'Logbook berhasil diperbarui');
                        } catch (e) {}
                        window.location.reload();
                    },
                    error: function(xhr){
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            const errs = xhr.responseJSON.errors;
                            Object.keys(errs).forEach(k => {
                                const input = editForm.querySelector(`[name="${k}"]`) || document.getElementById(`edit_${k}`);
                                if (input) input.classList.add('is-invalid');
                                const fb = editForm.querySelector(`.invalid-feedback[data-field="${k}"]`);
                                if (fb) fb.textContent = errs[k][0];
                            });
                            toastr?.error?.('Periksa kembali isian Anda.');
                            return;
                        }
                        toastr?.error?.('Terjadi kesalahan. Coba lagi.');
                    }
                });
            });

            // ========== Detail (Read-Only) ==========
            const detailModalEl = document.getElementById('logbookDetailModal');
            const detailModal = new bootstrap.Modal(detailModalEl);
            $(document).on('click', '.btnViewLogbookDetail', function(e){
                e.preventDefault();
                const id = $(this).data('id');
                if (!id) return;
                // Reset fields
                $('#detail_start_date').val('');
                $('#detail_end_date').val('');
                $('#detail_subject').val('');
                $('#detail_description').html('');
                $('#detail_remark_pembina').html('');
                $('#detail_remark_dosen').html('');
                const list = document.getElementById('detail_attachments');
                if (list) list.innerHTML = '';
                // Set ZIP all link
                const zipBtn = document.getElementById('btnZipAllDetail');
                if (zipBtn) {
                    const zipUrlTpl = "{{ route('mahasiswa.logbooks.download_zip', ['id' => 'LOGBOOK_ID']) }}";
                    zipBtn.href = zipUrlTpl.replace('LOGBOOK_ID', encodeURIComponent(id));
                }

                // Ambil detail dari endpoint yang sama digunakan saat Edit
                $.get(routes.store.replace('/logbooks', `/logbooks/${id}`), function(resp){
                    const d = resp?.data || {};
                    // Tanggal diformat lokal id jika moment tersedia
                    try {
                        if (window.moment) {
                            if (d.start_date) $('#detail_start_date').val(moment(d.start_date).locale('id').format('dddd, DD MMMM YYYY'));
                            if (d.end_date) $('#detail_end_date').val(moment(d.end_date).locale('id').format('dddd, DD MMMM YYYY'));
                        } else {
                            if (d.start_date) $('#detail_start_date').val(d.start_date);
                            if (d.end_date) $('#detail_end_date').val(d.end_date);
                        }
                    } catch (e) {
                        if (d.start_date) $('#detail_start_date').val(d.start_date);
                        if (d.end_date) $('#detail_end_date').val(d.end_date);
                    }
                    $('#detail_subject').val(d.subject || '');
                    // Deskripsi disajikan sebagai HTML read-only
                    document.getElementById('detail_description').innerHTML = d.description || '';
                    // Remarks
                    document.getElementById('detail_remark_pembina').innerHTML = d.remark_pembina || '<span class="text-muted">-</span>';
                    document.getElementById('detail_remark_dosen').innerHTML = d.remark_dosen || '<span class="text-muted">-</span>';
                    // Attachments list
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

                    detailModal.show();
                });
            });

            $(document).on('click', '.btnDeleteLogbook', function(e){
                e.preventDefault();
                const id = $(this).data('id');
                if (!id) return;
                const doDelete = () => {
                    $.ajax({
                        url: routes.store.replace('/logbooks', `/logbooks/${id}`),
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                        data: { _method: 'DELETE' },
                        success: function(resp){
                            try {
                                localStorage.setItem('logbook_deleted', resp?.message || 'Logbook berhasil dihapus');
                            } catch (e) {}
                            window.location.reload();
                        },
                        error: function(){
                            toastr?.error?.('Gagal menghapus data. Coba lagi.');
                        }
                    });
                };

                if (window.Swal && typeof Swal.fire === 'function') {
                    Swal.fire({
                        title: 'Hapus logbook?',
                        text: 'Tindakan ini tidak dapat dibatalkan.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal',
                        customClass: {
                            confirmButton: 'btn btn-danger',
                            cancelButton: 'btn btn-light'
                        },
                        buttonsStyling: false
                    }).then((r) => {
                        if (r.isConfirmed) doDelete();
                    });
                } else {
                    if (confirm('Yakin menghapus logbook ini?')) doDelete();
                }
            });
        })();
    </script>
@endsection
