@extends('admin.layouts.main')
@section('title', 'Regions')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-2 custom-text-heading">Edit Cost</h1>

    <div class="cust-form-wrapper">
        <div class="row">

            <div class="col-md-12">
                <form method="POST" action="{{ route('update-region-cost') }}">
                    @csrf
                    <div class="form-group">
                        <label><strong>Template Name :</strong></label>
                        <input class="form-control" type="text" placeholder="Template name" name="template_name" value="{{$region_cost->template_name}}" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Select Region :</strong></label>
                        <select class="form-control" name="region_id">
                            <option value="">Please select Region</option>
                            @foreach($regions as $region)
                            <option value="{{$region->id}}" {{ $region_cost->region_id == $region->id ? 'selected' : '' }}>{{$region->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><strong>Select Account Type :</strong></label>
                        <select class="form-control" name="account_type_id">
                            <option value="">Please select Account Type</option>
                            @foreach($account_type as $type)
                            <option value="{{$type->id}}" {{ $region_cost->account_type_id == $type->id ? 'selected' : '' }}>{{$type->type}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><strong>Applicable Start Date :</strong></label>
                        <input class="form-control" type="date" placeholder="Start Date" name="start_date" value="{{$region_cost->start_date}}" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Applicable End Date :</strong></label>
                        <input class="form-control" type="date" placeholder="End Date" name="end_date" value="{{$region_cost->end_date}}" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Select Meter Type :</strong></label>
                        <input type="checkbox" name="is_water" id="waterchk" {{ $region_cost->is_water == 1 ? 'checked' : '' }} /> Water
                        <input type="checkbox" name="is_electricity" id="electricitychk" {{ $region_cost->is_electricity == 1 ? 'checked' : '' }} /> Electricity
                    </div>
                    <div class="form-group water_used">
                        <label><strong>Water Usages :</strong></label>
                        <input class="form-control" type="text" placeholder="Water Usage" name="water_used" value="{{$region_cost->water_used ?? 0}}" required />
                    </div>
                    <div class="form-group ele_used">
                        <label><strong>Electricity Usages :</strong></label>
                        <input class="form-control" type="text" placeholder="Electricity Usage" name="electricity_used" value="{{$region_cost->electricity_used ?? 0}}" required />
                    </div>
                    <div class="water_in_section">
                        <hr>
                        <label><strong>Add Water In Cost : </strong> <a href="javascript:void(0)" id="add-waterin-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                        @if($region_cost->water_in != 'null' && $region_cost->water_used > 0)
                        @foreach(json_decode($region_cost->water_in) as $key => $value)
                        <div class="row">
                            <div class="col-md-2">
                                <label><strong>Min :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Min litres" name="waterin[{{$key}}][min]" value="{{$value->min}}" required />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Max :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Max litres" name="waterin[{{$key}}][max]" value="{{$value->max}}" required />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label><strong>Cost :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Cost" step=any name="waterin[{{$key}}][cost]" value="{{$value->cost}}" required />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label><strong>Total :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Total" name="waterin[{{$key}}][total]" value="{{$value->total}}" required disabled />
                                </div>
                            </div>

                            <div class="col-md-1">
                                <a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-cost-del-btn">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                        @endif
                        <div class="waterin-cost-container"></div>
                        <div class="col-md-2">
                            <label><strong>Water In Total :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Total" name="waterin_total" value="{{$region_cost->water_in_total ?? 0}}" required disabled />
                            </div>
                        </div>
                        <!-- start water out form -->
                    </div>
                    <div class="water_out_section">
                        <hr>

                        <label><strong>Add Water Out Cost : </strong> <a href="javascript:void(0)" id="add-waterout-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                        @if($region_cost->water_out != 'null' && $region_cost->water_used > 0)
                        @foreach(json_decode($region_cost->water_out) as $key => $value)
                        <div class="row">
                            <div class="col-md-2">
                                <label><strong>Min :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Min litres" name="waterout[{{$key}}][min]" value="{{$value->min}}" required />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Max :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Max litres" name="waterout[{{$key}}][max]" value="{{$value->max}}" required />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Percentage :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Percentage" name="waterout[{{$key}}][percentage]" value="{{$value->percentage}}" required />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Cost :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Cost" step=any name="waterout[{{$key}}][cost]" value="{{$value->cost}}" required />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Total :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Total" name="waterout[{{$key}}][total]" value="{{$value->total}}" required disabled />
                                </div>
                            </div>

                            <div class="col-md-1">
                                <a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-cost-del-out-btn">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                        @endif
                        <div class="waterout-cost-container"></div>
                        <div class="col-md-2">
                            <label><strong>Water Out Total :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Total" name="waterout_total" value="{{$region_cost->water_out_total ?? 0}}" required disabled />
                            </div>
                        </div>
                    </div>
                    <!-- end water out form -->
                    <!-- start Additional cost form -->

                    <div class="ele_section">
                        <hr>
                        <label><strong>Electricity : </strong> <a href="javascript:void(0)" id="add-electricity-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                        @if($region_cost->electricity != 'null')
                        @foreach(json_decode($region_cost->electricity) as $key => $value)
                        <div class="row">
                            <div class="col-md-2">
                                <label><strong>Min :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Min" name="electricity[{{$key}}][min]" value="{{$value->min}}" required />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Max :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Max" name="electricity[{{$key}}][max]" value="{{$value->max}}" required />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Cost :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="number" placeholder="Cost" step=any name="electricity[{{$key}}][cost]" value="{{$value->cost}}" required />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label><strong>Total :</strong></label>
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Total" name="electricity[{{$key}}][total]" value="{{$value->total ?? 0}}" required disabled />
                                </div>
                            </div>

                            <div class="col-md-1">
                                <a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-cost-del-ele-btn">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                        @endif
                        <div class="electricity-cost-container"></div>
                        <div class="col-md-2">
                            <label><strong>Electricity Total :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Total" name="electricity_total" value="{{$region_cost->electricity_total ?? 0}}" required disabled />
                            </div>
                        </div>
                    </div>

                    <!-- end water out form -->
                    <!-- start Additional cost form -->
                    <hr>
                    <label><strong>Additional Cost : </strong> <a href="javascript:void(0)" id="add-additional-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                    @if($region_cost->additional)
                    @foreach(json_decode($region_cost->additional) as $key => $value)
                    <div class="row">
                        <div class="col-md-2">
                            <label><strong>Name Of Cost :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Name" name="additional[{{$key}}][name]" value="{{$value->name}}" required />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label><strong>Cost :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="number" placeholder="Cost" name="additional[{{$key}}][cost]" value="{{$value->cost}}" required />
                            </div>
                        </div>
                        <div class="col-md-1">
                            <a href="javascript:void(0)" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger addi-cost-del-btn">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                    @endif
                    <div class="additional-cost-container"></div>
                    <div class="col-md-4" style="float:right;">
                        <label><strong>Sub Total :</strong></label>
                        <div class="form-group">
                            <input class="form-control" type="text" placeholder="Sub Total" name="sub_total" value="{{$region_cost->sub_total ?? 0}}" required disabled />
                        </div>
                        <label><strong>Vat In Percentage :</strong></label>
                        <div class="form-group">
                            <input class="form-control" type="number" placeholder="VAT Percentage" name="vat_percentage" value="{{$region_cost->vat_percentage ?? 0}}" required />
                        </div>
                        <label><strong>Subtotal of VAT Percentage</strong></label>
                        <div class="form-group">
                            <input class="form-control" type="number" placeholder="VAT Rate" name="vat_rate" value="{{$region_cost->sub_total_vat ?? 0}}" required />
                        </div>
                        <label><strong>Vat Rates :</strong></label>
                        <div class="form-group">
                            <input class="form-control" type="number" placeholder="VAT Rate" name="vat_rate" value="{{$region_cost->vat_rate ?? 0}}" required />
                        </div>
                        <label><strong>Final Total :</strong></label>
                        <div class="form-group">
                            <input class="form-control" type="number" placeholder="Final Total" name="final_total" value="{{$region_cost->final_total ?? 0}}" required disabled />
                        </div>
                        <input type="hidden" name="id" value="{{ $region_cost->id }}" />
                        <button type="submit" class="btn btn-warning">Update</button>

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

        var i = <?php
                if ($region_cost->water_in != 'null' && $region_cost->water_used > 0) {
                    echo count(json_decode($region_cost->water_in));
                } else {
                    echo 0;
                }
                ?>;
        var o = <?php
                if ($region_cost->water_out != 'null' && $region_cost->water_used > 0) {
                    echo count(json_decode($region_cost->water_out));
                } else {
                    echo 0;
                }
                ?>;
        var e = <?php
                if ($region_cost->electricity != 'null') {
                    echo count(json_decode($region_cost->electricity));
                } else {
                    echo 0;
                }
                ?>;
        var a = <?php
                if ($region_cost->additional) {
                    echo count(json_decode($region_cost->additional));
                } else {
                    echo 0;
                }
                ?>;

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