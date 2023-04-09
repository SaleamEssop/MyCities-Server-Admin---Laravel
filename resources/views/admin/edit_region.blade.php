@extends('admin.layouts.main')
@section('title', 'Regions')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 custom-text-heading">Edit Region</h1>

        <div class="cust-form-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('edit-region') }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Region Title :</strong></label>
                                    <input placeholder="Enter region title" value="{{ $region->name }}" type="text" class="form-control" name="region_name" required />
                                </div>
                                <div class="form-group">
                                    <label><strong>Water Email :</strong></label>
                                    <input placeholder="Enter Water Email" value="{{ $region->water_email }}" type="text" class="form-control" name="water_email" required />
                                </div>
                                <div class="form-group">
                                    <label><strong>Electricity Email :</strong></label>
                                    <input placeholder="Enter Electricity Email" value="{{ $region->electricity_email }}" type="text" class="form-control" name="electricity_email" required />
                                </div>
                                <!-- <div class="form-group">
                                    <label><strong>Water Base Unit :</strong></label>
                                    <input placeholder="Enter water base unit" value="{{ $region->water_base_unit_cost }}" type="text" class="form-control" name="water_base" />
                                </div>
                                <div class="form-group">
                                    <label><strong>Water Base Unit Value :</strong></label>
                                    <input placeholder="Enter water base unit value" value="{{ $region->water_base_unit }}" type="text" class="form-control" name="water_unit" />
                                </div>
                                <div class="form-group">
                                    <label><strong>Electricity Base Unit :</strong></label>
                                    <input placeholder="Enter electricity base unit" value="{{ $region->electricity_base_unit }}" type="text" class="form-control" name="elect_base" />
                                </div>
                                <div class="form-group">
                                    <label><strong>Electricity Base Unit Value :</strong></label>
                                    <input placeholder="Enter electricity base unit value" value="{{ $region->electricity_base_unit_cost }}" type="text" class="form-control" name="elect_unit" />
                                </div>
                                <div class="form-group">
                                    <label><strong>Cost :</strong></label>
                                    <input placeholder="Enter cost(optional)" value="{{ $region->cost }}" type="text" class="form-control" name="region_cost" />
                                </div> -->
                                <input type="hidden" name="region_id" value="{{ $region->id }}" />
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
