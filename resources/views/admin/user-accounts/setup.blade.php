@extends('admin.layouts.main')
@section('title', 'User Accounts - Setup')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-2 custom-text-heading">User Accounts - Setup Wizard</h1>
        <p class="mb-4">Create a new user account with step-by-step guidance through user details, region selection, tariff template, and account configuration.</p>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Create Test User Button -->
        <div class="mb-4">
            <form action="{{ route('user-accounts.setup.create-test-user') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-flask me-1"></i> Create Test User
                </button>
            </form>
            <small class="text-muted ms-2">Creates a test user with demo data (default test credentials will be shown after creation)</small>
        </div>

        <!-- Setup Wizard Vue Component -->
        <div id="user-account-setup-app" data-props="{{ json_encode([
            'csrfToken' => csrf_token(),
            'regions' => $regions,
            'meterTypes' => $meterTypes,
            'apiUrls' => [
                'store' => route('user-accounts.setup.store'),
                'storeUserOnly' => route('user-accounts.setup.store-user-only'),
                'validateEmail' => route('user-accounts.setup.validate-email'),
                'validatePhone' => route('user-accounts.setup.validate-phone'),
                'getTariffTemplates' => route('user-accounts.setup.tariffs', ['regionId' => '__REGION_ID__']),
            ]
        ]) }}">
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ mix('js/app.js') }}"></script>
@endsection
