@extends('admin.layouts.main')
@section('title', 'Add Tiered Cost')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Add Tiered Cost (Sliding Scale)</h1>
    <p class="mb-4">Set the price for a specific usage range (e.g. 0-600 units).</p>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('region-cost-store') }}" method="POST">
                @csrf
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label><strong>Select Region:</strong></label>
                        <select name="region_id" class="form-control" required>
                            <option value="">-- Select Region --</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}">{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label><strong>Meter Type:</strong></label>
                        <select name="meter_type_id" class="form-control" required>
                            <option value="">-- Select Type --</option>
                            @foreach($meterTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->title }} ({{ $type->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr>
                <h6 class="font-weight-bold text-primary">Step / Tier Definition</h6>
                
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label><strong>Min Usage:</strong></label>
                        <input type="number" step="0.01" name="min" class="form-control" value="0" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label><strong>Max Usage:</strong></label>
                        <input type="number" step="0.01" name="max" class="form-control" placeholder="e.g. 600" required>
                        <small class="text-muted">Enter a very large number for "Unlimited"</small>
                    </div>
                    <div class="form-group col-md-4">
                        <label><strong>Rate per Unit (R):</strong></label>
                        <input type="number" step="0.0001" name="amount" class="form-control" placeholder="0.00" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Cost Tier</button>
                <a href="{{ route('region-cost') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
