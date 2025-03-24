@extends('admin.layouts.main')
@section('title', 'Users')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 custom-text-heading">Edit Account</h1>

        <div class="cust-form-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('edit-account') }}">
                <input type="hidden" name="user_id" value="{{ $account->user->id }}" />
                        <div class="form-group">
                            <label><strong>Full Name:</strong></label>
                            <input type="text" value="{{ $account->user->name }}" class="form-control"
                                placeholder="Enter account title" name="name" required>
                        </div>
                        <div class="form-group">
                            <label><strong>Email:</strong></label>
                            <input type="text" value="{{ $account->user->email }}" class="form-control"
                                placeholder="Enter account title" name="email" required>
                        </div>
                        <div class="form-group">
                            <label><strong>Contact Number:</strong></label>
                            <input type="text" value="{{ $account->user->contact_number }}" class="form-control"
                                placeholder="Enter account title" name="contact_number" required>
                        </div>
                        <div class="form-group">
                            <label><strong>Account Description:</strong></label>
                            <input type="text" value="{{ $account->account_name }}" class="form-control"
                                placeholder="Enter account title" name="title" required>
                        </div>
                      
                        <div class="form-group">
                            <label><strong>Optional Information :</strong></label>
                            <input type="text" value="{{ $account->optional_information }}" name="optional_info"
                                class="form-control" placeholder="Enter optional information">
                        </div>
                        <hr>
                        <p><u>Default Costs</u></p>
                        <div class="row">
                            <div class="col-md-4"><b>Title</b></div>
                            <div class="col-md-4"><b>Default Value</b></div>
                            <div class="col-md-4"><b>Your Value</b></div>
                        </div>
                        <br>
                        @php
                            $uniqueFixedCosts = $account->defaultFixedCosts->unique(
                                fn($cost) => $cost->fixedCost->title ?? '',
                            );
                        @endphp
                        @foreach ($uniqueFixedCosts as $accDefaultCost)
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ $accDefaultCost->fixedCost->title ?? null }}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input class="form-control" type="text"
                                            value="{{ $accDefaultCost->fixedCost->value ?? null }}" readonly />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input class="form-control" type="number" name="default_cost_value[]"
                                            value="{{ $accDefaultCost->value ?? null }}" />
                                    </div>
                                </div>
                                <input type="hidden" name="default_ids[]" value="{{ $accDefaultCost->id ?? null }}" />
                            </div>
                        @endforeach
                        <hr>
                        <p>Fixed Costs</p>

                        @foreach ($account->fixedCosts as $fixedCost)
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input value="{{ $fixedCost->title }}" class="form-control" type="text"
                                            placeholder="Enter title" name="additional_cost_name[]" required />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input value="{{ $fixedCost->value }}" class="form-control" type="text"
                                            placeholder="Enter value" name="additional_cost_value[]" required />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <a href="#" data-id="{{ $fixedCost->id }}"
                                        class="btn btn-sm btn-circle btn-danger additional-cost-del-btn">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                                <input type="hidden" name="fixed_cost_id[]" value="{{ $fixedCost->id }}" />
                                <input type="hidden" name="fixed_cost_type[]" value="old" />
                            </div>
                        @endforeach
                        <input type="hidden" name="deleted" id="deletedCosts" value="" />
                        <div class="fixed-cost-container"></div>

                        <a href="#" id="add-cost" class="btn btn-sm btn-primary btn-circle"><i
                                class="fa fa-plus"></i></a>
                        <br>
                        <br>
                        <input type="hidden" name="account_id" value="{{ $account->id }}" />
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

            $("#add-cost").on("click", function() {

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
                    '                                <a href="#" data-id="" style="margin-top: 6px" class="btn btn-sm btn-circle btn-danger additional-cost-del-btn">\n' +
                    '                                    <i class="fa fa-trash"></i>\n' +
                    '                                </a>\n' +
                    '                            </div>\n' +
                    '                            <input type="hidden" name="fixed_cost_type[]" value="new" />\n' +
                    '                        </div>'

                $(".fixed-cost-container").append(html);
            });

            $(document).on("click", '.additional-cost-del-btn', function() {
                var ID = $(this).data('id');
                if (ID) {
                    var oldVal = $("#deletedCosts").val();
                    var newVal = oldVal + ',' + ID;
                    $("#deletedCosts").val(newVal);
                }
                $(this).parent().parent().remove();
            });

            $(document).on("change", '#user-select', function() {
                let user_id = $(this).val();
                let token = $("[name='_token']").val();
                // Get list of accounts added under this user
                $.ajax({
                    type: 'POST',
                    dataType: 'JSON',
                    headers: {
                        'X-CSRF-TOKEN': token
                    },
                    url: '/admin/accounts/get-user-sites',
                    data: {
                        user: user_id
                    },
                    success: function(result) {
                        $('#site-select').empty();
                        $.each(result.details, function(key, value) {
                            $('#site-select').append($('<option>', {
                                value: value.id,
                                text: value.title
                            }));
                        });
                        $('#site-select').prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endsection
