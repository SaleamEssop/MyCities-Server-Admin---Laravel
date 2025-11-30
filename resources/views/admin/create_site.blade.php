@extends('admin.layouts.main')
@section('title', 'Users')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 custom-text-heading">Create new site</h1>

        <div class="cust-form-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('add-site') }}">
                        <div class="form-group">
                            <label><strong>Region:</strong></label>
                            <select class="form-control" id="exampleFormControlSelect1" name="region_id" required>
                                <option disabled selected value="">--Select Region--</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label><strong>User:</strong></label>
                            <select class="form-control" id="exampleFormControlSelect1" name="user_id" required>
                                <option disabled selected value="">--Select User--</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label><strong>Title:</strong></label>
                            <input type="text" class="form-control" placeholder="Enter title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label><strong>Latitude:</strong></label>
                            <input type="text" class="form-control" placeholder="Enter lat" name="lat" required>
                        </div>
                        <div class="form-group">
                            <label><strong>Longitude:</strong></label>
                            <input type="text" name="lng" class="form-control" id="exampleInputEmail1" required aria-describedby="emailHelp" placeholder="Enter lng">
                        </div>
                        <div class="form-group">
                            <label><strong>Email:</strong></label>
                            <input type="email" name="email" class="form-control" id="exampleInputPassword1" placeholder="Enter email">
                        </div>
                        <div class="form-group">
                            <label><strong>Address:</strong></label>
                            <textarea name="address" placeholder="Enter address" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <label><strong>Billing Type:</strong></label>
                            <select class="form-control" name="billing_type" required>
                                <option value="monthly" selected>Monthly</option>
                                <option value="date-to-date">Date-to-Date</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><strong>Site Username:</strong></label>
                            <input type="text" class="form-control" placeholder="Enter site username" name="site_username">
                        </div>
                        <div class="form-group">
                            <label><strong>Site Password:</strong></label>
                            <input type="password" class="form-control" placeholder="Enter site password" name="site_password">
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
