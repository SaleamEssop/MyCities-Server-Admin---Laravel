@extends('admin.layouts.main')
@section('title', 'Add Account Type')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 custom-text-heading">Add Account Type</h1>

        <div class="cust-form-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('add-account-type') }}">
                        <div class="form-group">
                            <label><strong>Account Type Name :</strong></label>
                            <input placeholder="Enter account type name (e.g., Domestic, Commercial)" type="text" class="form-control" name="name" required />
                        </div>
                        <hr>
                        @csrf
                        <button type="submit" class="btn btn-warning">Create</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
@endsection

@section('page-level-scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#user-dataTable').dataTable();
        });
    </script>
@endsection
