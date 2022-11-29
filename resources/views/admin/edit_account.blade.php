@extends('admin.layouts.main')
@section('title', 'Users')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 text-gray-800">Edit Account</h1>

        <div class="cust-form-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('edit-account') }}">
                        <div class="form-group">
                            <select class="form-control" id="exampleFormControlSelect1" name="site_id" required>
                                <option disabled value="">--Select Site--</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}" {{ ($site->id == $account->site_id) ? 'selected' : '' }}>{{ $site->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="text" value="{{ $account->account_name }}" class="form-control" placeholder="Enter account title" name="title" required>
                        </div>
                        <div class="form-group">
                            <input type="text" value="{{ $account->account_number }}" class="form-control" placeholder="Enter account number" name="number" required>
                        </div>
                        <div class="form-group">
                            <input type="text" value="{{ $account->optional_information }}" name="optional_info" class="form-control" required placeholder="Enter optional information">
                        </div>
                        <hr>
                        <p>Fixed Costs</p>
                        @foreach($account->fixedCosts as $fixedCost)
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input value="{{ $fixedCost->title }}" class="form-control" type="text" placeholder="Enter title" name="additional_cost_name[]" required/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input value="{{ $fixedCost->value }}" class="form-control" type="text" placeholder="Enter value" name="additional_cost_value[]" required/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <a href="#" class="btn btn-sm btn-circle btn-danger">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                            <input type="hidden" name="fixed_cost_id[]" value="{{ $fixedCost->id }}" />
                            <input type="hidden" name="fixed_cost_type[]" value="old" />

                        @endforeach
                        <div class="fixed-cost-container"></div>

                        <a href="#" id="add-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a>
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
                    '                                <a href="#" style="margin-top: 6px" class="btn btn-sm btn-circle btn-danger">\n' +
                    '                                    <i class="fa fa-trash"></i>\n' +
                    '                                </a>\n' +
                    '                            </div>\n' +
                    '                            <input type="hidden" name="fixed_cost_type[]" value="new" />\n' +
                    '                        </div>'

                $(".fixed-cost-container").append(html);
            });
        });
    </script>
@endsection
