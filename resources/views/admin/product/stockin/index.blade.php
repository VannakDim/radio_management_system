@extends('admin.layout.admin')

@section('main_body')
    <div class="content">
        <!-- Top Statistics -->
        <div class="py-12">
            <div class="mx-auto">
                <div class="row">
                    <div class="col-12">
                        <!--  Stock Update Table -->
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
                                        <td><a class="text-dark mr-2" href="">{{ $stock->supplier }}</a></td>
                                        <td>{{ $stock->user->name }}</td>
                                        <td>{{ $stock->created_at->format('Y-m-d') }}</td>
                                        <td>{{ $stock->note }}</td>
                                        <td class="text-right">
                                            <div class="dropdown show d-inline-block widget-dropdown">
                                                <a class="dropdown-toggle icon-burger-mini" href="#" role="button" id="dropdown-recent-order5" data-toggle="dropdown" aria-haspopup="true"
                                                  aria-expanded="false" data-display="static"></a>
                                                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-recent-order5">
                                                  <li class="dropdown-item">
                                                    <a href="#" onclick="openViewPopup('${view}',${value.id})"><i class="fas fa-print"></i></a>
                                                  </li>
                                                  {{-- <li class="dropdown-item">
                                                    <a href="#" onclick="openPrintPopup('${view}',${value.id})">Print</a>
                                                  </li> --}}
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
