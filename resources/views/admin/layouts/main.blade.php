<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>LightsAndWater - @yield('title', 'Main')</title>

    <!-- Custom fonts for this template-->
    <link href="{{ url('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ url('/css/main.css')  }}" rel="stylesheet">
    <!-- NEW: Professional Theme Override -->
    <link href="{{ url('/css/custom-admin.css')  }}" rel="stylesheet">
    
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

        <!-- Nav Item - Users -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
               aria-expanded="true" aria-controls="collapseTwo">
                <i class="fas fa-fw fa-cog"></i>
                <span>Users</span>
            </a>
            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded cust-sidebar-bg">
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('show-users') }}">List</a>
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('add-user-form') }}">Add</a>
                </div>
            </div>
        </li>

        <!-- Nav Item - Sites -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSites"
               aria-expanded="true" aria-controls="collapseSites">
                <i class="fas fa-fw fa-location-arrow"></i>
                <span>Sites</span>
            </a>
            <div id="collapseSites" class="collapse" aria-labelledby="headingUtilities"
                 data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded cust-sidebar-bg">
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('show-sites') }}">List</a>
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('create-site-form') }}">Add</a>
                </div>
            </div>
        </li>
        
        <!-- Nav Item - User Accounts -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAccounts"
               aria-expanded="true" aria-controls="collapseAccounts">
                <i class="fas fa-fw fa-wrench"></i>
                <span>User Accounts</span>
            </a>
            <div id="collapseAccounts" class="collapse" aria-labelledby="headingUtilities"
                 data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded cust-sidebar-bg">
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('account-list') }}">List</a>
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('add-account-form') }}">Add</a>
                </div>
            </div>
        </li>

        <!-- Nav Item - Meters (Restored) -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMeters"
               aria-expanded="true" aria-controls="collapseMeters">
                <i class="fas fa-fw fa-wrench"></i>
                <span>Meters</span>
            </a>
            <div id="collapseMeters" class="collapse" aria-labelledby="headingUtilities"
                 data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded cust-sidebar-bg">
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('meters-list') }}">List</a>
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('add-meter-form') }}">Add</a>
                </div>
            </div>
        </li>

        <!-- Nav Item - Readings (Restored) -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReadings"
               aria-expanded="true" aria-controls="collapseReadings">
                <i class="fas fa-fw fa-wrench"></i>
                <span>Readings</span>
            </a>
            <div id="collapseReadings" class="collapse" aria-labelledby="headingUtilities"
                 data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded cust-sidebar-bg">
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('meter-reading-list') }}">List</a>
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('add-meter-reading-form') }}">Add</a>
                </div>
            </div>
        </li>

        <!-- NEW: Nav Item - Payments -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePayments"
               aria-expanded="true" aria-controls="collapsePayments">
                <i class="fas fa-fw fa-dollar-sign"></i>
                <span>Payments</span>
            </a>
            <div id="collapsePayments" class="collapse" aria-labelledby="headingUtilities"
                 data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded cust-sidebar-bg">
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('payments-list') }}">History</a>
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('add-payment-form') }}">Record Payment</a>
                </div>
            </div>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Heading -->
        <div class="sidebar-heading">
            Configuration
        </div>

        <!-- Nav Item - Regions -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseRegions"
               aria-expanded="true" aria-controls="collapseRegions">
                <i class="fas fa-fw fa-location-arrow"></i>
                <span>Regions</span>
            </a>
            <div id="collapseRegions" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded cust-sidebar-bg">
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('regions-list') }}">List</a>
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('add-region-form') }}">Add</a>
                </div>
            </div>
        </li>
        
        <!-- Nav Item - Account Types -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAccountType"
               aria-expanded="true" aria-controls="collapseAccountType">
                <i class="fas fa-fw fa-location-arrow"></i>
                <span>Account Types</span>
            </a>
            <div id="collapseAccountType" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded cust-sidebar-bg">
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('account-type-list') }}">List</a>
                    <a class="collapse-item cust-sidebar-sub" href="{{ route('add-account-type-form') }}">Add</a>
                </div>
            </div>
        </li>

        <!-- Nav Item - Region Costs -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseRegionsCost"
               aria-expanded="true" aria-controls="collapseRegionsCost">
                <i class="fas fa-fw fa-location-arrow"></i>
                <span>Region Costs</span>
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
        <li class="nav-item">
            <a class="nav-link" href="{{ route('alarms') }}">
                <i class="fas fa-fw fa-clock"></i>
                <span>Alarms</span></a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.logout') }}">
                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                <span>Logout</span></a>
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

                <!-- Topbar Navbar -->
                <ul class="navbar-nav ml-auto">

                    <div class="topbar-divider d-none d-sm-block"></div>

                    <!-- Nav Item - User Information -->
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ isset(auth()->user()->name) ? auth()->user()->name : 'Admin' }}</span>
                            <img class="img-profile rounded-circle"
                                 src="{{ url('img/undraw_profile.svg') }}">
                        </a>
                        <!-- Dropdown - User Information -->
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                             aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="{{ route('admin.logout') }}" data-toggle="modal" data-target="#logoutModal">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Logout
                            </a>
                        </div>
                    </li>

                </ul>

            </nav>
            <!-- End of Topbar -->

            <!-- Begin Page Content -->
            <div class="container-fluid">
                @if(\Illuminate\Support\Facades\Session::has('alert-message'))
                    <p class="alert {{ \Illuminate\Support\Facades\Session::get('alert-class', 'alert-info') }}">{{ \Illuminate\Support\Facades\Session::get('alert-message') }}</p>
                @endif
                @yield('content')
            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- End of Main Content -->

        <!-- Footer -->
        <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>Copyright &copy; LightsAndWater 2021</span>
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
                <a class="btn btn-primary" href="{{ route('admin.logout') }}">Logout</a>
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
<script src="{{ url('vendor/chart.js/Chart.min.js') }}"></script>

<!-- Page level custom scripts -->
<script src="{{ url('js/demo/chart-area-demo.js') }}"></script>
<script src="{{ url('js/demo/chart-pie-demo.js') }}"></script>
<!-- Page level plugins -->
<script src="{{ url('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ url('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

<!-- Page level custom scripts -->
<script src="{{ url('js/demo/datatables-demo.js') }}"></script>
<script>
    $(".alert").delay(4000).slideUp(200, function() {
        $(this).alert('close');
    });
</script>
@yield('script')
</body>

</html>
