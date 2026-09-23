<div class="card card-primary card-outline shadow-sm">
    <div class="card-header border-0 d-flex flex-wrap align-items-center justify-content-between">
        <h3 class="card-title font-weight-bold text-primary m-0 mb-2 mb-md-0">
            <i class="far fa-calendar-alt mr-2"></i> Kalender TSU
        </h3>
        <div class="d-flex align-items-center flex-wrap">
            <span class="badge badge-danger px-2 py-1 mr-2 font-weight-normal shadow-sm" style="font-size: 0.82rem;">
                <i class="fas fa-calendar-times mr-1"></i> Hari Libur
            </span>
            <span class="badge badge-primary px-2 py-1 mr-2 font-weight-normal shadow-sm" style="font-size: 0.82rem;">
                <i class="fas fa-user-clock mr-1"></i> Piket Sabtu
            </span>
            <span class="badge badge-info px-2 py-1 mr-2 font-weight-normal shadow-sm" style="font-size: 0.82rem; background-color: #17a2b8 !important;">
                <i class="fas fa-bullhorn mr-1"></i> Surat Edaran &amp; SK
            </span>
            <span class="badge text-white px-2 py-1 font-weight-normal shadow-sm mr-2" style="font-size: 0.82rem; background-color: #094b54 !important;">
                <i class="fas fa-calendar-check mr-1"></i> Kegiatan
            </span>
            <span class="badge text-white px-2 py-1 font-weight-normal shadow-sm" style="font-size: 0.82rem; background-color: #4f46e5 !important;">
                <i class="fas fa-graduation-cap mr-1"></i> Pelatihan / Training
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <div id="calendar" class="p-3"></div>
    </div>
</div>

@section('script')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                dayMaxEvents: 4, // Tampilkan hingga 4 baris event, selebihnya muncul popover '+more'
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listMonth'
                },
                buttonText: {
                    today: 'Hari Ini',
                    month: 'Bulan',
                    list: 'Agenda'
                },
                themeSystem: 'bootstrap',
                events: "{{ route('users.hari-libur.json') }}",

                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    var props = info.event.extendedProps || {};
                    var tglFormatted = info.event.start ? info.event.start.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) : '-';

                    if (props.type === 'piket') {
                        Swal.fire({
                            title: '<i class="fas fa-user-clock text-primary mr-1"></i> Jadwal Piket Sabtu',
                            html: `
                                <div class="text-left mt-3 p-2 bg-light rounded border">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td style="width: 110px;" class="font-weight-bold text-muted">Pegawai:</td>
                                            <td class="font-weight-bold text-dark">${props.nama || info.event.title}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-muted">Unit / Divisi:</td>
                                            <td><span class="badge badge-light border text-dark">${props.unit || '-'}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-muted">Hari/Tanggal:</td>
                                            <td><i class="far fa-calendar-alt text-primary mr-1"></i>${tglFormatted}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-muted">Jam Piket:</td>
                                            <td><i class="far fa-clock text-success mr-1"></i><strong>${props.jam || '-'}</strong></td>
                                        </tr>
                                        ${props.keterangan && props.keterangan !== '-' ? `
                                        <tr>
                                            <td class="font-weight-bold text-muted">Keterangan:</td>
                                            <td>${props.keterangan}</td>
                                        </tr>` : ''}
                                    </table>
                                </div>
                            `,
                            icon: 'info',
                            confirmButtonText: 'Tutup',
                            confirmButtonColor: '#007bff'
                        });
                    } else if (props.type === 'edaran') {
                        var tglAgenda = props.tanggal_kalender;
                        if (props.tanggal_selesai && props.tanggal_selesai !== props.tanggal_kalender) {
                            tglAgenda += ' s.d ' + props.tanggal_selesai;
                        }
                        Swal.fire({
                            title: '<i class="fas fa-file-invoice text-info mr-1"></i> ' + (props.kategori_label || 'Surat Edaran & SK'),
                            html: `
                                <div class="text-left mt-3 p-3 bg-light rounded border text-sm" style="font-size: 0.95rem;">
                                    <div class="mb-2 pb-2 border-bottom">
                                        <div class="font-weight-bold text-dark" style="font-size: 1.05rem;">${props.perihal}</div>
                                        <small class="text-muted"><i class="fas fa-hashtag mr-1"></i>${props.nomor_surat}</small>
                                    </div>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td style="width: 140px;" class="font-weight-bold text-muted">Tanggal Surat:</td>
                                            <td class="font-weight-bold text-dark"><i class="far fa-calendar-alt text-info mr-1"></i>${props.tanggal_surat} <small class="text-muted font-italic font-weight-normal">(Tgl Terbit)</small></td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-muted">Agenda Kalender:</td>
                                            <td class="font-weight-bold text-success"><i class="far fa-calendar-check mr-1"></i>${tglAgenda}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-muted">Sasaran Dokumen:</td>
                                            <td><span class="badge badge-light border text-dark">${props.target}</span></td>
                                        </tr>
                                        ${props.keterangan && props.keterangan !== '-' ? `
                                        <tr>
                                            <td class="font-weight-bold text-muted">Keterangan:</td>
                                            <td>${props.keterangan}</td>
                                        </tr>` : ''}
                                    </table>
                                </div>
                            `,
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonText: '<i class="fas fa-file-pdf mr-1"></i> Unduh / Buka Dokumen',
                            confirmButtonColor: '#17a2b8',
                            cancelButtonText: 'Tutup',
                            cancelButtonColor: '#6c757d'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.open(props.download_url, '_blank');
                            }
                        });
                    } else if (props.type === 'kegiatan') {
                        Swal.fire({
                            title: '<i class="fas fa-calendar-check text-success mr-2"></i>' + props.nama_kegiatan,
                            html: `
                                <div class="text-left mt-3 p-3 bg-light rounded border text-sm" style="font-size: 0.95rem;">
                                    <div class="mb-2 pb-2 border-bottom d-flex justify-content-between align-items-center">
                                        <span class="badge badge-info px-2 py-1 font-weight-bold">${props.kategori}</span>
                                        <span class="badge ${props.status === 'Dibuka' ? 'badge-success' : 'badge-secondary'} px-2 py-1 rounded-pill">${props.status}</span>
                                    </div>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td style="width: 140px;" class="font-weight-bold text-muted">Hari/Tanggal:</td>
                                            <td class="font-weight-bold text-dark"><i class="far fa-calendar-alt text-primary mr-1"></i>${props.tanggal}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-muted">Waktu:</td>
                                            <td class="font-weight-bold text-dark"><i class="far fa-clock text-secondary mr-1"></i>${props.jam}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-muted">Tempat / Lokasi:</td>
                                            <td class="font-weight-bold text-danger"><i class="fas fa-map-marker-alt mr-1"></i>${props.lokasi}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-muted">Sasaran Peserta:</td>
                                            <td><span class="badge badge-light border text-dark font-weight-bold"><i class="fas fa-users mr-1"></i>${props.target_peserta}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-muted">Penyelenggara:</td>
                                            <td>${props.penyelenggara}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-muted">Penanggung Jawab:</td>
                                            <td>${props.pic}</td>
                                        </tr>
                                        ${props.keterangan && props.keterangan !== '-' ? `
                                        <tr>
                                            <td class="font-weight-bold text-muted">Catatan Acara:</td>
                                            <td>${props.keterangan}</td>
                                        </tr>` : ''}
                                    </table>
                                </div>
                            `,
                            icon: 'info',
                            confirmButtonText: 'Tutup',
                            confirmButtonColor: '#094b54'
                        });
                    } else if (props.type === 'training') {
                        var tglAgenda = props.tanggal_mulai;
                        if (props.tanggal_selesai && props.tanggal_selesai !== props.tanggal_mulai) {
                            tglAgenda += ' s.d ' + props.tanggal_selesai;
                        }
                        Swal.fire({
                            title: '<i class="fas fa-graduation-cap mr-2" style="color: #4f46e5;"></i>' + props.nama_training,
                            html: `
                                <div class="text-left mt-3 p-3 bg-light rounded border text-sm" style="font-size: 0.95rem;">
                                    <div class="mb-2 pb-2 border-bottom d-flex justify-content-between align-items-center">
                                        <span class="badge text-white px-2 py-1 font-weight-bold" style="background-color: #4f46e5;">Pelatihan / Training</span>
                                        <span class="badge ${props.status === 'Selesai' ? 'badge-success' : (props.status === 'Berjalan' ? 'badge-warning' : 'badge-info')} px-2 py-1 rounded-pill">${props.status}</span>
                                    </div>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td style="width: 140px;" class="font-weight-bold text-muted">Tanggal:</td>
                                            <td class="font-weight-bold text-dark"><i class="far fa-calendar-alt text-primary mr-1"></i>${tglAgenda}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-muted">Tempat / Lokasi:</td>
                                            <td class="font-weight-bold text-danger"><i class="fas fa-map-marker-alt mr-1"></i>${props.lokasi}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold text-muted">Penyelenggara:</td>
                                            <td class="font-weight-bold text-dark">${props.penyelenggara}</td>
                                        </tr>
                                        ${props.status_kehadiran ? `
                                        <tr>
                                            <td class="font-weight-bold text-muted">Status Peserta:</td>
                                            <td><span class="badge badge-success px-2 py-1">${props.status_kehadiran}</span></td>
                                        </tr>` : ''}
                                        ${props.deskripsi && props.deskripsi !== '-' ? `
                                        <tr>
                                            <td class="font-weight-bold text-muted">Deskripsi:</td>
                                            <td>${props.deskripsi}</td>
                                        </tr>` : ''}
                                    </table>
                                </div>
                            `,
                            icon: 'info',
                            confirmButtonText: 'Tutup',
                            confirmButtonColor: '#4f46e5'
                        });
                    } else {
                        // Hari Libur
                        Swal.fire({
                            title: info.event.title,
                            html: `
                                <div class="text-left mt-3 p-2 bg-light rounded border">
                                    <p class="mb-1"><strong>Hari/Tanggal:</strong> ${tglFormatted}</p>
                                    <p class="mb-0"><strong>Kategori:</strong> <span class="badge badge-danger">${props.status || 'Hari Libur'}</span></p>
                                </div>
                            `,
                            icon: 'warning',
                            confirmButtonText: 'Tutup',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                }
            });

            calendar.render();
        });
    </script>
@endsection

@section('css')
    <style>
        .fc-event {
            cursor: pointer;
            border-radius: 4px;
            padding: 2px 4px;
            font-weight: 500;
            font-size: 0.8rem;
            margin-bottom: 2px;
            border: none !important;
        }
        .fc-daygrid-event {
            white-space: normal !important;
            align-items: flex-start;
        }
        .fc-daygrid-event-dot {
            display: none;
        }
        .fc-event-title {
            font-weight: 600;
            line-height: 1.25;
            padding: 1px 2px;
            word-break: break-word;
        }
        .fc-day-sun, .fc-day-sat {
            background-color: #fbfbfb;
        }
        .fc-day-today {
            background-color: #e8f4f8 !important;
        }
        .fc-popover {
            z-index: 1050;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>
@endsection
