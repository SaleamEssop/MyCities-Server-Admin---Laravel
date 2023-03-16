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
                        <label><strong>Select Meter Type :</strong></label>
                        <select class="form-control" name="meter_type_id">
                            <option value="">Please select Meter Type</option>
                            @foreach($meterType as $meter)
                            <option value="{{$meter->id}}" {{ $region_cost->meter_type_id == $meter->id ? 'selected' : '' }}>{{$meter->title}}</option>
                            @endforeach
                        </select>
                    </div>
                    <hr>
                    @if($region_cost->water_in)
                    <label><strong>Add Water In Cost : </strong> <a href="#" id="add-waterin-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
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
                            <label><strong>Applicable Date :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="date" placeholder="Applicable Date" name="waterin[{{$key}}][date]" value="{{$value->date}}" required />
                            </div>
                        </div>

                        <div class="col-md-1">
                            <a href="#" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-cost-del-btn">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                    <div class="waterin-cost-container"></div>
                    @endif

                    <hr>
                    @if($region_cost->water_out)
                    <label><strong>Add Water Out Cost : </strong> <a href="#" id="add-waterout-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
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
                        <div class="col-md-3">
                            <label><strong>Cost :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="number" placeholder="Cost" step=any name="waterout[{{$key}}][cost]" value="{{$value->cost}}" required />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label><strong>Percentage :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="number" placeholder="Percentage" name="waterout[{{$key}}][percentage]" value="{{$value->percentage}}" required />
                            </div>
                        </div>

                        <div class="col-md-1">
                            <a href="#" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-cost-del-out-btn">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    
                    @endforeach
                    <div class="waterout-cost-container"></div>
                    @endif
                    <hr>

                    <label><strong>Additional Cost & VAT : </strong></label>
                    <div class="row">
                        <div class="col-md-2">
                            <label><strong>Garbage Collection :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="number" step=any placeholder="Garbage Collection" name="garbase_collection_cost" value="{{$region_cost->garbase_collection_cost}}" />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label><strong>Infrastructure Levy :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="number" step=any placeholder="Infrastructure Levy" name="infrastructure_levy_cost" value="{{$region_cost->infrastructure_levy_cost}}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label><strong>VAT Percentage :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="number" step=any placeholder="VAT Percentage" name="vat_percentage" value="{{$region_cost->vat_percentage}}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label><strong>VAT Rate :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="number" step=any placeholder="VAT Rate" name="vat_rate" value="{{$region_cost->vat_rate}}" />
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="id" value="{{ $region_cost->id }}" />
                    <button type="submit" class="btn btn-warning">Update</button>
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

        var i = <?php echo count(json_decode($region_cost->water_in)) ?>;
        console.log(i);    
        var o =<?php echo count(json_decode($region_cost->water_out)) ?>;
        console.log(o); 
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
                '<label><strong>Applicable Date :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="date" placeholder="Applicable Date" name="waterin[' + i + '][date]" required />' +
                '</div>' +
                '</div>' +

                '<div class="col-md-1">' +
                '<a href="#" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-cost-del-btn">' +
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
                '<div class="col-md-3">' +
                '<label><strong>Cost :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Cost" step=any name="waterout[' + o + '][cost]" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-3">' +
                '<label><strong>Percentage :</strong></label>' +
                '<div class="form-group">' +
                '<input class="form-control" type="number" placeholder="Percentage" name="waterout[' + o + '][percentage]" required />' +
                '</div>' +
                '</div>' +

                '<div class="col-md-1">' +
                '<a href="#" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-cost-del-btn">' +
                '<i class="fa fa-trash"></i>' +
                '</a>' +
                '</div>' +
                '</div>';

            $(".waterout-cost-container").append(html);
            o++
        });

        $(document).on("click", '.additional-cost-del-btn', function() {
            $(this).parent().parent().remove();
            i--;
        });
        $(document).on("click", '.additional-cost-del-out-btn', function() {
            $(this).parent().parent().remove();
            o--;
        });

        // $(document).on("click", '.additional-cost-del-btn', function() {
        //     var ID = $(this).data('id');
        //     if (ID) {
        //         var oldVal = $("#deletedCosts").val();
        //         var newVal = oldVal + ',' + ID;
        //         $("#deletedCosts").val(newVal);
        //     }
        //     $(this).parent().parent().remove();
        // });
    });
</script>
@endsection