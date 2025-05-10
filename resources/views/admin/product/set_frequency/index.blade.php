@extends('admin.layout.admin')

@section('main_body')
    <div id="imageModal" class="modal"
        style="display:none; position:fixed; z-index:1999; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5);">
        <div class="modal-content"
            style="margin:auto; padding:20px; border:1px solid #888; width:80%; max-width:700px; background-color:#fff; position:relative;">
            <span class="close" onclick="closeImageModal()"
                style="position:absolute; top:10px; right:25px; color:#aaa; font-size:28px; font-weight:bold; cursor:pointer;">&times;</span>
            <img id="modalImage" src="" alt="Image" style="width:100%; cursor:zoom-in;" onclick="zoomImage(this)">
        </div>
    </div>

    <!-- Top Statistics -->
    <div class="py-12">
        <div class="mx-auto">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <!--  Set Frequency Update Table -->
                        <a href="{{ route('frequency.create') }}"
                            class="btn btn-primary mb-3 d-flex align-items-center justify-content-center"
                            style="height: 50px">
                            <strong>NEW RECORD</strong></a>
                        <div class="row">
                            <div class="col-md-9">
                            </div>
                            <div class="col-md-3 float-right">
                                <div class="form-group">
                                    <label for="trimesterSelect">ត្រីមាស:</label>
                                    <select id="trimesterSelect" class="form-control" onchange="changeTrimester(this.value)">
                                        <option value="">ជ្រើសរើសត្រីមាស </option>
                                        @foreach ($trimesters as $trimester)
                                            <option value="{{ $trimester['trimester'] }}">{{ $trimester['trimester'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <x-card-table title="SET FREQUENCRY RECORD" badge="success">
                            <x-slot name="header">
                                <tr>
                                    <th>ID</th>
                                    <th style="width: 30%">អ្នកប្រគល់</th>
                                    <th style="width: 30%" class="d-none d-md-table-cell">អង្គភាព</th>
                                    <th style="width: 20%" class="d-none d-md-table-cell">កាលបរិច្ឆេទ</th>
                                    <th style="width: 30%" class="d-none d-md-table-cell">ចំនួនវិទ្យុ</th>
                                    <th></th>
                                </tr>
                            </x-slot>
                            <x-slot name="body">
                                @foreach ($set_frequency as $record)
                                    <tr>
                                        <td>{{ str_pad($record['id'], 2, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ $record->name }}</td>
                                        <td>{{ $record->unit }}</td>
                                        <td>{{ $record->created_at->format('Y-m-d') }}</td>
                                        <td><span
                                                class="badge badge-secondary">{{ str_pad($record->detail->count(), 2, '0', STR_PAD_LEFT) }}</span>
                                        </td>

                                        <td class="text-right">
                                            <div class="dropdown show d-inline-block widget-dropdown">
                                                <a class="dropdown-toggle icon-burger-mini" href="#" role="button"
                                                    id="dropdown-recent-order5" data-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false" data-display="dynamic"></a>
                                                <ul class="dropdown-menu dropdown-menu-right"
                                                    aria-labelledby="dropdown-recent-order5" style="width: 120px;">
                                                    <li class="dropdown-item">
                                                        <div class="d-flex flex-column">
                                                            <a href="#" onclick="openPreview({{ $record->id }})"
                                                                class="text-dark">
                                                                <i class="fas fa-print mr-2"></i>Preview
                                                            </a>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </x-slot>
                        </x-card-table>
                        <div class="mt-4 text-center mb-4">
                            {{ $set_frequency->links() }}
                        </div>

                        {{-- <p>{{ $radio }}</p> --}}
                        <x-card-table title="RADIOS SET FREQUENCRY RECORD" badge="success">
                            <x-slot name="header">
                                <tr>
                                    <th>ID</th>
                                    <th style="width: 30%">Brand name</th>
                                    <th style="width: 30%">Model</th>
                                    <th style="width: 30%" class="d-none d-md-table-cell">ចំនួនវិទ្យុ</th>
                                </tr>
                            </x-slot>
                            <x-slot name="body">
                                @php
                                    $totalRadioCount = 0;
                                @endphp
                                @foreach ($radio as $record)
                                    @if ($record->accessory == 0 && $record->product_count > 0)
                                        @php
                                            $totalRadioCount += $record->product_count;
                                        @endphp
                                        <tr>
                                            <td>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                                            <td>{{ $record->brand->brand_name }}</td>
                                            <td>{{ $record->name }}</td>
                                            <td>{{ $record->product_count }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Total</strong></td>
                                    <td><strong>{{ $totalRadioCount }}</strong></td>
                                </tr>
                            </x-slot>
                        </x-card-table>

                        <x-card-table title="DETAILS" badge="info">
                            <x-slot name="header">
                                <div class="float-right mb-2">
                                    <button class="btn btn-secondary" onclick="printTable()">
                                        <i class="fas fa-print"></i> Print
                                    </button>
                                </div>
                                <tr>
                                    <th>ID</th>
                                    <th>Unit</th>
                                    <th>Product Count</th>
                                    <th>Model each unit</th>
                                </tr>
                            </x-slot>
                            <x-slot name="body">
                                @php
                                    $totalProductCount = 0;
                                @endphp
                                @foreach ($details->sortBy('unit_id') as $item)
                                    @php
                                        $totalProductCount += $item->product_count;
                                    @endphp
                                    <tr>
                                        <td>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ $item->unit }}</td>
                                        <td>{{ $item->product_count }}</td>
                                        <td>
                                            @foreach ($item->products as $product)
                                                {{ $product['model'] }} ({{ $product['count'] }})<br>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="2" class="text-right"><strong>Total</strong></td>
                                    <td><strong>{{ $totalProductCount }}</strong></td>
                                    <td></td>
                                </tr>
                            </x-slot>
                        </x-card-table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('backend/assets/js/image-viewer.js') }}"></script>
    <script>
        function openPreview(id) {
            window.open(`/product/set-frequency/print/${id}`, 'ViewWindow',
                `width=${screen.width},height=${screen.height},top=0,left=0`);
        }

        function printTable() {
            window.open(`/product/set-frequency-detail/print`, 'ViewWindow',
                `width=${screen.width},height=${screen.height},top=0,left=0`);
        }
    </script>
    <script>
        function changeTrimester(trimester) {
            if (trimester) {
            fetch(`{{ route('change.trimester') }}`, {
                method: 'POST',
                headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ trimester: trimester })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Trimester changed:', data);
                // Optionally, you can reload the page or update the UI here
            })
            .catch(error => console.error('Error:', error));
            }
        }
    </script>
@endsection
