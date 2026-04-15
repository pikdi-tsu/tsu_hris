<div class="card card-primary card-outline shadow-sm">
    <div class="card-header border-0">
        <h3 class="card-title"><i class="far fa-calendar-alt text-primary mr-2"></i> Kalender Hari Libur TSU</h3>
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
                    Swal.fire({
                        title: info.event.title,
                        html: `Tanggal: <b>${info.event.start.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</b>`,
                        icon: 'info',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#3085d6'
                    });
                }
            });

            calendar.render();
        });
    </script>
@endsection

@section('css')
    <style>
        .fc-event { cursor: pointer; border-radius: 4px; padding: 2px 4px; font-weight: 500; }
        .fc-day-sun, .fc-day-sat { background-color: #fcfcfc; }
        .fc-day-today { background-color: #e8f4f8 !important; }
    </style>
@endsection
