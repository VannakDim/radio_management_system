<!-- filepath: /Users/t2/Desktop/Radio TEAM/radio_management_system/resources/views/admin/product/borrow/edit.blade.php -->
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
                                <h2 class="badge badge-info text-white">RETURN BORROWED PRODUCT</h2>
                            </div>

                            <div class="card-body">
                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif
                                <form id="uploadForm" method="POST" action="{{ route('borrow.return', $borrow->id) }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-md-6">

                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Returner name:</label>
                                                <input type="text" name="returner_name" class="form-control"
                                                    id="borrower_input" placeholder="Returner name" required
                                                    value="{{ $borrow->receiver }}">
                                            </div>

                                            <label for="model">Product return:</label>
                                            <table class="table table-bordered" id="itemsTable">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 50%">Model name</th>
                                                        <th style="width: 50%">S/N</th>
                                                        <th class="text-center">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Items will be added here dynamically -->
                                                </tbody>
                                            </table>
                                            <!-- Hidden input to store the items data -->
                                            <input type="hidden" name="items" id="itemsInput">


                                            @if ($borrow->accessory->count())
                                                <div id="accessoriesSection">
                                                    <table class="table table-bordered" id="accessoryTable">
                                                        <thead>
                                                            <tr>
                                                                <th style="width: 50%">Accessory name</th>
                                                                <th style="width: 50%">Quantity</th>
                                                                <th class="text-center">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <!-- Items will be added here dynamically -->
                                                        </tbody>
                                                    </table>
                                                    <!-- Hidden input to store the items data -->
                                                    <input type="hidden" name="accessories" id="accessoryInput">
                                                </div>
                                            @endif
                                            <label class="badge-warning">បញ្ជាក់:
                                                សម្ភារៈទាំងអស់ក្នុងបញ្ជីខាងលើជាសម្ភារៈដែលយកមកសង</label>

                                            <div class="form-group">
                                                <label for="note">Note:</label>
                                                <textarea name="note" class="form-control" id="note" rows="4" placeholder="Enter any notes here...">{{ old('note', $borrow->note) }}</textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="post-img" id="img-preview"
                                                style="display: flex; justify-content: center; align-items: center; background-image: url({{ $borrow->image ? asset($borrow->image) : asset('backend/assets/img/default-image.avif') }}); background-size: contain; background-repeat: no-repeat; background-position: center; width: 100%; height: 100%;">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Loading indicator -->
                                    <div id="loading" style="display: none;">
                                        Saving record..., please wait...
                                    </div>

                                    <div class="row px-3 pt-3">
                                        <button type="submit" class="ladda-button btn btn-primary mr-1"
                                            data-style="expand-left">
                                            <span class="ladda-label">Save return!</span>
                                            <span class="ladda-spinner"></span>
                                        </button>
                                        <a href="{{ route('borrow.index') }}" class="btn btn-secondary">Back</a>
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

@section('script')
    <script>
        // Get the input file and preview image elements
        const default_img = '{{ URL::to('') }}' + '/backend/assets/img/default-image.avif';
        const previewImage = document.getElementById('img-preview');
    </script>

    {{-- Submit the form via AJAX --}}
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
                    window.location.href =
                        '{{ route('borrow.index') }}'; // Redirect to the "index" route
                },
                error: function(xhr) {
                    // Hide loading indicator
                    $('#loading').hide();
                    alert('Upload failed: ' + xhr.responseJSON.message);
                }
            });
        });
    </script>

    {{-- Working with items --}}
    <script>
        // Array to store added items
        let items = [];

        $(document).ready(function() {
            // Loop through existing borrow details and add them to the items array
            @foreach ($borrow->details as $product)
                items.push({
                    model_id: '{{ $product->product_id }}',
                    model_text: '{{ $product->product->model->name }}',
                    serial_number: '{{ $product->product->PID }}',
                });
            @endforeach

            // Update the table with the existing items
            updateTable();
        });


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

    {{-- Working with accessories --}}
    <script>
        let accessories = [];

        $(document).ready(function() {
            if ({{ $borrow->accessory->count() }}) {
                // Loop through existing borrow details and add them to the items array
                @foreach ($borrow->accessory as $accessory)
                    accessories.push({
                        model_id: '{{ $accessory->model_id }}',
                        model_text: '{{ $accessory->model->name }}',
                        quantity: '{{ $accessory->quantity }}',
                    });
                @endforeach

                // Update the table with the existing items
                updateAccessoryTable();
            } else {
                accessoriesSection.style.display = 'none';
            }
        });

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
@endsection
