@extends('admin.layout.admin')

@section('main_body')
    <div class="content kh-battambang">
        <!-- Top Statistics -->
        <div class="py-12">
            <div class="mx-auto">
                <div class="row">
                    <div class="col-12 mb-4">
                        <div id="stockCarousel" class="carousel slide" data-ride="carousel">
                            <div class="carousel-inner">
                                @foreach ($data as $index => $stock)
                                    @php
                                        $hasData = 
                                            (!is_null($stock['stock_in']) && $stock['stock_in'] > 0) ||
                                            (!is_null($stock['stock_out']) && $stock['stock_out'] > 0) ||
                                            (!is_null($stock['available_stock']) && $stock['available_stock'] > 0);
                                    @endphp
                                    @if ($hasData)
                                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                            <div class="card text-center shadow-sm">
                                                <div class="card-body">
                                                    <div class="card-img-contain mb-3" style="background-image: url({{ asset($stock['image']) }}); height: 120px; background-size: contain; background-repeat: no-repeat; background-position: center;"></div>
                                                    <h5 class="card-title mb-2">{{ $stock['model_name'] }}</h5>
                                                    <h6 class="card-subtitle mb-2 text-muted">{{ $stock['brand_name'] }}</h6>
                                                    <div class="mb-2">
                                                        @if (!is_null($stock['stock_in']) && $stock['stock_in'] > 0)
                                                            <span class="badge badge-primary">IN: {{ $stock['stock_in'] }}</span>
                                                        @endif
                                                        @if (!is_null($stock['stock_out']) && $stock['stock_out'] > 0)
                                                            <span class="badge badge-danger">OUT: {{ $stock['stock_out'] }}</span>
                                                        @endif
                                                        @if (!is_null($stock['available_stock']) && $stock['available_stock'] > 0)
                                                            <span class="badge badge-success">FREE: {{ $stock['available_stock'] }}</span>
                                                        @endif
                                                    </div>
                                                    @if (!empty($stock['borrow']))
                                                        <span class="badge badge-warning">Borrowed: {{ $stock['borrow'] }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <a class="carousel-control-prev" href="#stockCarousel" role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#stockCarousel" role="button" data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
                    </div>
                    <div class="col-12">
                        <!--  Stock Update Table -->
                        <x-card-table title="STOCK OVERVIEW" badge="success">
                            <x-slot name="header">
                                <tr>
                                    <th>ID</th>
                                    <th style="width: 30%">Model</th>
                                    <th style="width: 30%">Brand</th>
                                    <th style="width: 20%" class="d-none d-md-table-cell">
                                        <label class="badge badge-primary">ALL</label>
                                        <label class="badge badge-danger">OUT</label>
                                        <label class="badge badge-success">FREE</label>
                                    </th>
                                    <th style="width: 30%" class="d-none d-md-table-cell">Borrowed</th>
                                    {{-- <th></th> --}}
                                </tr>
                            </x-slot>
                            <x-slot name="body">
                                @foreach ($data as $stock)
                                    @if (
                                        !is_null($stock['stock_in']) && $stock['stock_in'] > 0 ||
                                        !is_null($stock['stock_out']) && $stock['stock_out'] > 0 ||
                                        !is_null($stock['available_stock']) && $stock['available_stock'] > 0
                                    )
                                        <tr>
                                            <td>{{ str_pad($stock['id'], 2, '0', STR_PAD_LEFT) }}</td>
                                            <td><a class="text-dark mr-2" href="">{{ $stock['model_name'] }}</a></td>
                                            <td>{{ $stock['brand_name'] }}</td>
                                            <td>
                                                <span style="font-size: 0.98rem !important;" class="badge badge-primary">{{ str_pad($stock['stock_in'], 3, '0', STR_PAD_LEFT) }}</span>
                                                <span style="font-size: 0.98rem !important;" class="badge badge-danger">{{ str_pad($stock['stock_out'], 3, '0', STR_PAD_LEFT) }}</span>
                                                <span style="font-size: 0.98rem !important;" class="badge badge-success">{{ str_pad($stock['available_stock'], 3, '#', STR_PAD_LEFT) }}</span>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                @if ($stock['borrow'] > 0)
                                                    <span style="font-size: 0.98rem !important;" class="badge badge-warning">{{ $stock['borrow'] }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </x-slot>
                        </x-card-table>

                        <!-- Stock Out Report -->
                        <div class="card card-table-border-none" id="recent-orders">
                            <div class="card-header justify-content-between">
                                <h2  title="ជ្រើសរើសកាលបរិច្ឆេទដើម្បីមើលរបាយការណ៍" class="badge badge-warning text-white">STOCK-OUT REPORT</h2>
                                <div class="date-range-report" id="filterStockOut" title="ជ្រើសរើសកាលបរិច្ឆេទដើម្បីមើលរបាយការណ៍"><span></span></div>

                            </div>
                            <div class="card-body pt-0 pb-5"  title="ជ្រើសរើសកាលបរិច្ឆេទដើម្បីមើលរបាយការណ៍">
                                <table class="table card-table table-responsive table-responsive-large table-striped"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th style="width: 30%">អ្នកទទួល</th>
                                            <th style="width: 30%" class="d-none d-md-table-cell">អ្នកប្រគល់</th>
                                            <th style="width: 20%" class="d-none d-md-table-cell">Date</th>
                                            <th style="width: 30%" class="d-none d-md-table-cell">Note</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="stockout-table-body">
                                    </tbody>
                                </table>
                                <div id="stockoutPagination" data-url="{{ route('stockout.paginate') }}">
                                    <nav aria-label="Page navigation example">
                                        <ul class="pagination justify-content-center"></ul>
                                    </nav>
                                </div>
                            </div>
                        </div>

                        <!-- Borrowing Report -->
                        @if ($data->sum('borrow') > 0)
                            <div class="card card-table-border-none" id="recent-orders">
                                <div class="card-header justify-content-between">
                                    <h2 class="badge badge-danger text-white">BORROWING REPORT</h2>
                                    <div class="date-range-report" id="filterBorrow"><span></span></div>
                                </div>
                                <div class="card-body pt-0 pb-5">
                                    <table class="table card-table table-responsive table-responsive-large table-striped" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th style="width: 30%">អ្នកទទួល</th>
                                                <th style="width: 30%" class="d-none d-md-table-cell">អ្នកប្រគល់</th>
                                                <th style="width: 20%" class="d-none d-md-table-cell">Date</th>
                                                <th style="width: 30%" class="d-none d-md-table-cell">Note</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="borrow-table-body">
                                        </tbody>
                                    </table>
                                    <div id="borrowPagination" data-url="{{ route('borrow.paginate') }}">
                                        <nav aria-label="Page navigation example">
                                            <ul class="pagination justify-content-center"></ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-12">
                        <h1 class="kh-koulen"><span class="welcome-text"></span></h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>
    <script>
        $(document).ready(function() {
            initializeDateRangePicker('#filterStockOut', "{{ route('stockout.paginate') }}", 1,
                '#stockout-table-body', '#stockoutPagination');
            initializeDateRangePicker('#filterBorrow', "{{ route('borrow.paginate') }}", 1, '#borrow-table-body',
                '#borrowPagination');
        });

        function initializeDateRangePicker(selector, url, page, tableBodySelector, paginationSelector) {
            $(selector).daterangepicker({
                opens: 'left',
                startDate: moment().startOf('month'),
                endDate: moment().endOf('month'),
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month')
                        .endOf('month')
                    ]
                },
                locale: {
                    format: 'MMM/DD/YYYY'
                }
            }, function(start, end, label) {
                $(selector + ' span').html(start.format('MMM/DD/YYYY') + ' - ' + end.format('MMM/DD/YYYY'));
                $.ajax({
                    url: url,
                    type: "GET",
                    data: {
                        start_date: start.format('YYYY-MM-DD'),
                        end_date: end.format('YYYY-MM-DD')
                    },
                    success: function(response) {
                        updateTable(tableBodySelector, response.data);
                        updatePagination(paginationSelector, response);
                    }
                });
            });
        }
    </script>
    <script>
        new Typed('.welcome-text', {
            strings: ['<i>សូមស្វាគមន៍មកកាន់</i>ប្រព័ន្ធគ្រប់គ្រងសម្ភារៈបច្ចេកទេស',
                'យើងនៅទីនេះដើម្បីជួយលោកអ្នកក្នុងការស្វែងរកដំណោះស្រាយចំពោះបញ្ហា'
            ],
            typeSpeed: 70,
            backSpeed: 30,
            loop: true,
        });

        function dateRangeDisplay(page, view) {
            let dateRange;

            if (view === 'stockout') {
                dateRange = $('#filterStockOut span').text();
                url = "{{ route('stockout.paginate') }}?page=" + page;
            } else {
                dateRange = $('#filterBorrow span').text();
                url = "{{ route('borrow.paginate') }}?page=" + page;
            }
            console.log(url);
            if (dateRange) {
                const [start, end] = dateRange.split(' - ');
                const startDate = moment(start.trim(), 'MMM/DD/YYYY').format('YYYY-MM-DD');
                const endDate = moment(end.trim(), 'MMM/DD/YYYY').format('YYYY-MM-DD');
                $.ajax({
                    url: url,
                    type: "GET",
                    data: {
                        start_date: startDate,
                        end_date: endDate
                    },
                    success: function(response) {

                        updateTable(`#${view}-table-body`, response.data);
                        updatePagination(`#${view}Pagination`, response);

                    }
                });
            }
        };

        function updateTable(selector, data) {
            let rows = '';
            // let command = '';

            $.each(data, function(index, value) {
                let download = '';
                if (selector === '#stockout-table-body') {
                    view = "stock-out";
                } else {
                    view = "borrow";
                }
                if(value.image !== null){
                    download = `<li class="dropdown-item">
                                    <a href="product/${view}/download/${value.id}" ><i class="fas fa-download mr-2"></i>ទាញ</a>
                                  </li>`;
                }
                rows += `<tr>
                            <td>${value.id}</td>
                            <td>${value.receiver}</td>
                            <td class="d-none d-md-table-cell">${value.user.name}</td>
                            <td>${moment(value.created_at).fromNow()}</td>
                            <td class="d-none d-md-table-cell">${value.note !== null ? value.note : ''}</td>
                            <td class="text-right">
                              <div class="dropdown show d-inline-block widget-dropdown">
                                <a class="dropdown-toggle icon-burger-mini" href="#" role="button" id="dropdown-recent-order5" data-toggle="dropdown" aria-haspopup="true"
                                  aria-expanded="false" data-display="dynamic"></a>
                                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-recent-order5">
                                  <li class="dropdown-item">
                                    <a href="#" onclick="openViewPopup('${view}',${value.id})"><i class="fas fa-eye mr-2"></i>មើល</a>
                                  </li>
                                  <li class="dropdown-item">
                                    <a href="#" onclick="openPrintPopup('${view}',${value.id})"><i class="fas fa-print mr-2"></i>ព្រីន</a>
                                  </li>
                                    ${download}
                                </ul>
                              </div>
                            </td>
                        </tr>`;
            });
            $(selector).html(rows);
        }

        function updatePagination(selector, response) {
            let view = (selector === '#stockoutPagination') ? "stock-out" : "borrow";
            // Responsive pagination: show only prev/next and page info on small screens
            let isMobile = window.innerWidth < 768;
            if (response.last_page > 1) {
                let pagination = '';
                if (isMobile) {
                    // Previous button
                    pagination += `<li class="page-item ${response.current_page === 1 ? 'disabled' : ''}">
                        <a href="#" class="page-link" data-view="${view}" data-page="${response.current_page - 1}" style="height: 38px;">&lt;</a>
                    </li>`;
                    // Page info
                    pagination += `<li class="page-item disabled">
                        <span class="page-link" style="height: 38px;">${response.current_page} / ${response.last_page}</span>
                    </li>`;
                    // Next button
                    pagination += `<li class="page-item ${response.current_page === response.last_page ? 'disabled' : ''}">
                        <a href="#" class="page-link" data-view="${view}" data-page="${response.current_page + 1}" style="height: 38px;">&gt;</a>
                    </li>`;
                } else {
                    // Previous button
                    pagination += `<li class="page-item ${response.current_page === 1 ? 'disabled' : ''}">
                        <a href="#" class="page-link" data-view="${view}" data-page="${response.current_page - 1}" style="height: 38px;">&lt;</a></li>`;

                    // Page numbers
                    for (let i = 1; i <= response.last_page; i++) {
                        pagination += `<li class="page-item ${response.current_page === i ? 'active' : ''}">
                            <a href="#" class="page-link" data-view="${view}" data-page="${i}" style="height: 38px;">${i}</a></li>`;
                    }

                    // Next button
                    pagination += `<li class="page-item ${response.current_page === response.last_page ? 'disabled' : ''}">
                        <a href="#" class="page-link" data-view="${view}" data-page="${response.current_page + 1}" style="height: 38px;">&gt;</a></li>`;
                }
                $(selector).find('.pagination').html(pagination);
            } else {
                $(selector).find('.pagination').html(`<span class="mr-2">Records found: ${response.total}</span>`);
            }
        }

        // Handle Pagination Click
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            let page = $(this).data('page');
            let view = $(this).data('view');
            if (view === 'stock-out') {
                dateRangeDisplay(page, 'stockout');
                console.log('Stock Out'+page);
            } else {
                dateRangeDisplay(page, 'borrow');
                console.log('Borrow'+page);
            }
        });


        $(document).ready(function() {
            loadTableData("{{ route('stockout.paginate') }}", '#stockout-table-body', '#stockoutPagination');
            loadTableData("{{ route('borrow.paginate') }}", '#borrow-table-body', '#borrowPagination');
        });

        function loadTableData(url, tableBodySelector, paginationSelector) {
            $.ajax({
                url: url,
                type: "GET",
                success: function(response) {
                    updateTable(tableBodySelector, response.data);
                    updatePagination(paginationSelector, response);
                }
            });
        }

        function openViewPopup(view, id) {
            window.open(`/product/${view}/show/${id}`, 'ViewWindow',
                `width=${screen.width},height=${screen.height},top=0,left=0`);
            // console.log(command);
            window.open('PrintWindow', `width=${screen.width},height=${screen.height},top=0,left=0`);
        }

        function openPrintPopup(view, id) {
            const popup = window.open(`/product/${view}/show/${id}`, 'PrintWindow',
                `width=${screen.width},height=${screen.height},top=0,left=0`);
            popup.print();
        }
    </script>
@endsection
