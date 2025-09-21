# InternTrack

Sistem manajemen magang yang membantu institusi/komunitas memantau aktivitas peserta magang secara terpadu: akun multi-peran, presensi, logbook harian, lampiran berkas, serta alur persetujuan oleh dosen/pembina.

---

## Tentang Project

InternTrack dibangun di atas Laravel untuk menyediakan platform pencatatan dan monitoring kegiatan magang. Proyek ini berfokus pada:

- Manajemen pengguna dengan peran berbeda (company_admin, dosen, pembina, mahasiswa).
- Pencatatan presensi (check-in/checkout) dengan bukti foto dan tanda tangan.
- Logbook kegiatan dengan lampiran file serta proses persetujuan berlapis.
- Relasi ke entitas kampus untuk mengelola konteks institusi.

## Fitur Utama

- Autentikasi pengguna dan manajemen sesi.
- Role-based access control: company_admin, dosen, pembina, mahasiswa.
- Profil pengguna (biodata dasar, NIM/NIK, program studi, periode magang).
- Presensi harian: check-in, check-out, status hadir/izin/sakit/alpa, bukti foto & ttd, approval dosen & pembina.
- Logbook aktivitas: subjek, deskripsi, rentang tanggal, persetujuan dosen & pembina, catatan/revisi, lampiran berkas.
- Relasi ke Kampus (campuses) untuk pengelolaan dan pelaporan lintas institusi.

## Teknologi yang Digunakan

- Laravel (PHP) sebagai backend framework.
- Blade sebagai templating engine.
- Eloquent ORM untuk akses database.
- Vite untuk asset bundling (JS/CSS).
- Database: kompatibel MySQL/MariaDB atau PostgreSQL.

## Prasyarat (Prerequisite)

- PHP 8.1 atau lebih baru.
- Composer.
- Node.js 18+ dan npm.
- Server database (MySQL/MariaDB atau PostgreSQL).
- Git.

## Langkah Instalasi (Dari Clone Repo)

1. Clone repository
   ```bash
   git clone <repo-url>
   cd InternTrack
   ```
2. Salin file environment dan sesuaikan konfigurasi
   ```bash
   cp .env.example .env
   # Edit .env -> sesuaikan DB_* (DB_DATABASE, DB_USERNAME, DB_PASSWORD), APP_URL, dll.
   ```
3. Install dependensi PHP dan JS
   ```bash
   composer install
   npm install
   ```
4. Generate application key
   ```bash
   php artisan key:generate
   ```
5. Jalankan migrasi database
   ```bash
   php artisan migrate
   ```
6. (Opsional) Buat symbolic link storage
   ```bash
   php artisan storage:link
   ```
7. Build aset frontend (pilih salah satu)
   ```bash
   npm run dev    # untuk pengembangan
   npm run build  # untuk produksi
   ```
8. Jalankan server lokal
   ```bash
   php artisan serve
   ```

## Dokumentasi Struktur Database

Daftar tabel inti yang digunakan sistem:

- users
- profiles
- campuses
- attendances
- logbooks
- logbook_attachments

Gambar ERD tersedia pada file berikut:

- Pratinjau: `InternTrack.png`
- Link download: [./InternTrack.png](./InternTrack.png)

Anda juga dapat mengimpor skema DBML pada `database/schema.dbml` ke [dbdiagram.io](https://dbdiagram.io/) untuk melihat ERD interaktif.

## Kontribusi

Kontribusi sangat dihargai! Prosedur umum:

1. Fork repository ini.
2. Buat branch fitur/bugfix: `git checkout -b feat/nama-fitur` atau `fix/nama-bug`.
3. Lakukan perubahan sesuai standar kode (PSR-12) dan tambahkan test bila relevan.
4. Commit dengan pesan yang jelas: `git commit -m "feat: deskripsi singkat"`.
5. Push ke fork Anda dan buat Pull Request ke branch utama.

## Kontak

Silakan lengkapi detail kontak di bawah ini agar pengguna lain mudah terhubung:

- Nama: [Isikan Nama Anda]
- Email: [email@contoh.com]
- Website/Portfolio: [https://contoh.com]
- LinkedIn/GitHub: [tautan-profil]

---

<!-- Template README Laravel di bawah ini dibiarkan untuk referensi; beri tahu saya jika ingin dihapus. -->
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
