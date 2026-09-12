<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratMasuk extends Model
{
    use HasFactory;

    protected $table = 'surat_masuks';

    protected $fillable = [
        'no_agenda',
        'no_surat_asal',
        'pengirim_instansi',
        'tgl_surat',
        'tgl_diterima',
        'perihal',
        'ringkasan_isi',
        'sifat_surat',
        'file_surat',
        'file_size',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tgl_surat' => 'date',
        'tgl_diterima' => 'date',
    ];

    protected $appends = [
        'status_badge',
        'sifat_badge',
        'file_url',
    ];

    public function disposisis()
    {
        return $this->hasMany(DisposisiSuratMasuk::class, 'surat_masuk_id', 'id')->orderBy('id', 'desc');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_surat || !$this->id) return null;
        if (str_starts_with($this->file_surat, 'http://') || str_starts_with($this->file_surat, 'https://')) {
            return $this->file_surat;
        }
        return route('admin.surat-masuk.stream-file', $this->id);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'terdaftar'   => '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-inbox mr-1"></i> Terdaftar</span>',
            'didisposisi' => '<span class="badge badge-info px-2 py-1"><i class="fas fa-paper-plane mr-1"></i> Didisposisikan</span>',
            'proses_unit' => '<span class="badge badge-warning px-2 py-1"><i class="fas fa-cogs mr-1"></i> Diproses Unit</span>',
            'selesai'     => '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Selesai</span>',
            default       => '<span class="badge badge-light px-2 py-1">' . htmlspecialchars(ucfirst($this->status)) . '</span>',
        };
    }

    public function getSifatBadgeAttribute(): string
    {
        return match($this->sifat_surat) {
            'biasa'   => '<span class="badge badge-secondary px-2 py-1">Biasa</span>',
            'penting' => '<span class="badge badge-primary px-2 py-1">Penting</span>',
            'segera'  => '<span class="badge badge-warning px-2 py-1">Segera</span>',
            'rahasia' => '<span class="badge badge-danger px-2 py-1">Rahasia</span>',
            default   => '<span class="badge badge-light px-2 py-1">' . htmlspecialchars(ucfirst($this->sifat_surat)) . '</span>',
        };
    }

    /**
     * Generate agenda number: AGD-YYYYMM-XXXX
     */
    public static function generateNoAgenda(): string
    {
        $prefix = 'AGD-' . date('Ym') . '-';
        $last = self::where('no_agenda', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('no_agenda');

        if ($last) {
            $seq = intval(substr($last, -4)) + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
