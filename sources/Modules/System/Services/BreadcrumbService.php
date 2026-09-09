<?php

namespace Modules\System\Services;

use Illuminate\Support\Str;

/**
 * BreadcrumbService
 *
 * Generates dynamic breadcrumb items based on the current Laravel route name.
 * Route name convention expected: {module}.{section}.{action}
 * e.g., admin.data-karyawan.index → [Dashboard, Data Karyawan]
 *       users.cuti.index          → [Self Service, Cuti Karyawan]
 */
class BreadcrumbService
{
    /**
     * Human-readable label map for route segments.
     * Key: route segment string | Value: display label
     */
    protected static array $labelMap = [
        // Modules / Roots
        'admin'                  => 'Dashboard',
        'users'                  => 'Self Service',
        'system'                 => 'System',

        // Admin module sections
        'dashboard'              => 'Dashboard',
        'data-karyawan'          => 'Data Karyawan',
        'absensi'                => 'Absensi',
        'riwayatabsensi'         => 'Riwayat Absensi',
        'rekap-absensi'          => 'Rekap Absensi',
        'saldo-cuti'             => 'Saldo Cuti',
        'riwayat-izincuti'       => 'Riwayat Izin & Cuti',
        'riwayat-jabatan'        => 'Riwayat Jabatan',
        'riwayat-lembur'         => 'Riwayat Lembur',
        'struktur-organisasi'    => 'Struktur Organisasi',
        'jadwal-piket'           => 'Jadwal Piket',
        'payroll'                => 'Payroll',
        'honorarium'             => 'Honorarium',
        'mpp'                    => 'Manpower Planning',
        'master-data'            => 'Master Data',
        'master-jabatan'         => 'Master Jabatan',
        'master-unit'            => 'Master Unit',
        'master-shift'           => 'Master Shift',
        'master-cuti'            => 'Master Cuti',
        'master-izin'            => 'Master Izin',
        'master-status-karyawan' => 'Status Karyawan',
        'master-tunjangan'       => 'Master Tunjangan',
        'master-gaji-pokok'      => 'Gaji Pokok',
        'master-lembur'          => 'Master Lembur',
        'master-tarif-honorarium'=> 'Tarif Honorarium',
        'master-komponen-presensi'=> 'Komponen Presensi',
        'master-hari-libur'      => 'Hari Libur',

        // Users module sections
        'cuti'                   => 'Cuti Karyawan',
        'izin'                   => 'Izin Karyawan',
        'lembur'                 => 'Lembur',
        'approvalcuti'           => 'Approval Cuti',
        'approvalizin'           => 'Approval Izin',
        'profile'                => 'Profil',
        'selfservice'            => 'Self Service',
        'notifications'          => 'Notifikasi',
        'user'                   => 'Manajemen User',

        // Actions (usually hidden from breadcrumb)
        'index'   => null,
        'create'  => 'Tambah Baru',
        'edit'    => 'Edit',
        'show'    => 'Detail',
        'store'   => null,
        'update'  => null,
        'destroy' => null,
        'json'    => null,
    ];

    /**
     * Route URL map: route_name => URL builder closure or null (use route())
     */
    protected static array $routeMap = [
        'admin'  => 'admin.dashboard',
        'users'  => 'users.dashboard',
        'system' => 'admin.dashboard',
    ];

    /**
     * Generate breadcrumb array from the current route name.
     *
     * @return array  Array of ['label' => string, 'url' => string|null]
     */
    public static function generate(): array
    {
        $routeName = request()->route()?->getName();

        if (! $routeName) {
            return [];
        }

        $segments = explode('.', $routeName);
        $breadcrumbs = [];
        $accumulated = [];

        foreach ($segments as $index => $segment) {
            $accumulated[] = $segment;

            // Gunakan array_key_exists agar nilai null di labelMap benar-benar terdeteksi
            // (operator ?? menganggap null = tidak ada, sehingga humanize() ikut terpanggil)
            if (array_key_exists($segment, static::$labelMap)) {
                $label = static::$labelMap[$segment];
            } else {
                $label = static::humanize($segment);
            }

            // Skip null labels (index, store, update, destroy, json, dll.)
            if ($label === null) {
                continue;
            }

            $url = null;

            // Build URL for root modules
            if ($index === 0 && isset(static::$routeMap[$segment])) {
                try {
                    $url = route(static::$routeMap[$segment]);
                } catch (\Exception $e) {
                    $url = null;
                }
            }
            // Build URL for intermediate segments (try .index route)
            elseif ($index < count($segments) - 1) {
                $candidateRoute = implode('.', $accumulated) . '.index';
                try {
                    $url = route($candidateRoute);
                } catch (\Exception $e) {
                    $url = null;
                }
            }

            $breadcrumbs[] = [
                'label' => $label,
                'url'   => $url,
            ];
        }

        return $breadcrumbs;
    }

    /**
     * Convert a kebab-case or snake_case segment into a Title Case human label.
     */
    protected static function humanize(string $segment): string
    {
        return Str::title(str_replace(['-', '_'], ' ', $segment));
    }
}
