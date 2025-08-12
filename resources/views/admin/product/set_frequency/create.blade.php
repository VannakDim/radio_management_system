@extends('admin.layout.admin')

@section('main_body')
    <div class="py-12">
        <div class=" mx-auto">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-default">
                            <div class="card-header card-header-border-bottom">
                                <h2 class="badge badge-info text-white">SET FREQUENCY</h2>
                            </div>

                            <div class="card-body">
                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif
                                <form id="uploadForm" method="POST" action="{{ route('frequency.store') }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">

                                            <div class="form-group">
                                                <label for="name">Name:</label>
                                                <input type="text" name="name" class="form-control" id="name"
                                                    placeholder="ឈ្មោះ" required>
                                            </div>

                                            <div class="form-group">
                                                <label for="unit_id">ជ្រើសរើសអង្គភាព:</label>
                                                <select name="unit_id" class="form-control" id="unit_id" required>
                                                    <option value="" disabled selected>Select Unit</option>
                                                    @foreach ($units as $unit)
                                                        <option value="{{ $unit->id }}">{{ $unit->unit_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="unit">Note Unit:</label>
                                                <input type="text" name="unit" class="form-control" id="unit"
                                                    placeholder="កំណត់ចំណាំបន្ថែម(អង្គភាព)" required>
                                            </div>

                                            <div class="form-group">
                                                <label for="purpose">Purpose:</label>
                                                <input type="text" name="purpose" class="form-control" id="purpose"
                                                    placeholder="គោលបំណង" required>
                                            </div>

                                            <div class="form-group">
                                                <label for="trimester">Trimester:</label>
                                                <select name="trimester" class="form-control" id="trimester" required>
                                                    <option value="" disabled selected>Select Trimester</option>
                                                    <option value="1"
                                                        {{ old('trimester', $trimester ?? '') == 1 ? 'selected' : '' }}>
                                                        ត្រីមាស ១</option>
                                                    <option value="2"
                                                        {{ old('trimester', $trimester ?? '') == 2 ? 'selected' : '' }}>
                                                        ត្រីមាស ២</option>
                                                    <option value="3"
                                                        {{ old('trimester', $trimester ?? '') == 3 ? 'selected' : '' }}>
                                                        ត្រីមាស ៣</option>
                                                    <option value="4"
                                                        {{ old('trimester', $trimester ?? '') == 4 ? 'selected' : '' }}>
                                                        ត្រីមាស ៤</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="setup_date">Date of Setup:</label>
                                                <input type="date" name="setup_date" class="form-control" id="setup_date"
                                                    value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                                            </div>

                                            <label for="model">Product detail:</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <select name="models" id="model" class="form-control" disabled>
                                                            <option value="" disabled selected>Auto select</option>
                                                            @foreach ($models as $model)
                                                                @if ($model->accessory == 0)
                                                                    <option value="{{ $model->id }}">
                                                                        {{ $model->name }}
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group d-flex">
                                                        <input type="text" class="form-control mr-2" id="serial_number"
                                                            placeholder="Serial number" list="availableProducts"
                                                            value="">
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
                                                        <th>#</th>
                                                        <th style="width: 50%">Model name</th>
                                                        <th style="width: 50%">S/N</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Items will be added here dynamically -->
                                                </tbody>
                                            </table>
                                            <!-- Hidden input to store the items data -->
                                            <input type="hidden" name="items" id="itemsInput" required>

                                        </div>
                                        <div class="col-md-6">
                                            <div class="post-img" id="img-preview"
                                                style="display: flex; justify-content: center; align-items: center; background-image: url({{ asset('backend/assets/img/default-image.avif') }}); background-size: contain; background-repeat: no-repeat; background-position: center; width: 100%; height: 100%;">
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Loading indicator -->
                                    {{-- <div id="loading" style="display: none;">
                                        Uploading..., please wait...
                                    </div> --}}

                                    <button type="submit" class="ladda-button btn btn-primary float-right"
                                        data-style="expand-left">
                                        <span class="ladda-label">Save!</span>
                                        <span class="ladda-spinner"></span>
                                    </button>
                                    <a href="{{ route('frequency.index') }}" class="btn btn-secondary float-right"
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
        $(document).ready(function() {

            $('#name').focus();
        });

        // Add event listener for pressing Enter key on serial_number input
        $('#serial_number').on('keypress', function(event) {
            if (event.key === 'Enter') {
                addItem(event);
            }
        });

        // Function to toggle the accessories section
        function toggleAccessories() {
            const accessoriesSection = document.getElementById('accessoriesSection');
            if (document.getElementById('withAccessories').checked) {
                accessoriesSection.style.display = 'block';
            } else {
                accessoriesSection.style.display = 'none';
            }
        }
    </script>
    <script>
        $('#uploadForm').on('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission

            // Show loading indicator
            // $('#loading').show();

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

                    // alert(response.message);
                    alert(response.message);
                    location.reload(); // Reload the page to see the new record
                },
                error: function(xhr, status, error) {
                    // Hide loading indicator
                    $('#loading').hide();

                    // Display error messages
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessage = '';
                        for (let key in errors) {
                            if (errors.hasOwnProperty(key)) {
                                errorMessage += errors[key].join(', ') + '\n';
                            }
                        }
                        alert(errorMessage);
                    } else {
                        alert(xhr.responseJSON.message);
                    }

                }
            });
        });
    </script>
    <script>
        // Array to store added items
        let items = [];

        // Function to add an item to the list
        function addItem(event) {
            event.preventDefault(); // Prevent form submission
            const modelId = document.getElementById('model').value;
            const modelText = document.getElementById('model').options[document.getElementById('model').selectedIndex]
                .text;
            const serial_number = document.getElementById('serial_number').value;

            // Check if modelId and serial_number are not empty
            if (!serial_number) {
                alert('Please fill the serial number.');
                document.getElementById('serial_number').focus();
                return;
            }

            // Check if the serial number already exists in the items array
            if (items.some(item => item.serial_number === serial_number)) {
                alert('This serial number already exists.');
                return;
            }

            // Find product by PID
            const product = @json($availableProducts).find(p => p.PID === serial_number);

            if (!product) {
                if (!modelId) {
                    document.getElementById('model').removeAttribute('disabled');
                    alert('លេខវិទ្យុនេះមិនមានក្នុងប្រព័ន្ធទេ។ សូមជ្រើសរើសម៉ូដែលវិទ្យុដើម្បីបញ្ចូលក្នុងប្រព័ន្ធ!');
                    document.getElementById('model').focus();
                    return;
                } else {
                    items.push({
                        model_id: modelId,
                        model_text: modelText,
                        serial_number: serial_number,
                    });
                }
            } else {
                // Add item to the array
                items.push({
                    product_id: product.id,
                    model_id: modelId,
                    model_text: product.model_name,
                    serial_number: serial_number,
                });
            }

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
                <td>${index + 1}</td>
                <td>${item.model_text}</td>
                <td>${item.serial_number}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(${index})"><i class="fas fa-trash-alt"></i></button>
                </td>
            `;
                tableBody.appendChild(row);
            });

            // Update the hidden input with the items data
            document.getElementById('itemsInput').value = JSON.stringify(items);
            document.getElementById('serial_number').focus();
        }

        // Function to remove an item from the list
        function removeItem(index) {
            items.splice(index, 1);
            updateTable();
        }
    </script>
@endsection
