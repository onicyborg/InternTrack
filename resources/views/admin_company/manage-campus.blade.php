@extends('layout.master')

@section('title', 'Manage Campuses')
@section('page_heading', 'Manage Campuses')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Daftar Kampus</h3>
            <button id="btnAddCampus" class="btn btn-primary">
                <i class="ki-duotone ki-plus fs-2"></i> Tambah Kampus
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="campusesTable" class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-semibold text-muted">
                            <th>Nama Kampus</th>
                            <th>Contact Person</th>
                            <th>Email</th>
                            <th>Alamat</th>
                            <th style="width: 140px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($campuses as $c)
                            <tr data-id="{{ $c->id }}">
                                <td class="nama_campus">{{ $c->nama_campus }}</td>
                                <td class="contact_person">{{ $c->contact_person }}</td>
                                <td class="email_campus">{{ $c->email_campus }}</td>
                                <td class="alamat_campus">{{ $c->alamat_campus }}</td>
                                <td>
                                    <button class="btn btn-sm btn-light-primary btnEdit" data-id="{{ $c->id }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light-danger btnDelete" data-id="{{ $c->id }}">
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

    {{-- Modal Add/Edit --}}
    <div class="modal fade" id="campusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="campusForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="campusModalTitle">Tambah Kampus</h5>
                        <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal"
                            aria-label="Close">
                            <i class="ki-duotone ki-cross fs-2x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="modalLoader" class="d-none w-100 py-5 d-flex align-items-center justify-content-center">
                            <div class="spinner-border" role="status" aria-hidden="true"></div>
                            <span class="ms-3">Memuat data...</span>
                        </div>
                        <input type="hidden" id="campus_id" name="id">
                        <div class="mb-5">
                            <label class="form-label">Nama Kampus</label>
                            <input type="text" name="nama_campus" id="nama_campus" class="form-control" required>
                            <div class="invalid-feedback" data-field="nama_campus"></div>
                        </div>
                        <div class="mb-5">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" id="contact_person" class="form-control" required>
                            <div class="invalid-feedback" data-field="contact_person"></div>
                        </div>
                        <div class="mb-5">
                            <label class="form-label">Email</label>
                            <input type="email" name="email_campus" id="email_campus" class="form-control" required>
                            <div class="invalid-feedback" data-field="email_campus"></div>
                        </div>
                        <div class="mb-5">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat_campus" id="alamat_campus" class="form-control" rows="3" required></textarea>
                            <div class="invalid-feedback" data-field="alamat_campus"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveCampus">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('extra_js')
    <script>
        // CSRF Token for AJAX
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const routes = {
            index: '{{ route('company_admin.campuses.index') }}',
            store: '{{ route('company_admin.campuses.store') }}',
            show: (id) => '{{ route('company_admin.campuses.show', ['id' => 'ID_PLACEHOLDER']) }}'.replace(
                'ID_PLACEHOLDER', id),
            update: (id) => '{{ route('company_admin.campuses.update', ['id' => 'ID_PLACEHOLDER']) }}'.replace(
                'ID_PLACEHOLDER', id),
            destroy: (id) => '{{ route('company_admin.campuses.destroy', ['id' => 'ID_PLACEHOLDER']) }}'.replace(
                'ID_PLACEHOLDER', id),
        };

        const campusModalEl = document.getElementById('campusModal');
        const campusModal = new bootstrap.Modal(campusModalEl);

        const campusForm = document.getElementById('campusForm');
        const campusId = document.getElementById('campus_id');
        const namaCampus = document.getElementById('nama_campus');
        const contactPerson = document.getElementById('contact_person');
        const emailCampus = document.getElementById('email_campus');
        const alamatCampus = document.getElementById('alamat_campus');

        const table = $('#campusesTable').DataTable({
            responsive: true,
            pageLength: 10,
            lengthChange: false,
            ordering: true,
            searching: true
        });

        // Reset validation feedback
        function clearValidation() {
            campusForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            campusForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        }

        // Fill modal for edit
        function fillFormFromRow($row) {
            campusId.value = $row.data('id');
            namaCampus.value = $row.find('.nama_campus').text().trim();
            contactPerson.value = $row.find('.contact_person').text().trim();
            emailCampus.value = $row.find('.email_campus').text().trim();
            alamatCampus.value = $row.find('.alamat_campus').text().trim();
        }

        // Open Add
        document.getElementById('btnAddCampus').addEventListener('click', () => {
            clearValidation();
            campusForm.reset();
            campusId.value = '';
            document.getElementById('campusModalTitle').textContent = 'Tambah Kampus';
            campusModal.show();
        });

        // Open Edit
        $('#campusesTable').on('click', '.btnEdit', function() {
            clearValidation();
            const id = $(this).data('id');
            // Prepare modal state
            campusForm.reset();
            campusId.value = id;
            document.getElementById('campusModalTitle').textContent = 'Edit Kampus';
            campusModal.show();

            // Show loader and disable inputs while fetching
            const loader = document.getElementById('modalLoader');
            loader.classList.remove('d-none');
            [...campusForm.elements].forEach(el => el.disabled = true);

            $.ajax({
                url: routes.show(id),
                method: 'GET',
                success: function(resp) {
                    const d = resp.data;
                    namaCampus.value = d.nama_campus || '';
                    contactPerson.value = d.contact_person || '';
                    emailCampus.value = d.email_campus || '';
                    alamatCampus.value = d.alamat_campus || '';
                },
                error: function() {
                    toastr?.error?.('Gagal memuat data kampus. Coba lagi.');
                    campusModal.hide();
                },
                complete: function() {
                    loader.classList.add('d-none');
                    [...campusForm.elements].forEach(el => el.disabled = false);
                }
            });
        });

        // Submit form (Create/Update)
        campusForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearValidation();

            const id = campusId.value;
            const url = id ? routes.update(id) : routes.store;
            const method = id ? 'PUT' : 'POST';

            const payload = {
                nama_campus: namaCampus.value,
                contact_person: contactPerson.value,
                email_campus: emailCampus.value,
                alamat_campus: alamatCampus.value,
            };

            $.ajax({
                url: url,
                method: method,
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                data: payload,
                success: function(resp) {
                    // On success, refresh page or update table row
                    if (!id) {
                        // Append new row
                        const d = resp.data;
                        const rowNode = table.row.add([
                            d.nama_campus,
                            d.contact_person,
                            d.email_campus,
                            d.alamat_campus,
                            `<button class="btn btn-sm btn-light-primary btnEdit" data-id="${d.id}">Edit</button>
                         <button class="btn btn-sm btn-light-danger btnDelete" data-id="${d.id}">Hapus</button>`
                        ]).draw(false).node();
                        $(rowNode).setAttribute && rowNode.setAttribute('data-id', d.id);
                        $(rowNode).attr && $(rowNode).attr('data-id', d.id);
                        // Add helper classes to cells for edit ease
                        $(rowNode).children().eq(0).addClass('nama_campus');
                        $(rowNode).children().eq(1).addClass('contact_person');
                        $(rowNode).children().eq(2).addClass('email_campus');
                        $(rowNode).children().eq(3).addClass('alamat_campus');
                    } else {
                        // Update row
                        const $row = $(`#campusesTable tr[data-id='${id}']`);
                        $row.find('.nama_campus').text(payload.nama_campus);
                        $row.find('.contact_person').text(payload.contact_person);
                        $row.find('.email_campus').text(payload.email_campus);
                        $row.find('.alamat_campus').text(payload.alamat_campus);
                    }

                    campusModal.hide();
                    toastr?.success?.(resp.message || 'Berhasil disimpan');
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errs = xhr.responseJSON.errors;
                        Object.keys(errs).forEach(field => {
                            const input = document.getElementById(field);
                            if (input) {
                                input.classList.add('is-invalid');
                            }
                            const fb = campusForm.querySelector(
                                `.invalid-feedback[data-field=\"${field}\"]`);
                            if (fb) {
                                fb.textContent = errs[field][0];
                            }
                        });
                        return;
                    }
                    toastr?.error?.('Terjadi kesalahan. Coba lagi.');
                }
            });
        });

        // Delete with SweetAlert confirmation (fallback to confirm if not available)
        $('#campusesTable').on('click', '.btnDelete', function() {
            const id = $(this).data('id');
            const name = $(this).closest('tr').find('.nama_campus').text().trim();

            function performDelete() {
                $.ajax({
                    url: routes.destroy(id),
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    success: function(resp) {
                        const row = $(`#campusesTable tr[data-id='${id}']`);
                        table.row(row).remove().draw(false);
                        if (window.Swal && Swal.fire) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus',
                                text: resp.message || 'Berhasil dihapus',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                        toastr?.success?.(resp.message || 'Berhasil dihapus');
                    },
                    error: function() {
                        if (window.Swal && Swal.fire) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal menghapus. Coba lagi.'
                            });
                        }
                        toastr?.error?.('Gagal menghapus. Coba lagi.');
                    }
                });
            }

            if (window.Swal && Swal.fire) {
                Swal.fire({
                    title: 'Hapus kampus?',
                    html: `Apakah Anda yakin ingin menghapus <b>${name || 'data ini'}</b>?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menghapus...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        performDelete();
                    }
                });
            } else {
                if (confirm('Yakin ingin menghapus kampus ini?')) {
                    performDelete();
                }
            }
        });
    </script>
@endsection
