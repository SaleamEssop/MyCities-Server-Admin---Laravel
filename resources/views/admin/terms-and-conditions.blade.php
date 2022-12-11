@extends('admin.layouts.main')
@section('title', 'Terms')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 text-gray-800">Terms & Conditions</h1>

        <div class="cust-form-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('updateTC') }}">
                        <div class="form-group">
                            <textarea rows="16" cols="25" class="form-control" name="tc">{{ $settings->terms_condition ?? '' }}</textarea>
                        </div>
                        <input type="hidden" name="setting_id" value="{{ $settings->id ?? '' }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
@endsection
