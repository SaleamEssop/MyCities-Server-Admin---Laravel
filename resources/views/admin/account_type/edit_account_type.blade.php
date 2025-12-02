@extends('admin.layouts.main')
@section('title', 'Edit Account Type')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-2 custom-text-heading">Edit Account Type</h1>

        <div class="cust-form-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('edit-account-type') }}">
                        @csrf
                        <!-- Hidden ID field -->
                        <input type="hidden" name="id" value="{{ $accountType->id }}">
                        
                        <div class="form-group">
                            <label>Type Name: </label>
                            <input type="text" class="form-control" name="type" value="{{ $accountType->type }}" required>
                        </div>
                        
                        <br>
                        <button type="submit" class="btn btn-primary">Update Account Type</button>
                        <a href="{{ route('account-type-list') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
