@extends('admin.layouts.main')
@section('title', 'Users')
<style>
    .choices {
        margin-bottom: 0 !important;
    }
</style>
@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 custom-text-heading">Create new Property</h1>

        <div class="cust-form-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('store-property') }}">
                        @csrf
                        <div class="form-group">
                            <label><strong>Property Manager:</strong></label>
                            <select id="property-manager" name="property_manager_id" class="form-control">
                                <option value="">Select Property Manager</option>
                                @foreach ($propertyManagers as $manager)
                                    <option value="{{ $manager->id }}"
                                        {{ old('property_manager_id') == $manager->id ? 'selected' : '' }}>
                                        {{ $manager->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label><strong>Tariff Template/ Region:</strong></label>
                            <select id="region-cost" name="region_cost_id" class="form-control">
                                <option value="">Select Tariff Template</option>
                                @foreach ($RegionsAccountTypeCost as $item)
                                    <option value="{{ $item->id }}"
                                        {{ old('region_cost_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->template_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    
                        <div class="form-group">
                            <label><strong>Property Name :</strong></label>
                            <input type="text" class="form-control" value="{{ old('name') }}"
                                placeholder="Enter property name" name="name" required>
                        </div>
                    
                        <div class="form-group">
                            <label><strong>Contact Person :</strong></label>
                            <input type="text" class="form-control" value="{{ old('contact_person') }}"
                                placeholder="Enter contact person name" name="contact_person" required>
                        </div>
                    
                        <div class="form-group">
                            <label><strong>Address :</strong></label>
                            <input type="text" class="form-control" value="{{ old('address') }}"
                                placeholder="Enter property address" name="address" required>
                        </div>
                    
                        <div class="form-group">
                            <label><strong>Phone :</strong></label>
                            <input type="number" class="form-control" value="{{ old('phone') }}"
                                placeholder="Enter phone number" name="phone" required>
                        </div>
                    
                        <div class="form-group">
                            <label><strong>WhatsApp :</strong></label>
                            <input type="number" class="form-control" value="{{ old('whatsapp') }}"
                                placeholder="Enter WhatsApp number" name="whatsapp">
                        </div>
                    
                        <!-- select a Date for Bill Period -->
                        <div class="form-group">
                            <label><strong>Select Day for Billing Period (Date to Date Billing Period):</strong></label>
                            <select id="billing-day" class="form-control" name="billing_day" required>
                                <option value="">Select a Date</option>
                                @for ($i = 1; $i <= 31; $i++)
                                <option value="{{ $i }}" {{ old('billing_day') }}>{{ $i }}{{ $i == 1 ? 'st' : ($i == 2 ? 'nd' : ($i == 3 ? 'rd' : 'th')) }}</option>
                            @endfor
                            </select>
                            <small class="form-text text-muted">Select a day for the billing period (e.g., 20th). The system will calculate the billing period from this day to the same day next month.</small>
                        </div>
                        <div class="form-group">
                            <label><strong>Description :</strong></label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Enter property description" >{{ old('description') }}</textarea>
                        </div>
                    
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
 <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        ['property-manager', 'region-cost', 'billing-day'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                new Choices(element, {
                    searchEnabled: true,
                    itemSelectText: ''
                });
            }
        });
    });
</script>

@endsection
