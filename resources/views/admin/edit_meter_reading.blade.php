@extends('admin.layouts.main')
@section('title', 'Meter Readings')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 custom-text-heading">Edit meter reading</h1>

        <div class="cust-form-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('edit-meter-reading') }}">
                        <div class="form-group">
                            <label><strong>Meter:</strong></label>
                            <select class="form-control" id="exampleFormControlSelect1" name="meter_id" required>
                                <option disabled value="">--Select Meter--</option>
                                @foreach ($meters as $meter)
                                    <option value="{{ $meter->id }}"
                                        {{ $meter->id == $meterReading->meter_id ? 'selected' : '' }}>
                                        {{ $meter->meter_title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label><strong>Meter Reading Date:</strong></label>
                            <input type="date" class="payment_date form-control"
                                value="{{ $meterReading->reading_date }}" placeholder="Enter meter reading date"
                                name="reading_date" required>
                        </div>
                        <div class="form-group">
                            <label><strong>Reading Value:</strong></label>
                            <input type="text" class="form-control" value="{{ $meterReading->reading_value }}"
                                placeholder="Enter meter reading value" name="reading_value" required
                                maxlength="{{ $meterReading->meter->meter_type_id == 1 ? 8 : ($meterReading->meter->meter_type_id == 2 ? 6 : '') }}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '');" id="readingValueInput">
                        </div>
                        <input type="hidden" name="meter_reading_id" value="{{ $meterReading->id }}" />
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('readingValueInput');
            const meterTypeId = {{ $meterReading->meter_type_id }};

            // Set max length based on meter_type_id
            if (meterTypeId === 1) {
                input.maxLength = 8;
            } else if (meterTypeId === 2) {
                input.maxLength = 6;
            }

            // Restrict input to numbers only and enforce max length
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, ''); // Allow only numbers
                if (meterTypeId === 1 && this.value.length > 8) {
                    this.value = this.value.slice(0, 8);
                } else if (meterTypeId === 2 && this.value.length > 6) {
                    this.value = this.value.slice(0, 6);
                }
            });
        });
    </script>
@endsection
