@extends('layout.master')

@section('title', 'Manage Mentors')
@section('page_heading', 'Manage Mentors')

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
            <h3 class="card-title mb-0 me-4">Daftar Pembina</h3>
            <div class="d-flex align-items-center gap-2 flex-wrap my-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="mentorsSearch" class="form-control form-control-sm" placeholder="Cari kata kunci...">
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button id="btnExportMentorsXlsx" class="btn btn-light-success btn-sm">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </button>
                <button id="btnAddMentor" class="btn btn-primary btn-sm">
                    <i class="ki-duotone ki-plus fs-2"></i> Tambah Pembina
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="mentorsTable" class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-semibold text-muted">
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>WhatsApp</th>
                            <th>Status</th>
                            <th style="width: 160px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mentors as $u)
                            <tr data-id="{{ $u->id }}">
                                <td class="full_name">{{ $u->profile->full_name ?? '-' }}</td>
                                <td class="email">{{ $u->email }}</td>
                                <td class="phone">{{ $u->profile->phone ?? '-' }}</td>
                                <td class="whatsapp">{{ $u->profile->whatsapp ?? '-' }}</td>
                                <td class="status">
                                    @if($u->is_active ?? true)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-light-primary btnEdit" data-id="{{ $u->id }}">
                                        <center><i class="bi bi-pencil-square"></i></center>
                                    </button>
                                    <button class="btn btn-sm btn-light-danger btnDelete" data-id="{{ $u->id }}">
                                        <center><i class="bi bi-trash"></i></center>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="mentorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="mentorForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mentorModalTitle">Tambah Pembina</h5>
                        <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ki-duotone ki-cross fs-2x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="modalLoader" class="d-none w-100 py-5 d-flex align-items-center justify-content-center">
                            <div class="spinner-border" role="status" aria-hidden="true"></div>
                            <span class="ms-3">Memuat data...</span>
                        </div>

                        <input type="hidden" id="mentor_id" name="id">

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
                        <button type="submit" class="btn btn-primary" id="btnSaveMentor">Simpan</button>
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
        index: '{{ route('company_admin.mentors.index') }}',
        store: '{{ route('company_admin.mentors.store') }}',
        show: (id) => '{{ route('company_admin.mentors.show', ['id' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
        update: (id) => '{{ route('company_admin.mentors.update', ['id' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
        destroy: (id) => '{{ route('company_admin.mentors.destroy', ['id' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
    };

    const mentorModalEl = document.getElementById('mentorModal');
    const mentorModal = new bootstrap.Modal(mentorModalEl);

    const mentorForm = document.getElementById('mentorForm');
    const mentorId = document.getElementById('mentor_id');
    const fullName = document.getElementById('full_name');
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');
    const whatsapp = document.getElementById('whatsapp');
    const password = document.getElementById('password');
    const isActive = document.getElementById('is_active');
    const photoInput = document.getElementById('photo');
    const photoWrapper = document.getElementById('photo_wrapper');
    const photoRemove = document.querySelector('input[name="photo_remove"]');

    const table = $('#mentorsTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthChange: false,
        ordering: true,
        searching: true
    });

    // Keyword search
    document.getElementById('mentorsSearch').addEventListener('input', function(){
        table.search(this.value).draw();
    });

    // Export XLSX (current filtered rows)
    function exportMentorsXlsx() {
        const rows = table.rows({ search: 'applied' }).nodes();
        const headers = ['Nama','Email','Phone','WhatsApp','Status'];
        const data = [headers];
        $(rows).each(function(){
            const $r = $(this);
            const statusHtml = $r.find('.status').html() || '';
            const statusTxt = $('<div>').html(statusHtml).text().trim();
            data.push([
                $r.find('.full_name').text().trim(),
                $r.find('.email').text().trim(),
                $r.find('.phone').text().trim(),
                $r.find('.whatsapp').text().trim(),
                statusTxt,
            ]);
        });
        const ws = XLSX.utils.aoa_to_sheet(data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Mentors');
        const dt = new Date();
        const pad = (n)=> (n<10?'0':'')+n;
        const fname = `mentors-${dt.getFullYear()}${pad(dt.getMonth()+1)}${pad(dt.getDate())}.xlsx`;
        XLSX.writeFile(wb, fname);
    }
    document.getElementById('btnExportMentorsXlsx').addEventListener('click', exportMentorsXlsx);

    function clearValidation() {
        mentorForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        mentorForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }

    document.getElementById('btnAddMentor').addEventListener('click', () => {
        clearValidation();
        mentorForm.reset();
        mentorId.value = '';
        isActive.checked = true;
        photoWrapper.style.backgroundImage = "url('{{ asset('assets/media/avatars/blank.png') }}')";
        if (photoRemove) photoRemove.value = '';
        document.getElementById('mentorModalTitle').textContent = 'Tambah Pembina';
        mentorModal.show();
    });

    $('#mentorsTable').on('click', '.btnEdit', function() {
        clearValidation();
        const id = $(this).data('id');
        mentorForm.reset();
        mentorId.value = id;
        document.getElementById('mentorModalTitle').textContent = 'Edit Pembina';
        mentorModal.show();

        const loader = document.getElementById('modalLoader');
        loader.classList.remove('d-none');
        [...mentorForm.elements].forEach(el => el.disabled = true);

        $.ajax({
            url: routes.show(id),
            method: 'GET',
            success: function(resp) {
                const d = resp.data || {};
                fullName.value = d.profile?.full_name || '';
                email.value = d.email || '';
                phone.value = d.profile?.phone || '';
                whatsapp.value = d.profile?.whatsapp || '';
                const imgUrl = d.profile?.photo_url || `{{ asset('assets/media/avatars/blank.png') }}`;
                photoWrapper.style.backgroundImage = `url('${imgUrl}')`;
                const active = (d.is_active === true) || (d.is_active === 1) || (d.is_active === '1');
                isActive.checked = active;
                if (photoRemove) photoRemove.value = '';
            },
            error: function() {
                toastr?.error?.('Gagal memuat data pembina. Coba lagi.');
                mentorModal.hide();
            },
            complete: function() {
                loader.classList.add('d-none');
                [...mentorForm.elements].forEach(el => el.disabled = false);
            }
        });
    });

    mentorForm.addEventListener('submit', function(e) {
        e.preventDefault();
        clearValidation();

        const id = mentorId.value;
        const formData = new FormData();
        formData.append('full_name', fullName.value);
        formData.append('email', email.value);
        formData.append('phone', phone.value);
        formData.append('whatsapp', whatsapp.value);
        formData.append('is_active', isActive.checked ? 1 : 0);
        if (password.value) formData.append('password', password.value);
        if (photoInput.files && photoInput.files[0]) {
            formData.append('photo', photoInput.files[0]);
            if (photoRemove) photoRemove.value = '';
        }
        if (photoRemove) { formData.append('photo_remove', photoRemove.value); }

        const ajaxUrl = id ? routes.update(id) : routes.store;
        if (id) { formData.append('_method', 'PUT'); }

        $.ajax({
            url: ajaxUrl,
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
                        d.profile?.phone || '-',
                        d.profile?.whatsapp || '-',
                        active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Nonaktif</span>',
                        `<button class="btn btn-sm btn-light-primary btnEdit" data-id="${d.id}">Edit</button>
                         <button class="btn btn-sm btn-light-danger btnDelete" data-id="${d.id}">Hapus</button>`
                    ]).draw(false).node();
                    $(rowNode).setAttribute && rowNode.setAttribute('data-id', d.id);
                    $(rowNode).attr && $(rowNode).attr('data-id', d.id);
                    $(rowNode).children().eq(0).addClass('full_name');
                    $(rowNode).children().eq(1).addClass('email');
                    $(rowNode).children().eq(2).addClass('phone');
                    $(rowNode).children().eq(3).addClass('whatsapp');
                    $(rowNode).children().eq(4).addClass('status').html(active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Nonaktif</span>');
                } else {
                    const $row = $(`#mentorsTable tr[data-id='${id}']`);
                    $row.find('.full_name').text(d.profile?.full_name || '-');
                    $row.find('.email').text(d.email);
                    $row.find('.phone').text(d.profile?.phone || '-');
                    $row.find('.whatsapp').text(d.profile?.whatsapp || '-');
                    $row.find('.status').html(active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Nonaktif</span>');
                }
                mentorModal.hide();
                toastr?.success?.(resp.message || 'Berhasil disimpan');
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errs = xhr.responseJSON.errors;
                    Object.keys(errs).forEach(field => {
                        const input = document.getElementById(field);
                        if (input) input.classList.add('is-invalid');
                        const fb = mentorForm.querySelector(`.invalid-feedback[data-field="${field}"]`);
                        if (fb) fb.textContent = errs[field][0];
                    });
                    return;
                }
                toastr?.error?.('Terjadi kesalahan. Coba lagi.');
            }
        });
    });

    $('#mentorsTable').on('click', '.btnDelete', function() {
        const id = $(this).data('id');
        const name = $(this).closest('tr').find('.full_name').text().trim();

        function performDelete() {
            $.ajax({
                url: routes.destroy(id),
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                success: function(resp) {
                    const row = $(`#mentorsTable tr[data-id='${id}']`);
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
                title: 'Hapus pembina?',
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
            if (confirm('Yakin ingin menghapus pembina ini?')) {
                performDelete();
            }
        }
    });
</script>
@endsection
