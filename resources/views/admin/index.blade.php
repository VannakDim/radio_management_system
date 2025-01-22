@extends('admin.layout.admin')

@section('main_body')
    <div class="content">
        <!-- Top Statistics -->
        <div class="py-12">
            <div class=" mx-auto">
                <div class="row">
                    <div class="col-12">
                        <!-- Recent Stock Update Table -->
                        <div class="card card-table-border-none" id="recent-orders">
                            <div class="card-header justify-content-between">
                                <h2 class="badge badge-primary"><strong style="color: white">PRODUCT AVAILABLE</strong></h2>

                            </div>
                            <div class="card-body pt-0 pb-5">
                                <table class="table card-table table-responsive table-responsive-large" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th style="width: 30%">Product Model</th>
                                            <th style="width: 30%" class="d-none d-md-table-cell">Brand</th>
                                            <th style="width: 20%" class="d-none d-md-table-cell">Available</th>
                                            <th style="width: 30%" class="d-none d-md-table-cell">Borrowed</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data as $stock)
                                            <tr>
                                                <td>{{ $stock['id'] }}</td>
                                                <td>
                                                    <a class="text-dark" href=""> {{ $stock['model_name'] }}</a>
                                                </td>
                                                <td>{{ $stock['brand_name'] }}</td>
                                                <td>{{ $stock['available_stock'] }}</td>
                                                <td><span class="badge badge-danger">{{ $stock['borrow'] }}</span></td>
                                                <td class="text-right">
                                                    <div class="dropdown show d-inline-block widget-dropdown">
                                                        <a class="dropdown-toggle icon-burger-mini" href=""
                                                            role="button" id="dropdown-recent-order1"
                                                            data-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false" data-display="static"></a>
                                                        <ul class="dropdown-menu dropdown-menu-right"
                                                            aria-labelledby="dropdown-recent-order1">
                                                            <li class="dropdown-item">
                                                                <a href="#">View</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div> <!-- End of Stock Update Table -->

                        {{-- Stock Out Report --}}
                        <div class="card card-table-border-none" id="recent-orders">
                            <div class="card-header justify-content-between">
                                <h2 class="badge badge-warning"><strong style="color: rgb(222, 51, 51)">STOCK OUT
                                        REPORT</strong></h2>
                                <div class="date-range-report ">
                                    <span></span>
                                </div>
                            </div>
                            <div class="card-body pt-0 pb-5">
                                <table class="table card-table table-responsive table-responsive-large" style="width:100%"
                                    id="stockout-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th style="width: 30%">Receiver</th>
                                            <th style="width: 30%" class="d-none d-md-table-cell">Giver</th>
                                            <th style="width: 20%" class="d-none d-md-table-cell">Date</th>
                                            <th style="width: 30%" class="d-none d-md-table-cell">Note</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                                <!-- Custom Pagination Bar -->
                                <div id="stockOutPagination">
                                </div>
                            </div>
                        </div>


                        {{-- @if ($borrows->count() > 0) --}}
                        {{-- Borrowing Report --}}
                        <div class="card card-table-border-none" id="recent-orders">
                            <div class="card-header justify-content-between">
                                <h2 class="badge badge-danger"><strong style="color: white">BORROWING REPORT</strong>
                                </h2>
                                <div class="date-range-report ">
                                    <span></span>
                                </div>
                            </div>
                            <div class="card-body pt-0 pb-5">
                                <table class="table card-table table-responsive table-responsive-large" style="width:100%"
                                    id="borrow-table">
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
                                    <tbody>
                                        
                                    </tbody>
                                </table>
                                <!-- Custom Pagination Bar -->
                                <div id="borrowPagination">
                                </div>
                            </div>
                            {{-- @endif --}}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">
                        <strong>
                            <h1 class="kh-koulen"><span class="welcome-text"></span>
                            </h1>
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}

    <!-- Setup and start animation! -->
    <script>
        var typed = new Typed('.welcome-text', {
            strings: ['<i>សូមស្វាគមន៍មកកាន់</i>ប្រព័ន្ធគ្រប់គ្រងសម្ភារៈបច្ចេកទេស',
                'យើងនៅទីនេះដើម្បីជួយលោកអ្នកក្នុងការស្វែងរកដំណោះស្រាយចំពោះបញ្ហា'
            ],
            typeSpeed: 70,
            backSpeed: 30,
            loop: true,
        });
    </script>
    <script>
        $(document).ready(function() {
            loadStockOutTable();
            loadBorrowTable();

            // Load Stock-Out Data
            function loadStockOutTable(page = 1) {
                $.ajax({
                    url: "{{ route('stockout.paginate') }}" + "?page=" + page,
                    type: "GET",
                    success: function(response) {
                        let rows = '';
                        // console.log(response.data[0].id);
                        $.each(response.data, function(index, value) {
                            rows += '<tr>' +
                                '<td>' + value.id + '</td>' +
                                '<td>' + value.receiver + '</td>' +
                                '<td>' + (value.user ? value.user.name : 'N/A') + '</td>' + 
                                '<td>' + moment(value.created_at).fromNow() + '</td>' +
                                '<td>' + value.note + '</td>' +
                                '<td class="text-right">'+
                                                    '<div class="dropdown show d-inline-block widget-dropdown">' +
                                                        '<a class="dropdown-toggle icon-burger-mini" href=""' +
                                                            'role="button" id="dropdown-recent-order1"' +
                                                            'data-toggle="dropdown" aria-haspopup="true"' +
                                                            'aria-expanded="false" data-display="static"></a>' +
                                                        '<ul class="dropdown-menu dropdown-menu-right"' +
                                                            'aria-labelledby="dropdown-recent-order1">' +
                                                            '<li class="dropdown-item">' +
                                                                '<a href="#" onclick="openViewPopup(' + value.id + ', \'/product/stock-out/show/\')">View</a>' +
                                                            '</li>' +
                                                            '<li class="dropdown-item">' +
                                                                '<a href="#" onclick="openPrintPopup(' + value.id + ')">Print</a>' +
                                                            '</li>' +
                                                        '</ul>' +
                                                    '</div>' +
                                                '</td>' +
                                '</tr>';
                        });
                        $('#stockout-table tbody').html(rows);

                        // Build pagination for Stock-Out
                        let pagination = '<nav aria-label="Page navigation example"><ul class="pagination justify-content-center">';
                        pagination += '<li class="page-item ' + (response.current_page === 1 ? 'disabled' : '') + '"><a href="#" class="page-link stock-out-page" data-page="' + (response.current_page - 1) + '" style="height: 38px;">\<</a></li>';
                        for (let i = 1; i <= response.last_page; i++) {
                            pagination += '<li class="page-item ' + (response.current_page === i ? 'active' : '') + '"><a href="#" class="page-link stock-out-page" data-page="' + i + '" style="height: 38px;">' + i + '</a></li>';
                        }
                        pagination += '<li class="page-item ' + (response.current_page === response.last_page ? 'disabled' : '') + '"><a href="#" class="page-link stock-out-page" data-page="' + (response.current_page + 1) + '" style="height: 38px;">\></a></li>';
                        pagination += '</ul></nav>';
                        $('#stockOutPagination').html(pagination);
                    }
                });
            }

            // Load Borrow Data
            function loadBorrowTable(page = 1) {
                $.ajax({
                    url: "{{ route('borrow.paginate') }}?page=" + page,
                    type: "GET",
                    success: function(response) {
                        let rows = '';
                        console.log(response.data);
                        $.each(response.data, function(index, value) {
                            rows += '<tr>' +
                                '<td>' + value.id + '</td>' +
                                '<td>' + value.receiver + '</td>' +
                                '<td>' + (value.user ? value.user.name : 'N/A') + '</td>' + 
                                '<td>' + moment(value.created_at).fromNow() + '</td>' +
                                '<td>' + value.note + '</td>' +
                                '<td class="text-right ">'+
                                                    '<div class="dropdown show d-inline-block widget-dropdown">' +
                                                        '<a class="dropdown-toggle icon-burger-mini" href=""' +
                                                            'role="button" id="dropdown-recent-order1"' +
                                                            'data-toggle="dropdown" aria-haspopup="true"' +
                                                            'aria-expanded="false" data-display="static"></a>' +
                                                        '<ul class="dropdown-menu dropdown-menu-right"' +
                                                            'aria-labelledby="dropdown-recent-order1">' +
                                                            '<li class="dropdown-item">' +
                                                                '<a href="#" onclick="openViewPopup(' + value.id + ', \'/product/borrow/show/\')">View</a>' +
                                                            '</li>' +
                                                            '<li class="dropdown-item">' +
                                                                '<a href="#" onclick="openPrintPopup(' + value.id + ', \'/product/borrow/show/\')">Print</a>' +
                                                            '</li>' +
                                                        '</ul>' +
                                                    '</div>' +
                                                '</td>' +
                                '</tr>';
                        });
                        $('#borrow-table tbody').html(rows);

                        // Build pagination for Borrow
                        let pagination = '<nav aria-label="Page navigation example"><ul class="pagination justify-content-center">';
                        pagination += '<li class="page-item ' + (response.current_page === 1 ? 'disabled' : '') + '"><a href="#" class="page-link borrow-page" data-page="' + (response.current_page - 1) + '" style="height: 38px;">\<</a></li>';
                        for (let i = 1; i <= response.last_page; i++) {
                            pagination += '<li class="page-item ' + (response.current_page === i ? 'active' : '') + '"><a href="#" class="page-link borrow-page" data-page="' + i + '">' + i + '</a></li>';
                        }
                        pagination += '<li class="page-item ' + (response.current_page === response.last_page ? 'disabled' : '') + '"><a href="#" class="page-link borrow-page" data-page="' + (response.current_page + 1) + '" style="height: 38px;">\></a></li>';
                        pagination += '</ul></nav>';
                        $('#borrowPagination').html(pagination);
                    }
                });
            }

            // Handle Stock-Out Pagination
            $(document).on('click', '.stock-out-page', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                loadStockOutTable(page);
            });

            // Handle Borrow Pagination
            $(document).on('click', '.borrow-page', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                loadBorrowTable(page);
            });
        });
    </script>

    <script>
        function openViewPopup(id,view) {
            var width = screen.width;
            var height = screen.height;
            var left = 0;
            var top = 0;
            window.open(view + id, 
            'PrintWindow', 'width=' + width + ',height=' + height + ',top=' + top + ',left=' + left);
        }
        function openPrintPopup(id, print) {
            var width = screen.width;
            var height = screen.height;
            var left = 0;
            var top = 0;
            var popup = window.open(print + id, 
            'PrintWindow', 'width=' + width + ',height=' + height + ',top=' + top + ',left=' + left);
            popup.print();
        }
    </script>
    
@endsection
