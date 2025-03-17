@extends('admin.layouts.main')
@section('title', 'Accounts')
<style>
    #site-suggestions {
        position: absolute;
        width: 100%;
        max-height: 150px;
        overflow-y: auto;
        border: 1px solid #ddd;
        border-top: none;
        background-color: white;
        z-index: 1000;
        padding-left: 0;
    }

    #site-suggestions .site-suggestion {
        cursor: pointer;
    }
</style>
@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 custom-text-heading">Create new Account</h1>

        <div class="cust-form-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('add-account') }}">
                        @csrf
                        @if (isset($property))

                            <div class="form-group">
                                <label for="name" class="font-weight-bold">Property</label>
                                <input type="hidden" name="property_id" value="{{ $property->id ?? '' }}">
                                <input type="text" class="form-control" value="{{ $property->name ?? '' }}" readonly>

                            </div>
                        @else
                            <div class="form-group">
                                <label for="user-select" class="font-weight-bold">Select Property</label>
                                <select class="form-control" id="property-select" name="property_id">
                                    <option disabled selected value="">-- Select Property --</option>
                                    @foreach ($properties as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif


                        <div class="form-group">
                            <label for="user-option" class="font-weight-bold">User Selection</label>
                            <select class="form-control" id="user-option" name="user_option" required>
                                <option value="" disabled selected>-- Choose an option --</option>
                                <option value="existing">Select Existing User</option>
                                <option value="new">Create New User</option>
                            </select>
                        </div>

                        <!-- Existing User Selection -->
                        <div id="existing-user-section" class="form-group d-none">
                            <label for="user-select" class="font-weight-bold">Select User</label>
                            <select class="form-control" id="user-select" name="user_id">
                                <option disabled selected value="">-- Select User --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- New User Form -->
                        <div id="new-user-section" class="d-none">
                            <div class="form-group">
                                <label for="name" class="font-weight-bold">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="Enter full name">
                            </div>
                            <div class="form-group">
                                <label for="email" class="font-weight-bold">Email Address</label>
                                <input type="email" class="form-control" name="email" placeholder="Enter email">
                            </div>
                            {{-- <div class="form-group">
                                <label for="password" class="font-weight-bold">Password</label>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Enter password">
                            </div> --}}
                            <div class="form-group">
                                <label for="contact_number" class="font-weight-bold">Contact Number</label>
                                <input type="text" class="form-control" id="contact_number" name="contact_number"
                                    placeholder="Enter contact number">
                            </div>
                        </div>

             
                        <!-- Region User Selection -->
                        <div class="form-group">
                            <label for="user-select" class="font-weight-bold">Select Account Type</label>
                            <select class="form-control" id="account-type-select" name="account_type_id">
                                <option disabled selected value="">-- Select Account Type --</option>
                                @foreach ($accountTypes as $item)
                                    <option value="{{ $item->id }}">{{ $item->type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Account Title: </label>
                            <input type="text" class="form-control" placeholder="Enter account title" name="title"
                                required>
                        </div>
                        <div class="form-group">
                            <label>Account Number: </label>
                            <input type="text" class="form-control" placeholder="Enter account number" name="number"
                                required>
                        </div>
                        @if (isset($property))
                            <div class="form-group">
                                <label>Billing Date: </label>
                                <input type="number" min="1" max="31" class="form-control"
                                    placeholder="Enter billing date" name="billing_date" readonly
                                    value="{{ $property->billing_day }}">
                            </div>
                        {{-- @else
                            <div class="form-group">
                                <label>Billing Date: </label>
                                <input type="number" min="1" max="31" class="form-control"
                                    placeholder="Enter billing date" name="billing_date" required>
                            </div> --}}
                        @endif

                        <div class="form-group">
                            <label>Optional Information: </label>
                            <input type="text" name="optional_info" class="form-control"
                                placeholder="Enter optional information">
                        </div>
                   
                        <br>
                   
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
@endsection

@section('page-level-scripts')


    {{-- user selection script --}}
    <script>
        document.getElementById('user-option').addEventListener('change', function() {
            let selectedOption = this.value;
            let existingUserSection = document.getElementById('existing-user-section');
            let newUserSection = document.getElementById('new-user-section');

            if (selectedOption === "existing") {
                existingUserSection.classList.remove('d-none');
                newUserSection.classList.add('d-none');
            } else if (selectedOption === "new") {
                existingUserSection.classList.add('d-none');
                newUserSection.classList.remove('d-none');
            } else {
                existingUserSection.classList.add('d-none');
                newUserSection.classList.add('d-none');
            }
        });
    </script>

    {{-- select2 script --}}
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            ['user-select', 'region-select', 'account-type-select', 'property-select'].forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    new Choices(element, {
                        searchEnabled: true,
                        itemSelectText: ''
                    });
                }
            });
        });
    </script>



   


@endsection
