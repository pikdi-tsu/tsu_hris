<style>
    /* Reset & Spacing */
    .nav-sidebar .nav-treeview {
        padding-left: 0;
        margin-left: 0;
    }

    .nav-sidebar .nav-link {
        display: flex;
        align-items: center;
        padding-left: var(--nav-pad-left, 0.8rem) !important;
        padding-right: 0.8rem;
        border-radius: 8px;
        margin: 2px 6px;
        transition: background-color 0.2s ease, color 0.2s ease;
        color: #d1e7ea;
    }

    .nav-sidebar .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1) !important;
        color: #ffffff !important;
    }

    /* Styling indikator (chevron / minus) */
    .nav-indicator {
        font-size: 0.72rem;
        width: 1rem;
        text-align: center;
        transition: transform 0.3s ease;
        color: #8bbec6;
        flex-shrink: 0;
    }

    .nav-indicator.fa-minus {
        font-size: 0.55rem;
        opacity: 0.6;
    }

    /* Menu Open */
    .nav-sidebar .nav-item.menu-open > .nav-link {
        color: #f8c12a !important;
    }
    .nav-sidebar .nav-item.menu-open > .nav-link .nav-indicator.fa-chevron-right {
        transform: rotate(90deg);
        color: #f8c12a;
    }

    /* Active State for Sidebar */
    .nav-sidebar .nav-link.active {
        background: linear-gradient(135deg, #0e6a77 0%, #167e8d 100%) !important;
        color: #f8c12a !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }
    .nav-sidebar .nav-link.active > .nav-indicator {
        color: #f8c12a !important;
        opacity: 1;
    }
    .nav-sidebar .nav-link.active > .nav-icon {
        color: #f8c12a !important;
    }

    .nav-sidebar .nav-link > .nav-icon {
        margin-left: 0 !important;
        margin-right: 0.65rem !important;
        font-size: 1rem;
        width: 1.25rem;
        text-align: center;
        flex-shrink: 0;
    }

    /* ============================================================
       SIDEBAR COLLAPSED / HIDE (MODE MINI)
       Tampilan ramping, presisi, dan terpusat di tengah
       ============================================================ */
    body.sidebar-collapse:not(.sidebar-focused) .main-sidebar:not(:hover) .nav-indicator {
        display: none !important;
    }

    body.sidebar-collapse:not(.sidebar-focused) .main-sidebar:not(:hover) .nav-link p {
        display: none !important;
    }

    body.sidebar-collapse:not(.sidebar-focused) .main-sidebar:not(:hover) .nav-treeview {
        display: none !important;
    }

    body.sidebar-collapse:not(.sidebar-focused) .main-sidebar:not(:hover) .nav-sidebar .nav-link {
        padding: 0 !important;
        margin: 4px auto !important;
        width: 42px !important;
        height: 40px !important;
        justify-content: center !important;
        align-items: center !important;
        border-radius: 8px !important;
    }

    body.sidebar-collapse:not(.sidebar-focused) .main-sidebar:not(:hover) .nav-sidebar .nav-link > .nav-icon {
        margin: 0 !important;
        font-size: 1.15rem !important;
        width: auto !important;
        text-align: center !important;
    }

    body.sidebar-collapse:not(.sidebar-focused) .main-sidebar:not(:hover) .nav-header {
        display: none !important;
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
