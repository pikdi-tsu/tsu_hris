<form action="{{ route('system.role.update', $role->id) }}" method="POST" id="form-role-permission">
    @csrf
    @method('PUT')

    {{-- HEADER --}}
    <div class="modal-header bg-purple text-white">
        <h5 class="modal-title font-weight-bold">
            <i class="fas fa-user-shield mr-1"></i> Edit Role: <b>{{ $role->name }}</b>
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    {{-- BODY --}}
    <div class="modal-body p-0" style="height: 70vh; display: flex; flex-direction: column;">

        {{-- INPUT NAMA ROLE --}}
        <div class="bg-white p-3 border-bottom shadow-sm" style="z-index: 10;">
            <div class="form-group mb-0">
                <label class="small text-muted font-weight-bold text-uppercase">Nama Role <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-pen text-primary"></i>
                        </span>
                    </div>
                    <input type="text" name="name" class="form-control font-weight-bold text-dark"
                           value="{{ $role->name }}"
                           placeholder="Contoh: staff-keuangan" required>
                </div>
            </div>
        </div>

        {{-- SPLIT VIEW (Sidebar + Content) --}}
        <div class="d-flex flex-fill overflow-hidden">
            {{-- SIDEBAR KIRI (Daftar Modul) --}}
            <div class="nav flex-column nav-pills p-2 bg-light border-right overflow-auto"
                 id="v-pills-tab" role="tablist" aria-orientation="vertical" style="width: 32%; min-width: 220px;">

                <div class="px-2 pb-2 mt-2 border-bottom mb-2 d-flex justify-content-between align-items-center">
                    <small class="text-muted text-uppercase font-weight-bold">Daftar Modul</small>
                    <small class="text-muted font-weight-bold">Akses</small>
                </div>

                @foreach($groupedPermissions as $groupName => $permissions)
                    @php
                        $groupSlug = Str::slug($groupName);
                        $totalCount = count($permissions);
                        $checkedCount = $permissions->filter(function($p) use ($rolePermissions) {
                            return in_array($p->name, $rolePermissions, true);
                        })->count();
                        $hasAccess = $checkedCount > 0;
                    @endphp
                    <a class="nav-link {{ $loop->first ? 'active' : '' }} text-sm mb-1 d-flex align-items-center justify-content-between nav-module-item"
                       id="v-pills-{{ $groupSlug }}-tab"
                       data-toggle="pill"
                       href="#v-pills-{{ $groupSlug }}"
                       role="tab"
                       data-group="{{ $groupSlug }}"
                       data-total="{{ $totalCount }}"
                       aria-controls="v-pills-{{ $groupSlug }}"
                       aria-selected="{{ $loop->first ? 'true' : 'false' }}">

                        <div class="d-flex align-items-center text-truncate mr-1">
                            {{-- Ikon Ceklis Hijau jika ada akses --}}
                            <i class="fas fa-check-circle text-success mr-2 icon-group-check {{ $hasAccess ? '' : 'd-none' }}"
                               id="check_icon_{{ $groupSlug }}"
                               title="Ada akses di modul ini"
                               style="font-size: 0.95rem;"></i>
                            {{-- Ikon Folder jika belum ada akses --}}
                            <i class="fas fa-folder mr-1 text-muted icon-folder {{ $hasAccess ? 'd-none' : '' }}"
                               id="folder_icon_{{ $groupSlug }}"></i>
                            <span class="font-weight-medium text-truncate module-name">{{ $groupName }}</span>
                        </div>

                        {{-- Counter Badge (misal: 2/4) --}}
                        <span class="badge {{ $hasAccess ? 'badge-success' : 'badge-light text-muted border' }} shadow-sm font-weight-bold badge-perm-counter ml-2"
                              id="counter_{{ $groupSlug }}">
                            {{ $checkedCount }}/{{ $totalCount }}
                        </span>
                    </a>
                @endforeach
            </div>

            {{-- KONTEN KANAN (Daftar Checkbox) --}}
            <div class="tab-content p-3 flex-fill overflow-auto bg-white" id="v-pills-tabContent" style="width: 68%;">

                @foreach($groupedPermissions as $groupName => $permissions)
                    @php
                        $groupSlug = Str::slug($groupName);
                        $totalCount = count($permissions);
                        $checkedCount = $permissions->filter(function($p) use ($rolePermissions) {
                            return in_array($p->name, $rolePermissions, true);
                        })->count();
                        $allChecked = $checkedCount === $totalCount && $totalCount > 0;
                    @endphp
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                         id="v-pills-{{ $groupSlug }}"
                         role="tabpanel"
                         aria-labelledby="v-pills-{{ $groupSlug }}-tab">

                        {{-- Header Group --}}
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom sticky-top bg-white"
                            style="top: -1rem; padding-top: 1rem; margin-top: -1rem;">
                            <h5 class="m-0 text-dark">
                                <i class="fas fa-cube text-primary mr-1"></i> Fitur Modul: <b>{{ $groupName }}</b>
                            </h5>

                            {{-- Check All Per Group --}}
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input check-all-group"
                                       id="check_all_{{ $groupSlug }}"
                                       data-group="{{ $groupSlug }}"
                                       {{ $allChecked ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold text-primary"
                                       for="check_all_{{ $groupSlug }}" style="cursor: pointer;">
                                    Pilih Semua
                                </label>
                            </div>
                        </div>

                        {{-- List Checkbox --}}
                        <div class="row">
                            @foreach($permissions as $perm)
                                @php
                                    $isChecked = in_array($perm->name, $rolePermissions, true);
                                @endphp
                                <div class="col-md-6 mb-2">
                                    <div class="permission-card p-2 border rounded hover-effect {{ $isChecked ? 'border-purple bg-purple-light' : '' }}"
                                         id="card_perm_{{ $perm->id }}">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox"
                                                   class="custom-control-input perm-item group-{{ $groupSlug }}"
                                                   id="perm_{{ $perm->id }}"
                                                   name="permissions[]"
                                                   value="{{ $perm->name }}"
                                                   data-group-slug="{{ $groupSlug }}"
                                                   data-perm-id="{{ $perm->id }}"
                                                {{ $isChecked ? 'checked' : '' }}>
                                            <label class="custom-control-label d-flex flex-column"
                                                   for="perm_{{ $perm->id }}" style="cursor: pointer;">
                                                <span class="text-dark font-weight-bold text-sm">{{ $perm->name }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="modal-footer bg-light justify-content-between">
        <div class="text-muted small">
            <i class="fas fa-info-circle text-primary mr-1"></i> Tanda ceklis hijau (<i class="fas fa-check-circle text-success"></i>) menandakan grup memiliki akses aktif.
        </div>
        <div>
            <button type="button" class="btn btn-secondary font-weight-bold mr-2" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-warning shadow-sm px-4 font-weight-bold">
                <i class="fas fa-save mr-1"></i> Simpan Perubahan
            </button>
        </div>
    </div>
</form>

<style>
    .bg-purple {
        background-color: #6f42c1 !important;
    }

    .border-purple {
        border-color: #6f42c1 !important;
    }

    .bg-purple-light {
        background-color: #f8f4fc;
    }

    .nav-pills .nav-link {
        color: #495057;
        border-radius: 0.35rem;
        transition: all 0.2s;
        padding: 0.55rem 0.75rem;
    }

    .nav-pills .nav-link:hover {
        background-color: #e9ecef;
    }

    .nav-pills .nav-link.active {
        background-color: #6f42c1; /* Purple */
        color: #fff !important;
    }

    .nav-pills .nav-link.active .module-name {
        color: #fff !important;
    }

    .nav-pills .nav-link.active .icon-folder {
        color: rgba(255, 255, 255, 0.8) !important;
    }

    .nav-pills .nav-link.active .icon-group-check {
        color: #2ecc71 !important;
        background-color: #fff;
        border-radius: 50%;
    }

    .nav-pills .nav-link.active .badge-success {
        background-color: #28a745 !important;
        color: #fff !important;
    }

    .nav-pills .nav-link.active .badge-light {
        background-color: rgba(255, 255, 255, 0.25) !important;
        color: #fff !important;
        border: none !important;
    }

    .permission-card {
        transition: all 0.2s;
    }

    .permission-card:hover {
        background-color: #f8f9fa;
        border-color: #6f42c1 !important;
    }

    /* Scrollbar */
    .overflow-auto::-webkit-scrollbar {
        width: 6px;
    }

    .overflow-auto::-webkit-scrollbar-thumb {
        background-color: #ccc;
        border-radius: 4px;
    }
</style>

<script>
    $(document).ready(function () {
        // Fungsi Update Status Grup secara Real-Time
        function updateGroupCounterAndStatus(groupSlug) {
            var $groupItems = $('.group-' + groupSlug);
            var total = $groupItems.length;
            var checked = $groupItems.filter(':checked').length;

            var $counter = $('#counter_' + groupSlug);
            var $checkIcon = $('#check_icon_' + groupSlug);
            var $folderIcon = $('#folder_icon_' + groupSlug);
            var $checkAllSwitch = $('#check_all_' + groupSlug);

            // Update Teks Counter (misal: 2/4)
            $counter.text(checked + '/' + total);

            // Update Ceklis Hijau & Badge Color
            if (checked > 0) {
                $checkIcon.removeClass('d-none');
                $folderIcon.addClass('d-none');
                $counter.removeClass('badge-light text-muted border').addClass('badge-success');
            } else {
                $checkIcon.addClass('d-none');
                $folderIcon.removeClass('d-none');
                $counter.removeClass('badge-success').addClass('badge-light text-muted border');
            }

            // Update Switch Pilih Semua
            $checkAllSwitch.prop('checked', checked === total && total > 0);
        }

        // Event Check All per Group
        $('.check-all-group').change(function () {
            var groupSlug = $(this).data('group');
            var isChecked = $(this).is(':checked');

            $('.group-' + groupSlug).prop('checked', isChecked).each(function () {
                var permId = $(this).data('perm-id');
                var $card = $('#card_perm_' + permId);
                if (isChecked) {
                    $card.addClass('border-purple bg-purple-light');
                } else {
                    $card.removeClass('border-purple bg-purple-light');
                }
            });

            updateGroupCounterAndStatus(groupSlug);
        });

        // Event Individual Checkbox
        $('.perm-item').change(function () {
            var groupSlug = $(this).data('group-slug');
            var permId = $(this).data('perm-id');
            var $card = $('#card_perm_' + permId);

            if ($(this).is(':checked')) {
                $card.addClass('border-purple bg-purple-light');
            } else {
                $card.removeClass('border-purple bg-purple-light');
            }

            updateGroupCounterAndStatus(groupSlug);
        });
    });
</script>
