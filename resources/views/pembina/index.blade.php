@extends('layout.master')

@section('title', 'Pembina - Dashboard')
@section('page_heading', 'Pembina')

@section('content')
    {{-- Ringkasan Statistik --}}
    <div class="row g-6 mb-6">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">Total Mahasiswa Binaan</div>
                        <div class="fs-2x fw-bolder">{{ $totalMahasiswaBinaan ?? 0 }}</div>
                    </div>
                    <div class="p-4 rounded-3 bg-light-primary">
                        <!-- Bootstrap Icons: people-fill -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" fill="currentColor"
                            class="text-primary" viewBox="0 0 16 16">
                            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1z" />
                            <path fill-rule="evenodd" d="M11 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6" />
                            <path
                                d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1z" />
                            <path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">Absensi Menunggu Konfirmasi</div>
                        <div class="fs-2x fw-bolder">{{ $pendingAbsensi ?? 0 }}</div>
                    </div>
                    <div class="p-4 rounded-3 bg-light-warning">
                        <!-- Bootstrap Icons: clipboard-check -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" fill="currentColor"
                            class="text-warning" viewBox="0 0 16 16">
                            <path
                                d="M10.854 6.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 8.793l2.646-2.647a.5.5 0 0 1 .708 0" />
                            <path
                                d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z" />
                            <path d="M9.5 1a.5.5 0 0 1 .5.5V3a.5.5 0 0 1-.5.5h-3A.5.5 0 0 1 6 3V1.5a.5.5 0 0 1 .5-.5z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">Logbook Menunggu Konfirmasi</div>
                        <div class="fs-2x fw-bolder">{{ $pendingLogbook ?? 0 }}</div>
                    </div>
                    <div class="p-4 rounded-3 bg-light-info">
                        <!-- Bootstrap Icons: journal-text -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" fill="currentColor"
                            class="text-info" viewBox="0 0 16 16">
                            <path
                                d="M10.854 7.854a.5.5 0 0 0-.708-.708L7.5 9.793 6.354 8.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Pembina --}}
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="card-title w-100 text-center">DATA PEMBINA</h3>
        </div>
        <div class="card-body bg-light">
            <div class="row g-6 align-items-start">
                <div class="col-md-8">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <tbody>
                                <tr>
                                    <td class="fw-semibold">Nama</td>
                                    <td>:</td>
                                    <td>{{ optional($user->profile)->full_name ?? $user->email }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Nomor WA</td>
                                    <td>:</td>
                                    <td>{{ optional($user->profile)->whatsapp ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Email</td>
                                    <td>:</td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-4 d-flex justify-content-md-end justify-content-center">
                    <div class="border rounded p-2 bg-white"
                        style="width: 240px; height: 260px; display:flex; align-items:center; justify-content:center;">
                        @php
                            $photo = optional($user->profile)->photo_url
                                ? asset(optional($user->profile)->photo_url)
                                : asset('assets/media/avatars/blank.png');
                        @endphp
                        <img src="{{ $photo }}" alt="Foto Pembina" class="img-fluid rounded"
                            style="max-height: 100%; object-fit: cover;">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
