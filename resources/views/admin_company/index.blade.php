@extends('layout.master')

@section('title', 'Company Admin - Dashboard')
@section('page_heading', 'Company Admin')

@section('content')
    @php
        $mahasiswaTotal = ($mahasiswaActive ?? 0) + ($mahasiswaInactive ?? 0);
        $dosenTotal = ($dosenActive ?? 0) + ($dosenInactive ?? 0);
        $pembinaTotal = ($pembinaActive ?? 0) + ($pembinaInactive ?? 0);
        $campusTotal = $campusTotal ?? 0;
    @endphp

    <div class="row g-6">
        <!-- Card: Total Mahasiswa -->
        <div class="col-sm-6 col-xxl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div class="me-4">
                        <div class="text-gray-600 fw-semibold mb-1">Total Mahasiswa</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <div class="fs-2hx fw-bold">{{ $mahasiswaTotal }}</div>
                        </div>
                        <div class="mt-3 text-gray-500 small">
                            Active: <span class="fw-semibold text-success">{{ $mahasiswaActive ?? 0 }}</span> ·
                            Tidak Aktif: <span class="fw-semibold text-danger">{{ $mahasiswaInactive ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="p-4 rounded-3 bg-light-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" fill="currentColor"
                            class="text-primary" viewBox="0 0 16 16">
                            <path
                                d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0L14 7.14V13a1 1 0 0 0-1 1v2h3v-2a1 1 0 0 0-1-1V6.739l.686-.275a.5.5 0 0 0 .025-.917z" />
                            <path
                                d="M4.176 9.032a.5.5 0 0 0-.656.327l-.5 1.7a.5.5 0 0 0 .294.605l4.5 1.8a.5.5 0 0 0 .372 0l4.5-1.8a.5.5 0 0 0 .294-.605l-.5-1.7a.5.5 0 0 0-.656-.327L8 10.466z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Total Dosen Pembimbing -->
        <div class="col-sm-6 col-xxl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div class="me-4">
                        <div class="text-gray-600 fw-semibold mb-1">Total Dosen Pembimbing</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <div class="fs-2hx fw-bold">{{ $dosenTotal }}</div>
                        </div>
                        <div class="mt-3 text-gray-500 small">
                            Active: <span class="fw-semibold text-success">{{ $dosenActive ?? 0 }}</span> ·
                            Tidak Aktif: <span class="fw-semibold text-danger">{{ $dosenInactive ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="p-4 rounded-3 bg-light-info">
                        <!-- Bootstrap Icons: person-badge-fill -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" fill="currentColor"
                            class="text-info" viewBox="0 0 16 16">
                            <path
                                d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm4.5 0a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6m5 2.755C12.146 12.825 10.623 12 8 12s-4.146.826-5 1.755V14a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Total Pembina Magang -->
        <div class="col-sm-6 col-xxl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div class="me-4">
                        <div class="text-gray-600 fw-semibold mb-1">Total Pembina Magang</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <div class="fs-2hx fw-bold">{{ $pembinaTotal }}</div>
                        </div>
                        <div class="mt-3 text-gray-500 small">
                            Active: <span class="fw-semibold text-success">{{ $pembinaActive ?? 0 }}</span> ·
                            Tidak Aktif: <span class="fw-semibold text-danger">{{ $pembinaInactive ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="p-4 rounded-3 bg-light-warning">
                        <!-- Bootstrap Icons: briefcase-fill -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" fill="currentColor"
                            class="text-warning" viewBox="0 0 16 16">
                            <path
                                d="M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v1.384l7.614 2.03a1.5 1.5 0 0 0 .772 0L16 5.884V4.5A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1zm0 1h3a.5.5 0 0 1 .5.5V3H6v-.5a.5.5 0 0 1 .5-.5" />
                            <path
                                d="M0 12.5A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5V6.85L8.129 8.947a.5.5 0 0 1-.258 0L0 6.85z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Total Campus -->
        <div class="col-sm-6 col-xxl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div class="me-4">
                        <div class="text-gray-600 fw-semibold mb-1">Total Campus</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <div class="fs-2hx fw-bold">{{ $campusTotal }}</div>
                        </div>
                    </div>
                    <div class="p-4 rounded-3 bg-light-success">
                        <!-- Bootstrap Icons: building-fill -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" fill="currentColor"
                            class="text-success" viewBox="0 0 16 16">
                            <path
                                d="M3 0a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h3v-3.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5V16h3a1 1 0 0 0 1-1V1a1 1 0 0 0-1-1zm1 2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5M4 5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM7.5 5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5m2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM4.5 8h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5m2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
