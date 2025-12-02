@extends('admin.layouts.main')
@section('title', 'Add Cost Template')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 custom-text-heading">Add Cost Template</h1>
    </div>
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
                    <div class="form-group" style="display:none;">
                        <label><strong>Water Email :</strong></label>
                        <input class="form-control water_email" type="email" placeholder="Water Email" name="water_email" value="{{ old('water_email') }}" />
                    </div>
                    <div class="form-group" style="display:none;">
                        <label><strong>Electricity Email :</strong></label>
                        <input class="form-control electricity_email" type="email" placeholder="Electricity Email" name="electricity_email" value="{{ old('electricity_email') }}" />
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
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="mr-2"><strong>Add Water In Cost : </strong></label>
                                <a href="javascript:void(0)" id="add-waterin-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a>
                            </div>
                        </div>
                        <div class="waterin-cost-container"></div>
                        
                        <div class="row mt-2">
                            <div class="col-md-2 offset-md-10">
                                <label><strong>Water In Total :</strong></label>
                                <input class="form-control" type="text" placeholder="Total" name="waterin_total" value="0" disabled />
                            </div>
                        </div>

                        <div class="row mb-3 mt-4">
                            <div class="col-md-12">
                                <label class="mr-2"><strong>Water in related Cost : </strong></label>
                                <a href="javascript:void(0)" id="add-waterin-additional-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a>
                            </div>
                        </div>
                        <div class="waterin-additional-cost-container"></div>
                        
                        <div class="row mt-2">
                            <div class="col-md-2 offset-md-10">
                                <label><strong>WaterIn related Total :</strong></label>
                                <input class="form-control" type="text" placeholder="Total" value="0" disabled />
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
                        <div class="waterout-cost-container"></div>
                        
                        <div class="row mt-2">
                            <div class="col-md-2 offset-md-10">
                                <label><strong>Water Out Total :</strong></label>
                                <input class="form-control" type="text" placeholder="Total" name="waterout_total" value="0" disabled />
                            </div>
                        </div>

                        <div class="row mb-3 mt-4">
                            <div class="col-md-12">
                                <label class="mr-2"><strong>Water out related Cost : </strong></label>
                                <a href="javascript:void(0)" id="add-waterout-additional-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a>
                            </div>
                        </div>
                        <div class="waterout-additional-cost-container"></div>
                        
                        <div class="row mt-2">
                            <div class="col-md-2 offset-md-10">
                                <label><strong>Waterout related Total:</strong></label>
                                <input class="form-control" type="text" placeholder="Total" value="0" disabled />
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
                        <div class="electricity-cost-container"></div>
                        
                        <div class="row mt-2">
                            <div class="col-md-2 offset-md-10">
                                <label><strong>Electricity Total :</strong></label>
                                <input class="form-control" type="text" placeholder="Total" name="electricity_total" value="0" disabled />
                            </div>
                        </div>

                        <div class="row mb-3 mt-4">
                            <div class="col-md-12">
                                <label class="mr-2"><strong>Electricity related Cost : </strong></label>
                                <a href="javascript:void(0)" id="add-electricity-additional-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a>
                            </div>
                        </div>
                        <div class="electricity-additional-cost-container"></div>
                        
                        <div class="row mt-2">
                            <div class="col-md-2 offset-md-10">
                                <label><strong>Electricity related Total :</strong></label>
                                <input class="form-control" type="text" placeholder="Total" value="0" disabled />
                            </div>
                        </div>
                    </div>

                    <!-- Additional Cost Section -->
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="mr-2"><strong>Additional Cost : </strong></label>
                            <a href="javascript:void(0)" id="add-additional-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a>
                        </div>
                    </div>
                    <div class="additional-cost-container"></div>

                    <div class="row">
                        <div class="col-md-4 ml-auto">
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
                    </div>
                </form>
            </div>
        </div>
    </div>

<script type="text/javascript">
    $(document).ready(function() {
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

        // Auto-add default rows when checkbox is enabled
        $('#waterchk').change(function() {
            if ($(this).is(':checked') && $('.waterin-cost-container .row').length === 0) {
                $('#add-waterin-cost').trigger('click');
                $('#add-waterin-cost').trigger('click');
            }
        });

        $('#electricitychk').change(function() {
            if ($(this).is(':checked') && $('.electricity-cost-container .row').length === 0) {
                $('#add-electricity-cost').trigger('click');
                $('#add-electricity-cost').trigger('click');
            }
        });

        var i = 0;
        var o = 0;
        var e = 0;
        var a = 0;
        var wa = 0;
        var wo = 0;
        var eo = 0;

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
                            <label>Min</label>
                            <input class="form-control allow_decimal" type="text" placeholder="Min litres" name="waterin[${i}][min]" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Max</label>
                            <input class="form-control allow_decimal" type="text" placeholder="Max litres" name="waterin[${i}][max]" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Cost</label>
                            <input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="waterin[${i}][cost]" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Total</label>
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
                            <label>Title</label>
                            <input class="form-control" type="text" placeholder="Title" name="waterin_additional[${wa}][title]" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Percentage</label>
                            <input class="form-control allow_decimal" type="text" step=any placeholder="%" name="waterin_additional[${wa}][percentage]" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Cost</label>
                            <input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="waterin_additional[${wa}][cost]" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Total</label>
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
                            <label>Min</label>
                            <input class="form-control allow_decimal" type="text" placeholder="Min" name="waterout[${o}][min]" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Max</label>
                            <input class="form-control allow_decimal" type="text" placeholder="Max" name="waterout[${o}][max]" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>%</label>
                            <input class="form-control allow_decimal" type="text" placeholder="%" name="waterout[${o}][percentage]" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Cost</label>
                            <input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="waterout[${o}][cost]" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Total</label>
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
                            <label>Title</label>
                            <input class="form-control" type="text" placeholder="Title" name="waterout_additional[${wo}][title]" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>%</label>
                            <input class="form-control allow_decimal" type="text" step=any placeholder="%" name="waterout_additional[${wo}][percentage]" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Cost</label>
                            <input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="waterout_additional[${wo}][cost]" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Total</label>
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
                            <label>Min</label>
                            <input class="form-control allow_decimal" type="text" placeholder="Min" name="electricity[${e}][min]" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Max</label>
                            <input class="form-control allow_decimal" type="text" placeholder="Max" name="electricity[${e}][max]" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Cost</label>
                            <input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="electricity[${e}][cost]" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Total</label>
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
                            <label>Title</label>
                            <input class="form-control" type="text" placeholder="Title" name="electricity_additional[${eo}][title]" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>%</label>
                            <input class="form-control allow_decimal" type="text" step=any placeholder="%" name="electricity_additional[${eo}][percentage]" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Cost</label>
                            <input class="form-control allow_decimal" type="text" placeholder="Cost" step=any name="electricity_additional[${eo}][cost]" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Total</label>
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
                            <label>Name Of Cost</label>
                            <input class="form-control" type="text" placeholder="Name" name="additional[${a}][name]" required />
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Cost</label>
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
</div>
@endsection
