<!-- filepath: /Users/t2/Desktop/Radio TEAM/radio_management_system/resources/views/admin/product/stockout/edit.blade.php -->
@extends('admin.layout.admin')
@section('link')
    {{-- CK Editor --}}
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/44.0.0/ckeditor5.css" crossorigin>
@endsection

@section('main_body')
    <div class="py-12">
        <div class=" mx-auto">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-default">
                            <div class="card-header card-header-border-bottom">
                                <h2 class="badge badge-danger text-white">EDIT STOCK OUT</h2>
                            </div>

                            <div class="card-body">
                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif
                                <form id="uploadForm" method="POST" action="{{ route('stockout.update', $stockout->id) }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-md-6">

                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Receiver:</label>
                                                <input type="text" name="receiver" class="form-control"
                                                    id="receiver_input" placeholder="Receiver"
                                                    value="{{ $stockout->receiver }}">
                                            </div>

                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Purpose:</label>
                                                <input type="text" name="type" class="form-control"
                                                    id="exampleInputEmail1" placeholder="Purpose or unit"
                                                    value="{{ $stockout->type }}">
                                            </div>

                                            <div class="form-group">
                                                <label for="created_at">Created At:</label>
                                                <input type="text" name="created_at" class="form-control" id="created_at"
                                                    value="{{ $stockout->created_at ? $stockout->created_at->format('Y-m-d') : '' }}"
                                                    placeholder="YYYY-MM-DD" autocomplete="off">
                                            </div>

                                            @push('scripts')
                                            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
                                            <script>
                                                $(function() {
                                                    $('#created_at').inputmask('9999-99-99', { placeholder: 'YYYY-MM-DD' });
                                                });
                                            </script>
                                            @endpush

                                            <label for="model">Product detail:</label>
                                            <div class="row">
                                                <div class="col-md-7">
                                                    <div class="form-group">
                                                        <select name="models" id="model" class="form-control">
                                                            @foreach ($models as $model)
                                                                @if ($model->accessory != 1)
                                                                    <option value="{{ $model->id }}">
                                                                        {{ $model->name }}
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="form-group d-flex">
                                                        <input type="text" class="form-control mr-2" id="serial_number"
                                                            placeholder="Serial number" list="availableProducts">
                                                        <datalist id="availableProducts">
                                                            @foreach ($availableProducts as $product)
                                                                <option value="{{ $product->PID }}"></option>
                                                            @endforeach
                                                        </datalist>
                                                        <button type="button" class="btn btn-primary"
                                                            onclick="addItem(event)"><i class="fas fa-plus"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                            <table class="table table-bordered" id="itemsTable">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 50%">Model name</th>
                                                        <th style="width: 50%">S/N</th>
                                                        <th class="text-center"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Items will be added here dynamically -->
                                                </tbody>
                                            </table>
                                            <!-- Hidden input to store the items data -->
                                            <input type="hidden" name="items" id="itemsInput" required>


                                            <div class="form-group">
                                                <label for="accessory">With Accessory:</label>
                                                <label class="switch">
                                                    <input type="checkbox" id="withAccessories"
                                                        onchange="toggleAccessories()"
                                                        {{ $stockout->stockOutDetails->count() ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                            <div id="accessoriesSection"
                                                style="display: {{ $stockout->stockOutDetails ? 'block' : 'none' }};">
                                                <div class="row">
                                                    <div class="col-md-7">
                                                        <div class="form-group">
                                                            <select name="models" id="model_accessory"
                                                                class="form-control">
                                                                @foreach ($models as $model)
                                                                    @if ($model->accessory == 1)
                                                                        <option value="{{ $model->id }}"
                                                                            {{ $model->id == $stockout->accessory_model_id ? 'selected' : '' }}>
                                                                            {{ $model->name }}
                                                                        </option>
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="form-group flex">
                                                            <input type="number" class="form-control mr-2" id="quantity"
                                                                placeholder="Quantity"
                                                                value="{{ $stockout->accessory_quantity }}">
                                                            <button type="button" class="btn btn-primary"
                                                                onclick="addAccessory(event)">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <table class="table table-bordered" id="accessoryTable">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 50%">Accessory name</th>
                                                            <th style="width: 50%">Quantity</th>
                                                            <th class="text-center"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Items will be added here dynamically -->
                                                    </tbody>
                                                </table>
                                                <!-- Hidden input to store the items data -->
                                                <input type="hidden" name="accessories" id="accessoryInput" required>
                                            </div>

                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Photo</label>
                                                <input type="file" name="image" id="input-image" accept="image/*"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="post-img" id="img-preview"
                                                style="display: flex; justify-content: center; align-items: center; background-image: url({{ $stockout->image ? asset($stockout->image) : asset('backend/assets/img/default-image.avif') }}); background-size: contain; background-repeat: no-repeat; background-position: center; width: 100%; height: 100%;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Note:</label>
                                        <textarea name="note" class="form-control" id="exampleInputEmail1" placeholder="Description your note"
                                            rows="3">{{ $stockout->note }}</textarea>
                                    </div>

                                    <!-- Loading indicator -->
                                    <div id="loading" style="display: none;">
                                        Uploading..., please wait...
                                    </div>

                                    <button type="submit" class="ladda-button btn btn-primary float-right"
                                        data-style="expand-left">
                                        <span class="ladda-label">Update!</span>
                                        <span class="ladda-spinner"></span>
                                    </button>
                                    <a href="{{ url()->previous() }}" class="btn btn-secondary float-right"
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

@section('script')
    <script>
        // Get the input file and preview image elements
        const default_img = '{{ URL::to('') }}' + '/backend/assets/img/default-image.avif';
        const imageInput = document.getElementById('input-image');
        const previewImage = document.getElementById('img-preview');

        // Listen for the file input change event
        imageInput.addEventListener('change', function(event) {
            const file = event.target.files[0]; // Get the selected file

            if (file) {
                // Create a file reader
                const reader = new FileReader();

                // Load the image and set it as the src of the previewImage
                reader.onload = function(e) {
                    previewImage.style.backgroundImage = `url('${e.target.result}')`;
                    previewImage.style.display = 'block'; // Make the image visible
                };

                // Read the file as a data URL
                reader.readAsDataURL(file);
            } else {
                // If no file is selected, hide the image preview
                previewImage.style.backgroundImage = `url('${default_img}')`;
                previewImage.style.display = 'none';
            }
        });
    </script>
    <script>
        function toggleAccessories() {
            const accessoriesSection = document.getElementById('accessoriesSection');
            if (document.getElementById('withAccessories').checked) {
                accessoriesSection.style.display = 'block';
            } else {
                accessoriesSection.style.display = 'none';
                accessories = []; // Clear the accessories array
                updateAccessoryTable(); // Update the table to reflect the change
            }
        }

        let accessories = [];

        $(document).ready(function() {
            if ({{ $stockout->stockOutDetails->count() }}) {
                // Loop through existing stockin details and add them to the items array
                @foreach ($stockout->stockOutDetails as $detail)
                    accessories.push({
                        model_id: '{{ $detail->product_model_id }}',
                        model_text: '{{ $detail->product->name }}',
                        quantity: '{{ $detail->quantity }}',
                    });
                @endforeach

                // Update the table with the existing items
                updateAccessoryTable();
            }else{
                accessoriesSection.style.display = 'none';
            }
        });


        function addAccessory(event) {
            event.preventDefault(); // Prevent form submission
            const modelId = document.getElementById('model_accessory').value;
            const modelText = document.getElementById('model_accessory').options[document.getElementById('model_accessory')
                    .selectedIndex]
                .text;
            const quantity = document.getElementById('quantity').value;

            if (!modelId || !quantity) {
                alert('Please fill the product and quantity.');
                document.getElementById('quantity').focus();
                return;
            }

            // Check if the model_id already exists in the accessories array
            if (accessories.some(item => item.model_id === modelId)) {
                alert('This accessory has already been added.');
                return;
            }

            // Add item to the array
            accessories.push({
                model_id: modelId,
                model_text: modelText,
                quantity: quantity,
            });

            // Update the table
            updateAccessoryTable();

            // Clear the form fields
            // document.getElementById('model_accessory').value = '';
            document.getElementById('quantity').value = '';
        }

        // Function to update the table with added items
        function updateAccessoryTable() {
            const tableBody = document.querySelector('#accessoryTable tbody');
            tableBody.innerHTML = '';

            accessories.forEach((item, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                <td>${item.model_text}</td>
                <td>${item.quantity}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeAccessory(${index})"><i class="fas fa-trash-alt"></i></button>
                </td>
            `;
                tableBody.appendChild(row);
            });

            // Update the hidden input with the items data
            document.getElementById('accessoryInput').value = JSON.stringify(accessories);
        }

        // Function to remove an item from the list
        function removeAccessory(index) {
            accessories.splice(index, 1);
            updateAccessoryTable();
        }

        // Initial call to populate the table with existing accessories
        updateAccessoryTable();
    </script>

    <script>
        $('#uploadForm').on('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission

            // Show loading indicator
            $('#loading').show();

            // Submit the form via AJAX
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(response) {
                    // Hide loading indicator
                    $('#loading').hide();
                    alert(response.message);
                    window.location.href = document.referrer || '{{ route('stockout.index') }}'; // Redirect back or to index
                },
                error: function(xhr) {
                    // Hide loading indicator
                    $('#loading').hide();
                    alert('Upload failed: ' + xhr.responseJSON.message);
                }
            });
        });
    </script>
    <script>
        // Array to store added items
        let items = [];

        $(document).ready(function() {
            // Loop through existing stockin details and add them to the items array
            @foreach ($stockout->products as $detail)
                items.push({
                    model_id: '{{ $detail->product_id }}',
                    model_text: '{{ $detail->product->model->name }}',
                    serial_number: '{{ $detail->product->PID }}',
                });
            @endforeach

            // Update the table with the existing items
            updateTable();
        });

        // Function to add an item to the list
        function addItem(event) {
            event.preventDefault(); // Prevent form submission
            const modelId = document.getElementById('model').value;
            const modelText = document.getElementById('model').options[document.getElementById('model').selectedIndex]
                .text;
            const serial_number = document.getElementById('serial_number').value;

            if (!modelId || !serial_number) {
                alert('Please fill the product and serial_number.');
                return;
            }

            // Check if the serial_number already exists in the items array
            if (items.some(item => item.serial_number === serial_number)) {
                alert('This serial number has already been added.');
                return;
            }

            // Add item to the array
            items.push({
                model_id: modelId,
                model_text: modelText,
                serial_number: serial_number,
            });


            // Update the table
            updateTable();

            // Clear the form fields
            // document.getElementById('model').value = '';
            document.getElementById('serial_number').value = '';
        }

        // Function to update the table with added items
        function updateTable() {
            const tableBody = document.querySelector('#itemsTable tbody');
            tableBody.innerHTML = '';

            items.forEach((item, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                <td>${item.model_text}</td>
                <td>${item.serial_number}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(${index})"><i class="fas fa-trash-alt"></i></button>
                </td>
            `;
                tableBody.appendChild(row);
            });

            // Update the hidden input with the items data
            document.getElementById('itemsInput').value = JSON.stringify(items);
        }

        // Function to remove an item from the list
        function removeItem(index) {
            items.splice(index, 1);
            updateTable();
        }

        // Initial call to populate the table with existing items
        updateTable();
    </script>
@endsection
