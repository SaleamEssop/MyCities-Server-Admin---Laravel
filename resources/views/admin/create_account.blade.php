@extends('admin.layouts.main')
@section('title', 'Accounts')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-2 custom-text-heading">Create New Account</h1>
        <div class="cust-form-wrapper">
            <div class="row">
                <div class="col-md-8">
                    <form method="POST" action="{{ route('add-account') }}">
                        
                        <!-- 1. User Selection -->
                        <div class="form-group">
                            <label>User (Account Owner): </label>
                            <select class="form-control" id="user-select" name="user_id" required>
                                <option disabled selected value="">--Select User--</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 2. Location Address (Site) -->
                        <div class="form-group">
                            <label>Location Address: </label>
                            <select class="form-control" id="site-select" name="site_id" required disabled>
                                <option disabled selected value="">--Select User First--</option>
                            </select>
                        </div>

                        <!-- 3. Region (Auto-Detected) -->
                        <div class="form-group">
                            <label>Region (Auto-Detected): </label>
                            <input type="text" id="region-display" class="form-control" placeholder="Select Location first..." readonly>
                        </div>

                        <!-- 4. Account Type -->
                        <div class="form-group">
                            <label>Account Type: </label>
                            <select class="form-control" name="account_type_id" required>
                                <option disabled selected value="">--Select Account Type--</option>
                                @foreach($accountTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->type }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 5. Emails (Auto-Fetched from Region) -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Water Email (Auto): </label>
                                    <input type="text" id="water-email" class="form-control" readonly placeholder="N/A">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Electricity Email (Auto): </label>
                                    <input type="text" id="elec-email" class="form-control" readonly placeholder="N/A">
                                </div>
                            </div>
                        </div>

                        <!-- 6. Account Name -->
                        <div class="form-group">
                            <label>Account Name (As per bill): </label>
                            <input type="text" class="form-control" placeholder="Enter name" name="title" required>
                        </div>
                        
                        <!-- 7. Account Number -->
                        <div class="form-group">
                            <label>Account Number (As per bill): </label>
                            <input type="text" class="form-control" placeholder="Enter account number" name="number" required>
                        </div>
                        
                        <!-- 8. Account Description -->
                        <div class="form-group">
                            <label>Account Description: </label>
                            <input type="text" name="optional_info" class="form-control" placeholder="e.g. Cottage, Main House">
                        </div>

                        <!-- 9. Bill Day & 10. Read Day -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Bill Day: </label>
                                    <input type="number" id="bill-day" min="1" max="31" class="form-control" placeholder="1-31" name="billing_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Read Day (Auto: Bill Day - 5): </label>
                                    <input type="text" id="read-day" class="form-control" readonly placeholder="Auto-calculated">
                                </div>
                            </div>
                        </div>
                        
                        <br>
                        @csrf
                        <button type="submit" class="btn btn-primary">Create Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-level-scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            
            // 1. Load Sites
            $(document).on("change", '#user-select', function () {
                let user_id = $(this).val();
                $.ajax({
                    type: 'POST',
                    dataType: 'JSON',
                    url: '{{ route("get-sites-by-user") }}',
                    data: { user_id: user_id, _token: '{{ csrf_token() }}' },
                    success: function (result) {
                        $('#site-select').empty();
                        $('#site-select').append('<option disabled selected value="">--Select Location Address--</option>');
                        
                        window.siteData = {}; 
                        $.each(result.data, function(key, value) {
                            window.siteData[value.id] = value;
                            $('#site-select').append($('<option>', { value: value.id, text: value.address }));
                        });
                        $('#site-select').prop('disabled', false);
                    }
                });
            });

            // 2. Auto-Fill Region/Emails
            $(document).on("change", '#site-select', function () {
                let siteId = $(this).val();
                let site = window.siteData[siteId];
                if(site && site.region) {
                    $('#region-display').val(site.region.title);
                    // Try to fetch emails if available in Region object
                    $('#water-email').val(site.region.water_email || 'N/A');
                    $('#elec-email').val(site.region.electricity_email || 'N/A');
                }
            });

            // 3. Auto-Calc Read Day
            $(document).on("keyup change", '#bill-day', function() {
                let billDay = parseInt($(this).val());
                if(billDay) {
                    let readDay = billDay - 5;
                    if(readDay < 1) readDay = 30 + readDay; 
                    $('#read-day').val(readDay);
                } else {
                    $('#read-day').val('');
                }
            });
        });
    </script>
@endsection
