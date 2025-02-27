@extends('admin.layouts.main')
@section('title', 'Sites')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 custom-text-heading">Property</h1>


        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Property Details</h6>
                <a href="{{ route('edit-property', $property->id) }}" class="text-decoration-none"
                    style="color: #fdd600; font-size: x-large;">
                    <i class="fas fa-edit"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Left Column for Details -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <p><strong>Property Manager:</strong> {{ $propertyManager->name }}</p>
                        </div>
                        <div class="form-group">
                            <p><strong>Name:</strong> {{ $property->name }}</p>
                        </div>
                        <div class="form-group">
                            <p><strong>Contact Person:</strong> {{ $property->contact_person }}</p>
                        </div>
                        <div class="form-group">
                            <p><strong>Address:</strong> {{ $property->address }}</p>
                        </div>

                    </div>


                    <div class="col-md-6">
                        <div class="form-group">
                            <p>
                                <strong>Tariff Template:</strong>
                                <span class="badge badge-primary" style="font-size: medium;">{{ $property->cost->template_name ?? 'No template' }}</span>
                            </p>
                        </div>
                        
                        <div class="form-group">

                            @php
                                function getDaySuffix($day)
                                {
                                    if (in_array($day % 10, [1, 2, 3]) && !in_array($day % 100, [11, 12, 13])) {
                                        return ['st', 'nd', 'rd'][($day % 10) - 1];
                                    }
                                    return 'th';
                                }
                                $billingDay = $property->billing_day;
                                $suffix = getDaySuffix($billingDay);
                            @endphp

                            <p><strong>Billing Period:</strong> {{ $billingDay }}{{ $suffix }} (Date to Date Billing
                                Period)</p>
                        </div>
                        <div class="form-group">
                            <p><strong>Phone:</strong> {{ $property->phone }}</p>
                        </div>
                        <div class="form-group">
                            <p><strong>Whatsapp:</strong> {{ $property->whatsapp }}</p>
                        </div>

                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <p><strong>Description:</strong></p>
                            <p>{{ $property->description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h1 class="h3 mb-2 custom-text-heading">Accounts</h1>

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">List of accounts added by users</h6>
                    <div>
                        <a href="{{ route('property.add-account-form', $property->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="sites-dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Site</th>
                                <th>Region</th>
                                <th>Acc Type</th>
                                <th>Account Holder</th>
                                <th>Acc Name</th>
                                <th>Acc Number</th>
                                {{-- <th>Billing Date</th> --}}
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            @foreach ($propertyAccounts as $account)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $account->site->title ?? '-' }}</td>
                                    <td>{{ $account->region->name ?? '-' }}</td>
                                    <td>{{ $account->accountType->type ?? '-' }}</td>
                                    <td>{{ $account->user->name ?? '-' }}</td>
                                    <td>{{ $account->account_name }}</td>
                                    <td>{{ $account->account_number }}</td>
                                    {{-- <td>{{ $account->billing_date }}{{ $account->billing_day_with_suffix }}</td> --}}

                                    {{-- <td>{{ $account->user->name }}</td>
                                <td>{{ $account->title }}</td>
                                <td>{{ $account->lat }}</td>
                                <td>{{ $account->lng }}</td>
                                <td>{{ $account->address }}</td>
                                <td>{{ $account->email }}</td>
                                <td>{{ $account->created_at }}</td> --}}
                                <td>
                                    <a href="{{ route('account.account-details', $account->id) }}"
                                        class="btn btn-success btn-circle">
                                        <i class="fas fa-book"></i>
                                    </a>
                                    <a href="{{ route('account.edit-account', $account->id) }}"
                                        class="btn btn-warning btn-circle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('account.delete-account', $account->id) }}"
                                        onclick="return confirm('Are you sure you want to delete this site?')"
                                        class="btn btn-danger btn-circle">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <a href="http://your-vue-project-url.com" 
                                        class="btn btn-primary btn-circle">
                                        {{-- <i class="fas fa-external-link-alt"></i> --}}
                                    </a>
                                </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <h1 class="h3 mb-2 custom-text-heading">Meters</h1>

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">List of meters added by users</h6>
                <div>
                    <a href="{{ route('property.add-meter-form', $property->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Create
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="acc-dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Meter Title</th>
                                <th>Meter Number</th>
                                <th>Meter Type</th>
                                <th>Account Name</th>
                                <th>Account Number</th>
                                <th>Account Holder</th>
                                <th>Created Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($propertyAccountsMeters as $meter)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $meter->meter_title }}</td>
                                    <td>{{ $meter->meter_number }}</td>
                                    <td>{{ $meter->meterTypes->title }}</td>
                                    <td>{{ $meter->account->account_name ?? '-' }}</td>
                                    <td>{{ $meter->account->account_number ?? '-' }}</td>
                                    <td>{{ $meter->account->user->name ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($account->created_at)->format('m/d/Y h:i A') }}</td>
                                    <td>
                                        <a href="{{ route('show-meter-detail', $meter->id) }}"
                                            class="btn btn-success btn-circle">
                                            <i class="fas fa-book"></i>
                                        </a>
                                        <a href="{{ url('admin/meter/edit/' . $meter->id) }}"
                                            class="btn btn-warning btn-circle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ url('admin/meter/delete/' . $meter->id) }}"
                                            onclick="return confirm('Are you sure you want to delete this meter?')"
                                            class="btn btn-danger btn-circle">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- <h1 class="h3 mb-2 custom-text-heading">Meters</h1> --}}

        <!-- DataTales Example -->
        {{-- <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">List of meters added under accounts</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="meter-dataTable" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Account Name</th>
                            <th>Meter Type</th>
                            <th>Meter Title</th>
                            <th>Meter Number</th>
                            <th>Created Date</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($userDetails->sites as $site)
                            @foreach ($site->account as $account)
                                @foreach ($account->meters as $meter)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $meter->account->account_name ?? '-' }}</td>
                                        <td>{{ $meter->meterTypes->title }}</td>
                                        <td>{{ $meter->meter_title }}</td>
                                        <td>{{ $meter->meter_number }}</td>
                                        <td>{{ $meter->created_at }}</td>
                                        <td>
                                            <a href="{{ url('admin/meter/edit/'.$meter->id) }}" class="btn btn-warning btn-circle">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ url('admin/meter/delete/'.$meter->id) }}" onclick="return confirm('Are you sure you want to delete this meter?')" class="btn btn-danger btn-circle">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div> --}}

        <h1 class="h3 mb-2 custom-text-heading">Meter Readings</h1>

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">List of readings recorded under meters</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="reading-dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Account</th>
                                <th>Account Number</th>
                                <th>Account Holder</th>
                                <th>Meter Title</th>
                                <th>Meter Number</th>
                                <th>Meter Type</th>
                                <th>Meter Reading</th>
                                <th>Meter Reading Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Account</th>
                                <th>Account Number</th>
                                <th>Account Holder</th>
                                <th>Meter Title</th>
                                <th>Meter Number</th>
                                <th>Meter Type</th>
                                <th>Meter Reading</th>
                                <th>Meter Reading Date</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                        <tbody>

                            @foreach ($propertyAccountsMetersReadings as $meterReading)
                       
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $meterReading->meter->account->account_name ?? '-' }}</td>
                                    <td>{{ $meterReading->meter->account->account_number ?? '-' }}</td>
                                    <td>{{ $meterReading->meter->account->user->name ?? '-' }}</td>
                                    <td>{{ $meterReading->meter->meter_title ?? '-' }}</td>
                                    <td>{{ $meterReading->meter->meter_number ?? '-' }}</td>
                                    <td>{{ $meterReading->meter->meterTypes->title ?? '-' }}</td>
                                    <td>{{ $meterReading->reading_value }}</td>
                                    <td>{{ \Carbon\Carbon::createFromFormat('d-m-Y', $meterReading->reading_date)->setTimezone('Africa/Johannesburg')->format('d/m/Y') }}</td>                                    {{-- <td>
                                                @if ($meterReading->reading_image)
                                                    <img src="{{ asset( $meterReading->reading_image) }}" alt="Reading Image" width="50" height="50" style="object-fit: cover; border-radius: 5px;">
                                                @else
                                                    No Image
                                                @endif
                                            </td> --}}
                                    <td>
                                        <a href="{{ url('admin/meter-reading/edit/' . $meterReading->id) }}"
                                            class="btn btn-warning btn-circle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ url('admin/meter-reading/delete/' . $meterReading->id) }}"
                                            onclick="return confirm('Are you sure you want to delete this meter reading?')"
                                            class="btn btn-danger btn-circle">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
@endsection

@section('page-level-scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#sites-dataTable').dataTable();
            $('#acc-dataTable').dataTable();
            $('#meter-dataTable').dataTable();
            $('#reading-dataTable').dataTable();
        });
    </script>
@endsection
