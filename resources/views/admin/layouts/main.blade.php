<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>LightsAndWater - @yield('title', 'Main')</title>

    <!-- Custom fonts for this template-->
    <link href="{{ url('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ url('/css/main.css')  }}" rel="stylesheet">
    <link href="{{ url('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
</head>

<body id="page-top">

<!-- Page Wrapper -->
<div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

        <!-- Sidebar - Brand -->
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('admin/') }}">
            <img src="{{ \Illuminate\Support\Facades\URL::to(\Illuminate\Support\Facades\Storage::url('public/images/logo.PNG')) }}" alt="logo-img" width="100%" />
        </a>

        <!-- Divider -->
        <hr class="sidebar-divider my-0">

        <!-- Nav Item - Dashboard -->
        <li class="nav-item active">
            <a class="nav-link" href="{{ url('admin/') }}">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span></a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Heading -->
        <div class="sidebar-heading">
            Interface
        </div>

        <!-- Nav Item - Pages Collapse Menu -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
               aria-expanded="true" aria-controls="collapseTwo">
                <i class="fas fa-fw fa-cog"></i>
                <span>Users Management</span>
            </a>
            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded cust-sidebar-bg">
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('show-users') }}">List</a>
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('add-user-form') }}">Add</a>
                    {{--<a class="collapse-item" href="cards.html">Cards</a>--}}
                </div>
            </div>
        </li>

        <!-- Nav Item - Utilities Collapse Menu -->
        

        <!-- Nav Item - Utilities Collapse Menu -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSites"
               aria-expanded="true" aria-controls="collapseSites">
                <i class="fas fa-fw fa-location-arrow"></i>
                <span>Sites Management</span>
            </a>
            <div id="collapseSites" class="collapse" aria-labelledby="headingUtilities"
                 data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('sites-list') }}">List</a>
                    <a class="collapse-item" href="{{ route('add-site-form') }}">Add</a>
                </div>
            </div>
        </li>

        <!-- Nav Item - Utilities Collapse Menu -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
               aria-expanded="true" aria-controls="collapseUtilities">
                <i class="fas fa-fw fa-wrench"></i>
                <span>Accounts Management</span>
            </a>
            <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
                 data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('account-list') }}">List</a>
                    <a class="collapse-item" href="{{ route('add-account-form') }}">Add</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMeter"
               aria-expanded="true" aria-controls="collapseMeter">
                <i class="fas fa-fw fa-meteor"></i>
                <span>Meters Management</span>
            </a>
            <div id="collapseMeter" class="collapse" aria-labelledby="headingUtilities"
                 data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('meters-list') }}">List</a>
                    <a class="collapse-item" href="{{ route('add-meter-form') }}">Add</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReadings"
               aria-expanded="true" aria-controls="collapseReadings">
                <i class="fas fa-fw fa-book-reader"></i>
                <span>Meter Readings</span>
            </a>
            <div id="collapseReadings" class="collapse" aria-labelledby="headingUtilities"
                 data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('readings-list') }}">List</a>
                    <a class="collapse-item" href="{{ route('add-readings-form') }}">Add</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAds"
               aria-expanded="true" aria-controls="collapseAds">
                <i class="fas fa-fw fa-book-reader"></i>
                <span>Ads Management</span>
            </a>
            <div id="collapseAds" class="collapse" aria-labelledby="headingUtilities"
                 data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('category-list') }}">Categories</a>
                    <a class="collapse-item" href="{{ route('ads-list') }}">Ads</a>
                </div>
            </div>
        </li>
        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Heading -->
        <div class="sidebar-heading">
            formula Cost
        </div>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseRegions"
               aria-expanded="true" aria-controls="collapseRegions">
                <i class="fas fa-fw fa-location-arrow"></i>
                <span>Regions Management</span>
            </a>
            <div id="collapseRegions" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded cust-sidebar-bg">
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('regions-list') }}">List</a>
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('add-region-form') }}">Add</a>
                </div>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAccountType"
               aria-expanded="true" aria-controls="collapseAccountType">
                <i class="fas fa-fw fa-location-arrow"></i>
                <span>AccountType</span>
            </a>
            <div id="collapseAccountType" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded cust-sidebar-bg">
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('account-type-list') }}">List</a>
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('add-account-type-form') }}">Add</a>
                </div>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseRegionsCost"
               aria-expanded="true" aria-controls="collapseRegionsCost">
                <i class="fas fa-fw fa-location-arrow"></i>
                <span>Regions Cost</span>
            </a>
            <div id="collapseRegionsCost" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded cust-sidebar-bg">
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('region-cost') }}">List</a>
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('region-cost-create') }}">Add</a>
                </div>
            </div>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Heading -->
        <div class="sidebar-heading">
            Addons
        </div>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('default-costs') }}">
                <i class="fas fa-fw fa-money-check"></i>
                <span>Default Costs</span></a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('alarms') }}">
                <i class="fas fa-fw fa-clock"></i>
                <span>Alarms</span></a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('tc') }}">
                <i class="fas fa-fw fa-money-check"></i>
                <span>Terms & Conditions</span></a>
        </li>
        <!-- Divider -->
        <hr class="sidebar-divider d-none d-md-block">

        <!-- Sidebar Toggler (Sidebar) -->
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
    </ul>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content">

            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                <!-- Sidebar Toggle (Topbar) -->
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>

                <!-- Topbar Search -->
                {{--<form
                    class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                    <div class="input-group">
                        <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                               aria-label="Search" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>--}}

                <!-- Topbar Navbar -->
                <ul class="navbar-nav ml-auto">

                    <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                    <li class="nav-item dropdown no-arrow d-sm-none">
                        <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-search fa-fw"></i>
                        </a>
                        <!-- Dropdown - Messages -->
                        <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                             aria-labelledby="searchDropdown">
                            <form class="form-inline mr-auto w-100 navbar-search">
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light border-0 small"
                                           placeholder="Search for..." aria-label="Search"
                                           aria-describedby="basic-addon2">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button">
                                            <i class="fas fa-search fa-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </li>

                    <div class="topbar-divider d-none d-sm-block"></div>

                    <!-- Nav Item - User Information -->
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small">Douglas McGee</span>
                            <img class="img-profile rounded-circle"
                                 src="{{ url('img/undraw_profile.svg') }}">
                        </a>
                        <!-- Dropdown - User Information -->
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                             aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                Profile
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Logout
                            </a>
                        </div>
                    </li>

                </ul>

            </nav>
            <!-- End of Topbar -->
            <div class="cust-alert-wrapper">
                @if(\Illuminate\Support\Facades\Session::has('alert-message'))
                    <p class="alert {{ \Illuminate\Support\Facades\Session::get('alert-class', 'alert-info') }}">{{ \Illuminate\Support\Facades\Session::get('alert-message') }}</p>
                @endif
            </div>
            @yield('content')
        </div>
        <!-- End of Main Content -->
        <!-- Footer -->
        <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>Copyright &copy; Lights and Water 2022</span>
                </div>
            </div>
        </footer>
        <!-- End of Footer -->

    </div>
    <!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <a class="btn btn-primary" href="login.html">Logout</a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript-->
<script src="{{ url('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ url('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<!-- Core plugin JavaScript-->
<script src="{{ url('vendor/jquery-easing/jquery.easing.min.js') }}"></script>

<!-- Custom scripts for all pages-->
<script src="{{ url('js/sb-admin-2.min.js') }}"></script>

<!-- Page level plugins -->
{{--<script src="{{ url('vendor/chart.js/Chart.min.js') }}"></script>--}}

<!-- Page level custom scripts -->
{{--
<script src="{{ url('js/demo/chart-area-demo.js') }}"></script>
<script src="{{ url('js/demo/chart-pie-demo.js') }}"></script>
--}}
<!-- Page level plugins -->
<script src="{{ url('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ url('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

@yield('page-level-scripts')
</body>

</html>
