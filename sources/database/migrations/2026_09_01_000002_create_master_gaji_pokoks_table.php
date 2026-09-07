<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_gaji_pokoks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('golongan', 20)->unique();
            $table->decimal('gaji_pokok_100', 15, 2)->default(0);
            $table->decimal('gaji_pokok_80', 15, 2)->default(0);
            $table->decimal('tahun_2', 15, 2)->default(0);
            $table->decimal('tahun_4', 15, 2)->default(0);
            $table->decimal('tahun_6', 15, 2)->default(0);
            $table->decimal('tahun_8', 15, 2)->default(0);
            $table->decimal('tahun_10', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Seed data dasar sesuai Dokumen Payroll TSU (Slide 3)
        $data = [
            ['golongan' => 'I/a',   'gaji_pokok_100' => 1685800, 'gaji_pokok_80' => 1348640, 'tahun_2' => 1483504, 'tahun_4' => 1631854, 'tahun_6' => 1795040, 'tahun_8' => 1974544, 'tahun_10' => 2171998],
            ['golongan' => 'I/b',   'gaji_pokok_100' => 1740800, 'gaji_pokok_80' => 1392640, 'tahun_2' => 1531904, 'tahun_4' => 1685094, 'tahun_6' => 1853604, 'tahun_8' => 2038964, 'tahun_10' => 2242861],
            ['golongan' => 'I/c',   'gaji_pokok_100' => 1797000, 'gaji_pokok_80' => 1437600, 'tahun_2' => 1581360, 'tahun_4' => 1739496, 'tahun_6' => 1913446, 'tahun_8' => 2104790, 'tahun_10' => 2315269],
            ['golongan' => 'I/d',   'gaji_pokok_100' => 1856700, 'gaji_pokok_80' => 1485360, 'tahun_2' => 1633896, 'tahun_4' => 1797014, 'tahun_6' => 1977014, 'tahun_8' => 2174716, 'tahun_10' => 2392187],
            ['golongan' => 'II/a',  'gaji_pokok_100' => 2184000, 'gaji_pokok_80' => 1747200, 'tahun_2' => 1921920, 'tahun_4' => 2114112, 'tahun_6' => 2325523, 'tahun_8' => 2558076, 'tahun_10' => 2813883],
            ['golongan' => 'II/b',  'gaji_pokok_100' => 2285800, 'gaji_pokok_80' => 1828640, 'tahun_2' => 2011504, 'tahun_4' => 2212654, 'tahun_6' => 2433920, 'tahun_8' => 2677312, 'tahun_10' => 2945043],
            ['golongan' => 'II/c',  'gaji_pokok_100' => 2390900, 'gaji_pokok_80' => 1912720, 'tahun_2' => 2103992, 'tahun_4' => 2314391, 'tahun_6' => 2545830, 'tahun_8' => 2800413, 'tahun_10' => 3080455],
            ['golongan' => 'II/d',  'gaji_pokok_100' => 2499400, 'gaji_pokok_80' => 1999520, 'tahun_2' => 2199472, 'tahun_4' => 2419419, 'tahun_6' => 2661361, 'tahun_8' => 2927497, 'tahun_10' => 3220247],
            ['golongan' => 'III/a', 'gaji_pokok_100' => 3037000, 'gaji_pokok_80' => 2429600, 'tahun_2' => 2672560, 'tahun_4' => 2939816, 'tahun_6' => 3233798, 'tahun_8' => 3557177, 'tahun_10' => 3912895],
            ['golongan' => 'III/b', 'gaji_pokok_100' => 3168900, 'gaji_pokok_80' => 2535120, 'tahun_2' => 2788632, 'tahun_4' => 3067495, 'tahun_6' => 3374245, 'tahun_8' => 3711669, 'tahun_10' => 4082836],
            ['golongan' => 'III/c', 'gaji_pokok_100' => 3304600, 'gaji_pokok_80' => 2643680, 'tahun_2' => 2908048, 'tahun_4' => 3198853, 'tahun_6' => 3518738, 'tahun_8' => 3870612, 'tahun_10' => 4257673],
            ['golongan' => 'III/d', 'gaji_pokok_100' => 3444400, 'gaji_pokok_80' => 2755520, 'tahun_2' => 3031072, 'tahun_4' => 3334179, 'tahun_6' => 3667597, 'tahun_8' => 4034357, 'tahun_10' => 4437793],
            ['golongan' => 'IV/a',  'gaji_pokok_100' => 3588600, 'gaji_pokok_80' => 2870880, 'tahun_2' => 3157968, 'tahun_4' => 3473765, 'tahun_6' => 3821141, 'tahun_8' => 4203255, 'tahun_10' => 4623581],
            ['golongan' => 'IV/b',  'gaji_pokok_100' => 3737500, 'gaji_pokok_80' => 2990000, 'tahun_2' => 3289000, 'tahun_4' => 3617900, 'tahun_6' => 3979690, 'tahun_8' => 4377659, 'tahun_10' => 4815425],
            ['golongan' => 'IV/c',  'gaji_pokok_100' => 3891400, 'gaji_pokok_80' => 3113120, 'tahun_2' => 3424432, 'tahun_4' => 3766875, 'tahun_6' => 4143563, 'tahun_8' => 4557919, 'tahun_10' => 5013711],
            ['golongan' => 'IV/d',  'gaji_pokok_100' => 4050500, 'gaji_pokok_80' => 3240400, 'tahun_2' => 3564440, 'tahun_4' => 3920884, 'tahun_6' => 4312972, 'tahun_8' => 4744270, 'tahun_10' => 5218697],
            ['golongan' => 'IV/e',  'gaji_pokok_100' => 4215100, 'gaji_pokok_80' => 3372080, 'tahun_2' => 3709288, 'tahun_4' => 4080217, 'tahun_6' => 4488238, 'tahun_8' => 4937062, 'tahun_10' => 5430769],
        ];

        foreach ($data as $item) {
            $item['id'] = (string) Str::uuid();
            $item['created_at'] = now();
            $item['updated_at'] = now();
            DB::table('master_gaji_pokoks')->insert($item);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_gaji_pokoks');
    }
};
