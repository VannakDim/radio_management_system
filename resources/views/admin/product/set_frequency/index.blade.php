@extends('admin.layout.admin')

@section('main_body')
    <div id="imageModal" class="modal" style="display:none; position:fixed; z-index:1999; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5);">
        <div class="modal-content" style="margin:auto; padding:20px; border:1px solid #888; width:80%; max-width:700px; background-color:#fff; position:relative;">
            <span class="close" onclick="closeImageModal()" style="position:absolute; top:10px; right:25px; color:#aaa; font-size:28px; font-weight:bold; cursor:pointer;">&times;</span>
            <img id="modalImage" src="" alt="Image" style="width:100%; cursor:zoom-in;" onclick="zoomImage(this)">
        </div>
    </div>

    <!-- Top Statistics -->
    <div class="py-12">
        <div class="mx-auto">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <!--  Stock Update Table -->
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
                                </tr>
                            </x-slot>
                            <x-slot name="body">
                                @foreach ($set_frequency as $record)
                                    <tr>
                                        <td>{{ str_pad($record['id'], 2, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ $record->name }}</td>
                                        <td>{{ $record->unit }}</td>
                                        <td>{{ $record->created_at->format('Y-m-d') }}</td>
                                        <td><span class="badge badge-secondary">{{ str_pad($record->detail->count(), 2, '0', STR_PAD_LEFT) }}</span></td>
                                        
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
                                                            {{-- <a href="{{ route('stockin.product', $record->id) }}"
                                                                class="text-dark mt-2">
                                                                <i class="fas fa-walkie-talkie mr-2"></i>Detail
                                                            </a>
                                                            <a href="{{ route('stockin.edit', $record->id) }}"
                                                                class="text-dark mt-2">
                                                                <i class="fas fa-edit mr-2"></i>Edit
                                                            </a>
                                                            @if ($record->image)
                                                                <a href="{{ route('stockin.download', $record->id) }}"
                                                                    class="text-dark mt-2">
                                                                    <i class="fas fa-download mr-2"></i>Download
                                                                </a>
                                                            @endif --}}
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
                                @foreach ($radio as $record)
                                    @if ($record->accessory == 0)
                                    <tr>
                                        <td>{{ str_pad($record['id'], 2, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ $record->brand->brand_name }}</td>
                                        <td>{{ $record->name }}</td>
                                        <td><span class="badge badge-info">{{ $record->product_count }}</span></td>
                                        
                                    </tr>
                                    @endif
                                @endforeach
                            </x-slot>
                        </x-card-table>
                        <div class="mt-4 text-center">
                            {{ $set_frequency->links() }}
                        </div>
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
    </script>
@endsection
