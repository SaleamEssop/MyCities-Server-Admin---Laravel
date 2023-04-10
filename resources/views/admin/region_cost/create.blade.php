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
                <form method="POST" action="{{ route('region-cost-store') }}">
                    @csrf
                    <div class="form-group">
                        <label><strong>Template Name :</strong></label>
                        <input class="form-control" type="text" placeholder="Template name" name="template_name" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Select Region :</strong></label>
                        <select class="form-control" name="region_id" id="select_regions" required>
                            <option value="">Please select Region</option>
                            @foreach($regions as $region)
                            <option value="{{$region->id}}">{{$region->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><strong>Select Account Type :</strong></label>
                        <select class="form-control" name="account_type_id" required>
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
                        <label><strong>Water Email :</strong></label>
                        <input class="form-control water_email" type="email" placeholder="Water Email" name="water_email" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Electricity Email :</strong></label>
                        <input class="form-control electricity_email" type="email" placeholder="Electricity Email" name="electricity_email" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Vat In Percentage :</strong></label>
                        <input class="form-control allow_decimal" type="number" placeholder="VAT Percentage" name="vat_percentage" value="15" required />
                    </div>
                    <hr>
                    <label style="font-size: 24px;font-weight: 800;"><strong>User Input : </strong></label>
                    <div class="form-group">
                        <label><strong>Billing Date :</strong></label>
                        <input class="form-control" type="date" placeholder="Billing Date" name="billing_date" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Read Day :</strong></label>
                        <input class="form-control" type="text" placeholder="Read Day" name="read_day" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Ratable Value :</strong></label>
                        <input class="form-control allow_decimal" type="text" placeholder="Ratable Value" name="ratable_value" value="{{$region_cost->ratable_value ?? 0}}" required />
                    </div>
                    <hr>

                    <div class="form-group">
                        <label><strong>Select Meter Type :</strong></label>
                        <input type="checkbox" name="is_water" id="waterchk" /> Water
                        <input type="checkbox" name="is_electricity" id="electricitychk" /> Electricity
                    </div>
                    <div class="form-group water_used">
                        <label><strong>Water Used in KL :</strong></label>
                        <input class="form-control allow_decimal" type="text" placeholder="Water Usage" name="water_used" required />
                    </div>
                    <div class="form-group ele_used">
                        <label><strong>Electricity Used in KWH :</strong></label>
                        <input class="form-control allow_decimal" type="text" placeholder="Electricity Usage" name="electricity_used" required />
                    </div>
                    <div class="water_in_section">
                        <hr>
                        <label><strong>Add Water In Cost : </strong> <a href="javascript:void(0)" id="add-waterin-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                        <div class="row">
                            <div class="col-md-2">
                                <label><strong>Min :</strong></label>
                                <div class="form-group">
                                    <input class="form-control allow_decimal" type="number" placeholder="Min litres" name="waterin[0][min]" required />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Max :</strong></label>
                                <div class="form-group">
                                    <input class="form-control allow_decimal" type="number" placeholder="Max litres" name="waterin[0][max]" required />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Cost :</strong></label>
                                <div class="form-group">
                                    <input class="form-control allow_decimal" type="number" placeholder="Cost" step=any name="waterin[0][cost]" required />
                                </div>
                            </div>
                            <div class="col-md-2">
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
                        <div class="col-md-2" style="margin-left: 50%;">
                            <label><strong>Water In Total :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Total" name="waterin_total" disabled />
                            </div>
                        </div>

                        <label><strong>Water in related Cost</strong> <a href="javascript:void(0)" id="add-waterin-additional-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                        <div class="row">
                            <div class="col-md-2">
                                <label><strong>Title :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Title" name="waterin_additional[0][title]" required />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Percentage :</strong></label>
                                <div class="form-group">
                                    <input class="form-control allow_decimal" type="number" placeholder="Percentage" name="waterin_additional[0][percentage]" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Cost :</strong></label>
                                <div class="form-group">
                                    <input class="form-control allow_decimal" type="number" placeholder="Cost" step=any name="waterin_additional[0][cost]" required />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Total :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Total" name="waterin_additional[0][total]" required disabled />
                                </div>
                            </div>

                            <div class="col-md-1">
                                <a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger waterin-additional-cost-del-btn">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <div class="waterin-additional-cost-container"></div>
                        <div class="col-md-2" style="margin-left: 50%;">
                            <label><strong>WaterIn related Total:</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Total" name="waterin_total" disabled />
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
                                    <input class="form-control allow_decimal" type="number" placeholder="Min litres" name="waterout[0][min]" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Max :</strong></label>
                                <div class="form-group">
                                    <input class="form-control allow_decimal" type="number" placeholder="Max litres" name="waterout[0][max]" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Percentage :</strong></label>
                                <div class="form-group">
                                    <input class="form-control allow_decimal" type="number" placeholder="Percentage" name="waterout[0][percentage]" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Cost :</strong></label>
                                <div class="form-group">
                                    <input class="form-control allow_decimal" type="number" placeholder="Cost" step=any name="waterout[0][cost]" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Total :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Total" name="waterout[0][total]" disabled />
                                </div>
                            </div>

                            <div class="col-md-1">
                                <a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-cost-del-out-btn">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <div class="waterout-cost-container"></div>
                        <div class="col-md-2" style="margin-left: 67%;">
                            <label><strong>Water Out Total :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Total" name="waterout_total" disabled />
                            </div>
                        </div>

                        <label><strong>Water out related Cost</strong> <a href="javascript:void(0)" id="add-waterout-additional-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                        <div class="row">
                            <div class="col-md-2">
                                <label><strong>Title :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Title" name="waterout_additional[0][title]" required />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Percentage :</strong></label>
                                <div class="form-group">
                                    <input class="form-control allow_decimal" type="number" placeholder="Percentage" name="waterout_additional[0][percentage]" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Cost :</strong></label>
                                <div class="form-group">
                                    <input class="form-control allow_decimal" type="number" placeholder="Cost" step=any name="waterout_additional[0][cost]" required />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Total :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Total" name="waterout_additional[0][total]" required disabled />
                                </div>
                            </div>

                            <div class="col-md-1">
                                <a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger waterout-additional-cost-del-btn">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <div class="waterout-additional-cost-container"></div>
                        <div class="col-md-2" style="margin-left: 50%;">
                            <label><strong>Waterout related Total:</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Total" name="waterout_total" disabled />
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
                                    <input class="form-control allow_decimal" type="number" placeholder="Min" name="electricity[0][min]" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Max :</strong></label>
                                <div class="form-group">
                                    <input class="form-control allow_decimal" type="number" placeholder="Max" name="electricity[0][max]" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Cost :</strong></label>
                                <div class="form-group">
                                    <input class="form-control allow_decimal" type="number" placeholder="Cost" step=any name="electricity[0][cost]" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Total :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Total" name="electricity[0][total]" disabled />
                                </div>
                            </div>

                            <div class="col-md-1">
                                <a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-cost-del-ele-btn">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <div class="electricity-cost-container"></div>
                        <div class="col-md-2" style="margin-left: 50%;">
                            <label><strong>Electricity Total :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Total" name="electricity_total" disabled />
                            </div>
                        </div>
                        <label><strong>Electricity related Cost</strong> <a href="javascript:void(0)" id="add-electricity-additional-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                        <div class="row">
                            <div class="col-md-2">
                                <label><strong>Title :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Title" name="electricity_additional[0][title]" required />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Percentage :</strong></label>
                                <div class="form-group">
                                    <input class="form-control allow_decimal" type="number" placeholder="Percentage" name="electricity_additional[0][percentage]" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Cost :</strong></label>
                                <div class="form-group">
                                    <input class="form-control allow_decimal" type="number" placeholder="Cost" step=any name="electricity_additional[0][cost]" required />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Total :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Total" name="electricity_additional[0][total]" required disabled />
                                </div>
                            </div>

                            <div class="col-md-1">
                                <a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-electricity-cost-del-btn">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <div class="electricity-additional-cost-container"></div>
                        <div class="col-md-2" style="margin-left: 50%;">
                            <label><strong>Electricity related Total:</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Total" name="electricity_total" disabled />
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
                                <input class="form-control allow_decimal" type="number" placeholder="Cost" name="additional[0][cost]" />
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
                            <input class="form-control allow_decimal" type="text" placeholder="Sub Total" name="sub_total" value="0" required disabled />
                        </div>

                        <label><strong>Vat Rates :</strong></label>
                        <div class="form-group">
                            <input class="form-control allow_decimal" type="number" placeholder="VAT Rate" name="vat_rate" value="0" required />
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
        $(".allow_decimal").on("input", function(evt) {
            var self = $(this);
            self.val(self.val().replace(/[^0-9\.]/g, ''));
            if ((evt.which != 46 || self.val().indexOf('.') != -1) && (evt.which < 48 || evt.which > 57)) {
                evt.preventDefault();
            }
        });
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
        var wa = 1; //water additional
        var wo = 1; //water out additional
        var eo = 1;
        $("#add-waterin-cost").on("click", function() {
            console.log('water-in');
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
                '<div class="col-md-2">' +
                '<label><strong>Cost :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Cost" step=any name="waterin[' + i + '][cost]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
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
        $("#add-waterin-additional-cost").on("click", function() {
            console.log('water-in-addtional');
            var html1 = '<div class="row">' +
                '<div class="col-md-2">' +
                '<label><strong>Title :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="text" placeholder="Title" name="waterin_additional[' + wa + '][title]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Percentage :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Percentage" name="waterin_additional[' + wa + '][percentage]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Cost :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Cost" step=any name="waterin_additional[' + wa + '][cost]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Total :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="text" placeholder="Total" name="waterin_additional[' + wa + '][total]" required disabled/>' +
                '</div>' +
                '</div>' +

                '<div class="col-md-1">' +
                '<a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger ele-additional-cost-del-btn">' +
                '<i class="fa fa-trash"></i>' +
                '</a>' +
                '</div>' +
                '</div>';
            console.log(html1);
            $(".waterin-additional-cost-container").append(html1);
            wa++;
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
        $("#add-waterout-additional-cost").on("click", function() {
            console.log('water-out-addtional');
            var html1 = '<div class="row">' +
                '<div class="col-md-2">' +
                '<label><strong>Title :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="text" placeholder="Title" name="waterout_additional[' + wo + '][title]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Percentage :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Percentage" name="waterout_additional[' + wo + '][percentage]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Cost :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Cost" step=any name="waterout_additional[' + wo + '][cost]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Total :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="text" placeholder="Total" name="waterout_additional[' + wo + '][total]" required disabled/>' +
                '</div>' +
                '</div>' +

                '<div class="col-md-1">' +
                '<a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-cost-del-btn">' +
                '<i class="fa fa-trash"></i>' +
                '</a>' +
                '</div>' +
                '</div>';
            console.log(html1);
            $(".waterout-additional-cost-container").append(html1);
            wo++;
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
        $("#add-electricity-additional-cost").on("click", function() {
            var html1 = '<div class="row">' +
                '<div class="col-md-2">' +
                '<label><strong>Title :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="text" placeholder="Title" name="electricity_additional[' + eo + '][title]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Percentage :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Percentage" name="electricity_additional[' + eo + '][percentage]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Cost :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Cost" step=any name="electricity_additional[' + eo + '][cost]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Total :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="text" placeholder="Total" name="electricity_additional[' + eo + '][total]" required disabled/>' +
                '</div>' +
                '</div>' +

                '<div class="col-md-1">' +
                '<a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-electricity-cost-del-btn">' +
                '<i class="fa fa-trash"></i>' +
                '</a>' +
                '</div>' +
                '</div>';

            $(".electricity-additional-cost-container").append(html1);
            eo++;
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
        $(document).on("click", '.waterin-additional-cost-del-btn', function() {
            $(this).parent().parent().remove();
            wa--;
        });
        $(document).on("click", '.waterout-additional-cost-del-btn', function() {
            $(this).parent().parent().remove();
            wo--;
        });
        $(document).on("click", '.additional-electricity-cost-del-btn', function() {
            $(this).parent().parent().remove();
            eo--;
        });

        $('#select_regions').change(function() {
            var id = $(this).val();
            var url = '{{ route("get-email-region", ":id") }}';
            url = url.replace(':id', id);
            $.ajax({
                url: url,
                type: 'get',
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    if (response != null) {
                        $('.water_email').val(response.water_email);
                        $('.electricity_email').val(response.electricity_email);
                    }

                }

            });
        });

    });
</script>
@endsection