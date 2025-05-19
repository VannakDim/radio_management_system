@extends('admin.layout.admin')

@section('main_body')
    <div class="py-12">
        <div class=" mx-auto">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Search</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.search') }}" method="GET">
                                    <div class="input-group mb-3">
                                        <input type="text" name="query" class="form-control"
                                            placeholder="Search by unit, name or radio ID" aria-label="Search query"
                                            aria-describedby="button-addon2" value="{{ request('query') }}" autofocus>
                                        <button class="btn btn-outline-secondary" type="submit"
                                            id="button-addon2">Search</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection