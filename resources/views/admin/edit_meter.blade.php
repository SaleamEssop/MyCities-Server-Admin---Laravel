@extends('admin.layouts.main')
@section('title', 'Meters')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 custom-text-heading">Edit Meter</h1>

        <div class="cust-form-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('edit-meter') }}">
                        <div class="form-group">
                            <select class="form-control" id="exampleFormControlSelect1" name="account_id" required>
                                <option disabled value="">--Select Account--</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ ($account->id == $meter->account_id)?'selected':'' }} >{{ $account->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <select class="form-control" id="exampleFormControlSelect1" name="meter_type_id" required>
                                <option disabled value="">--Select Meter Type--</option>
                                @foreach($meterTypes as $meterType)
                                    <option value="{{ $meterType->id }}" {{ ($meterType->id == $meter->meter_type_id)?'selected':'' }}>{{ $meterType->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" value="{{ $meter->meter_title }}" placeholder="Enter meter title" name="title" required>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" value="{{ $meter->meter_number }}" placeholder="Enter meter number" name="number" required>
                        </div>
                        <input type="hidden" name="meter_id" value="{{ $meter->id }}" />
                        @csrf
                        <button type="submit" class="btn btn-primary">Submit</button>
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
        });
    </script>
@endsection
