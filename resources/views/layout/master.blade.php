<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'InternTrack')</title>

    {{-- Global Fonts --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />

    {{-- Vendor Styles (only if needed per page via @push) --}}
    <link href="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
        type="text/css" />

    {{-- Global Styles --}}
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <!-- Bootstrap Icons for theme toggle -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Sidebar footer should blend with sidebar background */
        .app-sidebar .app-sidebar-footer {
            background: var(--bs-app-sidebar-bg);
            border-top: 1px dashed rgba(255,255,255,.08);
        }
        .app-sidebar .app-sidebar-footer .menu-link {
            display: flex;
            align-items: center;
            gap: .5rem;
            color: var(--bs-gray-300);
            padding: .5rem .75rem;
            border-radius: .475rem;
            background: transparent !important;
        }
        .app-sidebar .app-sidebar-footer .menu-link:hover {
            background: rgba(255,255,255,.05) !important;
            color: #ffffff;
        }
    </style>

    @stack('styles')
    @yield('extra_css')
</head>

<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true"
    data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true"
    data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true"
    data-kt-app-sidebar-push-footer="true" class="app-default">
    <!--begin::Theme mode setup on page load-->
    <script>
        var defaultThemeMode = "dark";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>
    <!--end::Theme mode setup on page load-->
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            <!--begin::Header-->
            <div id="kt_app_header" class="app-header bg-body">
                <header class="app-header py-4 px-6 border-bottom w-100 bg-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <!-- Mobile sidebar toggle -->
                            <button id="kt_app_sidebar_mobile_toggle" type="button" aria-label="Toggle sidebar"
                                    class="btn btn-icon btn-light d-inline-flex d-lg-none">
                                <i class="bi bi-list fs-2"></i>
                            </button>
                            <a href="/" class="fs-4 fw-bold text-decoration-none">InternTrack</a>
                            <span class="text-muted">@yield('page_heading')</span>
                        </div>
                        <div class="d-flex align-items-center">
                            {{-- Placeholder for user menu / actions --}}
                        </div>
                    </div>
                </header>
            </div>
            <!--end::Header-->

            <div class="app-wrapper d-flex flex-column flex-row-fluid" id="kt_app_wrapper">
                {{-- Sidebar by role --}}
                <aside id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true"
                    data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}"
                    data-kt-drawer-overlay="true" data-kt-drawer-width="225px" data-kt-drawer-direction="start"
                    data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
                    @auth
                        @php($role = auth()->user()->role)
                        @includeWhen($role === 'company_admin', 'layout.sidebar.admin_company')
                        @includeWhen($role === 'pembina', 'layout.sidebar.pembina')
                        @includeWhen($role === 'dosen', 'layout.sidebar.dosen')
                        @includeWhen($role === 'mahasiswa', 'layout.sidebar.mahasiswa')
                    @endauth
                    @guest
                        {{-- Sidebar for guests --}}
                    @endguest

                    <!--begin::Sidebar footer: Theme Toggle (global for all roles)-->
                    <div class="app-sidebar-footer border-top py-4 px-4 position-sticky bottom-0 start-0 end-0">
                        <a id="themeToggle" class="menu-link w-100" href="#" onclick="event.preventDefault();">
                            <i id="themeIcon" class="bi bi-moon-stars"></i>
                            <span id="themeLabel" class="menu-title">Dark Mode</span>
                        </a>
                    </div>
                    <!--end::Sidebar footer-->
                </aside>

                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">

                    {{-- Content --}}
                    <main class="app-content p-6">
                        @yield('content')
                    </main>

                    {{-- Footer --}}
                    <footer class="app-footer py-4 px-6 border-top">
                        <div class="text-muted">&copy; {{ date('Y') }} InternTrack</div>
                    </footer>
                </div>
            </div>
        </div>
    </div>

    {{-- Global Scripts --}}
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>

    {{-- Vendor Scripts (optional per page) --}}
    <script src="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>

    @stack('scripts')
    @yield('extra_js')

    <script>
        (function(){
            function currentTheme(){
                return document.documentElement.getAttribute('data-bs-theme') || 'light';
            }
            function applyToToggle(toggle, theme){
                const icon = toggle.querySelector('.js-theme-icon, #themeIcon');
                const label = toggle.querySelector('.js-theme-label, #themeLabel');
                if (icon) {
                    // preserve helper class if present
                    const base = (icon.className.indexOf('js-theme-icon') >= 0) ? 'js-theme-icon ' : '';
                    icon.className = base + (theme === 'dark' ? 'bi bi-moon-stars' : 'bi bi-brightness-high');
                }
                if (label) {
                    label.textContent = (theme === 'dark') ? 'Dark Mode' : 'Light Mode';
                }
            }
            function updateAll(theme){
                document.querySelectorAll('.js-theme-toggle, #themeToggle').forEach(t => applyToToggle(t, theme));
            }
            // initial apply (DOM ready enough at end of body)
            updateAll(currentTheme());
            // delegate click for both selectors
            document.addEventListener('click', function(e){
                const anchor = e.target.closest('.js-theme-toggle, #themeToggle');
                if (!anchor) return;
                e.preventDefault();
                const cur = currentTheme();
                const next = cur === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-bs-theme', next);
                try { localStorage.setItem('data-bs-theme', next); } catch (e) {}
                updateAll(next);
            });
        })();
    </script>

    <script>
        // Ensure all tables are responsive on mobile by wrapping with .table-responsive if missing
        (function(){
            function wrapResponsive(root){
                const tables = (root || document).querySelectorAll('table');
                tables.forEach(tb => {
                    if (tb.closest('.table-responsive')) return; // already wrapped
                    // Skip DataTables internal scroll containers
                    if (tb.closest('.dataTables_scroll')) return;
                    const wrapper = document.createElement('div');
                    wrapper.className = 'table-responsive';
                    tb.parentNode.insertBefore(wrapper, tb);
                    wrapper.appendChild(tb);
                    // ensure full width
                    tb.classList.add('w-100');
                });
            }
            // Initial wrap
            wrapResponsive(document);
            // Re-wrap when modals open (modal content may contain tables)
            document.addEventListener('shown.bs.modal', function(e){
                wrapResponsive(e.target);
            });
        })();
    </script>
</body>

</html>
