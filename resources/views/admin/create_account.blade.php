@extends('admin.layouts.main')
@section('title', 'Accounts')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 custom-text-heading">Create new Account</h1>

        <div class="cust-form-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('add-account') }}">
                        <div class="form-group">
                            <label>User: </label>
                            <select class="form-control" id="user-select" name="user_id" required>
                                <option disabled selected value="">--Select User--</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Site Address: </label>
                            <select class="form-control" id="site-select" name="site_id" required disabled>
                                <!-- Filled via AJAX -->
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Account Type: </label>
                            <select class="form-control" name="account_type_id" required>
                                <option disabled selected value="">--Select Account Type--</option>
                                @foreach($accountTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->type }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Account Name (As per bill): </label>
                            <input type="text" class="form-control" placeholder="Enter name as per bill" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Account Number (As per bill): </label>
                            <input type="text" class="form-control" placeholder="Enter account number" name="number" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Account Description: </label>
                            <input type="text" name="optional_info" class="form-control" placeholder="e.g. Main House, Tenant, etc.">
                        </div>

                        <div class="form-group">
                            <label>Bill Day: </label>
                            <input type="number" min="1" max="31" class="form-control" placeholder="Enter bill day (1-31)" name="billing_date" required>
                        </div>
                        
                        <br>
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

            $(document).on("change", '#user-select', function () {
                let user_id = $(this).val();
                
                $.ajax({
                    type: 'GET',
                    dataType: 'JSON',
                    url: '{{ route("get-sites-by-user") }}',
                    data: {user_id: user_id},
                    success: function (result) {
                        $('#site-select').empty();
                        $('#site-select').append('<option disabled selected value="">--Select Site Address--</option>');
                        $.each(result.data, function(key, value) {
                            // FIX: Showing address instead of title
                            $('#site-select').append($('<option>', {
                                value: value.id,
                                text: value.address
                            }));
                        });
                        $('#site-select').prop('disabled', false);
                    },
                    error: function(xhr) {
                        console.log("Error loading sites:", xhr);
                    }
                });
            });
        });
    </script>
@endsection
