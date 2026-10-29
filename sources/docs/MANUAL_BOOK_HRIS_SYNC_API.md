# Manual Book: TSU HRIS Employee Synchronization API Provider
## Panduan Teknis & Pengelolaan API Master Karyawan untuk Backend HRIS

Dokumen ini ditujukan bagi Administrator Sistem dan Backend Developer **TSU HRIS** (`tsu_hris`) untuk mengelola, mengamankan, dan memelihara endpoint API sinkronisasi data kepegawaian (Dosen dan Tenaga Kependidikan) yang dikonsumsi oleh seluruh sistem turunan di ekosistem TSU.

---

## 1. Peran HRIS dalam Ekosistem TSU

Sebagai **Single Source of Truth (SSOT)** kepegawaian:
- HRIS adalah **satu-satunya** tempat di mana data profil pegawai diinput, diperbarui (mutasi, kenaikan pangkat, perubahan gelar, perubahan unit), dan dinonaktifkan.
- Sistem lain (seperti SIMAK, PMB, TSU Template, LPPM) dilarang mengubah profil pegawai secara independen; mereka hanya mengonsumsi data dari HRIS.

---

## 2. Konfigurasi Server HRIS (`.env`)

Pada file `.env` di server TSU HRIS, kunci rahasia disimpan dalam bentuk **plaintext**:

```dotenv
# Secret sinkronisasi internal TSU HRIS
HRIS_SYNC_SECRET="kunci_rahasia_master_hris"

# Atau menggunakan PIKDI_SYNC_SECRET (fallback)
PIKDI_SYNC_SECRET="kunci_rahasia_master_hris"
```

> [!NOTE]
> Server HRIS menyimpan secret asli (plaintext) karena HRIS yang bertindak sebagai otoritas verifikasi. HRIS akan mencocokkan hash yang dikirim oleh sistem klien terhadap hash dari secret ini.

---

## 3. Tool Generator Hash untuk Proyek Downstream

Saat ada proyek baru (misal SIMAK atau PMB) yang membutuhkan akses ke data karyawan HRIS, developer HRIS **TIDAK PERLU** membagikan secret plaintext.

Jalankan perintah Artisan berikut di direktori `sources` HRIS:

```bash
php artisan hris:hash-secret
```

Perintah ini akan membaca `HRIS_SYNC_SECRET` dari `.env` HRIS dan menghasilkan output siap pakai:

```text
===============================================================
           TSU HRIS SYNC SECRET HASH GENERATOR                 
===============================================================
Plain Secret (HRIS)       : kunci_rahasia_master_hris
SHA-256 Hash (Proyek Lain): 4a5b6c7d8e9f... [64 karakter hex]
---------------------------------------------------------------
Salin baris berikut ke file .env proyek downstream:
HRIS_SYNC_SECRET=4a5b6c7d8e9f...
===============================================================
```

Berikan baris `HRIS_SYNC_SECRET` tersebut kepada tim developer modul turunan.

---

## 4. Spesifikasi Endpoint API

### `GET /api/v1/karyawan/sync-list`

Endpoint untuk mengambil seluruh daftar profil dosen dan tenaga kependidikan aktif maupun non-aktif.

#### Request Headers:
| Header | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `Accept` | string | Ya | `application/json` |
| `X-Sync-Secret` | string | Ya | Hash SHA-256 (atau hash Bcrypt) dari secret HRIS |

#### Response Sukses (`200 OK`):
```json
{
  "status": "success",
  "total": 128,
  "data": [
    {
      "nik": "3515081234560001",
      "nidn": "0712345601",
      "nip": null,
      "nama": "Ahmad Fauzi",
      "gelar_depan": "Dr. ",
      "gelar_belakang": "M.Kom.",
      "nama_lengkap": "Dr. Ahmad Fauzi, M.Kom.",
      "jenis_kelamin": "L",
      "no_hp": "081234567890",
      "unit_kerja": "Fakultas Sains dan Teknologi",
      "jabatan_aktif": "Ketua Program Studi Informatika",
      "jabatan_struktural": "Ketua Program Studi Informatika",
      "jabatan_fungsional": "Lektor",
      "status_karyawan": "Tetap",
      "is_active": 1,
      "email": "ahmad.fauzi@tsu.ac.id"
    }
  ]
}
```

#### Response Ditolak (`401 Unauthorized`):
```json
{
  "status": "error",
  "message": "Unauthorized: Invalid or missing X-Sync-Secret header."
}
```

---

## 5. Mekanisme Keamanan Multi-Tier

Controller `App\Http\Controllers\Api\KaryawanSyncApiController` menggunakan tiga lapis verifikasi berurutan:

```php
// 1. SHA-256 Timing-Safe Comparison (Standar Utama)
$expectedSha256 = hash('sha256', $plainSecret);
if (hash_equals($expectedSha256, $incomingSecret)) {
    $isAuthorized = true;
}

// 2. Bcrypt Verification (Fallback jika klien menggunakan Laravel Hash)
if (!$isAuthorized && str_starts_with($incomingSecret, '$2y$')) {
    if (password_verify($plainSecret, $incomingSecret)) {
        $isAuthorized = true;
    }
}

// 3. Plaintext Match (Fallback pengujian internal)
if (!$isAuthorized && hash_equals($plainSecret, $incomingSecret)) {
    $isAuthorized = true;
}
```

Setiap kegagalan otentikasi dicatat secara otomatis ke log sistem (`storage/logs/laravel.log`) lengkap dengan IP pemanggil dan User-Agent untuk kebutuhan audit keamanan:
```text
[WARNING] [TSU_HRIS_API_UNAUTHORIZED] Header X-Sync-Secret tidak cocok dengan secret HRIS. {"ip":"192.168.1.50"}
```

---

## 6. Struktur File Komponen di HRIS

| Lokasi File | Peran |
|---|---|
| `sources/routes/api.php` | Pendaftaran prefix route `v1/karyawan/sync-list` |
| `sources/app/Http/Controllers/Api/KaryawanSyncApiController.php` | Controller API otentikasi & format data profil |
| `sources/app/Console/Commands/GenerateSyncSecretHash.php` | Command artisan `hris:hash-secret` |
| `sources/app/Models/DataDosenTendik.php` | Model Eloquent master karyawan dengan relasi `unit` & `user` |
