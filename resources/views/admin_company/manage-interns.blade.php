@extends('layout.master')

@section('title', 'Manage Interns')
@section('page_heading', 'Manage Interns')

@section('extra_css')
<link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css"/>
<style>
    .image-input-placeholder { background-image: url('{{ asset('assets/media/svg/avatars/blank.svg') }}'); }
    [data-bs-theme="dark"] .image-input-placeholder { background-image: url('{{ asset('assets/media/svg/avatars/blank-dark.svg') }}'); }
    .image-input-wrapper { background-size: cover; background-position: center; }
    .image-input.image-input-circle .image-input-wrapper { border-radius: 50%; }
</style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header flex-wrap d-flex align-items-center gap-2">
            <h3 class="card-title mb-0 me-4">Daftar Mahasiswa</h3>
            <!-- Left controls: search + filter -->
            <div class="d-flex align-items-center gap-2 flex-wrap my-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="internsSearch" class="form-control form-control-sm" placeholder="Cari kata kunci...">
                </div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-funnel"></i></span>
                    <select id="programStudiFilter" class="form-select form-select-sm">
                        <option value="">Semua Program Studi</option>
                    </select>
                </div>
            </div>
            <!-- Right controls: buttons -->
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button id="btnExportXlsx" class="btn btn-light-success btn-sm">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </button>
                <button id="btnAddIntern" class="btn btn-primary btn-sm">
                    <i class="ki-duotone ki-plus fs-2"></i> Tambah Mahasiswa
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="internsTable" class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-semibold text-muted">
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Pembimbing</th>
                            <th>Dosen</th>
                            <th>Kampus</th>
                            <th>NIM</th>
                            <th>Program Studi</th>
                            <th>Status</th>
                            <th style="width: 160px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($interns as $u)
                            <tr data-id="{{ $u->id }}">
                                <td class="full_name">{{ $u->profile->full_name ?? '-' }}</td>
                                <td class="email">{{ $u->email }}</td>
                                <td class="pembina_name">{{ $u->pembina->profile->full_name ?? $u->pembina->email ?? '-' }}</td>
                                <td class="dosen_name">{{ $u->dosen->profile->full_name ?? $u->dosen->email ?? '-' }}</td>
                                <td class="campus_name" data-campus-id="{{ $u->campus_id }}">{{ $u->campus->nama_campus ?? '-' }}</td>
                                <td class="nim">{{ $u->profile->nim ?? '-' }}</td>
                                <td class="program_studi">{{ $u->profile->program_studi ?? '-' }}</td>
                                <td class="status">{!! ($u->is_active ?? true) ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Nonaktif</span>' !!}</td>
                                <td>
                                    <button class="btn btn-sm btn-light-primary btnEdit" data-id="{{ $u->id }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light-danger btnDelete" data-id="{{ $u->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="internModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="internForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="internModalTitle">Tambah Mahasiswa</h5>
                        <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ki-duotone ki-cross fs-2x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="modalLoader" class="d-none w-100 py-5 d-flex align-items-center justify-content-center">
                            <div class="spinner-border" role="status" aria-hidden="true"></div>
                            <span class="ms-3">Memuat data...</span>
                        </div>

                        <input type="hidden" id="intern_id" name="id">

                        <div class="row">
                            <div class="col-md-4 mb-5">
                                <label class="form-label d-block">Foto Profil</label>
                                <div class="image-input image-input-circle" data-kt-image-input="true" style="background-image: url('{{ asset('assets/media/svg/avatars/blank.svg') }}')">
                                    <div id="photo_wrapper" class="image-input-wrapper w-125px h-125px"></div>
                                    <label class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Change avatar">
                                        <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i>
                                        <input type="file" name="photo" id="photo" accept=".png, .jpg, .jpeg, .webp" />
                                        <input type="hidden" name="photo_remove" />
                                    </label>
                                    <span class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Cancel avatar">
                                        <i class="ki-outline ki-cross fs-3"></i>
                                    </span>
                                    <span class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Remove avatar">
                                        <i class="ki-outline ki-cross fs-3"></i>
                                    </span>
                                </div>
                                <div class="invalid-feedback d-block" data-field="photo"></div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-5">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="full_name" id="full_name" class="form-control" required>
                                    <div class="invalid-feedback" data-field="full_name"></div>
                                </div>
                                <div class="mb-5">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" id="email" class="form-control" required>
                                    <div class="invalid-feedback" data-field="email"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-5">
                                <label class="form-label">Pembina Magang</label>
                                <select name="pembina_user_id" id="pembina_user_id" class="form-select" required>
                                    <option value="">Pilih Pembina</option>
                                    @foreach ($mentors as $m)
                                        <option value="{{ $m->id }}">{{ $m->profile->full_name ?? $m->email }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" data-field="pembina_user_id"></div>
                            </div>
                            <div class="col-md-6 mb-5">
                                <label class="form-label">Kampus</label>
                                <select name="campus_id" id="campus_id" class="form-select" required>
                                    <option value="">Pilih Kampus</option>
                                    @foreach ($campuses as $c)
                                        <option value="{{ $c->id }}">{{ $c->nama_campus }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" data-field="campus_id"></div>
                            </div>
                        </div>
                        <div class="mb-5">
                            <label class="form-label">Dosen Pembimbing</label>
                            <select name="dosen_user_id" id="dosen_user_id" class="form-select" required disabled>
                                <option value="">Pilih Dosen (pilih kampus terlebih dahulu)</option>
                            </select>
                            <div class="invalid-feedback" data-field="dosen_user_id"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-5">
                                <label class="form-label">NIM</label>
                                <input type="text" name="nim" id="nim" class="form-control" required>
                                <div class="invalid-feedback" data-field="nim"></div>
                            </div>
                            <div class="col-md-6 mb-5">
                                <label class="form-label">Program Studi</label>
                                <input type="text" name="program_studi" id="program_studi" class="form-control" required>
                                <div class="invalid-feedback" data-field="program_studi"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-5">
                                <label class="form-label">Masuk Magang</label>
                                <input type="date" name="start_magang" id="start_magang" class="form-control">
                                <div class="invalid-feedback" data-field="start_magang"></div>
                            </div>
                            <div class="col-md-6 mb-5">
                                <label class="form-label">Selesai Magang</label>
                                <input type="date" name="end_magang" id="end_magang" class="form-control">
                                <div class="invalid-feedback" data-field="end_magang"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-5">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" id="phone" class="form-control">
                                <div class="invalid-feedback" data-field="phone"></div>
                            </div>
                            <div class="col-md-6 mb-5">
                                <label class="form-label">WhatsApp</label>
                                <input type="text" name="whatsapp" id="whatsapp" class="form-control">
                                <div class="invalid-feedback" data-field="whatsapp"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-5">
                                <label class="form-label">Password (opsional)</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Biarkan kosong jika tidak ingin mengubah">
                                <div class="invalid-feedback" data-field="password"></div>
                            </div>
                            <div class="col-md-6 mb-5">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_active">
                                    <label class="form-check-label" for="is_active">Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveIntern">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('extra_js')
<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
<!-- SheetJS for Excel export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
    const CSRF_TOKEN = '{{ csrf_token() }}';
    const routes = {
        index: '{{ route('company_admin.interns.index') }}',
        store: '{{ route('company_admin.interns.store') }}',
        show: (id) => '{{ route('company_admin.interns.show', ['id' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
        update: (id) => '{{ route('company_admin.interns.update', ['id' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
        destroy: (id) => '{{ route('company_admin.interns.destroy', ['id' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
        lecturersByCampus: (campusId) => '{{ route('company_admin.interns.lecturers_by_campus', ['campusId' => 'CID']) }}'.replace('CID', campusId),
    };

    const internModalEl = document.getElementById('internModal');
    const internModal = new bootstrap.Modal(internModalEl);

    const internForm = document.getElementById('internForm');
    const internId = document.getElementById('intern_id');
    const fullName = document.getElementById('full_name');
    const email = document.getElementById('email');
    const pembinaId = document.getElementById('pembina_user_id');
    const campusId = document.getElementById('campus_id');
    const dosenId = document.getElementById('dosen_user_id');
    const nim = document.getElementById('nim');
    const programStudi = document.getElementById('program_studi');
    const startMagang = document.getElementById('start_magang');
    const endMagang = document.getElementById('end_magang');
    const phone = document.getElementById('phone');
    const whatsapp = document.getElementById('whatsapp');
    const password = document.getElementById('password');
    const isActive = document.getElementById('is_active');
    const photoInput = document.getElementById('photo');
    const photoWrapper = document.getElementById('photo_wrapper');
    const photoRemove = document.querySelector('input[name="photo_remove"]');

    const table = $('#internsTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthChange: false,
        ordering: true,
        searching: true
    });

    // Populate Program Studi filter options from table data
    function populateProgramStudiFilter() {
        const set = new Set();
        $('#internsTable tbody tr').each(function(){
            const txt = $(this).find('.program_studi').text().trim();
            if (txt) set.add(txt);
        });
        const select = document.getElementById('programStudiFilter');
        const current = select.value;
        // clear except first option
        select.querySelectorAll('option:not(:first-child)').forEach(o=>o.remove());
        Array.from(set).sort().forEach(val => {
            const opt = document.createElement('option');
            opt.value = val;
            opt.textContent = val;
            select.appendChild(opt);
        });
        // restore selection if exists
        if ([...select.options].some(o=>o.value===current)) select.value = current;
    }
    populateProgramStudiFilter();

    // Keyword search
    document.getElementById('internsSearch').addEventListener('input', function(){
        table.search(this.value).draw();
    });

    // Program studi filter (column index 6)
    document.getElementById('programStudiFilter').addEventListener('change', function(){
        const val = this.value;
        if (!val) {
            table.column(6).search('').draw();
        } else {
            // exact match
            table.column(6).search('^'+$.fn.dataTable.util.escapeRegex(val)+'$', true, false).draw();
        }
    });

    // Export XLSX (current filtered rows)
    function tableToXlsx() {
        const rows = table.rows({ search: 'applied' }).nodes();
        const headers = ['Nama','Email','Pembimbing','Dosen','Kampus','NIM','Program Studi','Status'];
        const data = [headers];
        $(rows).each(function(){
            const $r = $(this);
            const statusHtml = $r.find('.status').html() || '';
            const statusTxt = $('<div>').html(statusHtml).text().trim();
            data.push([
                $r.find('.full_name').text().trim(),
                $r.find('.email').text().trim(),
                $r.find('.pembina_name').text().trim(),
                $r.find('.dosen_name').text().trim(),
                $r.find('.campus_name').text().trim(),
                $r.find('.nim').text().trim(),
                $r.find('.program_studi').text().trim(),
                statusTxt,
            ]);
        });
        const ws = XLSX.utils.aoa_to_sheet(data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Interns');
        const dt = new Date();
        const pad = (n)=> (n<10?'0':'')+n;
        const fname = `interns-${dt.getFullYear()}${pad(dt.getMonth()+1)}${pad(dt.getDate())}.xlsx`;
        XLSX.writeFile(wb, fname);
    }
    document.getElementById('btnExportXlsx').addEventListener('click', tableToXlsx);

    // Rebuild program studi options when table changes (e.g., adding new data)
    table.on('draw', function(){ /* no-op for now */ });

    function clearValidation() {
        internForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        internForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }

    function resetDosenDropdown() {
        dosenId.innerHTML = '<option value="">Pilih Dosen (pilih kampus terlebih dahulu)</option>';
        dosenId.disabled = true;
    }

    campusId.addEventListener('change', function() {
        const campusVal = campusId.value;
        resetDosenDropdown();
        if (!campusVal) return;
        // Fetch lecturers by campus
        $.ajax({
            url: routes.lecturersByCampus(campusVal),
            method: 'GET',
            success: function(resp) {
                const list = resp.data || [];
                list.forEach(l => {
                    const name = l.profile?.full_name || l.email;
                    const opt = document.createElement('option');
                    opt.value = l.id;
                    opt.textContent = name;
                    dosenId.appendChild(opt);
                });
                dosenId.disabled = false;
            },
            error: function() {
                toastr?.error?.('Gagal memuat daftar dosen untuk kampus tersebut');
            }
        });
    });

    document.getElementById('btnAddIntern').addEventListener('click', () => {
        clearValidation();
        internForm.reset();
        internId.value = '';
        isActive.checked = true;
        photoWrapper.style.backgroundImage = "url('{{ asset('assets/media/avatars/blank.png') }}')";
        if (photoRemove) photoRemove.value = '';
        resetDosenDropdown();
        document.getElementById('internModalTitle').textContent = 'Tambah Mahasiswa';
        internModal.show();
    });

    $('#internsTable').on('click', '.btnEdit', function() {
        clearValidation();
        const id = $(this).data('id');
        internForm.reset();
        internId.value = id;
        document.getElementById('internModalTitle').textContent = 'Edit Mahasiswa';
        internModal.show();

        const loader = document.getElementById('modalLoader');
        loader.classList.remove('d-none');
        [...internForm.elements].forEach(el => el.disabled = true);

        $.ajax({
            url: routes.show(id),
            method: 'GET',
            success: function(resp) {
                const d = resp.data || {};
                fullName.value = d.profile?.full_name || '';
                email.value = d.email || '';
                pembinaId.value = d.pembina_user_id || '';
                campusId.value = d.campus_id || '';
                nim.value = d.profile?.nim || '';
                programStudi.value = d.profile?.program_studi || '';
                // Dates: ensure formatted as YYYY-MM-DD
                const fmtDate = (s)=> {
                    if (!s) return '';
                    // if already yyyy-mm-dd, return as is
                    if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;
                    const dt = new Date(s);
                    if (isNaN(dt)) return '';
                    const pad = (n)=> (n<10?'0':'')+n;
                    return `${dt.getFullYear()}-${pad(dt.getMonth()+1)}-${pad(dt.getDate())}`;
                };
                startMagang.value = fmtDate(d.profile?.start_magang || '');
                endMagang.value = fmtDate(d.profile?.end_magang || '');
                phone.value = d.profile?.phone || '';
                whatsapp.value = d.profile?.whatsapp || '';
                const imgUrl = d.profile?.photo_url || `{{ asset('assets/media/avatars/blank.png') }}`;
                photoWrapper.style.backgroundImage = `url('${imgUrl}')`;
                const active = (d.is_active === true) || (d.is_active === 1) || (d.is_active === '1');
                isActive.checked = active;
                if (photoRemove) photoRemove.value = '';

                // Populate lecturers (dosen) by selected campus then set selected dosen
                resetDosenDropdown();
                if (d.campus_id) {
                    $.ajax({
                        url: routes.lecturersByCampus(d.campus_id),
                        method: 'GET',
                        success: function(r2) {
                            const list = r2.data || [];
                            list.forEach(l => {
                                const name = l.profile?.full_name || l.email;
                                const opt = document.createElement('option');
                                opt.value = l.id;
                                opt.textContent = name;
                                dosenId.appendChild(opt);
                            });
                            dosenId.disabled = false;
                            if (d.dosen_user_id) {
                                dosenId.value = d.dosen_user_id;
                            }
                        },
                        error: function() { toastr?.error?.('Gagal memuat dosen untuk kampus terpilih'); }
                    });
                }
            },
            error: function() {
                toastr?.error?.('Gagal memuat data mahasiswa. Coba lagi.');
                internModal.hide();
            },
            complete: function() {
                loader.classList.add('d-none');
                [...internForm.elements].forEach(el => el.disabled = false);
            }
        });
    });

    internForm.addEventListener('submit', function(e) {
        e.preventDefault();
        clearValidation();

        const id = internId.value;
        const formData = new FormData();
        formData.append('full_name', fullName.value);
        formData.append('email', email.value);
        formData.append('pembina_user_id', pembinaId.value);
        formData.append('campus_id', campusId.value);
        formData.append('dosen_user_id', dosenId.value);
        formData.append('nim', nim.value);
        formData.append('program_studi', programStudi.value);
        formData.append('start_magang', startMagang.value);
        formData.append('end_magang', endMagang.value);
        formData.append('phone', phone.value);
        formData.append('whatsapp', whatsapp.value);
        formData.append('is_active', isActive.checked ? 1 : 0);
        if (password.value) formData.append('password', password.value);
        if (photoInput.files && photoInput.files[0]) {
            formData.append('photo', photoInput.files[0]);
            if (photoRemove) photoRemove.value = '';
        }
        if (photoRemove) { formData.append('photo_remove', photoRemove.value); }
        if (id) formData.append('_method', 'PUT');

        $.ajax({
            url: id ? routes.update(id) : routes.store,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            data: formData,
            processData: false,
            contentType: false,
            success: function(resp) {
                const d = resp.data;
                const active = (d.is_active === true) || (d.is_active === 1) || (d.is_active === '1');
                if (!id) {
                    const rowNode = table.row.add([
                        d.profile?.full_name || '-',
                        d.email,
                        (d.pembina?.profile?.full_name || d.pembina?.email || '-'),
                        (d.dosen?.profile?.full_name || d.dosen?.email || '-'),
                        (d.campus?.nama_campus || '-'),
                        d.profile?.nim || '-',
                        d.profile?.program_studi || '-',
                        active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Nonaktif</span>',
                        `<button class="btn btn-sm btn-light-primary btnEdit" data-id="${d.id}">Edit</button>
                         <button class="btn btn-sm btn-light-danger btnDelete" data-id="${d.id}">Hapus</button>`
                    ]).draw(false).node();
                    $(rowNode).setAttribute && rowNode.setAttribute('data-id', d.id);
                    $(rowNode).attr && $(rowNode).attr('data-id', d.id);
                    $(rowNode).children().eq(0).addClass('full_name');
                    $(rowNode).children().eq(1).addClass('email');
                    $(rowNode).children().eq(2).addClass('pembina_name');
                    $(rowNode).children().eq(3).addClass('dosen_name');
                    $(rowNode).children().eq(4).addClass('campus_name').attr('data-campus-id', d.campus_id || '');
                    $(rowNode).children().eq(5).addClass('nim');
                    $(rowNode).children().eq(6).addClass('program_studi');
                    $(rowNode).children().eq(7).addClass('status').html(active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Nonaktif</span>');
                } else {
                    const $row = $(`#internsTable tr[data-id='${id}']`);
                    $row.find('.full_name').text(d.profile?.full_name || '-');
                    $row.find('.email').text(d.email);
                    $row.find('.pembina_name').text(d.pembina?.profile?.full_name || d.pembina?.email || '-');
                    $row.find('.dosen_name').text(d.dosen?.profile?.full_name || d.dosen?.email || '-');
                    $row.find('.campus_name').text(d.campus?.nama_campus || '-').attr('data-campus-id', d.campus_id || '');
                    $row.find('.nim').text(d.profile?.nim || '-');
                    $row.find('.program_studi').text(d.profile?.program_studi || '-');
                    $row.find('.status').html(active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Nonaktif</span>');
                }
                internModal.hide();
                toastr?.success?.(resp.message || 'Berhasil disimpan');
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errs = xhr.responseJSON.errors;
                    Object.keys(errs).forEach(field => {
                        const input = document.getElementById(field);
                        if (input) input.classList.add('is-invalid');
                        const fb = internForm.querySelector(`.invalid-feedback[data-field="${field}"]`);
                        if (fb) fb.textContent = errs[field][0];
                    });
                    return;
                }
                toastr?.error?.('Terjadi kesalahan. Coba lagi.');
            }
        });
    });

    $('#internsTable').on('click', '.btnDelete', function() {
        const id = $(this).data('id');
        const name = $(this).closest('tr').find('.full_name').text().trim();

        function performDelete() {
            $.ajax({
                url: routes.destroy(id),
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                success: function(resp) {
                    const row = $(`#internsTable tr[data-id='${id}']`);
                    table.row(row).remove().draw(false);
                    if (window.Swal && Swal.fire) {
                        Swal.fire({ icon: 'success', title: 'Terhapus', text: resp.message || 'Berhasil dihapus', timer: 1500, showConfirmButton: false });
                    }
                    toastr?.success?.(resp.message || 'Berhasil dihapus');
                },
                error: function() {
                    if (window.Swal && Swal.fire) {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal menghapus. Coba lagi.' });
                    }
                    toastr?.error?.('Gagal menghapus. Coba lagi.');
                }
            });
        }

        if (window.Swal && Swal.fire) {
            Swal.fire({
                title: 'Hapus mahasiswa?',
                html: `Apakah Anda yakin ingin menghapus <b>${name || 'data ini'}</b>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    performDelete();
                }
            });
        } else {
            if (confirm('Yakin ingin menghapus mahasiswa ini?')) {
                performDelete();
            }
        }
    });
</script>
@endsection
