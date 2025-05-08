@extends('admin.layout.admin')

@section('main_body')
    <div class="py-12">
        <div class=" mx-auto">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-default">
                            <div class="card-header card-header-border-bottom">
                                <h2 class="badge badge-danger text-white">ADD UNIT</h2>
                            </div>

                            <div class="card-body">
                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif
                                <form id="uploadForm" method="POST" action="{{ route('unit.store') }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="unit_name">Unit Name:</label>
                                                <input type="text" name="unit_name" class="form-control"
                                                    id="unit_name" placeholder="Enter unit name" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="sort_index">Sort Index:</label>
                                                <input type="number" name="sort_index" class="form-control" 
                                                    id="sort_index" placeholder="Enter sort index" 
                                                    value="{{ \App\Models\Unit::max('sort_index') + 1 }}" required>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    

                                    <!-- Loading indicator -->
                                    <div id="loading" style="display: none;">
                                        Uploading..., please wait...
                                    </div>

                                    <button type="submit" class="ladda-button btn btn-primary float-right"
                                        data-style="expand-left">
                                        <span class="ladda-label">Save!</span>
                                        <span class="ladda-spinner"></span>
                                    </button>
                                    <a href="{{ route('unit.list') }}" class="btn btn-secondary float-right"
                                        style="margin-right: 6px">Back</a>

                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
