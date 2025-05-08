@extends('admin.layout.admin')

@section('main_body')
    <div id="imageModal" class="modal" style="display:none; position:fixed; z-index:1999; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5);">
        <div class="modal-content" style="margin:auto; padding:20px; border:1px solid #888; width:80%; max-width:700px; background-color:#fff; position:relative;">
            <span class="close" onclick="closeImageModal()" style="position:absolute; top:10px; right:25px; color:#aaa; font-size:28px; font-weight:bold; cursor:pointer;">&times;</span>
            <img id="modalImage" src="" alt="Image" style="width:100%; cursor:zoom-in;" onclick="zoomImage(this)">
        </div>
    </div>

    <!-- Units Management -->
    <div class="py-12">
        <div class="mx-auto">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <!-- Add New Unit Button -->
                        <a href="{{ route('unit.create') }}"
                            class="btn btn-primary mb-3 d-flex align-items-center justify-content-center"
                            style="height: 50px">
                            <strong>NEW UNIT</strong></a>
                        <x-card-table title="UNITS RECORD" badge="info">
                            <x-slot name="header">
                                <tr>
                                    <th>ID</th>
                                    <th style="width: 40%">Unit Name</th>
                                    <th>Sort Index</th>
                                    <th></th>
                                </tr>
                            </x-slot>
                            <x-slot name="body">
                                @foreach ($units as $unit)
                                    <tr>
                                        <td>{{ str_pad($unit['id'], 2, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ $unit->unit_name }}</td>
                                        <td>
                                            <input type="number" value="{{ $unit->sort_index }}" 
                                                class="form-control" 
                                                style="width: 80px;" 
                                                onchange="updateSortIndex({{ $unit->id }}, this.value)">
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
                                                            <a href="{{ route('unit.edit', $unit->id) }}"
                                                                class="text-dark">
                                                                <i class="fas fa-edit mr-2"></i>Edit
                                                            </a>
                                                            <form action="{{ route('unit.softDel', $unit->id) }}" method="POST" class="mt-2">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-link text-dark p-0">
                                                                    <i class="fas fa-trash mr-2"></i>Delete
                                                                </button>
                                                            </form>
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
                            {{-- {{ $units->links() }} --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('backend/assets/js/image-viewer.js') }}"></script>
    <script>
        function openImageModal(imageUrl) {
            document.getElementById('modalImage').src = imageUrl;
            document.getElementById('imageModal').style.display = 'block';
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }
    </script>

    <script>
        function updateSortIndex(unitId, sortIndex) {
            $.ajax({
                url: '/unit/' + unitId + '/update-sort-index',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    sort_index: sortIndex
                },
                success: function(response) {
                    console.log('Sort index updated successfully');
                },
                error: function(xhr) {
                    console.error('Error updating sort index:', xhr);
                }
            });
        }
    </script>
@endsection