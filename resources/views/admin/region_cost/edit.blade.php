@extends('admin.layouts.main')
@section('title', 'Edit Cost Template')

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
    <h1 class="h3 mb-2 custom-text-heading">Edit Cost Template</h1>
    
    <div id="region-cost-app" 
         data-props="{{ json_encode([
             'regions' => $regions,
             'accountTypes' => $account_type,
             'csrfToken' => csrf_token(),
             'submitUrl' => route('update-region-cost'),
             'cancelUrl' => route('region-cost'),
             'getEmailUrl' => route('get-email-region', ['id' => '__ID__']),
             'existingData' => $region_cost
         ]) }}">
        <div class="text-center py-5">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->
@endsection

@section('script')
<script src="{{ mix('js/app.js') }}"></script>
@endsection
