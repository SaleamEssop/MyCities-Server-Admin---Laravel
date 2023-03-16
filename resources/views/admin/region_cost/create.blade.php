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
                        <label><strong>Select Meter Type :</strong></label>
                        <select class="form-control" name="meter_type_id">
                            <option value="">Please select Meter Type</option>
                            @foreach($meterType as $meter)
                            <option value="{{$meter->id}}">{{$meter->title}}</option>
                            @endforeach
                        </select>
                    </div>
                    <hr>

                    <label><strong>Add Water In Cost : </strong> <a href="#" id="add-waterin-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
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
                            <label><strong>Applicable Date :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="date" placeholder="Applicable Date" name="waterin[0][date]" required />
                            </div>
                        </div>

                        <div class="col-md-1">
                            <a href="#" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-cost-del-btn">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <div class="waterin-cost-container"></div>
                    <!-- start water out form -->
                    <hr>

                    <label><strong>Add Water Out Cost : </strong> <a href="#" id="add-waterout-cost" class="btn btn-sm btn-primary btn-circle"><i class="fa fa-plus"></i></a></label>
                    <div class="row">
                        <div class="col-md-2">
                            <label><strong>Min :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="number" placeholder="Min litres" name="waterout[0][min]" required />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label><strong>Max :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="number" placeholder="Max litres" name="waterout[0][max]" required />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label><strong>Cost :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="number" placeholder="Cost" step=any name="waterout[0][cost]" required />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label><strong>Percentage :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="number" placeholder="Percentage" name="waterout[0][percentage]" required />
                            </div>
                        </div>

                        <div class="col-md-1">
                            <a href="#" data-id="" style="margin-top: 35px;margin-left: -13px;" class="btn btn-sm btn-circle btn-danger additional-cost-del-out-btn">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <div class="waterout-cost-container"></div>
                    <!-- end water out form -->
                    <!-- start Additional cost form -->
                    <hr>

                    <label><strong>Additional Cost & VAT : </strong></label>
                    <div class="row">
                        <div class="col-md-2">
                            <label><strong>Garbage Collection :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="number" step=any placeholder="Garbage Collection" name="garbase_collection_cost" />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label><strong>Infrastructure Levy :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="number" step=any placeholder="Infrastructure Levy" name="infrastructure_levy_cost" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label><strong>VAT Percentage :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="number" step=any placeholder="VAT Percentage" name="vat_percentage" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label><strong>VAT Rate :</strong></label>
                            <div class="form-group">
                                <input class="form-control" type="number" step=any placeholder="VAT Rate" name="vat_rate" />
                            </div>
                        </div>
                    </div>

                    @csrf
                    <button type="submit" class="btn btn-warning">Save</button>
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

        var i = 1;
        var o = 1;
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
    });
</script>
@endsection