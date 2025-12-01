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
                            <label>Site: </label>
                            <select class="form-control" id="site-select" name="site_id" required disabled>

                            </select>
                        </div>
                        <div class="form-group">
                            <label>Account Title: </label>
                            <input type="text" class="form-control" placeholder="Enter account title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label>Account Number: </label>
                            <input type="text" class="form-control" placeholder="Enter account number" name="number" required>
                        </div>
                        <div class="form-group">
                            <label>Billing Date: </label>
                            <input type="number" min="1" max="31" class="form-control" placeholder="Enter billing date" name="billing_date" required>
                        </div>
                        <div class="form-group">
                            <label>Optional Information: </label>
                            <input type="text" name="optional_info" class="form-control" placeholder="Enter optional information">
                        </div>
                        <hr>
                        <p><u>Default Costs</u></p>
                        <div class="row">
                            <div class="col-md-4"><b>Title</b></div>
                            <div class="col-md-4"><b>Default Value</b></div>
                            <div class="col-md-4"><b>Your Value</b></div>
                        </div>
                        <br>
                        @foreach($defaultCosts as $defaultCost)
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ $defaultCost->title }}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input class="form-control" type="text" value="{{ $defaultCost->value }}" readonly/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input class="form-control" type="number" name="default_cost_value[]" />
                                    </div>
                                </div>
                                <input type="hidden" name="default_ids[]" value="{{$defaultCost->id}}" />
                            </div>
                        @endforeach
                        <hr>
                        <p>Fixed Costs</p>
                        <div class="fixed-cost-container"></div>
                        <a href="#" id="add-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a>
                        <br>
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

            $("#add-cost").on("click", function () {

                var html = '<div class="row">\n' +
                    '                            <div class="col-md-4">\n' +
                    '                                <div class="form-group">\n' +
                    '                                    <input class="form-control" type="text" placeholder="Enter title" name="additional_cost_name[]" required/>\n' +
                    '                                </div>\n' +
                    '                            </div>\n' +
                    '                            <div class="col-md-4">\n' +
                    '                                <div class="form-group">\n' +
                    '                                    <input class="form-control" type="text" placeholder="Enter value" name="additional_cost_value[]" required/>\n' +
                    '                                </div>\n' +
                    '                            </div>\n' +
                    '                            <div class="col-md-4">\n' +
                    '                                <a href="#" style="margin-top: 6px" class="btn btn-sm btn-circle btn-danger additional-cost-del-btn">\n' +
                    '                                    <i class="fa fa-trash"></i>\n' +
                    '                                </a>\n' +
                    '                            </div>\n' +
                    '                        </div>'

                $(".fixed-cost-container").append(html);
            });

            $(document).on("click", '.additional-cost-del-btn', function () {
                $(this).parent().parent().remove();
            });

            $(document).on("change", '#user-select', function () {
                let user_id = $(this).val();
                
                $.ajax({
                    // CHANGED: Use GET to avoid CSRF 419 error
                    type: 'GET',
                    dataType: 'JSON',
                    // URL uses route helper for accuracy
                    url: '{{ route("get-sites-by-user") }}',
                    data: {user_id: user_id},
                    success: function (result) {
                        $('#site-select').empty();
                        $.each(result.data, function(key, value) {
                            $('#site-select').append($('<option>', {
                                value: value.id,
                                text: value.title
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
