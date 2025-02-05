@extends('admin.layout.admin')

@section('main_body')
    <!-- Top Statistics -->
    <div class="py-12">
        <div class="mx-auto">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <!--  Stock Update Table -->
                        <a href="{{ route('stockin.create') }}"
                            class="btn btn-primary mb-3 d-flex align-items-center justify-content-center"
                            style="height: 50px">
                            <strong>NEW STOCK IN</strong></a>
                        <x-card-table title="STOCK IN RECORD" badge="success">
                            <x-slot name="header">
                                <tr>
                                    <th>ID</th>
                                    <th style="width: 30%">Supplier</th>
                                    <th style="width: 30%" class="d-none d-md-table-cell">Receiver</th>
                                    <th style="width: 20%" class="d-none d-md-table-cell">Date</th>
                                    <th style="width: 30%" class="d-none d-md-table-cell">Note</th>
                                    <th></th>
                                </tr>
                            </x-slot>
                            <x-slot name="body">
                                @foreach ($stock_ins as $stock)
                                    <tr>
                                        <td>{{ str_pad($stock['id'], 2, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ $stock->supplier }}</td>
                                        <td>{{ $stock->user->name }}</td>
                                        <td>{{ $stock->created_at->format('Y-m-d') }}</td>
                                        <td>{{ $stock->note }}</td>
                                        <td class="text-right">
                                            <div class="dropdown show d-inline-block widget-dropdown">
                                                <a class="dropdown-toggle icon-burger-mini" href="#" role="button"
                                                    id="dropdown-recent-order5" data-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false" data-display="dynamic"></a>
                                                <ul class="dropdown-menu dropdown-menu-right"
                                                    aria-labelledby="dropdown-recent-order5" style="width: 120px;">
                                                    <li class="dropdown-item">
                                                        <div class="d-flex flex-column">
                                                            <a href="#" onclick="openPreview({{ $stock->id }})" class="text-dark">
                                                                <i class="fas fa-print mr-2"></i>Preview
                                                            </a>
                                                            <a href="{{ route('stockin.edit', $stock->id) }}" class="text-dark mt-2">
                                                                <i class="fas fa-edit mr-2"></i>Edit
                                                            </a>
                                                            @if ($stock->image)
                                                            <a href="{{ route('stockin.download', $stock->id) }}" class="text-dark mt-2">
                                                                <i class="fas fa-download mr-2"></i>Download
                                                            </a>
                                                            @endif
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
                            {{ $stock_ins->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<script>
    function openPreview(id) {
        window.open(`/product/stock-in/show/${id}`, 'ViewWindow',
            `width=${screen.width},height=${screen.height},top=0,left=0`);
        // console.log('fail');
        window.open(`/product/stock-in/show/${id}`, 'PrintWindow', `width=${screen.width},height=${screen.height},top=0,left=0`);
    }
</script>
