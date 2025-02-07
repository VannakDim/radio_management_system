<!-- filepath: /Users/t2/Desktop/Radio TEAM/radio_management_system/resources/views/admin/product/borrow/index.blade.php -->
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
                        <!--  Borrow Table -->
                        <a href="{{ route('borrow.create') }}"
                            class="btn btn-primary mb-3 d-flex align-items-center justify-content-center"
                            style="height: 50px">
                            <strong>NEW BORROW</strong></a>
                        <x-card-table title="BORROW RECORD" badge="warning">
                            <x-slot name="header">
                                <tr>
                                    <th>ID</th>
                                    <th style="width: 30%">Borrower</th>
                                    <th style="width: 30%" class="d-none d-md-table-cell">Purpose</th>
                                    <th style="width: 20%" class="d-none d-md-table-cell">Date</th>
                                    <th style="width: 30%" class="d-none d-md-table-cell"></th>
                                    <th></th>
                                </tr>
                            </x-slot>
                            <x-slot name="body">
                                @foreach ($borrows as $borrow)
                                    <tr>
                                        <td>{{ str_pad($borrow['id'], 2, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ $borrow->receiver }}</td>
                                        <td>{{ $borrow->purpose }}</td>
                                        <td>{{ $borrow->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            @if ($borrow->image)
                                                <a href="#"
                                                    onclick="openImageModal('{{ asset($borrow->image) }}'); return false;">
                                                    <span class="badge badge-success">Image</span>
                                                </a>
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
                                                            <a href="#" onclick="openPreview({{ $borrow->id }})"
                                                                class="text-dark">
                                                                <i class="fas fa-print mr-2"></i>Preview
                                                            </a>
                                                            <a href="{{ route('borrow.edit', $borrow->id) }}"
                                                                class="text-dark mt-2">
                                                                <i class="fas fa-edit mr-2"></i>Edit
                                                            </a>
                                                            @if ($borrow->image)
                                                                <a href="{{ route('borrow.download', $borrow->id) }}"
                                                                    class="text-dark mt-2">
                                                                    <i class="fas fa-download mr-2"></i>Download
                                                                </a>
                                                            @endif
                                                            <a href="{{ route('return.index', $borrow->id) }}" class="text-dark mt-2">
                                                                <i class="fas fa-undo mr-2"></i>Return
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
                        <div class="mt-4 text-center">
                            {{ $borrows->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('backend/assets/js/image-viewer.js') }}"></script>
    <script>
        function openPreview(id) {
            window.open(`/product/borrow/show/${id}`, 'ViewWindow',
                `width=${screen.width},height=${screen.height},top=0,left=0`);
            window.open(`/product/borrow/show/${id}`, 'PrintWindow',
                `width=${screen.width},height=${screen.height},top=0,left=0`);
        }
    </script>
@endsection
