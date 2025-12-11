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

        <!-- Testing Section -->
        <div class="card mb-4 border-warning">
            <div class="card-header bg-warning text-dark">
                <i class="fas fa-flask me-1"></i> <strong>Testing</strong>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Create a complete test user with accounts, meters, and 3 months of readings.</p>
                
                <form action="{{ route('user-accounts.setup.create-test-user') }}" method="POST">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="create_new_region" id="createNewRegion" value="1">
                                <label class="form-check-label" for="createNewRegion">
                                    <i class="fas fa-map-marker-alt text-primary me-1"></i> Create from <strong>new Region</strong>
                                </label>
                                <small class="text-muted d-block">Creates a new "Test Region" with default settings</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="create_new_tariff" id="createNewTariff" value="1">
                                <label class="form-check-label" for="createNewTariff">
                                    <i class="fas fa-file-invoice-dollar text-success me-1"></i> Create from <strong>new Tariff Template</strong>
                                </label>
                                <small class="text-muted d-block">Creates a new tariff template with water tiers</small>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-warning btn-lg">
                        <i class="fas fa-user-plus me-1"></i> Create Test User Account
                    </button>
                    <small class="text-muted ms-2">Creates: User → Account(s) → Meters → Readings (3 months)</small>
                </form>
            </div>
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
