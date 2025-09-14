@extends('layout.master')

@section('title', 'Mahasiswa - Dashboard')
@section('page_heading', 'Mahasiswa')

@section('extra_css')
    <style>
        .stat-card {
            border: 1px solid rgba(0, 0, 0, .08);
        }

        .stat-title {
            font-size: .95rem;
            color: #6c757d;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
        }

        .absen-badge {
            font-size: .9rem;
        }

        .label-col {
            width: 260px;
            color: #6c757d;
        }

        .photo-box {
            width: 180px;
            height: 220px;
            border: 2px dashed #ced4da;
            border-radius: .5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }

        /* Modal elements */
        .sig-pad {
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            background: #fff;
            width: 100%;
            height: 200px;
            display: block;
            box-sizing: border-box;
        }

        .video-box {
            width: 100%;
            max-width: 320px;
            aspect-ratio: 4/3;
            background: #f1f3f5;
            border: 1px dashed #ced4da;
            border-radius: .5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .snapshot {
            width: 100%;
            max-width: 320px;
            aspect-ratio: 4/3;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            object-fit: cover;
            display: none;
        }

        @media (max-width: 992px) {
            .label-col {
                width: 180px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row g-6">
        <!-- Summary Stats -->
        <div class="col-12">
            <div class="row g-6">
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="stat-title">Total Logbook</div>
                                <div class="stat-value">{{ $totalLogbook ?? 0 }}</div>
                            </div>
                            <div class="symbol symbol-45px">
                                <i class="bi bi-person-lines-fill fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-title">Total Hadir</div>
                            <div class="stat-value">{{ $totalHadir ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-title">Total Sakit</div>
                            <div class="stat-value">{{ $totalSakit ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-title">Total Izin</div>
                            <div class="stat-value">{{ $totalIzin ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Absensi Hari Ini -->
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <h3 class="card-title m-0">STATUS ABSENSI HARI INI</h3>
                    <div class="text-end">
                        <div class="fw-semibold text-uppercase" id="tanggal_hari_ini">-</div>
                        <div class="fs-2 fw-bold" id="waktu_sekarang">-</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-5 align-items-center">
                        <div class="col-lg-8">
                            @php
                                $checkinAt = optional($attendanceToday)->checkin_at;
                                $checkoutAt = optional($attendanceToday)->checkout_at;
                                $hasCheckedIn = !empty($checkinAt);
                                $hasCheckedOut = !empty($checkoutAt);
                                $fmtIn = $hasCheckedIn
                                    ? \Carbon\Carbon::parse($checkinAt)
                                        ->timezone(config('app.timezone'))
                                        ->format('H:i:s')
                                    : '-';
                                $fmtOut = $hasCheckedOut
                                    ? \Carbon\Carbon::parse($checkoutAt)
                                        ->timezone(config('app.timezone'))
                                        ->format('H:i:s')
                                    : '-';
                            @endphp

                            <div class="d-flex align-items-center mb-4">
                                <div class="label-col">ABSEN MASUK</div>
                                <div class="me-3">
                                    {{ $fmtIn }}
                                    @if ($hasCheckedIn)
                                        <span class="badge bg-success ms-1 absen-badge">Sudah Check In</span>
                                    @else
                                        <span class="badge bg-secondary ms-1 absen-badge">Belum Check In</span>
                                    @endif
                                </div>
                                <button class="btn btn-primary btn-sm" id="btnOpenCheckin"
                                    {{ $hasCheckedIn ? 'disabled' : '' }}>
                                    Absen Masuk
                                </button>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="label-col">ABSEN PULANG</div>
                                <div class="me-3">
                                    {{ $fmtOut }}
                                    @if (!$hasCheckedOut)
                                        <span class="badge bg-secondary ms-1 absen-badge">Belum Check Out</span>
                                    @else
                                        <span class="badge bg-success ms-1 absen-badge">Sudah Check Out</span>
                                    @endif
                                </div>
                                <button class="btn btn-primary btn-sm" id="btnOpenCheckout"
                                    {{ $hasCheckedIn && !$hasCheckedOut ? '' : 'disabled' }}>
                                    Absen Pulang
                                </button>
                            </div>
                        </div> <!-- /.col -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Mahasiswa Magang -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title m-0">DATA MAHASISWA MAGANG</h3>
                </div>
                <div class="card-body">
                    <div class="row g-6">
                        <div class="col-lg-9">
                            @php
                                $p = optional($user)->profile;
                                $dosen = optional($user->dosen)->profile->full_name ?? '-';
                                $pembina = optional($user->pembina)->profile->full_name ?? '-';
                                $campus = optional($user->campus)->nama_campus ?? '-';
                            @endphp
                            <div class="d-flex mb-3">
                                <div class="label-col">NIM</div>
                                <div>: {{ $p->nim ?? '-' }}</div>
                            </div>
                            <div class="d-flex mb-3">
                                <div class="label-col">Nama Mahasiswa</div>
                                <div>: {{ $p->full_name ?? $user->email }}</div>
                            </div>
                            <div class="d-flex mb-3">
                                <div class="label-col">Penempatan Magang</div>
                                <div>: {{ $campus }}</div>
                            </div>
                            <div class="d-flex mb-3">
                                <div class="label-col">Dosen Pembimbing Magang</div>
                                <div>: {{ $dosen }}</div>
                            </div>
                            <div class="d-flex mb-3">
                                <div class="label-col">Pembina Magang</div>
                                <div>: {{ $pembina }}</div>
                            </div>
                            <div class="d-flex mb-3">
                                <div class="label-col">Hari & Tanggal Masuk Magang</div>
                                <div>:
                                    @if(!empty($p->start_magang))
                                        {{ \Carbon\Carbon::parse($p->start_magang)->locale('id')->translatedFormat('l, d F Y') }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex mb-3">
                                <div class="label-col">Hari & Tanggal Selesai Magang</div>
                                <div>:
                                    @if(!empty($p->end_magang))
                                        {{ \Carbon\Carbon::parse($p->end_magang)->locale('id')->translatedFormat('l, d F Y') }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="label-col">Nomor WA</div>
                                <div>: {{ $p->whatsapp ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-lg-3 d-flex justify-content-lg-end">
                            @php $photo = $p->photo_url ?? asset('assets/media/avatars/blank.png'); @endphp
                            <div class="photo-box p-0 border-0" style="border:none">
                                <img src="{{ $photo }}" alt="Foto"
                                    style="width:180px; height:220px; object-fit:cover; border-radius:.5rem; border:1px solid #dee2e6;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- /.row -->
@endsection

@section('extra_js')
    <script>
        // ---- Fallbacks to avoid ReferenceError if not provided from layout ----
        window.CSRF_TOKEN = window.CSRF_TOKEN || '{{ csrf_token() }}';
        window.ROUTES = window.ROUTES || {
            checkin: '{{ route('mahasiswa.attendance.checkin') }}',
            checkout: '{{ route('mahasiswa.attendance.checkout') }}'
        };

        // ---- Header clock (tanggal_hari_ini & waktu_sekarang) ----
        (function initHeaderClock() {
            const tanggalEl = document.getElementById('tanggal_hari_ini');
            const waktuEl = document.getElementById('waktu_sekarang');
            if (!tanggalEl || !waktuEl) return;

            const hari = ['MINGGU', 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'];
            const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
                'Oktober', 'November', 'Desember'
            ];
            const pad = n => n < 10 ? '0' + n : n;

            function tick() {
                const now = new Date();
                tanggalEl.textContent =
                    `${hari[now.getDay()]}, ${pad(now.getDate())} ${bulan[now.getMonth()]} ${now.getFullYear()}`;
                waktuEl.textContent = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
            }
            tick();
            setInterval(tick, 1000);
        })();

        // ---- Utils ----
        function dataURLtoBlob(dataurl) {
            const arr = dataurl.split(','),
                mime = arr[0].match(/:(.*?);/)[1];
            const bstr = atob(arr[1]);
            let n = bstr.length;
            const u8arr = new Uint8Array(n);
            while (n--) {
                u8arr[n] = bstr.charCodeAt(n);
            }
            return new Blob([u8arr], {
                type: mime
            });
        }

        // ---- Build modal dynamically (shared for checkin/checkout) ----
        function buildModal(id, title, withStatus) {
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.id = id;
            modal.tabIndex = -1;
            modal.innerHTML = `
<div class="modal-dialog modal-lg modal-dialog-centered">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">${title}</h5>
      <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal">
        <i class="ki-duotone ki-cross fs-2x"></i>
      </button>
    </div>
    <form class="p-0 m-0" id="${id}-form">
      <div class="modal-body">
        <div class="row g-6 align-items-start">
          <div class="col-lg-6">
            <div class="video-box mb-3">
              <video id="${id}-video" autoplay playsinline style="width:100%; height:100%;"></video>
            </div>
            <img id="${id}-snapshot" class="snapshot mb-2" />
            <div class="d-flex gap-2">
              <button class="btn btn-light" type="button" id="${id}-btnCapture">Ambil Foto</button>
              <button class="btn btn-secondary" type="button" id="${id}-btnRetake" style="display:none;">Ulangi</button>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="fw-semibold text-uppercase" id="${id}-tanggal">-</div>
                    <div class="fs-5 fw-bold" id="${id}-waktu">-</div>
                </div>
            </div>
            ${withStatus ? `
                <div class="mb-3">
                  <label class="form-label">Status Kehadiran</label>
                  <select class="form-select" id="${id}-status" required>
                    <option value="">-- Pilih Status Kehadiran --</option>
                    <option value="hadir">Hadir</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                  </select>
                  <div class="invalid-feedback" data-field="status"></div>
                </div>` : '' }
            <div class="mb-2">
              <label class="form-label">Tanda Tangan Mahasiswa</label>
              <canvas id="${id}-sig" class="sig-pad" width="500" height="200"></canvas>
              <div class="d-flex gap-2 mt-2">
                <button type="button" class="btn btn-light" id="${id}-sigClear">Bersihkan</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>`;
            document.body.appendChild(modal);
            return modal;
        }

        const modalCheckin = buildModal('modalCheckin', 'Absen Masuk', true);
        const modalCheckout = buildModal('modalCheckout', 'Absen Pulang', false);

        function initModal(id, postUrl, withStatus) {
            const el = document.getElementById(id);
            const form = el.querySelector('form');
            const video = el.querySelector(`#${id}-video`);
            const snapshot = el.querySelector(`#${id}-snapshot`);
            const btnCap = el.querySelector(`#${id}-btnCapture`);
            const btnRetake = el.querySelector(`#${id}-btnRetake`);
            const sig = el.querySelector(`#${id}-sig`);
            const sigClear = el.querySelector(`#${id}-sigClear`);
            const tanggal = el.querySelector(`#${id}-tanggal`);
            const waktu = el.querySelector(`#${id}-waktu`);
            const statusSel = withStatus ? el.querySelector(`#${id}-status`) : null;
            const videoBox = video.parentElement;

            let stream, photoDataUrl = null,
                drawing = false,
                ctx, timer;

            function tickTime() {
                const now = new Date();
                const hari = ['MINGGU', 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'];
                const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
                    'Oktober', 'November', 'Desember'
                ];
                const pad = n => n < 10 ? '0' + n : n;
                tanggal.textContent =
                    `${hari[now.getDay()]}, ${pad(now.getDate())} ${bulan[now.getMonth()]} ${now.getFullYear()}`;
                waktu.textContent = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
            }

            el.addEventListener('shown.bs.modal', async () => {
                timer = setInterval(tickTime, 1000);
                tickTime();
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: false
                    });
                    video.srcObject = stream;
                    video.play();
                } catch (e) {
                    console.error(e);
                    window.toastr?.error?.('Tidak bisa mengakses kamera');
                }
                // init signature
                ctx = sig.getContext('2d');
                ctx.strokeStyle = '#000';
                ctx.lineWidth = 2;
                // make canvas responsive to its container
                const resizeCanvas = () => {
                    const rect = sig.getBoundingClientRect();
                    sig.width = Math.max(300, Math.floor(rect.width));
                    sig.height = 200;
                };
                resizeCanvas();
                window.addEventListener('resize', resizeCanvas);
                const start = e => {
                    drawing = true;
                    ctx.beginPath();
                    ctx.moveTo(e.offsetX, e.offsetY);
                };
                const move = e => {
                    if (!drawing) return;
                    ctx.lineTo(e.offsetX, e.offsetY);
                    ctx.stroke();
                };
                const end = () => {
                    drawing = false;
                };
                sig.onmousedown = start;
                sig.onmousemove = move;
                sig.onmouseup = end;
                sig.onmouseleave = end;
                sigClear.onclick = () => ctx.clearRect(0, 0, sig.width, sig.height);

                // reset state
                snapshot.style.display = 'none';
                snapshot.src = '';
                photoDataUrl = null;
                btnRetake.style.display = 'none';
                // ensure camera is visible when opening
                videoBox.style.display = 'block';
                if (statusSel) {
                    statusSel.classList.remove('is-invalid');
                    statusSel.value = '';
                }
            });

            el.addEventListener('hidden.bs.modal', () => {
                clearInterval(timer);
                if (stream) stream.getTracks().forEach(t => t.stop());
                stream = null;
                video.pause();
                video.srcObject = null;
                window.removeEventListener('resize', () => {}); // safeguard; actual listener removed by page lifecycle
            });

            btnCap.onclick = () => {
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const cctx = canvas.getContext('2d');
                cctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                photoDataUrl = canvas.toDataURL('image/jpeg');
                snapshot.src = photoDataUrl;
                snapshot.style.display = 'block';
                btnRetake.style.display = 'inline-block';
                // hide and stop camera after capture
                videoBox.style.display = 'none';
                if (stream) {
                    stream.getTracks().forEach(t => t.stop());
                    stream = null;
                }
                video.pause();
                video.srcObject = null;
            };
            btnRetake.onclick = () => {
                snapshot.style.display = 'none';
                snapshot.src = '';
                photoDataUrl = null;
                btnRetake.style.display = 'none';
                // reopen camera on retake
                (async () => {
                    try {
                        stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                        video.srcObject = stream;
                        await video.play();
                        videoBox.style.display = 'block';
                    } catch (e) {
                        console.error(e);
                        window.toastr?.error?.('Tidak bisa mengakses kamera');
                    }
                })();
            };

            form.onsubmit = async (e) => {
                e.preventDefault();
                const fd = new FormData();
                if (withStatus) {
                    if (!statusSel.value) {
                        statusSel.classList.add('is-invalid');
                        return;
                    }
                    statusSel.classList.remove('is-invalid');
                    fd.append('status', statusSel.value);
                }
                // Photo is required by backend; validate before submit
                if (!photoDataUrl) {
                    window.toastr?.error?.('Silakan ambil foto terlebih dahulu.');
                    return;
                }
                fd.append('photo', dataURLtoBlob(photoDataUrl), 'photo.jpg');

                // Validate signature is not empty
                const isCanvasBlank = (canvas) => {
                    const blank = document.createElement('canvas');
                    blank.width = canvas.width;
                    blank.height = canvas.height;
                    return canvas.toDataURL() === blank.toDataURL();
                };
                if (isCanvasBlank(sig)) {
                    window.toastr?.error?.('Silakan isi tanda tangan terlebih dahulu.');
                    return;
                }
                fd.append('signature_base64', sig.toDataURL('image/png'));

                try {
                    const resp = await fetch(postUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': window.CSRF_TOKEN,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: fd
                    });
                    const ct = resp.headers.get('content-type') || '';
                    if (resp.ok) {
                        const data = ct.includes('application/json') ? await resp.json().catch(() => ({})) : {};
                        window.toastr?.success?.(data.message || 'Berhasil');
                        window.location.reload();
                        return;
                    }
                    // Handle errors
                    if (ct.includes('application/json')) {
                        const errData = await resp.json().catch(() => ({}));
                        const messages = [];
                        if (errData.message) messages.push(errData.message);
                        if (errData.errors) {
                            for (const k in errData.errors) {
                                messages.push(...(errData.errors[k] || []));
                            }
                        }
                        window.toastr?.error?.(messages.join('\n') || 'Terjadi kesalahan');
                    } else {
                        const text = await resp.text().catch(() => 'Terjadi kesalahan');
                        window.toastr?.error?.(text || 'Terjadi kesalahan');
                    }
                } catch (err) {
                    const msg = err?.message || (typeof err === 'string' ? err : null) || 'Gagal menyimpan';
                    window.toastr?.error?.(msg);
                }
            };
        }

        // init modals
        initModal('modalCheckin', window.ROUTES.checkin, true);
        initModal('modalCheckout', window.ROUTES.checkout, false);

        // open buttons
        const checkinBtn = document.getElementById('btnOpenCheckin');
        const checkoutBtn = document.getElementById('btnOpenCheckout');
        checkinBtn && checkinBtn.addEventListener('click', () => new bootstrap.Modal(document.getElementById(
            'modalCheckin')).show());
        checkoutBtn && checkoutBtn.addEventListener('click', () => new bootstrap.Modal(document.getElementById(
            'modalCheckout')).show());
    </script>
@endsection
