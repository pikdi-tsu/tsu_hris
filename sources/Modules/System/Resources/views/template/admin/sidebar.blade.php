<style>
    .nav-sidebar .nav-treeview {
        padding-left: 0; margin-left: 0;
    }

    /* Styling indikator */
    .nav-indicator {
        font-size: 0.75rem;
        width: 1rem;
        text-align: center;
        transition: transform 0.3s ease;
        color: #adb5bd; /* Warna abu-abu */
    }

    /* Strip (-) */
    .nav-indicator.fa-minus {
        font-size: 0.6rem;
        opacity: 0.7;
    }

    /* Menu Open */
    .nav-sidebar .nav-item.menu-open > .nav-link {
        color: yellow !important;
    }
    .nav-sidebar .nav-item.menu-open > .nav-link .nav-indicator.fa-chevron-right {
        transform: rotate(90deg);
        color: yellow;
    }

    /* Active State for Sidebar */
    .nav-sidebar .nav-link.active {
        background-color: teal !important;
        color: yellow !important;
    }
    .nav-sidebar .nav-link.active > .nav-indicator {
        color: yellow !important;
        opacity: 1;
    }

    .nav-sidebar .nav-link > .nav-icon {
        margin-left: 0 !important;
        margin-right: 0.6rem !important;
        font-size: 1rem;
        width: 1.2rem;
        text-align: center;
    }
</style>

<aside class="main-sidebar sidebar-dark-primary elevation-4" style="background-color: #094b54;">
    <a href="{{ route('admin.dashboard') }}" class="brand-link" style="background-color: #063940; border-bottom: 1px solid #0f6c7a;">
        <img src="{{ asset('public/assetsku/img/logotsu.png') }}" alt="TSU Logo" class="brand-image"
             style="opacity: .9">
        <span class="brand-text font-weight-light" style="font-size: 16px; font-weight: bold; color: #f8c12a;">Tiga Serangkai University</span>
    </a>

    <div class="sidebar">

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column text-sm" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-header">Main Navigation</li>
                <x-layouts.sidebar />
            </ul>
        </nav>
    </div>
</aside>
