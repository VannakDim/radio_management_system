@extends('admin.layout.admin')

@section('main_body')
    <!-- Top Statistics -->
    <div class="py-12">
        <div class="mx-auto">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <!--  Set Frequency Update Table -->
                        <a href="{{ route('owners.create') }}"
                            class="btn btn-primary mb-3 d-flex align-items-center justify-content-center"
                            style="height: 50px">
                            <strong>NEW RECORD</strong></a>
                        <div class="mt-4 text-center mb-4">
                            {{ $owners->links('vendor.pagination.custom') }}
                        </div>
                        <x-card-table title="LIST OF OWNERS" badge="success">
                            <x-slot name="header">
                                <tr>
                                    <th>ID</th>
                                    <th style="width: 30%">ឈ្មោះម្ចាស់កម្មសិទ្ធ</th>
                                    <th style="width: 30%" class="d-none d-sm-table-cell">អង្គភាព</th>
                                    <th style="width: 30%" class="d-none d-sm-table-cell">លេខវិទ្យុ</th>
                                    <th></th>
                                    <th class="d-none d-md-table-cell"></th>
                                </tr>
                            </x-slot>
                            <x-slot name="body">
                                @foreach ($owners as $record)
                                    {{-- {{ $record }} --}}
                                    <tr>
                                        <td>{{ str_pad($record['id'], 2, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ $record->name }}</td>
                                        <td>{{ $record->unit->unit_name }}</td>
                                        <td>
                                        @foreach($record->ownProducts as $product)
                                            {{ $product->product->PID }}</br>
                                        @endforeach
                                        </td>


                                        <td class="text-right d-none d-md-table-cell">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Top Statistics -->

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
