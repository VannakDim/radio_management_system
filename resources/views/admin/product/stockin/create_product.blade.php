@extends('admin.layout.admin')


@section('main_body')
    <div class="py-12">
        <div class=" mx-auto">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-default">
                            <div class="card-header card-header-border-bottom">
                                <h2 class="badge badge-success text-white">Add detail stock-in</h2>
                            </div>

                            <div class="card-body">
                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif
                                <form id="uploadForm" action="{{ route('stockin.store.product') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="model">Product detail:</label>
                                            <div class="row">
                                                <div class="col-md-7">
                                                    <div class="form-group">
                                                        <select name="models" id="model" class="form-control">
                                                            @foreach ($models as $model)
                                                                @if ($model->accessory != 1)
                                                                    <option value="{{ $model->id }}" data-qty="{{ $model->quantity }}" id="{{ $model->id }}">
                                                                        {{ $model->name }}
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="form-group flex">
                                                        <input type="text" class="form-control mr-2" id="serial_number"
                                                            placeholder="Serial number">
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

                                        <button type="submit" class="ladda-button btn btn-primary float-right"
                                            data-style="expand-left">
                                            <span class="ladda-label">Save!</span>
                                            <span class="ladda-spinner"></span>
                                        </button>
                                        <a href="{{ route('stockin.index') }}" class="btn btn-secondary float-right"
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

            // Set focus to the serial number input field
            document.getElementById('serial_number').focus();

            // Call addItem function when Enter key is pressed while focus is on serial_number
            document.getElementById('serial_number').addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    addItem(event);
                }
            });

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
                        window.location.href =
                            '{{ route('stockin.index') }}'; // Redirect to the "home" route
                        alert(response.message);
                    },
                    error: function(xhr) {
                        // Hide loading indicator
                        $('#loading').hide();
                        alert('Upload failed: ' + xhr.responseJSON.message);
                    }
                });
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
        const modelText = document.getElementById('model').options[document.getElementById('model').selectedIndex].text;
        const serial_number = document.getElementById('serial_number').value;

        // Check if the serial number already exists in the items array
        const serialExists = items.some(item => item.serial_number === serial_number);
        if (serialExists) {
            alert('This serial number already exists.');
            return;
        }

        // Check if the number of items for the selected model exceeds the available quantity
        const selectedModel = document.getElementById(modelId);
        const selectedModelQty = selectedModel.getAttribute('data-qty');
        const selectedModelItems = items.filter(item => item.model_id === modelId);
        if (selectedModelItems.length >= selectedModelQty) {
            alert('The number of items for this model exceeds the available quantity.');
            return;
        }
        // console.log(selectedModelItems.length + ' ' + selectedModelQty);


        if (!modelId || !serial_number) {
            alert('Please fill the product and serial_number.');
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
            <td>${index + 1}</td>
            <td>${item.model_text}</td>
            <td>${item.serial_number}</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm text-center" onclick="removeItem(${index})"><i class="fas fa-trash-alt"></i></button>
            </td>
        `;
            tableBody.appendChild(row);
        });

        // Update the hidden input with the items data
        document.getElementById('itemsInput').value = JSON.stringify(items);

        // Set focus to the serial number input field
        document.getElementById('serial_number').focus();
    }

    // Function to remove an item from the list
    function removeItem(index) {
        items.splice(index, 1);
        updateTable();
    }
</script>
@endsection
