@extends('admin.layouts.main')
@section('title', 'Users')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 text-gray-800">Edit Site</h1>

        <div class="cust-form-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('edit-site') }}">
                        <div class="form-group">
                            <select class="form-control" id="exampleFormControlSelect1" name="region_id" required>
                                <option disabled selected value="">--Select Region--</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->id }}" {{ ($site->region_id == $region->id) ? 'selected' : '' }}>{{ $region->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <select class="form-control" id="exampleFormControlSelect1" name="user_id" required>
                                <option disabled value="">--Select User--</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ ($site->user_id == $user->id) ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Enter title" name="title" required value="{{ $site->title }}">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Enter lat" name="lat" required value="{{ $site->lat }}">
                        </div>
                        <div class="form-group">
                            <input type="text" name="lng" class="form-control" id="exampleInputEmail1" required value="{{ $site->lng }}" aria-describedby="emailHelp" placeholder="Enter lng">
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" class="form-control" id="exampleInputPassword1" value="{{ $site->email }}" placeholder="Enter email">
                        </div>
                        <div class="form-group">
                            <textarea name="address" placeholder="Enter address" class="form-control" rows="4">{{ $site->address }}</textarea>
                        </div>
                        <input type="hidden" name="site_id" value="{{ $site->id }}" />
                        @csrf
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
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
