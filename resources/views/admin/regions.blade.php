@extends('admin.layouts.main')
@section('title', 'Regions')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="cust-page-head">
            <h1 class="h3 mb-2 text-gray-800">Regions</h1>
            <button type="button" class="btn btn-primary btn-circle" data-toggle="modal" data-target="#costModal">
                <i class="fas fa-plus-square"></i>
            </button>
        </div>

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">List of regions added by the admin</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="sites-dataTable" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Cost</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Cost</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                        </tfoot>
                        <tbody>
                        @foreach($regions as $region)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $region->name }}</td>
                                <td>{{ $region->cost }}</td>
                                <td>{{ $region->created_at }}</td>
                                <td>
                                    <a href="#" id="updateCostBtn" data-id="{{ $region->id }}" data-title="{{ $region->name }}" data-cost="{{ $region->cost }}" data-toggle="modal" data-target="#updateModal" class="btn btn-info btn-circle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ url('admin/region/delete/'.$region->id) }}" onclick="return confirm('Are you sure you want to delete this region?')" class="btn btn-danger btn-circle">
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

    <!-- Modal -->
    <div class="modal fade" id="costModal" tabindex="-1" role="dialog" aria-labelledby="costModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="costModalLabel">Add New Region</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="{{ route('add-region') }}">
                    <div class="modal-body">
                        <div class="form-group">
                            <input placeholder="Enter region name" type="text" class="form-control" name="region_name" required />
                        </div>
                        <div class="form-group">
                            <input placeholder="Enter region cost" type="text" class="form-control" name="region_cost" />
                        </div>
                        @csrf
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->

    <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="costModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="costModalLabel">Update Default Cost</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="{{ route('edit-region') }}">
                    <div class="modal-body">
                        <div class="form-group">
                            <input id="upd-name" placeholder="Enter default cost title" type="text" class="form-control" name="region_name" required />
                        </div>
                        <div class="form-group">
                            <input id="upd-cost" placeholder="Enter cost(optional)" type="text" class="form-control" name="region_cost" />
                        </div>
                        <input type="hidden" name="region_id" id="upd-id" />
                        @csrf
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('page-level-scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#sites-dataTable').dataTable();

            $(document).on("click", "#updateCostBtn", function() {
                var ID = $(this).data('id');
                var title = $(this).data('title');
                var defaultValue = $(this).data('cost');

                // Now add these values to the fields
                $("#upd-id").val(ID);
                $("#upd-name").val(title);
                $("#upd-cost").val(defaultValue);
            });
        });
    </script>
@endsection
