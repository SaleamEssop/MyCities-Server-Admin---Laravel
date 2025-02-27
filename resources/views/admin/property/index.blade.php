@extends('admin.layouts.main')
@section('title', 'Users')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 custom-text-heading">Properties</h1>

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">List of Properties</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="user-dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Property Manager</th>
                                <th>Tariff Template</th>
                                <th>Contact Person</th>
                                <th>Phone</th>
                                <th>Whatsapp</th>
                                <th>Billing Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Property Manager</th>
                                <th>Tariff Template</th>
                                <th>Contact Person</th>
                                <th>Phone</th>
                                <th>Whatsapp</th>
                                <th>Billing Date</th>
                                <th>Actions</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach ($properties as $property)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $property->name }}</td>
                                    <td>{{ $property->property_manager->name ?? '' }}</td>
                                    <td>{{ $property->cost->template_name ?? '' }}</td>
                                    <td>{{ $property->contact_person }}</td>
                                    <td>{{ $property->phone }}</td>
                                    <td>{{ $property->whatsapp }}</td>                                  
                                    <td>{{ $property->billing_day }}{{ $property->billing_day_with_suffix }}</td>
                                    <td>
                                        <a href="{{ route('show-property', $property->id) }}" class="btn btn-success btn-circle">
                                            <i class="fas fa-book"></i>
                                        </a>
                                        <a href="{{ route('edit-property', $property->id) }}" class="btn btn-warning btn-circle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ url('admin/property/delete/' . $property->id) }}"
                                           onclick="return confirm('Are you sure you want to delete this user? Please note that all data under this user will also get deleted.')"
                                           class="btn btn-danger btn-circle">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    
                                        <?php
                                        $whatsapp = $property->whatsapp;
                                        $whatsapp = preg_replace('/[^0-9+]/', '', $whatsapp);   
                                        if (!str_starts_with($whatsapp, '+')) {
                                            $whatsapp = '+27' . ltrim($whatsapp, '0'); 
                                        }
                                    ?>
                                    <a href="https://wa.me/{{ $whatsapp }}" class="btn btn-primary btn-circle" target="_blank" style="background-color: #1cc88a; border-color: #1cc88a;">
                                        <i class="fab fa-whatsapp" style="font-size: x-large;"></i>
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
            $('#user-dataTable').dataTable();
        });
    </script>
@endsection
