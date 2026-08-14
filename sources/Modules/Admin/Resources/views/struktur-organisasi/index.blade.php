@extends('system::template.admin.header')

@section('title')
    {{ $title }}
@endsection

@section('content')
<style>
    .org-card {
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        border-top: 4px solid #007bff;
    }
    .org-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .breadcrumb-org {
        background: #f8f9fa;
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        font-weight: bold;
    }
    .breadcrumb-item-org {
        display: inline;
        cursor: pointer;
        color: #007bff;
    }
    .breadcrumb-item-org:hover {
        text-decoration: underline;
    }
    .breadcrumb-separator {
        margin: 0 10px;
        color: #6c757d;
    }
</style>

<div class="row">
    <div class="col-12">
        
        <!-- Breadcrumb Navigation for Org Chart -->
        <div class="breadcrumb-org" id="org-breadcrumb" style="display: none;">
            <!-- Dinamis via JS -->
        </div>

        <!-- Section: Unit List (Cards) -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title" id="current-unit-title">Tiga Serangkai (Unit Utama)</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-secondary" id="btn-reset" style="display: none;" onclick="loadCoreUnits()">
                        <i class="fas fa-home"></i> Kembali ke Awal
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row" id="unit-container">
                    <!-- Loading state -->
                    <div class="col-12 text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2">Memuat struktur organisasi...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Karyawan & Jabatan (Tabel) -->
        <div class="card card-outline card-info" id="employee-section" style="display: none;">
            <div class="card-header">
                <h3 class="card-title">Daftar Jabatan & Karyawan di <span id="emp-unit-name" class="font-weight-bold"></span></h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover m-0" id="employee-table">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="30%">Nama Lengkap</th>
                                <th width="25%">Jabatan Struktural (di Unit Ini)</th>
                                <th width="20%">Posisi Harian</th>
                                <th width="20%">Tipe Karyawan</th>
                            </tr>
                        </thead>
                        <tbody id="employee-tbody">
                            <!-- Dinamis via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('script')
<script>
    // Menyimpan jejak navigasi (Breadcrumb)
    let navigationHistory = [];

    $(document).ready(function() {
        loadCoreUnits();
    });

    // Menyiapkan token CSRF untuk semua request AJAX POST
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function loadCoreUnits() {
        // Reset state
        navigationHistory = [];
        updateBreadcrumb();
        $('#btn-reset').hide();
        $('#employee-section').hide();
        $('#current-unit-title').text('Tiga Serangkai Universitas (Unit Utama)');
        $('#unit-container').html('<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div><p>Memuat...</p></div>');

        $.ajax({
            url: "{{ route('admin.struktur-organisasi.core-units') }}",
            type: "GET",
            success: function(response) {
                if(response.success) {
                    renderUnits(response.data);
                }
            },
            error: function() {
                $('#unit-container').html('<div class="col-12 text-center text-danger">Gagal memuat data.</div>');
            }
        });
    }

    function loadUnitDetails(unitId, unitName) {
        // Tambahkan ke history jika belum ada di posisi terakhir
        if (navigationHistory.length === 0 || navigationHistory[navigationHistory.length - 1].id !== unitId) {
            navigationHistory.push({id: unitId, name: unitName});
        }
        
        updateBreadcrumb();
        $('#btn-reset').show();
        $('#current-unit-title').text('Sub-Unit dari: ' + unitName);
        $('#emp-unit-name').text(unitName);
        
        $('#unit-container').html('<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div><p>Memuat Sub-Unit...</p></div>');
        $('#employee-tbody').html('<tr><td colspan="5" class="text-center">Memuat data karyawan...</td></tr>');
        $('#employee-section').show();

        $.ajax({
            url: "{{ route('admin.struktur-organisasi.unit-details') }}",
            type: "POST",
            data: { id: unitId },
            success: function(response) {
                if(response.success) {
                    // Render Sub Units
                    if (response.sub_units.length > 0) {
                        renderUnits(response.sub_units);
                    } else {
                        $('#unit-container').html('<div class="col-12 text-center text-muted py-4"><i>Tidak ada sub-unit di bawah unit ini.</i></div>');
                    }

                    // Render Employees
                    renderEmployees(response.employees);
                }
            },
            error: function() {
                $('#unit-container').html('<div class="col-12 text-center text-danger">Gagal memuat sub-unit.</div>');
                $('#employee-tbody').html('<tr><td colspan="5" class="text-center text-danger">Gagal memuat data karyawan.</td></tr>');
            }
        });
    }

    function renderUnits(units) {
        let html = '';
        units.forEach(function(unit) {
            let childIndicator = unit.has_children ? '<span class="badge badge-info float-right" title="Memiliki Sub-Unit"><i class="fas fa-sitemap"></i></span>' : '';
            
            html += `
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 org-card" onclick="loadUnitDetails('${unit.id}', '${unit.name.replace(/'/g, "\\'")}')">
                        <div class="card-body text-center">
                            ${childIndicator}
                            <h5 class="text-primary font-weight-bold mt-2">${unit.name}</h5>
                            <hr>
                            <p class="mb-1 text-muted small">Kepala / Pimpinan:</p>
                            <h6 class="font-weight-bold mb-1">${unit.head_name}</h6>
                            <span class="badge badge-secondary">${unit.title}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        $('#unit-container').html(html);
    }

    function renderEmployees(employees) {
        let html = '';
        if (employees.length === 0) {
            html = '<tr><td colspan="5" class="text-center text-muted"><i>Tidak ada karyawan yang terdaftar langsung di unit ini.</i></td></tr>';
        } else {
            employees.forEach(function(emp, index) {
                let badgeClass = emp.jabatan_struktural !== 'Staf/Anggota' ? 'badge-primary' : 'badge-light border';
                html += `
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td class="font-weight-bold">${emp.nama}</td>
                        <td><span class="badge ${badgeClass}">${emp.jabatan_struktural}</span></td>
                        <td>${emp.posisi_harian || '-'}</td>
                        <td>${emp.tipe}</td>
                    </tr>
                `;
            });
        }
        $('#employee-tbody').html(html);
    }

    function navigateToHistory(index) {
        if (index === -1) {
            loadCoreUnits();
            return;
        }
        
        // Potong history sampai index yang di-klik
        let target = navigationHistory[index];
        navigationHistory = navigationHistory.slice(0, index);
        
        // Load ulang target
        loadUnitDetails(target.id, target.name);
    }

    function updateBreadcrumb() {
        if (navigationHistory.length === 0) {
            $('#org-breadcrumb').hide();
            return;
        }

        let breadcrumbHtml = `<span class="breadcrumb-item-org" onclick="navigateToHistory(-1)"><i class="fas fa-home"></i> Awal</span>`;
        
        navigationHistory.forEach(function(item, index) {
            breadcrumbHtml += `<span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>`;
            if (index === navigationHistory.length - 1) {
                // Item aktif terakhir
                breadcrumbHtml += `<span class="text-dark font-weight-bold">${item.name}</span>`;
            } else {
                // Item history yang bisa diklik
                breadcrumbHtml += `<span class="breadcrumb-item-org" onclick="navigateToHistory(${index})">${item.name}</span>`;
            }
        });

        $('#org-breadcrumb').html(breadcrumbHtml).show();
    }
</script>
@endsection
