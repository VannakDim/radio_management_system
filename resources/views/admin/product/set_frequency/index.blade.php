@extends('admin.layout.admin')

@section('main_body')
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

                        <x-card-table title="SET FREQUENCRY RECORD" badge="success">
                            <x-slot name="header">
                                <tr>
                                    <th>ID</th>
                                    <th style="width: 30%">អ្នកប្រគល់</th>
                                    <th style="width: 30%" class="d-none d-md-table-cell">អង្គភាព</th>
                                    <th style="width: 20%" class="d-none d-md-table-cell">កាលបរិច្ឆេទ</th>
                                    <th style="width: 30%" class="d-none d-md-table-cell">ចំនួនវិទ្យុ</th>
                                    <th></th>
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
                                        <td>
                                            @if (!empty($record->image))
                                                <button type="button" class="btn btn-info mt-1"
                                                    onclick="window.open('{{ asset('storage/' . $record->image) }}', 'ImagePopup', 'width=' + screen.width + ',height=' + screen.height + ',top=0,left=0')">
                                                    <i class="fas fa-image"></i>
                                                </button>
                                            @else
                                                <form action="{{ route('setfrequency.upload', $record->id) }}"
                                                    method="POST" enctype="multipart/form-data" style="display:inline;">
                                                    @csrf
                                                    <input type="file" name="file" id="fileInput{{ $record->id }}"
                                                        style="display:none;" onchange="this.form.submit()">
                                                    <button type="button" class="btn btn-secondary"
                                                        onclick="document.getElementById('fileInput{{ $record->id }}').click();">
                                                        <i class="fas fa-upload"></i>
                                                    </button>
                                                </form>
                                            @endif
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
                            {{ $set_frequency->links('vendor.pagination.custom') }}
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
                                    <td colspan="3" class="text-right text-danger strong"><strong>សរុប</strong></td>
                                    <td><strong class="badge badge-danger" style="font-size: 1rem;">{{ $totalRadioCount }}
                                            គ្រឿង</strong></td>
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
                                    <td colspan="2" class="text-right text-danger"><strong>សរុប</strong></td>
                                    <td><strong class="badge badge-danger"
                                            style="font-size: 1rem;">{{ $totalProductCount }} គ្រឿង</strong></td>
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
@endsection
