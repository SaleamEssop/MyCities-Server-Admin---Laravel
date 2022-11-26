@extends('admin.layouts.main')
@section('title', 'Accounts')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 text-gray-800">Accounts</h1>

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">List of accounts under sites added by users</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="acc-dataTable" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Site</th>
                            <th>Account Name</th>
                            <th>Account Number</th>
                            <th>Optional Information</th>
                            <th>Created Date</th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Site</th>
                            <th>Account Name</th>
                            <th>Account Number</th>
                            <th>Optional Information</th>
                            <th>Created Date</th>
                        </tr>
                        </tfoot>
                        <tbody>
                        @foreach($accounts as $account)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $account->site->title }}</td>
                                <td>{{ $account->account_name }}</td>
                                <td>{{ $account->account_number }}</td>
                                <td>{{ $account->optional_information }}</td>
                                <td>{{ $account->created_at }}</td>
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
            $('#acc-dataTable').dataTable();
        });
    </script>
@endsection
