@extends('admin.layouts.main')
@section('title', 'Account Type')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-2 custom-text-heading">Add Cost</h1>

    <div class="cust-form-wrapper">
        <div class="row">
            <div class="col-md-12">
                <form method="POST" action="{{ route('region-cost-store') }}" novalidate>
                    @csrf
                    <div class="form-group">
                        <label><strong>Template Name :</strong></label>
                        <input class="form-control" type="text" placeholder="Template name" name="template_name" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Select Region :</strong></label>
                        <select class="form-control" name="region_id">
                            <option value="">Please select Region</option>
                            @foreach($regions as $region)
                            <option value="{{$region->id}}">{{$region->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><strong>Select Account Type :</strong></label>
                        <select class="form-control" name="account_type_id">
                            <option value="">Please select Account Type</option>
                            @foreach($account_type as $type)
                            <option value="{{$type->id}}">{{$type->type}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><strong>Applicable Start Date :</strong></label>
                        <input class="form-control" type="date" placeholder="Start Date" name="start_date" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Applicable End Date :</strong></label>
                        <input class="form-control" type="date" placeholder="End Date" name="end_date" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Ratable Value :</strong></label>
                        <input class="form-control" type="text" placeholder="Ratable Value" name="ratable_value" value="{{$region_cost->ratable_value ?? 0}}" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Select Meter Type :</strong></label>
                        <input type="checkbox" name="is_water" id="waterchk" /> Water
                        <input type="checkbox" name="is_electricity" id="electricitychk" /> Electricity
                    </div>
                    <div class="form-group water_used">
                        <label><strong>Water Usages :</strong></label>
                        <input class="form-control" type="text" placeholder="Water Usage" name="water_used" required />
                    </div>
                    <div class="form-group ele_used">
                        <label><strong>Electricity Usages :</strong></label>
                        <input class="form-control" type="text" placeholder="Electricity Usage" name="electricity_used" required />
                    </div>
                    <div class="water_in_section">
                        <hr>

                        <label><strong>Add Water In Cost : </strong> <a href="javascript:void(0)" id="add-waterin-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                        <div class="row">
                            <div class="col-md-2">
                                <label><strong>Min :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Min litres" name="waterin[0][min]" required />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Max :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Max litres" name="waterin[0][max]" required />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label><strong>Cost :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Cost" step=any name="waterin[0][cost]" required />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label><strong>Total :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Total" name="waterin[0][total]" required disabled />
                                </div>
                            </div>

                            <div class="col-md-1">
                                <a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-cost-del-btn">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <div class="waterin-cost-container"></div>
                        <div class="col-md-2">
                            <label><strong>Water In Total :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Total" name="waterin_total"  disabled />
                            </div>
                        </div>
                        <!-- start water out form -->
                        <hr>
                    </div>
                    <div class="water_out_section">
                        <label><strong>Add Water Out Cost : </strong> <a href="javascript:void(0)" id="add-waterout-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                        <div class="row">
                            <div class="col-md-2">
                                <label><strong>Min :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Min litres" name="waterout[0][min]"  />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Max :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Max litres" name="waterout[0][max]"  />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Percentage :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Percentage" name="waterout[0][percentage]"  />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Cost :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Cost" step=any name="waterout[0][cost]"  />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Total :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Total" name="waterout[0][total]"  disabled />
                                </div>
                            </div>

                            <div class="col-md-1">
                                <a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-cost-del-out-btn">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <div class="waterout-cost-container"></div>
                        <div class="col-md-2">
                            <label><strong>Water Out Total :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Total" name="waterout_total"  disabled />
                            </div>
                        </div>
                    </div>
                    <!-- end water out form -->
                    <!-- start Additional cost form -->

                    <div class="ele_section">
                        <hr>
                        <label><strong>Electricity : </strong> <a href="javascript:void(0)" id="add-electricity-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                        <div class="row">
                            <div class="col-md-2">
                                <label><strong>Min :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Min" name="electricity[0][min]"  />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Max :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Max" name="electricity[0][max]"  />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Cost :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Cost" step=any name="electricity[0][cost]"  />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Total :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Total" name="electricity[0][total]"  disabled />
                                </div>
                            </div>

                            <div class="col-md-1">
                                <a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-cost-del-ele-btn">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <div class="electricity-cost-container"></div>
                        <div class="col-md-2">
                            <label><strong>Electricity Total :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Total" name="electricity_total"  disabled />
                            </div>
                        </div>
                    </div>
                    <!-- end water out form -->
                    <!-- start Additional cost form -->
                    <hr>

                    <label><strong>Additional Cost : </strong> <a href="javascript:void(0)" id="add-additional-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                    <div class="row">
                        <div class="col-md-2">
                            <label><strong>Name Of Cost :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Name" name="additional[0][name]" />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label><strong>Cost :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="number" placeholder="Cost" name="additional[0][cost]" />
                            </div>
                        </div>
                        <div class="col-md-1">
                            <a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger addi-cost-del-btn">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <div class="additional-cost-container"></div>
                    <div class="col-md-4" style="float:right;">
                        <label><strong>Sub Total :</strong></label>
                        <div class="form-group">
                            <input class="form-control" type="text" placeholder="Sub Total" name="sub_total" value="0" required disabled />
                        </div>
                        <label><strong>Vat In Percentage :</strong></label>
                        <div class="form-group">
                            <input class="form-control" type="number" placeholder="VAT Percentage" name="vat_percentage" value="15" required />
                        </div>
                        <label><strong>Vat Rates :</strong></label>
                        <div class="form-group">
                            <input class="form-control" type="number" placeholder="VAT Rate" name="vat_rate" value="0" required />
                        </div>
                        <label><strong>Final Total :</strong></label>
                        <div class="form-group">
                            <input class="form-control" type="number" placeholder="Final Total" name="final_total" value="0" required disabled />
                        </div>

                        <button type="submit" class="btn btn-warning">Save</button>
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

        $('.ele_used').css('display', 'none');
        $('.water_used').css('display', 'none');
        $('.ele_section').css('display', 'none');
        $('.water_in_section').css('display', 'none');
        $('.water_out_section').css('display', 'none');


        // checkbox chk
        if ($('#waterchk').is(':checked')) {
            $('.water_used').css('display', 'block');
            $('.water_in_section').css('display', 'block');
            $('.water_out_section').css('display', 'block');
        } else {
            $('.water_used').css('display', 'none');
            $('.water_in_section').css('display', 'none');
            $('.water_out_section').css('display', 'none');
        }
        if ($('#electricitychk').is(':checked')) {
            $('.ele_used').css('display', 'block');
            $('.ele_section').css('display', 'block');
        } else {
            $('.ele_used').css('display', 'none');
            $('.ele_section').css('display', 'none');
        }
        $('#waterchk').change(function() {
            if (this.checked) {
                $('.water_used').css('display', 'block');
                $('.water_in_section').css('display', 'block');
                $('.water_out_section').css('display', 'block');
            } else {
                $('.water_used').css('display', 'none');
                $('.water_in_section').css('display', 'none');
                $('.water_out_section').css('display', 'none');
            }
        });
        $('#electricitychk').change(function() {
            if (this.checked) {
                $('.ele_used').css('display', 'block');
                $('.ele_section').css('display', 'block');
            } else {
                $('.ele_used').css('display', 'none');
                $('.ele_section').css('display', 'none');
            }
        });

        var i = 1;
        var o = 1;
        var e = 1;
        var a = 1;
        $("#add-waterin-cost").on("click", function() {

            var html = '<div class="row">' +
                '<div class="col-md-2">' +
                '<label><strong>Min :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Min litres" name="waterin[' + i + '][min]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Max :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Max litres" name="waterin[' + i + '][max]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-3">' +
                '<label><strong>Cost :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Cost" step=any name="waterin[' + i + '][cost]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-3">' +
                '<label><strong>Total :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="text" placeholder="Total" name="waterin[' + i + '][total]" required disabled/>' +
                '</div>' +
                '</div>' +

                '<div class="col-md-1">' +
                '<a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-cost-del-btn">' +
                '<i class="fa fa-trash"></i>' +
                '</a>' +
                '</div>' +
                '</div>';
            console.log(html);
            $(".waterin-cost-container").append(html);
            i++;
        });

        $("#add-waterout-cost").on("click", function() {

            var html = '<div class="row">' +
                '<div class="col-md-2">' +
                '<label><strong>Min :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Min litres" name="waterout[' + o + '][min]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Max :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Max litres" name="waterout[' + o + '][max]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Percentage :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Percentage" name="waterout[' + o + '][percentage]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Cost :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Cost" step=any name="waterout[' + o + '][cost]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Total :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="text" placeholder="Total" name="waterout[' + o + '][total]" required disabled/>' +
                '</div>' +
                '</div>' +
                '<div class="col-md-1">' +
                '<a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-cost-del-btn">' +
                '<i class="fa fa-trash"></i>' +
                '</a>' +
                '</div>' +
                '</div>';

            $(".waterout-cost-container").append(html);
            o++
        });

        // Electricity
        $("#add-electricity-cost").on("click", function() {

            var html = '<div class="row">' +
                '<div class="col-md-2">' +
                '<label><strong>Min :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Min" name="electricity[' + e + '][min]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Max :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Max" name="electricity[' + e + '][max]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Cost :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Cost" step=any name="electricity[' + e + '][cost]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Total :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="text" placeholder="Total" name="electricity[' + e + '][total]" required disabled/>' +
                '</div>' +
                '</div>' +

                '<div class="col-md-1">' +
                '<a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-cost-del-btn">' +
                '<i class="fa fa-trash"></i>' +
                '</a>' +
                '</div>' +
                '</div>';

            $(".electricity-cost-container").append(html);
            e++;
        });

        // Additional cost
        $("#add-additional-cost").on("click", function() {

            var html = '<div class="row">' +
                '<div class="col-md-2">' +
                '<label><strong>Name Of Cost :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="text" placeholder="Name" name="additional[' + a + '][name]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Cost :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Cost" name="additional[' + a + '][cost]" required />' +
                '</div>' +
                '</div>' +

                '<div class="col-md-1">' +
                '<a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger addi-cost-del-btn">' +
                '<i class="fa fa-trash"></i>' +
                '</a>' +
                '</div>' +
                '</div>';

            $(".additional-cost-container").append(html);
            a++;
        });

        $(document).on("click", '.additional-cost-del-btn', function() {
            $(this).parent().parent().remove();
            i--;
        });
        $(document).on("click", '.addi-cost-del-btn', function() {
            $(this).parent().parent().remove();
            a--;
        });

        $(document).on("click", '.additional-cost-del-out-btn', function() {
            $(this).parent().parent().remove();
            o--;
        });
        $(document).on("click", '.additional-cost-del-ele-btn', function() {
            $(this).parent().parent().remove();
            e--;
        });
    });
</script>
@endsection