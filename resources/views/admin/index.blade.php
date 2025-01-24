@extends('admin.layout.admin')

@section('main_body')
    <div class="content">
        <!-- Top Statistics -->
        <div class="py-12">
            <div class="mx-auto">
                <div class="row">
                    <div class="col-12">
                        <!-- Recent Stock Update Table -->
                        <x-card-table title="PRODUCT AVAILABLE" badge="primary">
                            <x-slot name="header">
                                <tr>
                                    <th>ID</th>
                                    <th style="width: 30%">Product Model</th>
                                    <th style="width: 30%" class="d-none d-md-table-cell">Brand</th>
                                    <th style="width: 20%" class="d-none d-md-table-cell">Available</th>
                                    <th style="width: 30%" class="d-none d-md-table-cell">Borrowed</th>
                                    <th></th>
                                </tr>
                            </x-slot>
                            <x-slot name="body">
                                @foreach ($data as $stock)
                                    <tr>
                                        <td>{{ $stock['id'] }}</td>
                                        <td><a class="text-dark" href="">{{ $stock['model_name'] }}</a></td>
                                        <td>{{ $stock['brand_name'] }}</td>
                                        <td>{{ $stock['available_stock'] }}</td>
                                        <td><span class="badge badge-danger">{{ $stock['borrow'] }}</span></td>
                                        <td class="text-right">
                                            <x-dropdown>
                                                <x-dropdown-item href="#">View</x-dropdown-item>
                                            </x-dropdown>
                                        </td>
                                    </tr>
                                @endforeach
                            </x-slot>
                        </x-card-table>

                        <!-- Stock Out Report -->
                        <div class="card card-table-border-none" id="recent-orders">
                            <div class="card-header justify-content-between">
                                <h2 class="badge badge-warning">STOCK-OUT REPORT</h2>
                                <div class="date-range-report" id="filterStockOut"><span></span></div>
                                <button class="btn btn-sm btn-outline-primary" onclick="dateRangeStockOut()"
                                    style="margin-left: 10px">Filter</button>
                            </div>
                            <div class="card-body pt-0 pb-5">
                                <table class="table card-table table-responsive table-responsive-large" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th style="width: 30%">Receiver</th>
                                            <th style="width: 30%" class="d-none d-md-table-cell">Borrower</th>
                                            <th style="width: 20%" class="d-none d-md-table-cell">Date</th>
                                            <th style="width: 30%" class="d-none d-md-table-cell">Note</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="stockout-table-body">
                                    </tbody>
                                </table>
                                <div id="stockOutPagination" data-url="{{ route('stockout.paginate') }}">
                                    <nav aria-label="Page navigation example">
                                        <ul class="pagination justify-content-center"></ul>
                                    </nav>
                                </div>
                            </div>
                        </div>

                        <!-- Borrowing Report -->
                        <div class="card card-table-border-none" id="recent-orders">
                            <div class="card-header justify-content-between">
                                <h2 class="badge badge-danger">BORROWING REPORT</h2>
                                <div class="date-range-report" id="filterBorrow"><span></span></div>
                                <button class="btn btn-sm btn-outline-primary" onclick="dateRangeBorrow()"
                                    style="margin-left: 10px">Filter</button>
                            </div>
                            <div class="card-body pt-0 pb-5">
                                <table class="table card-table table-responsive table-responsive-large" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th style="width: 30%">Receiver</th>
                                            <th style="width: 30%" class="d-none d-md-table-cell">Borrower</th>
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
        new Typed('.welcome-text', {
            strings: ['<i>សូមស្វាគមន៍មកកាន់</i>ប្រព័ន្ធគ្រប់គ្រងសម្ភារៈបច្ចេកទេស',
                'យើងនៅទីនេះដើម្បីជួយលោកអ្នកក្នុងការស្វែងរកដំណោះស្រាយចំពោះបញ្ហា'
            ],
            typeSpeed: 70,
            backSpeed: 30,
            loop: true,
        });

        function dateRangeStockOut(page) {
            let dateRange = $('#filterStockOut span').text();
            if (dateRange) {
                const [start, end] = dateRange.split(' - ');
                const startDate = moment(start.trim(), 'MMM/DD/YYYY').format('YYYY-MM-DD');
                const endDate = moment(end.trim(), 'MMM/DD/YYYY').format('YYYY-MM-DD');
                $.ajax({
                    url: "{{ route('stockout.paginate') }}?page=" + page,
                    type: "GET",
                    data: {
                        start_date: startDate,
                        end_date: endDate
                    },
                    success: function(response) {
                        updateTable('#stockout-table-body', response.data);
                        updatePagination('#stockOutPagination', response);
                    }
                });
            }
        }

        function dateRangeBorrow(page) {
            let dateRange = $('#filterBorrow span').text();
            if (dateRange) {
                const [start, end] = dateRange.split(' - ');
                const startDate = moment(start.trim(), 'MMM/DD/YYYY').format('YYYY-MM-DD');
                const endDate = moment(end.trim(), 'MMM/DD/YYYY').format('YYYY-MM-DD');
                $.ajax({
                    url: "{{ route('borrow.paginate') }}?page=" + page,
                    type: "GET",
                    data: {
                        start_date: startDate,
                        end_date: endDate
                    },
                    success: function(response) {
                        // console.log(response);
                        updateTable('#borrow-table-body', response.data);
                        updatePagination('#borrowPagination', response);
                    }
                });
            }
        }

        function updateTable(selector, data) {
            let rows = '';
            let command = '';

            $.each(data, function(index, value) {
                if (selector === '#stockout-table-body') {
                    view = "stock-out";
                } else {
                    view = "borrow";
                }
                // console.log(view);
                rows += `<tr>
                            <td>${value.id}</td>
                            <td>${value.receiver}</td>
                            <td>${value.user.name}</td>
                            <td>${moment(value.created_at).fromNow()}</td>
                            <td>${value.note}</td>
                            <td class="text-right">
                              <div class="dropdown show d-inline-block widget-dropdown">
                                <a class="dropdown-toggle icon-burger-mini" href="#" role="button" id="dropdown-recent-order5" data-toggle="dropdown" aria-haspopup="true"
                                  aria-expanded="false" data-display="static"></a>
                                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-recent-order5">
                                  <li class="dropdown-item">
                                    <a href="#" onclick="openViewPopup('${view}',${value.id})">View</a>
                                  </li>
                                  <li class="dropdown-item">
                                    <a href="#" onclick="openPrintPopup('${view}',${value.id})">Print</a>
                                  </li>
                                </ul>
                              </div>
                            </td>
                        </tr>`;
            });
            $(selector).html(rows);
        }

        function updatePagination(selector, response) {
            if(selector === '#stockOutPagination') {
                view = "stock-out";
            } else {
                view = "borrow";
            }
            if (response.last_page > 1) {
                let pagination = '<nav aria-label="Page navigation example"><ul class="pagination justify-content-center">';
                pagination +=
                    `<li class="page-item ${response.current_page === 1 ? 'disabled' : ''}">
                                <a href="#" class="page-link" data-view="${view}" data-page="${response.current_page - 1}" style="height: 38px;">\<</a></li>`;
                for (let i = 1; i <= response.last_page; i++) {
                    pagination +=
                        `<li class="page-item ${response.current_page === i ? 'active' : ''}">
                                    <a href="#" class="page-link" data-view="${view}" data-page="${i}" style="height: 38px;">${i}</a></li>`;
                }
                pagination +=
                    `<li class="page-item ${response.current_page === response.last_page ? 'disabled' : ''}">
                                <a href="#" class="page-link" data-view="${view}" data-page="${response.current_page + 1}" style="height: 38px;">\></a></li>`;
                pagination += '</ul></nav>';
                $(selector).find('.pagination').html(pagination);
            }
        }

        // Handle Pagination Click
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            let page = $(this).data('page');
            let view = $(this).data('view');
            if (view === 'stock-out') {
                dateRangeStockOut(page);
                // console.log('Stock Out'+page);
            } else {
                dateRangeBorrow(page);
                // console.log('Borrow'+page);
            }
        });


        $(document).ready(function() {
            loadTableData("{{ route('stockout.paginate') }}", '#stockout-table-body', '#stockOutPagination');
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
            window.open(command, 'PrintWindow', `width=${screen.width},height=${screen.height},top=0,left=0`);
        }

        function openPrintPopup(view, id) {
            const popup = window.open(`/product/${view}/show/${id}`, 'PrintWindow',
                `width=${screen.width},height=${screen.height},top=0,left=0`);
            popup.print();
        }
    </script>
@endsection
