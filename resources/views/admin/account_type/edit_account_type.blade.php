@extends('admin.layouts.main')
@section('title', 'Regions')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 custom-text-heading">Edit Account Type</h1>

        <div class="cust-form-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('edit-account-type') }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Account Type Title :</strong></label>
                                    <input placeholder="Enter account type title" value="{{ $account_type->type }}" type="text" class="form-control" name="name" required />
                                </div>
                                <input type="hidden" name="id" value="{{ $account_type->id }}" />
                                @csrf
                                <button type="submit" class="btn btn-warning">Submit</button>
                            </div>

                        </div>

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

            // $("#add-cost").on("click", function () {

            //     var html = '<div class="row">\n' +
            //         '                            <div class="col-md-4">\n' +
            //         '                                <div class="form-group">\n' +
            //         '                                    <input class="form-control" type="text" placeholder="Enter title" name="additional_cost_name[]" required/>\n' +
            //         '                                </div>\n' +
            //         '                            </div>\n' +
            //         '                            <div class="col-md-4">\n' +
            //         '                                <div class="form-group">\n' +
            //         '                                    <input class="form-control" type="text" placeholder="Enter value" name="additional_cost_value[]" required/>\n' +
            //         '                                </div>\n' +
            //         '                            </div>\n' +
            //         '                            <div class="col-md-4">\n' +
            //         '                                <a href="#" data-id="" style="margin-top: 6px" class="btn btn-sm btn-circle btn-danger additional-cost-del-btn">\n' +
            //         '                                    <i class="fa fa-trash"></i>\n' +
            //         '                                </a>\n' +
            //         '                            </div>\n' +
            //         '                            <input type="hidden" name="fixed_cost_type[]" value="new" />\n' +
            //         '                        </div>'

            //     $(".fixed-cost-container").append(html);
            // });

            $(document).on("click", '.additional-cost-del-btn', function () {
                var ID = $(this).data('id');
                if(ID) {
                    var oldVal = $("#deletedCosts").val();
                    var newVal = oldVal + ',' + ID;
                    $("#deletedCosts").val(newVal);
                }
                $(this).parent().parent().remove();
            });
        });
    </script>
@endsection
