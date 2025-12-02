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
                        <div class="d-flex align-items-center mb-3">
                            <label class="mr-2 mb-0"><strong>Add Water In Cost : </strong></label>
                            <a href="javascript:void(0)" id="add-waterin-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a>
                        </div>
                        <div class="waterin-cost-container"></div>
                        
                        <div class="row mt-2">
                            <div class="col-md-2 offset-md-9">
                                <label><strong>Water In Total :</strong></label>
                                <input class="form-control" type="text" placeholder="Total" name="waterin_total" value="0" disabled />
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-3 mt-4">
                            <label class="mr-2 mb-0"><strong>Water in related Cost</strong></label>
                            <a href="javascript:void(0)" id="add-waterin-additional-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a>
                        </div>
                        <div class="waterin-additional-cost-container"></div>
                        
                        <div class="row mt-2">
                            <div class="col-md-2 offset-md-9">
                                <label><strong>WaterIn related Total :</strong></label>
                                <input class="form-control" type="text" placeholder="Total" value="0" disabled />
                            </div>
                        </div>
                    </div>

                    <!-- Water Out Section -->
                    <div class="water_out_section">
                        <hr>
                        <div class="d-flex align-items-center mb-3">
                            <label class="mr-2 mb-0"><strong>Add Water Out Cost : </strong></label>
                            <a href="javascript:void(0)" id="add-waterout-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a>
                        </div>
                        <div class="waterout-cost-container"></div>
                        
                        <div class="row mt-2">
                            <div class="col-md-2 offset-md-9">
                                <label><strong>Water Out Total :</strong></label>
                                <input class="form-control" type="text" placeholder="Total" name="waterout_total" value="0" disabled />
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-3 mt-4">
                            <label class="mr-2 mb-0"><strong>Water out related Cost</strong></label>
                            <a href="javascript:void(0)" id="add-waterout-additional-cost"
