@extends('admin.layouts.main')
@section('title', 'Users')
<style>
    .form-check-inline {
        margin-right: 3.75rem !important;
    }
</style>
@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 custom-text-heading">Create new User</h1>

        <div class="cust-form-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('add-user') }}">
                        @csrf
                        <div class="form-group">
                            <label><strong>Name :</strong></label>
                            <input type="text" class="form-control" value="{{ old('name') }}" placeholder="Enter name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label><strong>Contact Number :</strong></label>
                            <input type="text" class="form-control" value="{{ old('contact_number') }}" placeholder="Enter contact number" name="contact_number" required>
                        </div>
                        <div class="form-group">
                            <label><strong>Email :</strong></label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" id="exampleInputEmail1" required aria-describedby="emailHelp" placeholder="Enter email">
                        </div>
                        <div class="form-group">
                            <label><strong>Password :</strong></label>
                            <input type="password" name="password" class="form-control" required id="exampleInputPassword1" placeholder="Password">
                        </div>
                        <div class="form-group">
                            <label><strong>Select Role:</strong></label><br>
                            <div class="d-flex">
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" class="form-check-input" name="is_admin" value="1" {{ old('is_admin') ? 'checked' : '' }}>
                                    <label class="form-check-label">Admin</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" class="form-check-input" name="is_super_admin" value="1" {{ old('is_super_admin') ? 'checked' : '' }}>
                                    <label class="form-check-label">Super Admin</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" class="form-check-input" name="is_property_manager" value="1" {{ old('is_property_manager') ? 'checked' : '' }}>
                                    <label class="form-check-label">Property Manager</label>
                                </div>
                            </div>
                        </div>
                      
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
