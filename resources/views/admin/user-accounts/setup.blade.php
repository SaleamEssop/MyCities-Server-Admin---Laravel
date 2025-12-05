@extends('admin.layouts.main')
@section('title', 'User Accounts - Setup')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-2 custom-text-heading">User Accounts - Setup Wizard</h1>
        <p class="mb-4">Create a new user account with step-by-step guidance through user details, region selection, tariff template, and account configuration.</p>

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
                'generateTestData' => route('user-accounts.setup.generate-test-data'),
            ]
        ]) }}">
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ mix('js/app.js') }}"></script>
@endsection
