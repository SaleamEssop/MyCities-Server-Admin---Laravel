@extends('admin.layouts.main')
@section('title', 'Users')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 text-gray-800">Create new Account</h1>

        <div class="cust-form-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('add-account') }}">
                        <div class="form-group">
                            <select class="form-control" id="exampleFormControlSelect1" name="site_id" required>
                                <option disabled selected value="">--Select Site--</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}">{{ $site->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Enter account title" name="title" required>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Enter account number" name="number" required>
                        </div>
                        <div class="form-group">
                            <input type="text" name="optional_info" class="form-control" required placeholder="Enter optional information">
                        </div>
                        @csrf
                        <button type="submit" class="btn btn-primary">Submit</button>
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
