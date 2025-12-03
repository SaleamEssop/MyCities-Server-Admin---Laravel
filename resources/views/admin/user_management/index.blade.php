@extends('admin.layouts.main')
@section('title', 'User Management')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-2 custom-text-heading">User Management</h1>
        <p class="mb-4">Complete user setup from one screen - manage users, sites, accounts, meters, and readings all in one place.</p>

        <!-- User Management Vue Component -->
        <div id="user-management-app" data-props="{{ json_encode([
            'csrfToken' => csrf_token(),
            'users' => $users,
            'regions' => $regions,
            'accountTypes' => $accountTypes,
            'meterTypes' => $meterTypes,
            'apiUrls' => [
                'search' => route('user-management.search'),
                'store' => route('user-management.store'),
                'update' => url('admin/user-management'),
                'delete' => url('admin/user-management'),
                'getUserData' => url('admin/user-management'),
                'generateTestUser' => route('user-management.generate-test'),
                'deleteTestUsers' => route('user-management.delete-test'),
                'cloneUser' => url('admin/user-management/clone'),
            ]
        ]) }}">
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ mix('js/app.js') }}"></script>
@endsection
