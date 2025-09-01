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
                                @if(session('alert'))
                                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                        {{ session('alert') }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body">

                                {{-- {{ $owners }} --}}
                                @if($owners->isNotEmpty())
                                    <div class="mt-4">
                                        <h5><strong>Owners</strong></h5>
                                        <ul class="list-group">
                                            @foreach($owners as $owner)
                                                <li class="list-group-item">
                                                    <strong>Name:</strong> {{ $owner->name }}<br>
                                                    @if($owner->phone)
                                                        <strong>Phone:</strong> {{ $owner->phone }}<br>
                                                    @endif
                                                    @if($owner->address)
                                                        <strong>Address:</strong> {{ $owner->address }}<br>
                                                    @endif
                                                    @if($owner->ownProducts && count($owner->ownProducts))
                                                        <div class="mt-2">
                                                            <strong>Owned Products:</strong>
                                                            <ul>
                                                                @foreach($owner->ownProducts as $ownProduct)
                                                                    <li>
                                                                        <strong>PID:</strong> {{ $ownProduct->product->PID }}<br>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if($brands->isNotEmpty())
                                    <div class="mt-4">
                                        <h5><strong>Brands</strong></h5>
                                        <ul class="list-group">
                                            @foreach($brands as $brand)
                                                <li class="list-group-item">
                                                    <strong>Model Name:</strong> {{ $brand->name }}<br>
                                                    @if($brand->image)
                                                        <img src="{{ asset($brand->image) }}" alt="Brand Logo" style="max-width: 100px; max-height: 100px;">
                                                    @endif
                                                    <br>
                                                    <strong>Frequency:</strong> {{ ucfirst($brand->frequency) }}<br>
                                                    @if($brand->type)
                                                        <strong>Type:</strong> {{ $brand->type }}<br>
                                                    @endif
                                                    @if($brand->brand_country)
                                                        <strong>Country:</strong> {{ $brand->brand_country }}<br>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

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

                                {{-- Result of Product Models --}}
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

                                {{-- Result of Product Stock-Outs --}}
                                @if($stock_outs->isNotEmpty())
                                    <div class="mt-4">
                                        <h5><strong>Product Stock-Outs</strong></h5>
                                        <ul class="list-group">
                                            @foreach($stock_outs as $stock_out)
                                                <li class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong>អ្នកប្រគល់:</strong> {{ $stock_out->user->name }}<br>
                                                            <strong>អ្នកទទួល:</strong> {{ $stock_out->receiver }}<br>
                                                            <strong>គោលបំណង:</strong> {{ $stock_out->type }}<br>
                                                            <strong>Created At:</strong> {{ $stock_out->created_at->timezone('Asia/Phnom_Penh')->format('Y-m-d H:i:s') }}<br>
                                                            @if($stock_out->note)
                                                                <strong>Note:</strong> {{ $stock_out->note }}<br>
                                                            @endif
                                                        </div>
                                                        @if($stock_out->image)
                                                            <img src="{{ asset($stock_out->image) }}" alt="Stock Out Image" style="max-width: 120px; max-height: 120px; cursor:pointer;"
                                                                 onclick="showStockOutImageModal('{{ asset($stock_out->image) }}')">
                                                            <div id="stockOutImageModal" class="modal" tabindex="-1" role="dialog" style="display:none; background: rgba(0,0,0,0.5); box-shadow: 0 0 30px 10px rgba(0,0,0,0.5);">
                                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                                    <div class="modal-content" style="box-shadow: 0 8px 40px 0 rgba(0,0,0,0.5);">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title">Stock Out Image</h5>
                                                                            <button type="button" class="close" onclick="closeStockOutImageModal()" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body text-center">
                                                                            <img id="stockOutImageModalImg" src="" alt="Stock Out Image" style="max-width:100%; max-height:600px;">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <script>
                                                                function showStockOutImageModal(src) {
                                                                    document.getElementById('stockOutImageModalImg').src = src;
                                                                    document.getElementById('stockOutImageModal').style.display = 'block';
                                                                }
                                                                function closeStockOutImageModal() {
                                                                    document.getElementById('stockOutImageModal').style.display = 'none';
                                                                }
                                                                // Optional: close modal when clicking outside
                                                                document.addEventListener('click', function(event) {
                                                                    var modal = document.getElementById('stockOutImageModal');
                                                                    if (modal && event.target === modal) {
                                                                        closeStockOutImageModal();
                                                                    }
                                                                });
                                                            </script>
                                                        @endif
                                                    </div>
                                                    @if($stock_out->products && count($stock_out->products))
                                                        <div class="mt-2">
                                                            <strong>Products:</strong>
                                                            <ul>
                                                                @foreach($stock_out->products as $product)
                                                                    <li>
                                                                        @if($product->product->PID == $query)
                                                                            <strong>PID:</strong> <span style="text-decoration: underline; text-decoration-color: red; text-decoration-thickness: 2px;">{{ $product->product->PID }}</span><br>
                                                                        @else
                                                                            <strong>PID:</strong> {{ $product->product->PID }}<br>
                                                                        @endif
                                                                        @if($product->note)
                                                                            <br><strong>Note:</strong> {{ $product->note }}
                                                                        @endif
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                

                                {{-- Results for Set Frequencies: --}}
                                {{-- {{ $set_frequencies}} --}}
                                @if($set_frequencies->isNotEmpty())
                                    <div class="mt-4">
                                        <h5><strong>Set Frequencies</strong></h5>
                                        <ul class="list-group">
                                            @foreach($set_frequencies as $set_frequency)
                                                <li class="list-group-item position-relative" style="min-height: 100px;">
                                                    @if($set_frequency->image)
                                                        <div style="position: absolute; top: 10px; right: 10px;">
                                                            <i class="fa fa-image" 
                                                               style="font-size: 2rem; color: #007bff; cursor: pointer; float: right;" 
                                                               onclick="showSetFrequencyImage('{{ asset('storage/' . $set_frequency->image) }}')"
                                                               title="View Image"></i>
                                                        </div>
                                                        <div id="setFrequencyImageModal" class="modal" tabindex="-1" role="dialog" style="display:none;">
                                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Record Image</h5>
                                                                        <button type="button" class="close" onclick="closeSetFrequencyImage()" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body text-center">
                                                                        <img id="setFrequencyImageModalImg" src="" alt="Set Frequency Image" style="max-width:100%; max-height:600px;">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <script>
                                                            function showSetFrequencyImage(src) {
                                                                document.getElementById('setFrequencyImageModalImg').src = src;
                                                                document.getElementById('setFrequencyImageModal').style.display = 'block';
                                                            }
                                                            function closeSetFrequencyImage() {
                                                                document.getElementById('setFrequencyImageModal').style.display = 'none';
                                                            }
                                                            // Optional: close modal when clicking outside
                                                            document.addEventListener('click', function(event) {
                                                                var modal = document.getElementById('setFrequencyImageModal');
                                                                if (modal && event.target === modal) {
                                                                    closeSetFrequencyImage();
                                                                }
                                                            });
                                                        </script>
                                                    @endif
                                                    <strong>Name:</strong> {{ $set_frequency->name }}<br>
                                                    <strong>Unit:</strong> {{ $set_frequency->unit }}<br>
                                                    <strong>Purpose:</strong> {{ $set_frequency->purpose }}<br>
                                                    <strong>Trimester:</strong> {{ $set_frequency->trimester }}<br>
                                                    <strong>Date of Setup:</strong> {{ $set_frequency->created_at->timezone('Asia/Phnom_Penh')->format('Y-m-d H:i:s') }}<br>
                                                    <strong>Product Count:</strong> {{ $set_frequency->detail->count() }}<br>
                                                    @if($set_frequency->detail->isNotEmpty())
                                                        <ul class="mt-2">
                                                            @foreach($set_frequency->detail as $detail)
                                                                <li>
                                                                    <strong>PID:</strong> {{ $detail->product->PID }}
                                                                    <span class="mx-3 badge badge-danger">{{ $detail->product->model->name }}</span><br>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                {{-- Results for Product With Model --}}
                                @if($product_by_model->isNotEmpty())
                                    <div class="mt-4">
                                        <h5><strong>Product List:</strong></h5>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style="width: 50px;">No</th>
                                                    <th>PID</th>
                                                    <th>Model</th>
                                                    <th class="d-none d-md-table-cell">Brand</th>
                                                    <th class="d-none d-md-table-cell">Created date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($product_by_model as $frequency)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $frequency->PID }}</td>
                                                        <td>{{ $frequency->model->name }}</td>
                                                        <td class="d-none d-md-table-cell">{{ $frequency->model->brand->brand_name }}</td>
                                                        <td class="d-none d-md-table-cell">{{ $frequency->created_at }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                {{-- Results for Product Set Frequencies: --}}
                                @if($product_set_frequency)
                                    <div class="mt-4">
                                        <h5><strong>History of Set Frequency</strong></h5>
                                        <ul class="list-group">
                                            <li class="list-group-item position-relative" style="min-height: 100px;">
                                                @if($product_set_frequency->image)
                                                    <div style="position: absolute; top: 10px; right: 10px;">
                                                        <i class="fa fa-image"
                                                           style="font-size: 2rem; color: #007bff; cursor: pointer; float: right;"
                                                           onclick="showSetFrequencyImage('{{ asset('storage/' . $product_set_frequency->image) }}')"
                                                           title="View Image"></i>
                                                    </div>
                                                    <div id="setFrequencyImageModal" class="modal" tabindex="-1" role="dialog" style="display:none;">
                                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Record Image</h5>
                                                                    <button type="button" class="close" onclick="closeSetFrequencyImage()" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body text-center">
                                                                    <img id="setFrequencyImageModalImg" src="" alt="Set Frequency Image" style="max-width:100%; max-height:600px;">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <script>
                                                        function showSetFrequencyImage(src) {
                                                            document.getElementById('setFrequencyImageModalImg').src = src;
                                                            document.getElementById('setFrequencyImageModal').style.display = 'block';
                                                        }
                                                        function closeSetFrequencyImage() {
                                                            document.getElementById('setFrequencyImageModal').style.display = 'none';
                                                        }
                                                        document.addEventListener('click', function(event) {
                                                            var modal = document.getElementById('setFrequencyImageModal');
                                                            if (modal && event.target === modal) {
                                                                closeSetFrequencyImage();
                                                            }
                                                        });
                                                    </script>
                                                @endif
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