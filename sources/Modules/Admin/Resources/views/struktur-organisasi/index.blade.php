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

<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <!-- Breadcrumb Navigation for Org Chart -->
            <div class="breadcrumb-org mb-0" id="org-breadcrumb" style="display: none;">
                <!-- Dinamis via JS -->
            </div>
        </div>
        <div>
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-outline-primary active" id="btn-mode-card">
                    <input type="radio" name="options" autocomplete="off" checked> <i class="fas fa-th-large"></i> Mode Kartu
                </label>
                <label class="btn btn-outline-primary" id="btn-mode-tree">
                    <input type="radio" name="options" autocomplete="off"> <i class="fas fa-sitemap"></i> Mode Bagan (Full Tree)
                </label>
            </div>
            <button class="btn btn-success ml-2" id="btn-export-img" style="display:none;"><i class="fas fa-download"></i> Download Bagan</button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">

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

        <!-- Section: Full Tree Chart (Hidden by default) -->
        <div class="card card-outline card-success" id="full-tree-section" style="display: none;">
            <div class="card-body p-0" style="overflow: hidden; border-radius: 0 0 0.25rem 0.25rem;">
                <div id="chart-container" style="width: 100%; height: calc(100vh - 180px); background-color: #f8f9fa;"></div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('script')
<script src="https://d3js.org/d3.v7.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/d3-org-chart@3.1.0"></script>
<script src="https://cdn.jsdelivr.net/npm/d3-flextree@2.1.2/build/d3-flextree.js"></script>

<script>
    // Menyimpan jejak navigasi (Breadcrumb)
    let navigationHistory = [];
    let allUnitsList = []; // Untuk dropdown pindah induk
    let chart = null; // Instance d3-org-chart

    $(document).ready(function() {
        loadCoreUnits();
        fetchAllUnits();

        // Toggle Mode Kartu
        $('#btn-mode-card').click(function() {
            $('#full-tree-section').hide();
            $('#btn-export-img').hide();
            $('.card-outline.card-primary').show();
            if($('#employee-tbody').find('tr').length > 1) {
                $('#employee-section').show();
            }
            if (navigationHistory.length > 0) {
                $('#org-breadcrumb').show();
            }
        });

        // Toggle Mode Bagan
        $('#btn-mode-tree').click(function() {
            $('.card-outline.card-primary').hide();
            $('#employee-section').hide();
            $('#org-breadcrumb').hide();
            $('#full-tree-section').show();
            $('#btn-export-img').show();
            
            if (!chart) {
                renderFullTree();
            }
        });

        // Export Bagan
        $('#btn-export-img').click(function() {
            if(chart) {
                chart.exportImg({full: true});
            }
        });
    });

    function fetchAllUnits() {
        $.ajax({
            url: "{{ route('admin.struktur-organisasi.all-units') }}",
            type: "GET",
            success: function(res) {
                if(res.success) {
                    allUnitsList = res.data;
                }
            }
        });
    }

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
                        <div class="card-footer p-2 text-center bg-white border-top-0">
                            <button class="btn btn-sm btn-outline-primary w-100" onclick="event.stopPropagation(); showMoveUnitModal('${unit.id}', '${unit.name.replace(/'/g, "\\'")}')">
                                <i class="fas fa-exchange-alt"></i> Pindah Induk
                            </button>
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

    function showMoveUnitModal(unitId, unitName) {
        let optionsHtml = '<option value="">-- Tidak Ada (Tingkat Tertinggi) --</option>';
        allUnitsList.forEach(function(u) {
            if (u.id != unitId) {
                optionsHtml += `<option value="${u.id}">${u.nama_unit}</option>`;
            }
        });

        Swal.fire({
            title: 'Pindah Induk Unit',
            html: `
                <p>Pilih unit induk baru untuk <b>${unitName}</b>:</p>
                <select id="swal-parent-unit" class="form-control mt-3 text-left">
                    ${optionsHtml}
                </select>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save"></i> Simpan Perubahan',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                return document.getElementById('swal-parent-unit').value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let parentId = result.value;
                
                // Tampilkan loading
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: "{{ route('admin.struktur-organisasi.move-unit') }}",
                    type: "POST",
                    data: {
                        unit_id: unitId,
                        parent_unit_id: parentId
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Berhasil!', response.message, 'success');
                            // Refresh view
                            if (navigationHistory.length > 0) {
                                let currentTarget = navigationHistory[navigationHistory.length - 1];
                                navigationHistory.pop(); // Pop agar bisa push ulang di fungsi load
                                loadUnitDetails(currentTarget.id, currentTarget.name);
                            } else {
                                loadCoreUnits();
                            }
                        } else {
                            Swal.fire('Gagal!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                    }
                });
            }
        });
    }

    function renderFullTree() {
        $('#chart-container').html('<div class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border text-primary"></div><span class="ml-2 font-weight-bold">Memuat Bagan...</span></div>');
        
        d3.json("{{ route('admin.struktur-organisasi.full-tree-data') }}").then(function(response) {
            if(response.success) {
                $('#chart-container').empty();
                chart = new d3.OrgChart()
                    .container('#chart-container')
                    .data(response.data)
                    .nodeHeight((d) => d.data.type === 'employee' ? 80 : 125)
                    .nodeWidth((d) => d.data.type === 'employee' ? 240 : 280)
                    .childrenMargin((d) => 50)
                    .compactMarginBetween((d) => 15)
                    .compactMarginPair((d) => 80)
                    .onNodeClick(d => {
                        let unitId = typeof d === 'string' ? d : (d.data ? d.data.id : d);
                        if (unitId === 'root-tsu') return;
                        
                        let unitNode = response.data.find(x => x.id == unitId);

                        if (unitNode) {
                            if (unitNode.type === 'employee') {
                                // Tampilkan modal biodata ringkas karyawan
                                Swal.fire({
                                    title: '<i class="fas fa-id-card text-info"></i> Profil Karyawan',
                                    html: `
                                        <div class="text-center mt-3">
                                            <div style="width: 100px; height: 100px; border-radius: 50%; background-color: #f1f3f5; margin: 0 auto 15px auto; display: flex; align-items: center; justify-content: center; border: 3px solid #dee2e6; color: #adb5bd; font-size: 45px; overflow: hidden;">
                                                ${unitNode.image_url ? `<img src="${unitNode.image_url}" style="width:100%;height:100%;object-fit:cover;">` : `<i class="fas fa-user"></i>`}
                                            </div>
                                            <h5 class="font-weight-bold text-primary mb-1">${unitNode.name}</h5>
                                            <span class="badge badge-secondary mb-3">${unitNode.tipe_karyawan}</span>
                                            
                                            <div class="text-left bg-light p-3 rounded border">
                                                <p class="mb-2"><b><i class="fas fa-sitemap text-muted"></i> Jabatan / Status:</b><br><span class="badge badge-primary mt-1">${unitNode.title}</span></p>
                                                <p class="mb-0"><b><i class="fas fa-briefcase text-muted"></i> Posisi Harian:</b><br>${unitNode.posisi}</p>
                                            </div>
                                        </div>
                                    `,
                                    showCloseButton: true,
                                    showConfirmButton: false,
                                    width: '450px'
                                });
                                return; // Hentikan eksekusi di sini agar tidak memuat unit modal
                            }

                            // Tampilkan loading modal untuk Unit Node
                            Swal.fire({
                                title: 'Memuat data...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            // Ambil data detail unit menggunakan endpoint yang sama dengan mode kartu
                            $.ajax({
                                url: "{{ route('admin.struktur-organisasi.unit-details') }}",
                                type: "POST",
                                data: { id: unitNode.id },
                                success: function(res) {
                                    if(res.success) {
                                        let empHtml = '';
                                        if(res.employees.length === 0) {
                                            empHtml = '<p class="text-muted text-center mt-3"><i>Tidak ada karyawan di unit ini.</i></p>';
                                        } else {
                                            empHtml = '<div class="table-responsive mt-3" style="max-height: 300px; overflow-y: auto;"><table class="table table-sm table-bordered text-left" style="font-size: 14px;"><thead><tr class="bg-light"><th>Nama Karyawan</th><th>Jabatan / Posisi</th></tr></thead><tbody>';
                                            res.employees.forEach(function(emp) {
                                                let badgeClass = emp.jabatan_struktural !== 'Staf/Anggota' ? 'badge-primary' : 'badge-light border';
                                                empHtml += `<tr>
                                                    <td><span class="font-weight-bold">${emp.nama}</span><br><small class="text-muted">${emp.tipe}</small></td>
                                                    <td><span class="badge ${badgeClass} mb-1">${emp.jabatan_struktural}</span><br><small>${emp.posisi_harian || '-'}</small></td>
                                                </tr>`;
                                            });
                                            empHtml += '</tbody></table></div>';
                                        }

                                        Swal.fire({
                                            title: '<i class="fas fa-building text-primary"></i> ' + unitNode.name,
                                            html: `
                                                <div class="text-left">
                                                    <div class="bg-light p-3 rounded border">
                                                        <p class="mb-1"><b><i class="fas fa-user-tie"></i> Kepala / Pimpinan:</b><br>${unitNode.head_name} <span class="badge badge-secondary ml-1">${unitNode.title}</span></p>
                                                        <p class="mb-0 mt-2"><b><i class="fas fa-users"></i> Total Karyawan:</b> ${res.employees.length} Orang</p>
                                                    </div>
                                                    <h6 class="font-weight-bold mt-4 mb-0">Daftar Karyawan:</h6>
                                                    ${empHtml}
                                                </div>
                                            `,
                                            width: '700px',
                                            showCloseButton: true,
                                            showConfirmButton: false
                                        });
                                    } else {
                                        Swal.fire('Error', 'Gagal memuat detail unit.', 'error');
                                    }
                                },
                                error: function() {
                                    Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
                                }
                            });
                        }
                    })
                    .nodeContent(function(d, i, arr, state) {
                        if (d.data.type === 'employee') {
                            return `
                                <div style="background-color: #ffffff; border: 1px solid #ced4da; border-radius: 6px; width: ${d.width}px; height: ${d.height}px; padding: 10px; display: flex; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                    <div style="min-width: 40px; height: 40px; border-radius: 50%; background-color: #f1f3f5; display: flex; align-items: center; justify-content: center; margin-right: 12px; border: 1px solid #dee2e6; color: #adb5bd; font-size: 16px; overflow: hidden;">
                                        ${d.data.image_url ? `<img src="${d.data.image_url}" style="width:100%;height:100%;object-fit:cover;">` : `<i class="fas fa-user"></i>`}
                                    </div>
                                    <div style="flex: 1; overflow: hidden;">
                                        <h6 style="margin: 0 0 3px 0; font-size: 12px; color: #343a40; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${d.data.name}">${d.data.name}</h6>
                                        <div style="font-size: 10px; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${d.data.title}">${d.data.title}</div>
                                        <div style="font-size: 10px; color: #17a2b8; margin-top: 2px;">${d.data.posisi}</div>
                                    </div>
                                </div>
                            `;
                        }

                        // Tampilan untuk Unit Node
                        return `
                            <div style="background-color: #ffffff; border-top: 4px solid #007bff; border-radius: 8px; width: ${d.width}px; height: ${d.height}px; padding: 15px; display: flex; align-items: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                <div style="min-width: 55px; height: 55px; border-radius: 50%; background-color: #f1f3f5; display: flex; align-items: center; justify-content: center; margin-right: 15px; border: 2px solid #dee2e6; color: #adb5bd; font-size: 24px; overflow: hidden;">
                                    ${d.data.image_url && d.data.head_name !== 'Kosong' ? `<img src="${d.data.image_url}" style="width:100%;height:100%;object-fit:cover;">` : `<i class="fas fa-user-tie"></i>`}
                                </div>
                                <div style="flex: 1; overflow: hidden;">
                                    <h6 style="margin: 0 0 5px 0; font-size: 14px; color: #007bff; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${d.data.name}">${d.data.name}</h6>
                                    <div style="font-size: 12px; color: #343a40; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${d.data.head_name}">${d.data.head_name}</div>
                                    <div style="font-size: 11px; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${d.data.title}">${d.data.title}</div>
                                    <div style="font-size: 11px; color: #28a745; margin-top: 5px; font-weight: bold;"><i class="fas fa-users"></i> ${d.data.employee_count} Karyawan</div>
                                </div>
                            </div>
                        `;
                    })
                    .render()
                    .fit(); // Automatically fits the root nodes to the screen. 
                    // We don't call expandAll() here, so it respects the user's wish to only show roots by default.
            }
        }).catch(function(error) {
            console.error("D3 Org Chart Error:", error);
            $('#chart-container').html('<div class="d-flex justify-content-center align-items-center h-100 text-danger"><i class="fas fa-exclamation-triangle mr-2"></i> Gagal memuat data bagan. Cek console log.</div>');
        });
    }
</script>
@endsection
