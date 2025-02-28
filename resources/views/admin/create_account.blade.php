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
                            <div class="form-group">
                                <label for="password" class="font-weight-bold">Password</label>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Enter password">
                            </div>
                            <div class="form-group">
                                <label for="contact_number" class="font-weight-bold">Contact Number</label>
                                <input type="text" class="form-control" id="contact_number" name="contact_number"
                                    placeholder="Enter contact number">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="site">Site:</label>
                            <input type="text" class="form-control" id="site" placeholder="Enter site"
                                name="site" required>
                            <ul id="suggestions-list" class="list-group position-absolute w-50" style="display: none;"></ul>
                        </div>

                        <div class="form-group mt-3">
                            <label for="email">Associated Email:</label>
                            <input type="text" class="form-control" id="fetched-email" name="fetched-email" readonly>
                        </div>

                        <!-- Region User Selection -->
                        <div class="form-group">
                            <label for="region-select" class="font-weight-bold">Select Region</label>
                            <select class="form-control" id="region-select" name="region_id">
                                <option disabled selected value="">-- Select Region --</option>
                                @foreach ($regions as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Electricity Email -->
                        <div class="form-group">
                            <label for="electricity-email" class="font-weight-bold">Electricity Email</label>
                            <input type="email" class="form-control" id="electricity-email" name="electricity_email"
                                readonly>
                        </div>

                        <!-- Water Email -->
                        <div class="form-group">
                            <label for="water-email" class="font-weight-bold">Water Email</label>
                            <input type="email" class="form-control" id="water-email" name="water_email" readonly>
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
                        @else
                            <div class="form-group">
                                <label>Billing Date: </label>
                                <input type="number" min="1" max="31" class="form-control"
                                    placeholder="Enter billing date" name="billing_date" required>
                            </div>
                        @endif

                        <div class="form-group">
                            <label>Optional Information: </label>
                            <input type="text" name="optional_info" class="form-control"
                                placeholder="Enter optional information">
                        </div>
                        <hr>
                        <p><u>Default Costs</u></p>
                        <div class="row">
                            <div class="col-md-4"><b>Title</b></div>
                            <div class="col-md-4"><b>Default Value</b></div>
                            <div class="col-md-4"><b>Your Value</b></div>
                        </div>
                        <br>
                        @foreach ($defaultCosts as $defaultCost)
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ $defaultCost->title }}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input class="form-control" type="text" value="{{ $defaultCost->value }}"
                                            readonly />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input class="form-control" type="number" name="default_cost_value[]" />
                                    </div>
                                </div>
                                <input type="hidden" name="default_ids[]" value="{{ $defaultCost->id }}" />
                            </div>
                        @endforeach
                        <hr>
                        <p>Fixed Costs</p>
                        <div class="fixed-cost-container"></div>
                        {{-- <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Enter title" name="additional_cost_name[]" required/>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Enter value" name="additional_cost_value[]" required/>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <a href="#" class="btn btn-sm btn-circle btn-danger">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </div> --}}
                        <a href="#" id="add-cost" class="btn btn-sm btn-primary btn-circle"><i
                                class="fa fa-plus"></i></a>
                        <br>
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
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#user-dataTable').dataTable();

            $("#add-cost").on("click", function() {

                var html = '<div class="row">\n' +
                    '                            <div class="col-md-4">\n' +
                    '                                <div class="form-group">\n' +
                    '                                    <input class="form-control" type="text" placeholder="Enter title" name="additional_cost_name[]" required/>\n' +
                    '                                </div>\n' +
                    '                            </div>\n' +
                    '                            <div class="col-md-4">\n' +
                    '                                <div class="form-group">\n' +
                    '                                    <input class="form-control" type="text" placeholder="Enter value" name="additional_cost_value[]" required/>\n' +
                    '                                </div>\n' +
                    '                            </div>\n' +
                    '                            <div class="col-md-4">\n' +
                    '                                <a href="#" style="margin-top: 6px" class="btn btn-sm btn-circle btn-danger additional-cost-del-btn">\n' +
                    '                                    <i class="fa fa-trash"></i>\n' +
                    '                                </a>\n' +
                    '                            </div>\n' +
                    '                        </div>'

                $(".fixed-cost-container").append(html);
            });

            $(document).on("click", '.additional-cost-del-btn', function() {
                $(this).parent().parent().remove();
            });

            // $(document).on("change", '#user-select', function () {
            //     let user_id = $(this).val();
            //     let token = $("[name='_token']").val();
            //     // Get list of accounts added under this user
            //     $.ajax({
            //         type: 'POST',
            //         dataType: 'JSON',
            //         headers: { 'X-CSRF-TOKEN': token },
            //         url: '/admin/accounts/get-user-sites',
            //         data: {user: user_id},
            //         success: function (result) {
            //             $('#site-select').empty();
            //             $.each(result.details, function(key, value) {
            //                 $('#site-select').append($('<option>', {
            //                     value: value.id,
            //                     text: value.title
            //                 }));
            //             });
            //             $('#site-select').prop('disabled', false);
            //         }
            //     });
            // });




            const siteInput = document.getElementById('site');
            const suggestionsList = document.getElementById('suggestions-list');
            const emailInput = document.getElementById('fetched-email');

            const GEOCODE_API_TOKEN =
                "AAPKc12c49d88ad5489486e82db8ebefb94aXNVU8kLARKQJ0rA5KFeUOYRjHqTU9l2phoZf1pFANCXNR-hkFOOQJmeFUYp4nnzQ";

            // Fetch location suggestions from ArcGIS API
            const fetchSuggestions = async (query) => {
                try {
                    const response = await axios.get(
                        `https://geocode-api.arcgis.com/arcgis/rest/services/World/GeocodeServer/suggest?f=pjson&countryCode=za&token=${GEOCODE_API_TOKEN}&text=${encodeURIComponent(query)}`
                    );
                    return response.data.suggestions || [];
                } catch (error) {
                    console.error('Error fetching suggestions:', error);
                    return [];
                }
            };

            // Fetch address details (latitude & longitude)
            const fetchAddressDetails = async (singleLine, magicKey) => {
                try {
                    const response = await axios.get(
                        `https://geocode-api.arcgis.com/arcgis/rest/services/World/GeocodeServer/findAddressCandidates?f=pjson&token=${GEOCODE_API_TOKEN}&singleLine=${encodeURIComponent(singleLine)}&magicKey=${magicKey}&outSR=4326&countryCode=ZAF`
                    );
                    return response.data.candidates[0] || null;
                } catch (error) {
                    console.error('Error fetching address details:', error);
                    return null;
                }
            };

            // Fetch email using location geometry
            const fetchEmailFromLocation = async (geometry) => {
                try {
                    const response = await axios.get(
                        "https://services3.arcgis.com/HO0zfySJshlD6Twu/arcgis/rest/services/MeterReadingSuburbs/FeatureServer/0/query", {
                            params: {
                                f: "json",
                                returnGeometry: false,
                                spatialRel: "esriSpatialRelIntersects",
                                geometryType: "esriGeometryPoint",
                                geometry: JSON.stringify(geometry),
                                inSR: 4326,
                                outFields: "*",
                                outSR: 4326
                            }
                        }
                    );
                    return response.data.features[0]?.attributes?.MREMAIL || "No email found";
                } catch (error) {
                    console.error('Error fetching email:', error);
                    return "No email found";
                }
            };

            // Handle input change and show suggestions
            const handleInputChange = async () => {
                const query = siteInput.value;

                if (query.length > 2) {
                    const suggestions = await fetchSuggestions(query);
                    suggestionsList.innerHTML = '';

                    if (suggestions.length > 0) {
                        suggestionsList.style.display = 'block';
                        suggestions.forEach(suggestion => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item';
                            li.textContent = suggestion.text;
                            li.onclick = async () => {
                                siteInput.value = suggestion.text;
                                suggestionsList.style.display = 'none';

                                const addressDetails = await fetchAddressDetails(suggestion
                                    .text, suggestion.magicKey);
                                if (addressDetails) {
                                    const geometry = {
                                        x: addressDetails.location.x,
                                        y: addressDetails.location.y
                                    };

                                    const email = await fetchEmailFromLocation(geometry);
                                    emailInput.value = email;
                                } else {
                                    emailInput.value = "No email found";
                                }
                            };
                            suggestionsList.appendChild(li);
                        });
                    } else {
                        suggestionsList.style.display = 'none';
                    }
                } else {
                    suggestionsList.style.display = 'none';
                }
            };

            siteInput.addEventListener('input', handleInputChange);
            document.addEventListener('click', (e) => {
                if (!siteInput.contains(e.target) && !suggestionsList.contains(e.target)) {
                    suggestionsList.style.display = 'none';
                }
            });


        });
    </script>

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



    {{-- get region emails script --}}
    <script>
        $(document).ready(function() {
            $('#region-select').change(function() {
                var regionId = $(this).val();

                if (regionId) {
                    $.ajax({
                        url: '/admin/get-region-emails/' + regionId,
                        type: 'GET',
                        success: function(response) {
                            if (response) {
                                $('#electricity-email').val(response.electricity_email || '');
                                $('#water-email').val(response.water_email || '');
                            }
                        }
                    });
                } else {
                    $('#electricity-email').val('');
                    $('#water-email').val('');
                }
            });
        });
    </script>


@endsection
