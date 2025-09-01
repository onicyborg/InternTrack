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
            <div id="kt_app_header" class="app-header">
                <header class="app-header py-4 px-6 border-bottom w-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
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
</body>

</html>
