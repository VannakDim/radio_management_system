<!-- resources/views/stock_out/partials/table.blade.php -->
<table class="table card-table table-responsive table-responsive-large" style="width:100%">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th>Receiver</th>
            <th>Giver</th>
            <th>Date</th>
            <th class="d-none d-md-table-cell">Note</th>
        </tr>
    </thead>
    <tbody>
        @php
            $index = ($stockOut->currentPage() - 1) * $stockOut->perPage() + 1;
        @endphp
        @foreach ($stockOut as $stock)
            <tr>
                <td>{{ $index++ }}</td>
                <td>{{ $stock->receiver }}</td>
                <td>{{ $stock->user->name }}</td>
                <td>{{ $stock->created_at->diffForHumans() }}</td>
                <td class="d-none d-md-table-cell">{{ $stock->note }}</td>

                <td class="text-right">
                    <div class="dropdown show d-inline-block widget-dropdown">
                        <a class="dropdown-toggle icon-burger-mini" href="" role="button"
                            id="dropdown-recent-order1" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false" data-display="static"></a>
                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-recent-order1">

                            <li class="dropdown-item">
                                <a href="{{ route('stockout.show', $stock->id) }}" target="_blank" rel="noopener noreferrer" onclick="openPrintPopup(event, this.href)">Print</a>
                            </li>
                            <li class="dropdown-item">
                                <a href="#">Remove</a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>


<!-- Custom Pagination Bar -->
<div class="pagination-container text-center">
    <div class="pagination-buttons">
        {{ $stockOut->links('vendor.pagination.custom') }}
    </div>
    <div class="pagination-description mt-2">
        <p>Showing {{ $stockOut->firstItem() }} to {{ $stockOut->lastItem() }} of {{ $stockOut->total() }} results</p>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).on('click', '.pagination a', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');

        $.ajax({
            url: url,
            success: function (data) {
                $('#pagination-data').html(data);
            }
        });
    });
</script>
<script>
    function openPrintPopup(event, url) {
        event.preventDefault();
        window.open(url, 'printWindow', 'width=800,height=900');
    }
</script>