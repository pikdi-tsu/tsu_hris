<!-- TSU Quick Search Modal (Command Palette) -->
<div id="tsuQuickSearchModal" class="tsu-quick-search-modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="tsu-quick-search-backdrop" id="tsuQuickSearchBackdrop"></div>
    <div class="tsu-quick-search-box">
        <!-- Search Input Bar -->
        <div class="tsu-search-header">
            <i class="fas fa-search tsu-search-icon"></i>
            <input type="text" id="tsuQuickSearchInput" class="tsu-search-input" placeholder="Ketik nama menu atau fitur... (misal: Surat, KPI, Presensi)" autocomplete="off" spellcheck="false">
            <button type="button" class="tsu-search-clear-btn" id="tsuQuickSearchClear" style="display: none;" title="Hapus teks pencarian">
                <i class="fas fa-times-circle"></i>
            </button>
            <span class="tsu-search-esc-hint"><kbd>ESC</kbd></span>
        </div>

        <!-- Search Results List -->
        <div class="tsu-search-body" id="tsuSearchResultsWrapper">
            <!-- Rendered dynamically by JavaScript -->
        </div>

        <!-- Footer / Keyboard Shortcut Hints -->
        <div class="tsu-search-footer">
            <div class="tsu-search-shortcut-hints">
                <span><kbd>↑</kbd><kbd>↓</kbd> Navigasi</span>
                <span><kbd>↵ Enter</kbd> Buka Menu</span>
                <span><kbd>Esc</kbd> Tutup</span>
            </div>
            <div class="tsu-search-branding">
                <i class="fas fa-bolt text-warning mr-1"></i> TSU Quick Menu
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    // Menu Index Cache & LocalStorage Key (Isolasi per User ID agar riwayat tidak tertukar antar user)
    const RECENT_STORAGE_KEY = 'tsu_hris_recent_menus_{{ auth()->id() ?? 0 }}';
    const MAX_RECENTS = 5;

    let tsuMenuItems = [];
    let isIndexed = false;
    let selectedIndex = 0;

    // DOM Elements
    let $modal = null;
    let $backdrop = null;
    let $input = null;
    let $clearBtn = null;
    let $resultsWrapper = null;
    let $triggerBtn = null;

    // Helper: LocalStorage Management untuk Terakhir Dibuka
    function getRecentMenus() {
        try {
            let data = localStorage.getItem(RECENT_STORAGE_KEY);
            return data ? JSON.parse(data) : [];
        } catch (e) {
            return [];
        }
    }

    function saveRecentMenu(item) {
        if (!item || !item.url) return;
        let recents = getRecentMenus();
        // Hapus duplikat berdasarkan URL
        recents = recents.filter(function(r) {
            return r.url !== item.url;
        });
        // Sisipkan di posisi teratas
        recents.unshift({
            title: item.title,
            url: item.url,
            category: item.category,
            icon: item.icon,
            timestamp: Date.now()
        });
        if (recents.length > MAX_RECENTS) {
            recents = recents.slice(0, MAX_RECENTS);
        }
        try {
            localStorage.setItem(RECENT_STORAGE_KEY, JSON.stringify(recents));
        } catch (e) {}
    }

    function clearRecentMenus() {
        try {
            localStorage.removeItem(RECENT_STORAGE_KEY);
        } catch (e) {}
    }

    function initQuickSearch() {
        $modal = $('#tsuQuickSearchModal');
        $backdrop = $('#tsuQuickSearchBackdrop');
        $input = $('#tsuQuickSearchInput');
        $clearBtn = $('#tsuQuickSearchClear');
        $resultsWrapper = $('#tsuSearchResultsWrapper');
        $triggerBtn = $('#tsuQuickSearchTrigger');

        if (!$modal.length) return;

        // Deteksi platform OS untuk badge shortcut di navbar & modal
        let isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
        if (isMac) {
            $('.kbd-ctrl').text('⌘');
        }

        // Index menu awal dan rekam halaman yang sedang dibuka saat ini
        indexSidebarMenus();

        // Event trigger tombol navbar
        $triggerBtn.on('click', function(e) {
            e.preventDefault();
            openQuickSearch();
        });

        // Event klik backdrop untuk tutup
        $backdrop.on('click', function() {
            closeQuickSearch();
        });

        // Event input pencarian
        $input.on('input', function() {
            let query = $(this).val();
            if (query.trim().length > 0) {
                $clearBtn.show();
            } else {
                $clearBtn.hide();
            }
            renderResults(query);
        });

        // Event tombol clear input
        $clearBtn.on('click', function() {
            $input.val('').focus();
            $clearBtn.hide();
            renderResults('');
        });

        // Delegated click pada tombol Hapus Riwayat
        $resultsWrapper.on('click', '#tsuClearHistoryBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            clearRecentMenus();
            renderResults($input.val());
        });

        // Delegated click pada item hasil pencarian
        $resultsWrapper.on('click', '.tsu-search-item', function(e) {
            e.preventDefault();
            let url = $(this).data('url');
            let itemIndex = $(this).data('index');
            let item = findItemByUrl(url);
            if (item) {
                saveRecentMenu(item);
            }
            if (url) {
                window.location.href = url;
            }
        });

        // Hover sync selection
        $resultsWrapper.on('mouseenter', '.tsu-search-item', function() {
            $resultsWrapper.find('.tsu-search-item').removeClass('is-selected');
            $(this).addClass('is-selected');
            selectedIndex = parseInt($(this).attr('data-index')) || 0;
        });

        // Global Keyboard Shortcut: Ctrl + K (or Cmd + K) & Escape & Navigasi
        $(document).on('keydown', function(e) {
            // Toggle via Ctrl+K atau Cmd+K
            if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
                e.preventDefault();
                if ($modal.hasClass('is-active')) {
                    closeQuickSearch();
                } else {
                    openQuickSearch();
                }
                return;
            }

            // Jika modal sedang terbuka
            if ($modal.hasClass('is-active')) {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    closeQuickSearch();
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    navigateItems(1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    navigateItems(-1);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    let $selected = $resultsWrapper.find('.tsu-search-item.is-selected');
                    if ($selected.length) {
                        let url = $selected.data('url');
                        let item = findItemByUrl(url);
                        if (item) {
                            saveRecentMenu(item);
                        }
                        if (url) {
                            window.location.href = url;
                        }
                    }
                }
            }
        });
    }

    function findItemByUrl(url) {
        if (!url) return null;
        for (let i = 0; i < tsuMenuItems.length; i++) {
            if (tsuMenuItems[i].url === url) return tsuMenuItems[i];
        }
        return null;
    }

    // Index menu dari DOM Sidebar (Zero-Latency, Hak Akses RBAC Otomatis Terpenuhi)
    function indexSidebarMenus() {
        tsuMenuItems = [];
        let seenUrls = {};

        $('.main-sidebar .nav-sidebar a.nav-link').each(function() {
            let $a = $(this);
            let href = $a.attr('href');
            if (!href || href === '#' || href.startsWith('javascript:')) {
                return; // Abaikan folder accordion / link kosong
            }

            // Normalisasi URL
            if (seenUrls[href]) return;
            seenUrls[href] = true;

            // Dapatkan judul menu (bersihkan spasi & teks badge/child icon)
            let $p = $a.find('p').clone();
            $p.find('.nav-indicator, .badge, .right').remove();
            let title = $p.text().trim();
            if (!title) return;

            // Kategori / Breadcrumb dari parent treeview
            let categories = [];
            $a.parents('.nav-treeview').each(function() {
                let $parentLink = $(this).prev('a.nav-link');
                let $parentP = $parentLink.find('p').clone();
                $parentP.find('.nav-indicator, .badge, .right').remove();
                let parentTitle = $parentP.text().trim();
                if (parentTitle) {
                    categories.unshift(parentTitle);
                }
            });

            let category = categories.length ? categories.join(' › ') : 'Menu Utama';

            // Ikon menu
            let iconClass = 'fas fa-circle-notch';
            let $icon = $a.find('.nav-icon');
            if ($icon.length) {
                let cls = $icon.attr('class') || '';
                cls = cls.replace('nav-icon', '').replace('mr-2', '').trim();
                if (cls) iconClass = cls;
            }

            let isActive = $a.hasClass('active');

            let menuItem = {
                title: title,
                url: href,
                category: category,
                icon: iconClass,
                isActive: isActive
            };

            tsuMenuItems.push(menuItem);

            // Jika menu ini adalah halaman yang sedang dibuka saat ini, simpan ke recent menus
            if (isActive) {
                saveRecentMenu(menuItem);
            }
        });

        isIndexed = true;
    }

    function openQuickSearch() {
        if (!isIndexed || tsuMenuItems.length === 0) {
            indexSidebarMenus();
        }

        $modal.addClass('is-active').attr('aria-hidden', 'false');
        $('body').addClass('tsu-search-modal-open');

        // Reset input & render
        $input.val('');
        $clearBtn.hide();
        renderResults('');

        setTimeout(function() {
            $input.focus();
        }, 50);
    }

    function closeQuickSearch() {
        $modal.removeClass('is-active').attr('aria-hidden', 'true');
        $('body').removeClass('tsu-search-modal-open');
    }

    function navigateItems(direction) {
        let $items = $resultsWrapper.find('.tsu-search-item');
        if (!$items.length) return;

        selectedIndex += direction;
        if (selectedIndex < 0) {
            selectedIndex = $items.length - 1;
        } else if (selectedIndex >= $items.length) {
            selectedIndex = 0;
        }

        $items.removeClass('is-selected');
        let $currentItem = $items.filter(`[data-index="${selectedIndex}"]`).addClass('is-selected');

        // Scroll to active item jika di luar view
        if ($currentItem.length) {
            let container = $resultsWrapper[0];
            let item = $currentItem[0];
            let itemTop = item.offsetTop - container.offsetTop;
            let itemBottom = itemTop + item.offsetHeight;
            let containerTop = container.scrollTop;
            let containerBottom = containerTop + container.offsetHeight;

            if (itemTop < containerTop) {
                container.scrollTop = itemTop;
            } else if (itemBottom > containerBottom) {
                container.scrollTop = itemBottom - container.offsetHeight;
            }
        }
    }

    function escapeHtml(text) {
        let map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function highlightText(text, query) {
        if (!query) return escapeHtml(text);
        let escapedText = escapeHtml(text);
        let escapedQuery = escapeHtml(query);
        let regex = new RegExp('(' + escapedQuery.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return escapedText.replace(regex, '<mark class="tsu-highlight">$1</mark>');
    }

    function renderResults(query) {
        query = (query || '').trim().toLowerCase();
        selectedIndex = 0;

        let html = '';
        let globalIndex = 0;

        if (!query) {
            // === KONDISI AWAL (BELUM NGETIK) ===
            // 1. Ambil Menu Terakhir Dibuka dari LocalStorage
            let rawRecents = getRecentMenus();
            let validRecents = [];
            let recentUrls = {};

            rawRecents.forEach(function(r) {
                let found = findItemByUrl(r.url);
                if (found) {
                    validRecents.push({
                        title: found.title,
                        url: found.url,
                        category: found.category,
                        icon: found.icon,
                        isActive: found.isActive,
                        isRecent: true
                    });
                    recentUrls[found.url] = true;
                }
            });

            // Bagian A: Menu Terakhir Dibuka (Jika Ada)
            if (validRecents.length > 0) {
                html += `
                    <div class="tsu-search-section-header">
                        <div class="tsu-search-section-label">
                            <i class="far fa-clock mr-1 text-warning"></i> Terakhir Dibuka
                        </div>
                        <button type="button" class="tsu-search-clear-history-btn" id="tsuClearHistoryBtn" title="Hapus riwayat menu terakhir dibuka">
                            <i class="fas fa-trash-alt mr-1"></i> Hapus Riwayat
                        </button>
                    </div>
                `;

                validRecents.forEach(function(item) {
                    let isSelectedClass = globalIndex === 0 ? 'is-selected' : '';
                    let badgeHtml = item.isActive 
                        ? '<span class="tsu-item-active-badge">Sedang Dibuka</span>' 
                        : '<span class="tsu-item-recent-badge"><i class="far fa-clock mr-1"></i> Riwayat</span>';

                    html += `
                        <a href="${item.url}" class="tsu-search-item ${isSelectedClass}" data-index="${globalIndex}" data-url="${item.url}">
                            <div class="tsu-search-item-icon">
                                <i class="${item.icon}"></i>
                            </div>
                            <div class="tsu-search-item-info">
                                <div class="tsu-search-item-title">
                                    ${escapeHtml(item.title)}
                                    ${badgeHtml}
                                </div>
                                <div class="tsu-search-item-category">
                                    ${escapeHtml(item.category)}
                                </div>
                            </div>
                            <div class="tsu-search-item-action">
                                <span class="tsu-item-enter-hint"><kbd>↵</kbd></span>
                            </div>
                        </a>
                    `;
                    globalIndex++;
                });
            }

            // Bagian B: Menu Rekomendasi / Menu Lainnya
            let otherItems = tsuMenuItems.filter(function(item) {
                return !recentUrls[item.url];
            }).slice(0, validRecents.length > 0 ? 6 : 10);

            if (otherItems.length > 0) {
                let otherLabel = validRecents.length > 0 
                    ? '<i class="fas fa-compass mr-1 text-info"></i> Menu Rekomendasi' 
                    : '<i class="fas fa-star mr-1 text-warning"></i> Menu Rekomendasi & Akses Cepat';

                html += `<div class="tsu-search-section-label ${validRecents.length > 0 ? 'mt-2' : ''}">${otherLabel}</div>`;

                otherItems.forEach(function(item) {
                    let isSelectedClass = globalIndex === 0 ? 'is-selected' : '';
                    let badgeHtml = item.isActive ? '<span class="tsu-item-active-badge">Sedang Dibuka</span>' : '';

                    html += `
                        <a href="${item.url}" class="tsu-search-item ${isSelectedClass}" data-index="${globalIndex}" data-url="${item.url}">
                            <div class="tsu-search-item-icon">
                                <i class="${item.icon}"></i>
                            </div>
                            <div class="tsu-search-item-info">
                                <div class="tsu-search-item-title">
                                    ${escapeHtml(item.title)}
                                    ${badgeHtml}
                                </div>
                                <div class="tsu-search-item-category">
                                    ${escapeHtml(item.category)}
                                </div>
                            </div>
                            <div class="tsu-search-item-action">
                                <span class="tsu-item-enter-hint"><kbd>↵</kbd></span>
                            </div>
                        </a>
                    `;
                    globalIndex++;
                });
            }
        } else {
            // === KONDISI SEDANG MENGETIK KATA KUNCI ===
            let filtered = tsuMenuItems.filter(function(item) {
                let matchTitle = item.title.toLowerCase().indexOf(query) !== -1;
                let matchCategory = item.category.toLowerCase().indexOf(query) !== -1;
                return matchTitle || matchCategory;
            });

            // Ranking: Yang judulnya diawali kata kunci ditaruh di atas
            filtered.sort(function(a, b) {
                let aStarts = a.title.toLowerCase().indexOf(query) === 0 ? 1 : 0;
                let bStarts = b.title.toLowerCase().indexOf(query) === 0 ? 1 : 0;
                if (aStarts !== bStarts) return bStarts - aStarts;

                let aTitleMatch = a.title.toLowerCase().indexOf(query) !== -1 ? 1 : 0;
                let bTitleMatch = b.title.toLowerCase().indexOf(query) !== -1 ? 1 : 0;
                return bTitleMatch - aTitleMatch;
            });

            if (filtered.length === 0) {
                $resultsWrapper.html(`
                    <div class="tsu-search-empty">
                        <div class="tsu-empty-icon"><i class="fas fa-search-minus"></i></div>
                        <div class="tsu-empty-title">Menu tidak ditemukan</div>
                        <div class="tsu-empty-desc">Tidak ada menu yang sesuai dengan kata kunci "<strong>${escapeHtml(query)}</strong>". Coba kata kunci lain atau periksa hak akses menu Anda.</div>
                    </div>
                `);
                return;
            }

            html += `<div class="tsu-search-section-label"><i class="fas fa-search mr-1 text-info"></i> Hasil Pencarian (${filtered.length} menu)</div>`;

            filtered.forEach(function(item) {
                let isSelectedClass = globalIndex === 0 ? 'is-selected' : '';
                let activeBadge = item.isActive ? '<span class="tsu-item-active-badge">Sedang Dibuka</span>' : '';

                html += `
                    <a href="${item.url}" class="tsu-search-item ${isSelectedClass}" data-index="${globalIndex}" data-url="${item.url}">
                        <div class="tsu-search-item-icon">
                            <i class="${item.icon}"></i>
                        </div>
                        <div class="tsu-search-item-info">
                            <div class="tsu-search-item-title">
                                ${highlightText(item.title, query)}
                                ${activeBadge}
                            </div>
                            <div class="tsu-search-item-category">
                                ${highlightText(item.category, query)}
                            </div>
                        </div>
                        <div class="tsu-search-item-action">
                            <span class="tsu-item-enter-hint"><kbd>↵</kbd></span>
                        </div>
                    </a>
                `;
                globalIndex++;
            });
        }

        $resultsWrapper.html(html);
    }

    // Inisialisasi saat DOM siap
    $(document).ready(function() {
        initQuickSearch();
    });
})();
</script>
