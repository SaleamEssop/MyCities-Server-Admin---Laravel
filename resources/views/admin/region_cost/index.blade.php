@extends('admin.layouts.main')
@section('title', 'Region Costs')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Region Cost Structure</h1>
    <p class="mb-4">Manage sliding scale tariffs for each region and meter type.</p>

    @if(Session::has('alert-message'))
        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show">
            {{ Session::get('alert-message') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <a href="{{ route('region-cost-create') }}" class="btn btn-primary btn-sm float-right">Add New Tier</a>
            <h6 class="m-0 font-weight-bold text-primary">Current Tariffs</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Region</th>
                            <th>Meter Type</th>
                            <th>Step Range</th>
                            <th>Rate (R)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($costs) && $costs->count() > 0)
                            @foreach($costs as $cost)
                            <tr>
                                <td>{{ $cost->region->name ?? 'Unknown' }}</td>
                                <td>
                                    {{ $cost->meterType->title ?? 'Unknown' }} 
                                    <small class="text-muted">({{ $cost->meterType->unit ?? '' }})</small>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $cost->min }} - {{ $cost->max }}
                                    </span>
                                </td>
                                <td>R {{ number_format($cost->amount, 4) }}</td>
                                <td>
                                    <a href="{{ url('admin/region_cost/delete/'.$cost->id) }}" class="btn btn-danger btn-sm" onclick="return confirm('Delete this tier?')">Delete</a>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr><td colspan="5" class="text-center">No tariffs defined yet.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
