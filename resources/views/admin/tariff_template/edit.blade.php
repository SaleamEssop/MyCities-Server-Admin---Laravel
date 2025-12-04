@extends('admin.layouts.main')
@section('title', 'Edit Tariff Template')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div style="float: right;">
        <form method="POST" action="{{ route('copy-tariff-template') }}">
            @csrf
            <button type="submit" class="btn btn-warning" style="background: green;">Make a Copy</button>
            <input type="hidden" name="id" value="{{ $tariff_template->id }}" />
        </form>
    </div>
    <h1 class="h3 mb-2 custom-text-heading">Edit Tariff Template</h1>
    
    <div id="tariff-template-app" 
         data-props="{{ json_encode([
             'regions' => $regions,
             'csrfToken' => csrf_token(),
             'submitUrl' => route('update-tariff-template'),
             'cancelUrl' => route('tariff-template'),
             'getEmailUrl' => route('get-email-region', ['id' => '__ID__']),
             'existingData' => $tariff_template
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
