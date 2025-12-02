@extends('admin.layouts.main')
@section('title', 'Regions')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div style="float: right;">
        <form method="POST" action="{{ route('copy-region-cost') }}">
            @csrf
            <button type="submit" class="btn btn-warning" style="background: green;">Make a Copy</button>
            <input type="hidden" name="id" value="{{ $region_cost->id }}" />
        </form>
    </div>
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
                        <select class="form-control" name="region_id" id="select_regions" required>
                            <option value="">Please select Region</option>
                            @foreach($regions as $region)
                            <option value="{{$region->id}}" {{ $region_cost->region_id == $region->id ? 'selected' : '' }}>{{$region->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><strong>Select Account Type :</strong></label>
                        <select class="form-control" name="account_type_id" required>
                            <option value="">Please select Account Type</option>
                            @foreach($account_type as $type)
                            <option value="{{$type->id}}" {{ $region_cost->account_type_id == $type->id ? 'selected' : '' }}>{{$type->type}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><strong>Applicable Start Date :</strong></label>
                        <input class="form-control" type="date" placeholder="Start Date" name="start_date" value="{{ $region_cost->start_date }}" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Applicable End Date :</strong></label>
                        <input class="form-control" type="date" placeholder="End Date" name="end_date" value="{{$region_cost->end_date}}" required />
                    </div>
                    <div class="form-group" style="display:none;">
                        <label><strong>Water Email :</strong></label>
                        <input class="form-control water_email" type="email" placeholder="Water Email" name="water_email" value="{{$region_cost->water_email}}" required />
                    </div>
                    <div class="form-group" style="display:none;">
                        <label><strong>Electricity Email :</strong></label>
                        <input class="form-control electricity_email" type="email" placeholder="Electricity Email" name="electricity_email" value="{{$region_cost->electricity_email}}" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Vat In Percentage :</strong></label>
                        <input class="form-control allow_decimal" type="text" placeholder="VAT Percentage" name="vat_percentage" value="{{$region_cost->vat_percentage ?? 0}}" required />
                    </div>
                    <hr>
                    <label style="font-size: 24px;font-weight: 800;"><strong>User Input : </strong></label>
                    <div class="form-group">
                        <label><strong>Billing Day :</strong></label>
                        <input class="form-control allow_decimal" type="text" placeholder="Billing Day" name="billing_day" value="{{$region_cost->billing_day}}" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Read Day :</strong></label>
                        <input class="form-control allow_decimal" type="text" placeholder="Read Day" name="read_day" value="{{$region_cost->read_day}}" required />
                    </div>
                    <div class="form-group">
                        <label><strong>Ratable Value :</strong></label>
                        <input class="form-control allow_decimal" type="text" placeholder="Ratable Value" name="ratable_value" value="{{$region_cost->ratable_value ?? 0}}" required />
                    </div>
                    <hr>
                    <div class="form-group">
                        <label><strong>Select Meter Type :</strong></label>
                        <input type="checkbox" name="is_water" id="waterchk" {{ $region_cost->is_water == 1 ? 'checked' : '' }} /> Water
                        <input type="checkbox" name="is_electricity" id="electricitychk" {{ $region_cost->is_electricity == 1 ? 'checked' : '' }} /> Electricity
                    </div>
                    <div class="form-group water_used">
                        <label><strong>Water Used in KL :</strong></label>
                        <input class="form-control allow_decimal" type="text" placeholder="Water Usage" name="water_used" value="{{$region_cost->water_used ?? 1}}" />
                    </div>
                    <div class="form-group ele_used">
                        <label><strong>Electricity Used in KWH :</strong></label>
                        <input class="form-control allow_decimal" type="text" placeholder="Electricity Usage" name="electricity_used" value="{{$region_cost->electricity_used ?? 1}}" />
                    </div>
                    
                    <!-- Water In Section -->
                    <div class="water_in_section">
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="mr-2"><strong>Add Water In Cost : </strong></label>
                                <a href="javascript:void(0)" id="add-waterin-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a>
                            </div>
                        </div>

                        <?php $waterIn = json_decode($region_cost->water_in); ?>
                        @if(is_array($waterIn))
                        @foreach($waterIn as $key => $value)
                        <div class="row mb-2">
                            <div class="col-md-3">
                                <label><strong>Min :</strong></label>
                                <input class="form-control allow_decimal" type="text" placeholder="Min litres" name="waterin[{{$key}}][min]" value="{{$value->min}}" required />
                            </div>
                            <div class="col-md-3">
                                <label><strong>Max :</strong></label>
                                <input class="form-control allow_decimal" type="text" placeholder="Max litres" name="waterin[{{$key}}][max]" value="{{$value->max}}" required />
                            </div>
                            <div class="col-md-3">
                                <label><strong>Cost :</strong></label>
                                <input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="waterin[{{$key}}][cost]" value="{{$value->cost}}" required />
                            </div>
                            <div class="col-md-2">
                                <label><strong>Total :</strong></label>
                                <input class="form-control" type="text" placeholder="Total" name="waterin[{{$key}}][total]" value="{{$value->total ?? 0}}" required disabled />
                            </div>
                            <div class="col-md-1">
                                <a href="javascript:void(0)" style="margin-top: 32px;" class="btn btn-sm btn-circle btn-danger remove-row">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                        @endif
                        <div class="waterin-cost-container"></div>
                        
                        <div class="row mt-2">
                            <div class="col-md-2 offset-md-10">
                                <label><strong>Water In Total :</strong></label>
                                <input class="form-control" type="text" placeholder="Total" name="waterin_total" value="{{$region_cost->water_in_total ?? 0}}" required disabled />
                            </div>
                        </div>

                        <div class="row mb-3 mt-4">
                            <div class="col-md-12">
                                <label class="mr-2"><strong>Water in related Cost : </strong></label>
                                <a href="javascript:void(0)" id="add-waterin-additional-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a>
                            </div>
                        </div>

                        <?php $waterInAdd = json_decode($region_cost->waterin_additional); ?>
                        @if(is_array($waterInAdd))
                        @foreach($waterInAdd as $key => $value)
                        <div class="row mb-2">
                            <div class="col-md-3">
                                <label><strong>Title :</strong></label>
                                <input class="form-control" type="text" placeholder="Title" name="waterin_additional[{{$key}}][title]" value="{{$value->title}}" required />
                            </div>
                            <div class="col-md-3">
                                <label><strong>Percentage :</strong></label>
                                <input class="form-control allow_decimal" type="text" placeholder="Percentage" name="waterin_additional[{{$key}}][percentage]" value="{{$value->percentage}}" />
                            </div>
                            <div class="col-md-3">
                                <label><strong>Cost :</strong></label>
                                <input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="waterin_additional[{{$key}}][cost]" value="{{$value->cost}}" required />
                            </div>
                            <div class="col-md-2">
                                <label><strong>Total :</strong></label>
                                <input class="form-control" type="text" placeholder="Total" name="waterin_additional[0][total]" value="{{$value->total ?? 0}}" required disabled />
                            </div>
                            <div class="col-md-1">
                                <a href="javascript:void(0)" style="margin-top: 32px;" class="btn btn-sm btn-circle btn-danger remove-row">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                        @endif
                        <div class="waterin-additional-cost-container"></div>
                        
                         <div class="row mt-2">
                            <div class="col-md-2 offset-md-10">
                                <label><strong>WaterIn related Total :</strong></label>
                                <input class="form-control" type="text" placeholder="Total" name="waterin_total" value="{{$region_cost->water_in_related_total ?? 0}}" required disabled />
                            </div>
                        </div>
                    </div>
                    
                    <!-- Water Out Section -->
                    <div class="water_out_section">
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="mr-2"><strong>Add Water Out Cost : </strong></label>
                                <a href="javascript:void(0)" id="add-waterout-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a>
                            </div>
                        </div>

                        <?php $waterOut = json_decode($region_cost->water_out); ?>
                        @if(is_array($waterOut))
                        @foreach($waterOut as $key => $value)
                        <div class="row mb-2">
                            <div class="col-md-2">
                                <label><strong>Min :</strong></label>
                                <input class="form-control allow_decimal" type="text" placeholder="Min litres" name="waterout[{{$key}}][min]" value="{{$value->min}}" required />
                            </div>
                            <div class="col-md-2">
                                <label><strong>Max :</strong></label>
                                <input class="form-control allow_decimal" type="text" placeholder="Max litres" name="waterout[{{$key}}][max]" value="{{$value->max}}" required />
                            </div>
                            <div class="col-md-2">
                                <label><strong>Percentage :</strong></label>
                                <input class="form-control allow_decimal" type="text" placeholder="Percentage" name="waterout[{{$key}}][percentage]" value="{{$value->percentage}}" required />
                            </div>
                            <div class="col-md-3">
                                <label><strong>Cost :</strong></label>
                                <input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="waterout[{{$key}}][cost]" value="{{$value->cost}}" required />
                            </div>
                            <div class="col-md-2">
                                <label><strong>Total :</strong></label>
                                <input class="form-control" type="text" placeholder="Total" name="waterout[{{$key}}][total]" value="{{$value->total ?? 0}}" required disabled />
                            </div>
                            <div class="col-md-1">
                                <a href="javascript:void(0)" style="margin-top: 32px;" class="btn btn-sm btn-circle btn-danger remove-row">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                        @endif
                        <div class="waterout-cost-container"></div>
                        
                        <div class="row mt-2">
                            <div class="col-md-2 offset-md-10">
                                <label><strong>Water Out Total :</strong></label>
                                <input class="form-control" type="text" placeholder="Total" name="waterout_total" value="{{$region_cost->water_out_total ?? 0}}" required disabled />
                            </div>
                        </div>

                        <div class="row mb-3 mt-4">
                            <div class="col-md-12">
                                <label class="mr-2"><strong>Water out related Cost : </strong></label>
                                <a href="javascript:void(0)" id="add-waterout-additional-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a>
                            </div>
                        </div>

                        <?php $waterOutAdd = json_decode($region_cost->waterout_additional); ?>
                        @if(is_array($waterOutAdd))
                        @foreach($waterOutAdd as $key => $value)
                        <div class="row mb-2">
                            <div class="col-md-3">
                                <label><strong>Title :</strong></label>
                                <input class="form-control" type="text" placeholder="Title" name="waterout_additional[{{$key}}][title]" value="{{$value->title}}" required />
                            </div>
                            <div class="col-md-3">
                                <label><strong>Percentage :</strong></label>
                                <input class="form-control allow_decimal" type="text" placeholder="Percentage" name="waterout_additional[{{$key}}][percentage]" value="{{$value->percentage}}" />
                            </div>
                            <div class="col-md-3">
                                <label><strong>Cost :</strong></label>
                                <input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="waterout_additional[{{$key}}][cost]" value="{{$value->cost}}" required />
                            </div>
                            <div class="col-md-2">
                                <label><strong>Total :</strong></label>
                                <input class="form-control" type="text" placeholder="Total" name="waterout_additional[0][total]" value="{{$value->total ?? 0}}" required disabled />
                            </div>
                            <div class="col-md-1">
                                <a href="javascript:void(0)" style="margin-top: 32px;" class="btn btn-sm btn-circle btn-danger remove-row">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                        @endif
                        <div class="waterout-additional-cost-container"></div>
                        
                        <div class="row mt-2">
                            <div class="col-md-2 offset-md-10">
                                <label><strong>Waterout related Total:</strong></label>
                                <input class="form-control" type="text" placeholder="Total" name="waterout_total" value="{{$region_cost->water_out_related_total ?? 0}}" required disabled />
                            </div>
                        </div>
                    </div>

                    <!-- Electricity Section -->
                    <div class="ele_section">
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="mr-2"><strong>Electricity : </strong></label>
                                <a href="javascript:void(0)" id="add-electricity-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a>
                            </div>
                        </div>

                        <?php $elec = json_decode($region_cost->electricity); ?>
                        @if(is_array($elec))
                        @foreach($elec as $key => $value)
                        <div class="row mb-2">
                            <div class="col-md-3">
                                <label><strong>Min :</strong></label>
                                <input class="form-control allow_decimal" type="text" placeholder="Min" name="electricity[{{$key}}][min]" value="{{$value->min}}" required />
                            </div>
                            <div class="col-md-3">
                                <label><strong>Max :</strong></label>
                                <input class="form-control allow_decimal" type="text" placeholder="Max" name="electricity[{{$key}}][max]" value="{{$value->max}}" required />
                            </div>
                            <div class="col-md-3">
                                <label><strong>Cost :</strong></label>
                                <input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="electricity[{{$key}}][cost]" value="{{$value->cost}}" required />
                            </div>
                            <div class="col-md-2">
                                <label><strong>Total :</strong></label>
                                <input class="form-control" type="text" placeholder="Total" name="electricity[{{$key}}][total]" value="{{$value->total ?? 0}}" required disabled />
                            </div>
                            <div class="col-md-1">
                                <a href="javascript:void(0)" style="margin-top: 32px;" class="btn btn-sm btn-circle btn-danger remove-row">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                        @endif
                        <div class="electricity-cost-container"></div>
                        
                        <div class="row mt-2">
                            <div class="col-md-2 offset-md-10">
                                <label><strong>Electricity Total :</strong></label>
                                <input class="form-control" type="text" placeholder="Total" name="electricity_total" value="{{$region_cost->electricity_total ?? 0}}" required disabled />
                            </div>
                        </div>

                        <div class="row mb-3 mt-4">
                            <div class="col-md-12">
                                <label class="mr-2"><strong>Electricity related Cost : </strong></label>
                                <a href="javascript:void(0)" id="add-electricity-additional-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a>
                            </div>
                        </div>

                        <?php $elecAdd = json_decode($region_cost->electricity_additional); ?>
                        @if(is_array($elecAdd))
                        @foreach($elecAdd as $key => $value)
                        <div class="row mb-2">
                            <div class="col-md-3">
                                <label><strong>Title :</strong></label>
                                <input class="form-control" type="text" placeholder="Title" name="electricity_additional[{{$key}}][title]" value="{{$value->title}}" required />
                            </div>
                            <div class="col-md-3">
                                <label><strong>Percentage :</strong></label>
                                <input class="form-control allow_decimal" type="text" placeholder="Percentage" name="electricity_additional[{{$key}}][percentage]" value="{{$value->percentage}}" />
                            </div>
                            <div class="col-md-3">
                                <label><strong>Cost :</strong></label>
                                <input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="electricity_additional[{{$key}}][cost]" value="{{$value->cost}}" required />
                            </div>
                            <div class="col-md-2">
                                <label><strong>Total :</strong></label>
                                <input class="form-control" type="text" placeholder="Total" name="electricity_additional[0][total]" value="{{$value->total ?? 0}}" required disabled />
                            </div>
                            <div class="col-md-1">
                                <a href="javascript:void(0)" style="margin-top: 32px;" class="btn btn-sm btn-circle btn-danger remove-row">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                        @endif
                        <div class="electricity-additional-cost-container"></div>
                        
                        <div class="row mt-2">
                            <div class="col-md-2 offset-md-10">
                                <label><strong>Electricity related Total :</strong></label>
                                <input class="form-control" type="text" placeholder="Total" name="electricity_total" value="{{$region_cost->electricity_related_total ?? 0}}" required disabled />
                            </div>
                        </div>
                    </div>

                    <!-- Additional cost form -->
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="mr-2"><strong>Additional Cost : </strong></label>
                            <a href="javascript:void(0)" id="add-additional-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a>
                        </div>
                    </div>

                    <?php $addCost = json_decode($region_cost->additional); ?>
                    @if(is_array($addCost))
                    @foreach($addCost as $key => $value)
                    <div class="row mb-2">
                        <div class="col-md-5">
                            <label><strong>Name Of Cost :</strong></label>
                            <input class="form-control" type="text" placeholder="Name" name="additional[{{$key}}][name]" value="{{$value->name}}" required />
                        </div>
                        <div class="col-md-5">
                            <label><strong>Cost :</strong></label>
                            <input class="form-control " type="text" placeholder="Cost" name="additional[{{$key}}][cost]" value="{{$value->cost}}" required />
                        </div>
                        <div class="col-md-2">
                            <a href="javascript:void(0)" style="margin-top: 32px;" class="btn btn-sm btn-circle btn-danger remove-row">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                    @endif
                    <div class="additional-cost-container"></div>
                    
                    <div class="row">
                        <div class="col-md-4 ml-auto">
                            <label><strong>Sub Total :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Sub Total" name="sub_total" value="{{$region_cost->sub_total ?? 0}}" required disabled />
                            </div>
                            <label><strong>VAT</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="VAT Rate" name="vat_rate" value="{{$region_cost->sub_total_vat ?? 0}}" required disabled />
                            </div>
                            <label><strong>Rates :</strong></label>
                            <div class="form-group">
                                <input class="form-control allow_decimal" type="text" placeholder="VAT Rate" name="vat_rate" value="{{$region_cost->vat_rate ?? 0}}" required />
                            </div>
                            <label><strong>Rates Rebate :</strong></label>
                            <div class="form-group">
                                <input class="form-control allow_decimal" type="text" placeholder="Rates Rebate" name="rates_rebate" value="{{$region_cost->rates_rebate ?? 0}}" />
                            </div>
                            <label><strong>Final Total :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Final Total" name="final_total" value="{{$region_cost->final_total ?? 0}}" required disabled />
                            </div>
                            <input type="hidden" name="id" value="{{ $region_cost->id }}" />
                            <button type="submit" class="btn btn-warning">Save / Update</button>
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

        // Toggle visibility based on checkboxes
        function toggleSections() {
            if ($('#waterchk').is(':checked')) {
                $('.water_used, .water_in_section, .water_out_section').show();
            } else {
                $('.water_used, .water_in_section, .water_out_section').hide();
            }

            if ($('#electricitychk').is(':checked')) {
                $('.ele_used, .ele_section').show();
            } else {
                $('.ele_used, .ele_section').hide();
            }
        }

        // Initial check and event listeners
        toggleSections();
        $('#waterchk, #electricitychk').change(toggleSections);

        // Initialize counters based on PHP counts
        var i = <?php $wIn = json_decode($region_cost->water_in); echo (is_array($wIn)) ? count($wIn) : 0; ?>;
        var o = <?php $wOut = json_decode($region_cost->water_out); echo (is_array($wOut)) ? count($wOut) : 0; ?>;
        var e = <?php $elec = json_decode($region_cost->electricity); echo (is_array($elec)) ? count($elec) : 0; ?>;
        var a = <?php $add = json_decode($region_cost->additional); echo (is_array($add)) ? count($add) : 0; ?>;
        var wa = <?php $waAdd = json_decode($region_cost->waterin_additional); echo (is_array($waAdd)) ? count($waAdd) : 0; ?>;
        var wo = <?php $woAdd = json_decode($region_cost->waterout_additional); echo (is_array($woAdd)) ? count($woAdd) : 0; ?>;
        var eo = <?php $eoAdd = json_decode($region_cost->electricity_additional); echo (is_array($eoAdd)) ? count($eoAdd) : 0; ?>;

        // Decimal validation
        $(document).on("input", ".allow_decimal", function(evt) {
            var self = $(this);
            self.val(self.val().replace(/[^0-9\.]/g, ''));
            if ((evt.which != 46 || self.val().indexOf('.') != -1) && (evt.which < 48 || evt.which > 57)) {
                evt.preventDefault();
            }
        });

        // --- DYNAMIC ROW APPEND LOGIC ---

        // WATER IN
        $("#add-waterin-cost").on("click", function() {
            var html = `
                <div class="row mb-2">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>Min :</strong></label>
                            <input class="form-control allow_decimal" type="text" placeholder="Min litres" name="waterin[${i}][min]" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>Max :</strong></label>
                            <input class="form-control allow_decimal" type="text" placeholder="Max litres" name="waterin[${i}][max]" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>Cost :</strong></label>
                            <input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="waterin[${i}][cost]" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><strong>Total :</strong></label>
                            <input class="form-control" type="text" placeholder="Total" name="waterin[${i}][total]" disabled/>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <button type="button" style="margin-top: 32px;" class="btn btn-danger btn-sm btn-circle remove-row"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>`;
            $(".waterin-cost-container").append(html);
            i++;
        });

        $("#add-waterin-additional-cost").on("click", function() {
            var html = `
                <div class="row mb-2">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>Title :</strong></label>
                            <input class="form-control" type="text" placeholder="Title" name="waterin_additional[${wa}][title]" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>Percentage :</strong></label>
                            <input class="form-control allow_decimal" type="text" step=any placeholder="%" name="waterin_additional[${wa}][percentage]" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>Cost :</strong></label>
                            <input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="waterin_additional[${wa}][cost]" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><strong>Total :</strong></label>
                            <input class="form-control" type="text" placeholder="Total" name="waterin_additional[${wa}][total]" disabled/>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <button type="button" style="margin-top: 32px;" class="btn btn-danger btn-sm btn-circle remove-row"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>`;
            $(".waterin-additional-cost-container").append(html);
            wa++;
        });

        // WATER OUT
        $("#add-waterout-cost").on("click", function() {
            var html = `
                <div class="row mb-2">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><strong>Min :</strong></label>
                            <input class="form-control allow_decimal" type="text" placeholder="Min" name="waterout[${o}][min]" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><strong>Max :</strong></label>
                            <input class="form-control allow_decimal" type="text" placeholder="Max" name="waterout[${o}][max]" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><strong>% :</strong></label>
                            <input class="form-control allow_decimal" type="text" placeholder="%" name="waterout[${o}][percentage]" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>Cost :</strong></label>
                            <input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="waterout[${o}][cost]" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><strong>Total :</strong></label>
                            <input class="form-control" type="text" placeholder="Total" name="waterout[${o}][total]" disabled/>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <button type="button" style="margin-top: 32px;" class="btn btn-danger btn-sm btn-circle remove-row"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>`;
            $(".waterout-cost-container").append(html);
            o++;
        });

        $("#add-waterout-additional-cost").on("click", function() {
            var html = `
                <div class="row mb-2">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>Title :</strong></label>
                            <input class="form-control" type="text" placeholder="Title" name="waterout_additional[${wo}][title]" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>% :</strong></label>
                            <input class="form-control allow_decimal" type="text" step=any placeholder="%" name="waterout_additional[${wo}][percentage]" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>Cost :</strong></label>
                            <input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="waterout_additional[${wo}][cost]" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><strong>Total :</strong></label>
                            <input class="form-control" type="text" placeholder="Total" name="waterout_additional[${wo}][total]" disabled/>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <button type="button" style="margin-top: 32px;" class="btn btn-danger btn-sm btn-circle remove-row"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>`;
            $(".waterout-additional-cost-container").append(html);
            wo++;
        });

        // ELECTRICITY
        $("#add-electricity-cost").on("click", function() {
            var html = `
                <div class="row mb-2">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>Min :</strong></label>
                            <input class="form-control allow_decimal" type="text" placeholder="Min" name="electricity[${e}][min]" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>Max :</strong></label>
                            <input class="form-control allow_decimal" type="text" placeholder="Max" name="electricity[${e}][max]" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>Cost :</strong></label>
                            <input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="electricity[${e}][cost]" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><strong>Total :</strong></label>
                            <input class="form-control" type="text" placeholder="Total" name="electricity[${e}][total]" disabled/>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <button type="button" style="margin-top: 32px;" class="btn btn-danger btn-sm btn-circle remove-row"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>`;
            $(".electricity-cost-container").append(html);
            e++;
        });

        $("#add-electricity-additional-cost").on("click", function() {
            var html = `
                <div class="row mb-2">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>Title :</strong></label>
                            <input class="form-control" type="text" placeholder="Title" name="electricity_additional[${eo}][title]" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>% :</strong></label>
                            <input class="form-control allow_decimal" type="text" step=any placeholder="%" name="electricity_additional[${eo}][percentage]" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>Cost :</strong></label>
                            <input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="electricity_additional[${eo}][cost]" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><strong>Total :</strong></label>
                            <input class="form-control" type="text" placeholder="Total" name="electricity_additional[${eo}][total]" disabled/>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <button type="button" style="margin-top: 32px;" class="btn btn-danger btn-sm btn-circle remove-row"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>`;
            $(".electricity-additional-cost-container").append(html);
            eo++;
        });

        // ADDITIONAL
        $("#add-additional-cost").on("click", function() {
            var html = `
                <div class="row mb-2">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label><strong>Name Of Cost :</strong></label>
                            <input class="form-control" type="text" placeholder="Name" name="additional[${a}][name]" required />
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label><strong>Cost :</strong></label>
                            <input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="additional[${a}][cost]" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <button type="button" style="margin-top: 32px;" class="btn btn-danger btn-sm btn-circle remove-row"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>`;
            $(".additional-cost-container").append(html);
            a++;
        });

        // Universal Remove Button
        $(document).on("click", '.remove-row', function() {
            $(this).closest('.row').remove();
        });

        // Email Fetch
        $('#select_regions').change(function() {
            var id = $(this).val();
            var url = '{{ route("get-email-region", ":id") }}';
            url = url.replace(':id', id);
            $.ajax({
                url: url,
                type: 'get',
                dataType: 'json',
                success: function(response) {
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
