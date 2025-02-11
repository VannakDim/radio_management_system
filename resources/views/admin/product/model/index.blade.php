@extends('admin.layout.admin')

@section('main_body')
    <div class="py-12">
        <div class=" mx-auto">
            <div class="container">
                <div class="row">
                    <div class="col">
                        <a class="btn btn-primary mb-3 d-flex align-items-center justify-content-center" id="add-post" href="{{ route('product.model.create') }}"
                        style="height: 50px"><i class="bi bi-database-add"></i><strong> ADD NEW MODEL </strong></a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">

                        @session('success')
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>{{ session('success') }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            
                        @endsession
                        <div class="row">
                            @foreach ($data as $item)
                                <div class="col-md-6 col-xl-3">
                                    <div class="card mb-4">
                                        <div class="card-img-contain" style="background-image: url({{ asset($item['image']) }});">
                                        </div>
                                        <div class="card-body">
                                            <h4 class="card-title text-primary"><strong class="badge-primary kh-battambang px-1 mr-1">{{$item['model_name']}}</strong>{{$item['brand_name']}}</h4>
                                            <p class="badge badge-warning mr-2">{{ $item['frequency']}}</p><span  class="badge badge-success">{{$item['type']}}</span>
                                            <div class="row">
                                                <div class="col-10 text-right pr-0">
                                                    <h1>TOTAL:
                                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                                            {{ $item['stock_in'] }}
                                                        </span>
                                                    </h1>
                                                </div>
                                                
                                                <div class="col-10 text-right pr-0">
                                                    <h1>STOCK OUT:
                                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                            {{ $item['stock_out'] }}
                                                        </span>
                                                    </h1>
                                                </div>
                                                
                                                <div class="col-10 text-right pr-0">
                                                    <h1>BORROWED:
                                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                                                            {{ $item['borrowed'] }}
                                                        </span>
                                                    </h1>
                                                </div>
                                            </div>
                                            <a href="/product/model/edit/{{$item['id']}}" class="btn btn-outline-primary edit-button">Edit</a>
                                            <a class="btn btn-danger" href="{{ url('product/softDel/' . $item['id']) }}"
                                                href="">Delete</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection


