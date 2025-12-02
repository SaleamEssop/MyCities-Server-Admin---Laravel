@extends('admin.layouts.main')
@section('title', 'Add Cost Template')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 custom-text-heading">Add Cost Template</h1>
    <p class="mb-4">Create a new billing template for a region and account type.</p>

    <div class="cust-form-wrapper">
        <div class="row">
            <div class="col-md-12">
                <form method="POST" action="{{ route('region-cost-store') }}">
                    @csrf
                    <div class="form-group">
                        <label><strong>Template Name :</strong></label>
                        <input class="form-control" type="text" placeholder="Template name" name="template_name" value="{{ old('template_name') }}" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Select Region :</strong></label>
                        <select class="form-control" name="region_id" id="select_regions" required>
                            <option value="">Please select Region</option>
                            @foreach($regions as $region)
                            <option value="{{$region->id}}" {{ old('region_id') == $region->id ? 'selected' : '' }}>{{$region->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><strong>Select Account Type :</strong></label>
                        <select class="form-control" name="account_type_id" required>
                            <option value="">Please select Account Type</option>
                            @foreach($account_types as $type)
                            <option value="{{$type->id}}" {{ old('account_type_id') == $type->id ? 'selected' : '' }}>{{$type->type}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><strong>Applicable Start Date :</strong></label>
                        <input class="form-control" type="date" placeholder="Start Date" name="start_date" value="{{ old('start_date') }}" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Applicable End Date :</strong></label>
                        <input class="form-control" type="date" placeholder="End Date" name="end_date" value="{{ old('end_date') }}" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Vat In Percentage :</strong></label>
                        <input class="form-control allow_decimal" type="text" placeholder="VAT Percentage" name="vat_percentage" value="{{ old('vat_percentage', 0) }}" required />
                    </div>
                    <hr>
                    <label style="font-size: 24px;font-weight: 800;"><strong>User Input : </strong></label>
                    <div class="form-group">
                        <label><strong>Billing Day :</strong></label>
                        <input class="form-control allow_decimal" type="text" placeholder="Billing Day" name="billing_day" value="{{ old('billing_day') }}" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Read Day :</strong></label>
                        <input class="form-control allow_decimal" type="text" placeholder="Read Day" name="read_day" value="{{ old('read_day') }}" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Ratable Value :</strong></label>
                        <input class="form-control allow_decimal" type="text" placeholder="Ratable Value" name="ratable_value" value="{{ old('ratable_value', 0) }}" required />
                    </div>
                    <hr>
                    <div class="form-group">
                        <label><strong>Select Meter Type :</strong></label>
                        <input type="checkbox" name="is_water" id="waterchk" {{ old('is_water') ? 'checked' : '' }} /> Water
                        <input type="checkbox" name="is_electricity" id="electricitychk" {{ old('is_electricity') ? 'checked' : '' }} /> Electricity
                    </div>
                    <div class="form-group water_used">
                        <label><strong>Water Used in KL :</strong></label>
                        <input class="form-control allow_decimal" type="text" placeholder="Water Usage" name="water_used" value="{{ old('water_used', 1) }}" />
                    </div>
                    <div class="form-group ele_used">
                        <label><strong>Electricity Used in KWH :</strong></label>
                        <input class="form-control allow_decimal" type="text" placeholder="Electricity Usage" name="electricity_used" value="{{ old('electricity_used', 1) }}" />
                    </div>

                    <!-- Water In Section -->
                    <div class="water_in_section">
                        <hr>
                        <label><strong>Add Water In Cost : </strong> <a href="javascript:void(0)" id="add-waterin-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                        <div class="waterin-cost-container"></div>
                        <div class="col-md-2" style="margin-left: 50%;">
                            <label><strong>Water In Total :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Total" name="waterin_total" value="0" disabled />
                            </div>
                        </div>

                        <label><strong>Water in related Cost</strong> <a href="javascript:void(0)" id="add-waterin-additional-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                        <div class="waterin-additional-cost-container"></div>
                        <div class="col-md-2" style="margin-left: 50%;">
                            <label><strong>WaterIn related Total :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Total" value="0" disabled />
                            </div>
                        </div>
                    </div>

                    <!-- Water Out Section -->
                    <div class="water_out_section">
                        <hr>
                        <label><strong>Add Water Out Cost : </strong> <a href="javascript:void(0)" id="add-waterout-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                        <div class="waterout-cost-container"></div>
                        <div class="col-md-2" style="margin-left: 67%;">
                            <label><strong>Water Out Total :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Total" name="waterout_total" value="0" disabled />
                            </div>
                        </div>

                        <label><strong>Water out related Cost</strong> <a href="javascript:void(0)" id="add-waterout-additional-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                        <div class="waterout-additional-cost-container"></div>
                        <div class="col-md-2" style="margin-left: 50%;">
                            <label><strong>Waterout related Total:</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Total" value="0" disabled />
                            </div>
                        </div>
                    </div>

                    <!-- Electricity Section -->
                    <div class="ele_section">
                        <hr>
                        <label><strong>Electricity : </strong> <a href="javascript:void(0)" id="add-electricity-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                        <div class="electricity-cost-container"></div>
                        <div class="col-md-2" style="margin-left: 50%;">
                            <label><strong>Electricity Total :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Total" name="electricity_total" value="0" disabled />
                            </div>
                        </div>

                        <label><strong>Electricity related Cost</strong> <a href="javascript:void(0)" id="add-electricity-additional-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                        <div class="electricity-additional-cost-container"></div>
                        <div class="col-md-2" style="margin-left: 50%;">
                            <label><strong>Electricity related Total :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Total" value="0" disabled />
                            </div>
                        </div>
                    </div>

                    <!-- Additional Cost Section -->
                    <hr>
                    <label><strong>Additional Cost : </strong> <a href="javascript:void(0)" id="add-additional-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                    <div class="additional-cost-container"></div>

                    <div class="col-md-4" style="float:right;">
                        <label><strong>Sub Total :</strong></label>
                        <div class="form-group">
                            <input class="form-control" type="text" placeholder="Sub Total" name="sub_total" value="0" disabled />
                        </div>
                        <label><strong>VAT</strong></label>
                        <div class="form-group">
                            <input class="form-control" type="text" placeholder="VAT Rate" value="0" disabled />
                        </div>
                        <label><strong>Rates :</strong></label>
                        <div class="form-group">
                            <input class="form-control allow_decimal" type="text" placeholder="VAT Rate" name="vat_rate" value="{{ old('vat_rate', 0) }}" />
                        </div>
                        <label><strong>Rates Rebate :</strong></label>
                        <div class="form-group">
                            <input class="form-control allow_decimal" type="text" placeholder="Rates Rebate" name="rates_rebate" value="{{ old('rates_rebate', 0) }}" />
                        </div>
                        <label><strong>Final Total :</strong></label>
                        <div class="form-group">
                            <input class="form-control" type="text" placeholder="Final Total" name="final_total" value="0" disabled />
                        </div>
                        <button type="submit" class="btn btn-warning">Save Template</button>
                        <a href="{{ route('region-cost') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-level-scripts')
<script type="text/javascript">
    $(document).ready(function() {
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
        }
        if ($('#electricitychk').is(':checked')) {
            $('.ele_used').css('display', 'block');
            $('.ele_section').css('display', 'block');
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

        var i = 0;
        var o = 0;
        var e = 0;
        var a = 0;
        var wa = 0;
        var wo = 0;
        var eo = 0;

        $(".allow_decimal").on("input", function(evt) {
            var self = $(this);
            self.val(self.val().replace(/[^0-9\.]/g, ''));
            if ((evt.which != 46 || self.val().indexOf('.') != -1) && (evt.which < 48 || evt.which > 57)) {
                evt.preventDefault();
            }
        });

        $("#add-waterin-cost").on("click", function() {
            var html = '<div class="row align-items-end mb-2">' +
                '<div class="col-md-2">' +
                '<label><strong>Min :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control allow_decimal" type="text" placeholder="Min litres" name="waterin[' + i + '][min]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Max :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control allow_decimal" type="text" placeholder="Max litres" name="waterin[' + i + '][max]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Cost :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="waterin[' + i + '][cost]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Total :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control" type="text" placeholder="Total" name="waterin[' + i + '][total]" disabled/>' +
                '</div>' +
                '</div>' +
                '<div class="col-md-1 d-flex align-items-end">' +
                '<a href="javascript:void(0)" class="btn btn-sm btn-circle btn-danger additional-cost-del-btn">' +
                '<i class="fa fa-trash"></i>' +
                '</a>' +
                '</div>' +
                '</div>';
            $(".waterin-cost-container").append(html);
            i++;
        });

        $("#add-waterin-additional-cost").on("click", function() {
            var html1 = '<div class="row align-items-end mb-2">' +
                '<div class="col-md-2">' +
                '<label><strong>Title :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control" type="text" placeholder="Title" name="waterin_additional[' + wa + '][title]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Percentage :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control allow_decimal" type="text" step=any placeholder="Percentage" name="waterin_additional[' + wa + '][percentage]" />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Cost :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="waterin_additional[' + wa + '][cost]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Total :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control" type="text" placeholder="Total" name="waterin_additional[' + wa + '][total]" disabled/>' +
                '</div>' +
                '</div>' +
                '<div class="col-md-1 d-flex align-items-end">' +
                '<a href="javascript:void(0)" class="btn btn-sm btn-circle btn-danger waterin-additional-cost-del-btn">' +
                '<i class="fa fa-trash"></i>' +
                '</a>' +
                '</div>' +
                '</div>';
            $(".waterin-additional-cost-container").append(html1);
            wa++;
        });

        $("#add-waterout-cost").on("click", function() {
            var html = '<div class="row align-items-end mb-2">' +
                '<div class="col-md-2">' +
                '<label><strong>Min :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control allow_decimal" type="text" placeholder="Min litres" name="waterout[' + o + '][min]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Max :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control allow_decimal" type="text" placeholder="Max litres" name="waterout[' + o + '][max]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Percentage :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control allow_decimal" type="text" step=any placeholder="Percentage" name="waterout[' + o + '][percentage]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Cost :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="waterout[' + o + '][cost]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Total :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control" type="text" placeholder="Total" name="waterout[' + o + '][total]" disabled/>' +
                '</div>' +
                '</div>' +
                '<div class="col-md-1 d-flex align-items-end">' +
                '<a href="javascript:void(0)" class="btn btn-sm btn-circle btn-danger additional-cost-del-out-btn">' +
                '<i class="fa fa-trash"></i>' +
                '</a>' +
                '</div>' +
                '</div>';
            $(".waterout-cost-container").append(html);
            o++;
        });

        $("#add-waterout-additional-cost").on("click", function() {
            var html1 = '<div class="row align-items-end mb-2">' +
                '<div class="col-md-2">' +
                '<label><strong>Title :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control" type="text" placeholder="Title" name="waterout_additional[' + wo + '][title]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Percentage :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control allow_decimal" type="text" step=any placeholder="Percentage" name="waterout_additional[' + wo + '][percentage]" />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Cost :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="waterout_additional[' + wo + '][cost]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Total :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control" type="text" placeholder="Total" name="waterout_additional[' + wo + '][total]" disabled/>' +
                '</div>' +
                '</div>' +
                '<div class="col-md-1 d-flex align-items-end">' +
                '<a href="javascript:void(0)" class="btn btn-sm btn-circle btn-danger waterout-additional-cost-del-btn">' +
                '<i class="fa fa-trash"></i>' +
                '</a>' +
                '</div>' +
                '</div>';
            $(".waterout-additional-cost-container").append(html1);
            wo++;
        });

        // Electricity
        $("#add-electricity-cost").on("click", function() {
            var html = '<div class="row align-items-end mb-2">' +
                '<div class="col-md-2">' +
                '<label><strong>Min :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control allow_decimal" type="text" placeholder="Min" name="electricity[' + e + '][min]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Max :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control allow_decimal" type="text" placeholder="Max" name="electricity[' + e + '][max]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Cost :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="electricity[' + e + '][cost]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Total :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control" type="text" placeholder="Total" name="electricity[' + e + '][total]" disabled/>' +
                '</div>' +
                '</div>' +
                '<div class="col-md-1 d-flex align-items-end">' +
                '<a href="javascript:void(0)" class="btn btn-sm btn-circle btn-danger additional-cost-del-ele-btn">' +
                '<i class="fa fa-trash"></i>' +
                '</a>' +
                '</div>' +
                '</div>';
            $(".electricity-cost-container").append(html);
            e++;
        });

        $("#add-electricity-additional-cost").on("click", function() {
            var html1 = '<div class="row align-items-end mb-2">' +
                '<div class="col-md-2">' +
                '<label><strong>Title :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control" type="text" placeholder="Title" name="electricity_additional[' + eo + '][title]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Percentage :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control allow_decimal" type="text" step=any placeholder="Percentage" name="electricity_additional[' + eo + '][percentage]"  />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Cost :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="electricity_additional[' + eo + '][cost]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Total :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control" type="text" placeholder="Total" name="electricity_additional[' + eo + '][total]" disabled/>' +
                '</div>' +
                '</div>' +
                '<div class="col-md-1 d-flex align-items-end">' +
                '<a href="javascript:void(0)" class="btn btn-sm btn-circle btn-danger electricity-additional-cost-del-btn">' +
                '<i class="fa fa-trash"></i>' +
                '</a>' +
                '</div>' +
                '</div>';
            $(".electricity-additional-cost-container").append(html1);
            eo++;
        });

        // Additional cost
        $("#add-additional-cost").on("click", function() {
            var html = '<div class="row align-items-end mb-2">' +
                '<div class="col-md-2">' +
                '<label><strong>Name Of Cost :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control" type="text" placeholder="Name" name="additional[' + a + '][name]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label><strong>Cost :</strong></label>' +
                '<div class="form-group mb-0">' +
                '<input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="additional[' + a + '][cost]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-1 d-flex align-items-end">' +
                '<a href="javascript:void(0)" class="btn btn-sm btn-circle btn-danger addi-cost-del-btn">' +
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
        $(document).on("click", '.electricity-additional-cost-del-btn', function() {
            $(this).parent().parent().remove();
            eo--;
        });
    });
</script>
@endsection
