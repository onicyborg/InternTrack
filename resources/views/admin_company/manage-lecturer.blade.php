@extends('layout.master')

@section('title', 'Manage Lecturers')
@section('page_heading', 'Manage Lecturers')

@section('extra_css')
<link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css"/>
<!--begin::Image input placeholder-->
<style>
    .image-input-placeholder { background-image: url('{{ asset('assets/media/svg/avatars/blank.svg') }}'); }
    [data-bs-theme="dark"] .image-input-placeholder { background-image: url('{{ asset('assets/media/svg/avatars/blank-dark.svg') }}'); }
    .image-input-wrapper { background-size: cover; background-position: center; }
    .image-input.image-input-circle .image-input-wrapper { border-radius: 50%; }
    .image-input.image-input-circle { background-image: url('{{ asset('assets/media/svg/avatars/blank.svg') }}'); }
    [data-bs-theme="dark"] .image-input.image-input-circle { background-image: url('{{ asset('assets/media/svg/avatars/blank-dark.svg') }}'); }
    .image-input.image-input-circle { background-repeat: no-repeat; background-position: center; }
    .image-input.image-input-circle .btn { margin-top: .25rem; }
    .image-input.image-input-circle .image-input-wrapper { box-shadow: inset 0 0 0 1px rgba(0,0,0,0.05); }
    .image-input.image-input-circle .btn.btn-icon { display: inline-flex; align-items: center; justify-content: center; }
</style>
<!--end::Image input placeholder-->
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Daftar Dosen</h3>
            <button id="btnAddLecturer" class="btn btn-primary">
                <i class="ki-duotone ki-plus fs-2"></i> Tambah Dosen
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="lecturersTable" class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-semibold text-muted">
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Kampus</th>
                            <th>Phone</th>
                            <th>WhatsApp</th>
                            <th>Status</th>
                            <th style="width: 160px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lecturers as $u)
                            <tr data-id="{{ $u->id }}">
                                <td class="full_name">{{ $u->profile->full_name ?? '-' }}</td>
                                <td class="email">{{ $u->email }}</td>
                                <td class="campus_name" data-campus-id="{{ $u->campus_id }}">{{ $u->campus->nama_campus ?? '-' }}</td>
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
                                    <button class="btn btn-sm btn-light-primary btnEdit" data-id="{{ $u->id }}">Edit</button>
                                    <button class="btn btn-sm btn-light-danger btnDelete" data-id="{{ $u->id }}">Hapus</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Add/Edit --}}
    <div class="modal fade" id="lecturerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="lecturerForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="lecturerModalTitle">Tambah Dosen</h5>
                        <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ki-duotone ki-cross fs-2x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="modalLoader" class="d-none w-100 py-5 d-flex align-items-center justify-content-center">
                            <div class="spinner-border" role="status" aria-hidden="true"></div>
                            <span class="ms-3">Memuat data...</span>
                        </div>

                        <input type="hidden" id="lecturer_id" name="id">

                        <div class="row">
                            <div class="col-md-4 mb-5">
                                <label class="form-label d-block">Foto Profil</label>
                                <!--begin::Image input (circle) -->
                                <div class="image-input image-input-circle" data-kt-image-input="true" style="background-image: url('{{ asset('assets/media/svg/avatars/blank.svg') }}')">
                                    <!--begin::Image preview wrapper-->
                                    <div id="photo_wrapper" class="image-input-wrapper w-125px h-125px" style="background-image: url('{{ asset('assets/media/avatars/300-20.jpg') }}')"></div>
                                    <!--end::Image preview wrapper-->

                                    <!--begin::Edit button-->
                                    <label class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="change"
                                        data-bs-toggle="tooltip"
                                        data-bs-dismiss="click"
                                        title="Change avatar">
                                        <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i>

                                        <!--begin::Inputs-->
                                        <input type="file" name="photo" id="photo" accept=".png, .jpg, .jpeg, .webp" />
                                        <input type="hidden" name="photo_remove" />
                                        <!--end::Inputs-->
                                    </label>
                                    <!--end::Edit button-->

                                    <!--begin::Cancel button-->
                                    <span class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="cancel"
                                        data-bs-toggle="tooltip"
                                        data-bs-dismiss="click"
                                        title="Cancel avatar">
                                        <i class="ki-outline ki-cross fs-3"></i>
                                    </span>
                                    <!--end::Cancel button-->

                                    <!--begin::Remove button-->
                                    <span class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="remove"
                                        data-bs-toggle="tooltip"
                                        data-bs-dismiss="click"
                                        title="Remove avatar">
                                        <i class="ki-outline ki-cross fs-3"></i>
                                    </span>
                                    <!--end::Remove button-->
                                </div>
                                <!--end::Image input -->
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

                        <div class="mb-5">
                            <label class="form-label">Kampus</label>
                            <select name="campus_id" id="campus_id" class="form-select" required>
                                <option value="">Pilih Kampus</option>
                                @foreach ($campuses as $c)
                                    <option value="{{ $c->id }}">{{ $c->nama_campus }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" data-field="campus_id"></div>
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
                                <label class="form-label">NIK</label>
                                <input type="text" name="nik" id="nik" class="form-control">
                                <div class="invalid-feedback" data-field="nik"></div>
                            </div>
                            <div class="col-md-6 mb-5">
                                <label class="form-label">Password (opsional)</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Biarkan kosong jika tidak ingin mengubah">
                                <div class="invalid-feedback" data-field="password"></div>
                            </div>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active">
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveLecturer">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('extra_js')
<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
<script>
    const CSRF_TOKEN = '{{ csrf_token() }}';
    const routes = {
        index: '{{ route('company_admin.lecturers.index') }}',
        store: '{{ route('company_admin.lecturers.store') }}',
        show: (id) => '{{ route('company_admin.lecturers.show', ['id' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
        update: (id) => '{{ route('company_admin.lecturers.update', ['id' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
        destroy: (id) => '{{ route('company_admin.lecturers.destroy', ['id' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', id),
    };

    const lecturerModalEl = document.getElementById('lecturerModal');
    const lecturerModal = new bootstrap.Modal(lecturerModalEl);

    const lecturerForm = document.getElementById('lecturerForm');
    const lecturerId = document.getElementById('lecturer_id');
    const fullName = document.getElementById('full_name');
    const email = document.getElementById('email');
    const campusId = document.getElementById('campus_id');
    const phone = document.getElementById('phone');
    const whatsapp = document.getElementById('whatsapp');
    const nik = document.getElementById('nik');
    const photoInput = document.getElementById('photo');
    const photoWrapper = document.getElementById('photo_wrapper');
    const photoRemove = document.querySelector('input[name="photo_remove"]');
    const password = document.getElementById('password');
    const isActive = document.getElementById('is_active');

    const table = $('#lecturersTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthChange: false,
        ordering: true,
        searching: true
    });

    function clearValidation() {
        lecturerForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        lecturerForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }

    // Open Add
    document.getElementById('btnAddLecturer').addEventListener('click', () => {
        clearValidation();
        lecturerForm.reset();
        lecturerId.value = '';
        isActive.checked = true;
        // reset preview
        photoWrapper.style.backgroundImage = "url('{{ asset('assets/media/avatars/blank.png') }}')";
        if (photoRemove) photoRemove.value = '';
        document.getElementById('lecturerModalTitle').textContent = 'Tambah Dosen';
        lecturerModal.show();
    });

    // Open Edit (load via AJAX with loader)
    $('#lecturersTable').on('click', '.btnEdit', function() {
        clearValidation();
        const id = $(this).data('id');
        lecturerForm.reset();
        lecturerId.value = id;
        document.getElementById('lecturerModalTitle').textContent = 'Edit Dosen';
        lecturerModal.show();

        const loader = document.getElementById('modalLoader');
        loader.classList.remove('d-none');
        [...lecturerForm.elements].forEach(el => el.disabled = true);

        $.ajax({
            url: routes.show(id),
            method: 'GET',
            success: function(resp) {
                const d = resp.data || {};
                fullName.value = d.profile?.full_name || '';
                email.value = d.email || '';
                campusId.value = d.campus_id || '';
                phone.value = d.profile?.phone || '';
                whatsapp.value = d.profile?.whatsapp || '';
                nik.value = d.profile?.nik || '';
                const imgUrl = d.profile?.photo_url || `{{ asset('assets/media/avatars/blank.png') }}`;
                photoWrapper.style.backgroundImage = `url('${imgUrl}')`;
                const active = (d.is_active === true) || (d.is_active === 1) || (d.is_active === '1');
                isActive.checked = active;
                if (photoRemove) photoRemove.value = '';
            },
            error: function() {
                toastr?.error?.('Gagal memuat data dosen. Coba lagi.');
                lecturerModal.hide();
            },
            complete: function() {
                loader.classList.add('d-none');
                [...lecturerForm.elements].forEach(el => el.disabled = false);
            }
        });
    });

    // Submit (create/update)
    lecturerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        clearValidation();

        const id = lecturerId.value;
        const url = id ? routes.update(id) : routes.store;
        const method = id ? 'PUT' : 'POST';

        const formData = new FormData();
        formData.append('full_name', fullName.value);
        formData.append('email', email.value);
        formData.append('campus_id', campusId.value);
        formData.append('phone', phone.value);
        formData.append('whatsapp', whatsapp.value);
        formData.append('nik', nik.value);
        formData.append('is_active', isActive.checked ? 1 : 0);
        if (password.value) formData.append('password', password.value);
        if (photoInput.files && photoInput.files[0]) {
            formData.append('photo', photoInput.files[0]);
            if (photoRemove) photoRemove.value = '';
        }
        if (photoRemove) { formData.append('photo_remove', photoRemove.value); }
        // For update, spoof method PUT to support multipart
        const ajaxMethod = id ? 'POST' : 'POST';
        const ajaxUrl = id ? routes.update(id) : routes.store;
        if (id) { formData.append('_method', 'PUT'); }

        $.ajax({
            url: ajaxUrl,
            method: ajaxMethod,
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
                        d.campus?.nama_campus || '-',
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
                    $(rowNode).children().eq(2).addClass('campus_name').attr('data-campus-id', d.campus_id || '');
                    $(rowNode).children().eq(3).addClass('phone');
                    $(rowNode).children().eq(4).addClass('whatsapp');
                    $(rowNode).children().eq(5).addClass('status').html(active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Nonaktif</span>');
                } else {
                    const $row = $(`#lecturersTable tr[data-id='${id}']`);
                    $row.find('.full_name').text(d.profile?.full_name || '-');
                    $row.find('.email').text(d.email);
                    $row.find('.campus_name').text(d.campus?.nama_campus || '-').attr('data-campus-id', d.campus_id || '');
                    $row.find('.phone').text(d.profile?.phone || '-');
                    $row.find('.whatsapp').text(d.profile?.whatsapp || '-');
                    $row.find('.status').html(active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Nonaktif</span>');
                }
                lecturerModal.hide();
                toastr?.success?.(resp.message || 'Berhasil disimpan');
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errs = xhr.responseJSON.errors;
                    Object.keys(errs).forEach(field => {
                        const input = document.getElementById(field);
                        if (input) input.classList.add('is-invalid');
                        const fb = lecturerForm.querySelector(`.invalid-feedback[data-field="${field}"]`);
                        if (fb) fb.textContent = errs[field][0];
                    });
                    return;
                }
                toastr?.error?.('Terjadi kesalahan. Coba lagi.');
            }
        });
    });

    // Delete with SweetAlert
    $('#lecturersTable').on('click', '.btnDelete', function() {
        const id = $(this).data('id');
        const name = $(this).closest('tr').find('.full_name').text().trim();

        function performDelete() {
            $.ajax({
                url: routes.destroy(id),
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                success: function(resp) {
                    const row = $(`#lecturersTable tr[data-id='${id}']`);
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
                title: 'Hapus dosen?',
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
            if (confirm('Yakin ingin menghapus dosen ini?')) {
                performDelete();
            }
        }
    });
</script>
@endsection
