@extends('admin.layout.admin')

@section('main_body')
    <div class="py-12">
        <div class=" mx-auto">
            <div class="container">
                <div class="row">
                    
                    <div class="col-md-12 mt-3">
                        <div class="card">
                            <div class="card-header">
                                <h4>Search Results</h4>
                            </div>
                            <div class="card-body">
                                {{-- {{ $products }} --}}
                                @if($products->isNotEmpty())
                                    <div class="mt-4">
                                        <h5><strong>Products</strong></h5>
                                        <div class="row">
                                            @foreach($products as $product)
                                                <div class="col-md-4 mb-4">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h6><strong>PID:</strong> {{ $product->PID }}</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <p><strong>Model Name:</strong> {{ $product->model->name }}</p>
                                                            <p><strong>Frequency:</strong> {{ $product->model->frequency }}</p>
                                                            <p><strong>Type:</strong> {{ $product->model->type }}</p>
                                                            <p><strong>Power:</strong> {{ $product->model->power }}</p>
                                                            @if($product->model->image)
                                                                <img src="{{ asset($product->model->image) }}" alt="Model Image" style="max-width: 100%; height: auto;">
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if($product_models->isNotEmpty())
                                    <div class="mt-4">
                                        <h5><strong>Product Models</strong></h5>
                                        <ul class="list-group">
                                            @foreach($product_models as $model)
                                                <li class="list-group-item">
                                                    <strong>Name:</strong> {{ $model->name }}<br>
                                                    <strong>Frequency:</strong> {{ $model->frequency }}<br>
                                                    <strong>Type:</strong> {{ $model->type }}<br>
                                                    <strong>Power:</strong> {{ $model->power }}<br>
                                                    @if($model->description)
                                                        <strong>Description:</strong> {{ $model->description }}<br>
                                                    @endif
                                                    @if($model->image)
                                                        <img src="{{ asset($model->image) }}" alt="Model Image" style="max-width: 100px; max-height: 100px;">
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                {{-- Results for Product Frequencies: --}}

                                @if($units->isNotEmpty())
                                    <div class="mt-4">
                                        
                                        <ul class="list-group">
                                            @foreach($units->sortBy('units.sort_index') as $result)
                                                <li class="list-group-item">
                                                    @if($query == $result->units->unit_name)
                                                        <strong>Unit:</strong><span class="badge-danger"> {{ $result->units->unit_name }}</span><br>
                                                    @else
                                                        <strong>Unit:</strong> {{ $result->units->unit_name }}<br>
                                                    @endif
                                                    <strong>Name:</strong> {{ $result->name }}<br>
                                                    <strong>Trimester:</strong> {{ $result->trimester }}<br>
                                                    <strong>Date of Setup:</strong> {{ $result->created_at }}<br>
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th style="width: 50px;">No</th>
                                                                <th style="width: 40%;">PID</th>
                                                                <th>Model</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($result->detail as $product)
                                                                <tr>
                                                                    <td>{{ sprintf('%02d', $loop->iteration) }}</td>
                                                                    <td>{{ $product->product->PID }}</td>
                                                                    <td>{{ $product->product->model->name }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                

                                {{-- Results for Set Frequencies: --}}
                                @if($set_frequencies->isNotEmpty())
                                    <div class="mt-4">
                                        <h5><strong>Set Frequencies</strong></h5>
                                        <ul class="list-group">
                                            @foreach($set_frequencies as $set_frequency)
                                                <li class="list-group-item">
                                                    <strong>Name:</strong> {{ $set_frequency->name }}<br>
                                                    <strong>Unit:</strong> {{ $set_frequency->unit }}<br>
                                                    <strong>Purpose:</strong> {{ $set_frequency->purpose }}<br>
                                                    <strong>Trimester:</strong> {{ $set_frequency->trimester }}<br>
                                                    <strong>Date of Setup:</strong> {{ $set_frequency->created_at }}<br>
                                                    <strong>Product Count:</strong> {{ $set_frequency->detail->count() }}<br>
                                                    @if($set_frequency->detail->isNotEmpty())
                                                        <ul class="mt-2">
                                                            @foreach($set_frequency->detail as $detail)
                                                                <li>
                                                                    <strong>PID:</strong> {{ $detail->product->PID }}<span class="mx-3 badge badge-danger">{{$detail->product->model->name}}</span><br>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                {{-- Results for Product Set Frequencies: --}}
                                @if($product_set_frequency)
                                    <div class="mt-4">
                                        <h5><strong>History of Set Frequency</strong></h5>
                                        <ul class="list-group">
                                            <li class="list-group-item">
                                                <strong>Name:</strong> {{ $product_set_frequency->name }}<br>
                                                <strong>Unit:</strong> {{ $product_set_frequency->unit }}<br>
                                                <strong>Purpose:</strong> {{ $product_set_frequency->purpose }}<br>
                                                <strong>Trimester:</strong> {{ $product_set_frequency->trimester }}<br>
                                                <strong>Date of Setup:</strong> {{ $product_set_frequency->date_of_setup }}<br>
                                                <strong>Created At:</strong> {{ $product_set_frequency->created_at }}<br>
                                                <strong>Updated At:</strong> {{ $product_set_frequency->updated_at }}<br>
                                                <strong>Product Details:</strong>
                                                @if($product_set_frequency->detail->isNotEmpty())
                                                    <ul class="mt-2">
                                                        @foreach($product_set_frequency->detail as $detail)
                                                            <li>
                                                                @if($detail->product->PID == $query)
                                                                <strong>PID:</strong><span class="badge-danger"> {{ $detail->product->PID }}</span>
                                                                @else
                                                                    <strong>PID:</strong> {{ $detail->product->PID }}
                                                                @endif
                                                                @if($detail->product->model)
                                                                    <span class="mx-3 badge badge-info">{{ $detail->product->model->name }}</span>
                                                                @endif
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </li>
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection