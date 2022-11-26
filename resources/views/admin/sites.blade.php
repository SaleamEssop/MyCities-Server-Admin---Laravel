@extends('admin.layouts.main')
@section('title', 'Accounts')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 text-gray-800">Sites</h1>

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">List of sites added by users</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="sites-dataTable" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Title</th>
                            <th>Lat</th>
                            <th>Lng</th>
                            <th>Address</th>
                            <th>Email</th>
                            <th>Created Date</th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Title</th>
                            <th>Lat</th>
                            <th>Lng</th>
                            <th>Address</th>
                            <th>Email</th>
                            <th>Created Date</th>
                        </tr>
                        </tfoot>
                        <tbody>
                        @foreach($sites as $site)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $site->user->name }}</td>
                                <td>{{ $site->title }}</td>
                                <td>{{ $site->lat }}</td>
                                <td>{{ $site->lng }}</td>
                                <td>{{ $site->address }}</td>
                                <td>{{ $site->email }}</td>
                                <td>{{ $site->created_at }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
@endsection

@section('page-level-scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#sites-dataTable').dataTable();
        });
    </script>
@endsection
