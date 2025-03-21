@extends('admin.layouts.main')
@section('title', 'Sites')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 custom-text-heading">Account</h1>


        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Account Details</h6>
                <a href="{{ route('account.edit-account', $account->id) }}" class="text-decoration-none"
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
                            <p><strong>Property Name:</strong> {{ $property->name }}</p>
                        </div>

                        <!-- Display account details -->
                        <div class="form-group">
                            <p><strong>Account Name:</strong> {{ $account->account_name }}</p>
                        </div>
                        <div class="form-group">
                            <p><strong>Account Number:</strong> {{ $account->account_number }}</p>
                        </div>
                        <div class="form-group">
                            <p><strong>Account Type:</strong> {{ $account->accountType->type }}</p>
                        </div>
                    </div>
             
                    <div class="col-md-6">
                        <div class="form-group">
                            <p><strong>Property Contact Person:</strong> {{ $property->contact_person }}</p>
                        </div>
                        <div class="form-group">
                            <p><strong>Property Address:</strong> {{ $property->address }}</p>
                        </div>

                        <div class="form-group">
                            <p><strong>Account Site:</strong> {{ $account->site->title }}</p>
                        </div>
                        <div class="form-group">
                            <p><strong>Region Account:</strong> {{ $property->region->name ?? '' }}</p>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <h1 class="h3 mb-2 custom-text-heading">Meters</h1>

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">List of meters</h6>
                <div>
                    <a href="{{ route('account.add-account-meter-form', $account->id) }}" class="btn btn-primary btn-sm">
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
                                <th>User Name</th>
                                <th>Meter Title</th>
                                <th>Meter Number</th>
                                <th>Meter Type</th>
                                <th>Meter Category</th>
                                <th>Created Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($propertyAccountsMeters as $meter)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $meter->account->user->name }}</td>
                                    <td>{{ $meter->meter_title }}</td>
                                    <td>{{ $meter->meter_number }}</td>
                                    <td>{{ $meter->meterTypes->title }}</td>
                                    <td>{{ $meter->meterCategory->name }}</td>

                                    <td>{{ \Carbon\Carbon::parse($meter->created_at)->format('m/d/Y h:i A') }}</td>

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
                                <th>Account User</th>
                                <th>Meter Title</th>
                                <th>Meter Number</th>
                                <th>Meter Reading</th>
                                <th>Meter Reading Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Account User</th>
                                <th>Meter Title</th>
                                <th>Meter Number</th>
                                <th>Meter Reading</th>
                                <th>Meter Reading Date</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                        <tbody>

                            @foreach ($propertyAccountsMetersReadings as $meterReading)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $meterReading->meter->account->user->name ?? '-' }}</td>
                                    <td>{{ $meterReading->meter->meter_title ?? '-' }}</td>
                                    <td>{{ $meterReading->meter->meter_number ?? '-' }}</td>
                                    <td>{{ $meterReading->reading_value }}</td>

                                    <td>
                                        {{ $meterReading->reading_date ? \Carbon\Carbon::parse($meterReading->reading_date)
                                            ->setTimezone('Africa/Johannesburg')
                                            ->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    
                                    
                                    
                                    {{-- <td>

                                    <td>{{ \Carbon\Carbon::parse($meterReading->reading_date)->format('m/d/Y h:i A') }}</td>

                                    {{-- <td>
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
