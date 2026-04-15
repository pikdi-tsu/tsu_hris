# <p align="center"> TSU HRIS <br> (Human Resource Information System) </p>

## 📢 Deskripsi Sistem

TSU HRIS adalah sistem informasi pengelolaan sumber daya manusia strategis yang dikembangkan khusus untuk Universitas Tiga Serangkai (TSU). Sistem ini mengadaptasi pendekatan arsitektur *Modular Monolith*, yang memisahkan logika bisnis berdasarkan domain spesifik (Modul) guna memastikan skalabilitas, kemudahan pemeliharaan, serta kinerja yang optimal dalam ekosistem digital institusi.

## 📋 Tinjauan Proyek (Project Overview)

Dikembangkan di atas fondasi **TSU Project Template**, aplikasi ini mempertahankan struktur direktori kustom `sources/` untuk mendukung integrasi jangka panjang. Saat ini, sistem beroperasi menggunakan koneksi basis data langsung dengan autentikasi lokal berbasis sesi yang terintegrasi dengan ekosistem SSO TSU. Arsitektur *Service Layer* telah dirancang sedemikian rupa untuk mengakomodasi transisi menuju implementasi berbasis API (*API-Driven*) di masa mendatang.

## 🏗️ Struktur Direktori & Arsitektur Domain

Keunggulan arsitektural pada sistem ini terletak pada pemisahan *core logic* ke dalam direktori `sources/` sebagai *root namespace* utama, dengan pembagian modul sebagai berikut:

```text
root/
├── public/assets/      # Aset statis (AdminLTE, Plugins, Custom UI)
├── sources/            # Direktori Utama Logika Aplikasi
│   ├── app/            # Logika Global (Shared Controllers, Models, Helpers)
│   └── Modules/        # Domain-Driven Modules
│       ├── Admin/      # Operasional Administrator & Master Data
│       ├── System/     # Core Engine (Auth, Spatie ACL, Dynamic Menus)
│       ├── Users/      # Manajemen Profil (Dosen, Tendik, Mahasiswa)
│       ├── Calendar/   # [Q1] Manajemen Kalender Kerja & Libur Nasional
│       ├── Employee/   # [Q1] Manajemen Data Master Pegawai
│       ├── Time/       # [Q2] Manajemen Absensi, Cuti, Izin, & Lembur
│       └── Finance/    # [Q2] Manajemen Payroll & Slip Gaji
```

*Catatan: Implementasi ini menggunakan pola `nwidart/laravel-modules` untuk memastikan isolasi fungsionalitas antar domain bisnis.*

## 🛠️ Spesifikasi Teknis (Tech Stack)

- **Framework Core**: Laravel
- **Architecture Pattern**: Modular Monolith
- **Database Interface**: Eloquent ORM & `yajra/laravel-datatables-oracle`
- **Authentication & Authorization**:
   - Custom Local Authentication terintegrasi dengan TSU SSO.
   - `spatie/laravel-permission` untuk Manajemen Hak Akses dinamis.
- **Frontend Stack**: Blade Templating, Bootstrap 4, AdminLTE, Select2, SweetAlert2, Chart.js.

---

## ⚙️ Panduan Instalasi & Setup Standar

Ikuti instruksi berikut untuk mengonfigurasi lingkungan pengembangan lokal (*local environment*):

1. **Kloning & Instalasi Dependensi**
   ```bash
   git clone https://github.com/pikdi-tsu/tsu_hris.git
   cd tsu_hris
   composer install atau composer update
   ```

2. **Konfigurasi Environment (Krusial)**
   Salin file `.env.example` menjadi `.env`. Pastikan Anda menyesuaikan variabel berikut agar fitur SSO dan identitas sistem tidak berbenturan:

   **🔹 Core & Database**
   - `APP_NAME`: TSU HRIS
   - `APP_URL`: URL lokal Anda (Contoh: `http://tsu_hris.test`)
   - `SESSION_COOKIE`: `hris_session` atau `session` (Penting untuk menghindari konflik sesi lokal)
   - `DB_*`: Kredensial basis data lokal.

   **🔹 Integrasi SSO & API (Homebase TSU)**
   - `TSU_SSO_CLIENT_ID` & `SECRET`: Kredensial SSO dari TSU Homebase.
   - `TSU_SSO_REDIRECT_URI`: Endpoint callback (Contoh: `http://tsu_hris.test/login/sso/callback`).

   **🔹 Keamanan & Hak Akses**
   - `PIKDI_EMERGENCY_SECRET` & `RESCUE_SECRET`: Kunci otorisasi darurat internal PIKDI.

3. **Generate Key & Sinkronisasi**
   ```bash
   php artisan key:generate
   php artisan config:clear
   ```

4. **Inisiasi Basis Data & Modul**
   Aktifkan seluruh modul esensial sebelum menjalankan migrasi untuk memastikan relasi tabel terjalin dengan sempurna.
   ```bash
   php artisan module:enable System Admin Users (Optional)
   php artisan migrate --seed
   ```

5. **Akses Aplikasi**
   ```bash
   php artisan serve
   ```
   *(Atau akses melalui domain lokal Laragon Anda, misal: `tsu_hris.test`)*

---

<div align="center">
  <strong>Pusat Informasi, Komunikasi dan Digital (PIKDI)</strong><br>
  Tiga Serangkai University &copy; 2026
</div>