{{-- filepath: /Users/t2/Desktop/Radio TEAM/radio_management_system/resources/views/vendor/pagination/custom.blade.php --}}
@if ($paginator->hasPages())
    <nav>
        <ul class="pagination custom-pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link">&lsaquo;</span></li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo;</a>
                </li>
            @endif

            {{-- Page 1 --}}
            <li class="page-item {{ $paginator->currentPage() == 1 ? 'active' : '' }}">
                @if ($paginator->currentPage() == 1)
                    <span class="page-link">1</span>
                @else
                    <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                @endif
            </li>

            {{-- Page 2 --}}
            @if ($paginator->lastPage() > 1)
                <li class="page-item {{ $paginator->currentPage() == 2 ? 'active' : '' }}">
                    @if ($paginator->currentPage() < 4)
                        <a class="page-link" href="{{ $paginator->url(2) }}">2</a>
                    @elseif ($paginator->currentPage() == 3)
                        <span class="page-link">2</span>
                    @else
                        <span class="page-link">...</span>
                    @endif
                </li>
            @endif

            {{-- Page 3 --}}
            @if ($paginator->lastPage() > 2)
                <li class="page-item {{ $paginator->currentPage() == 3 ? 'active' : '' }}">
                    @if ($paginator->currentPage() == 3)
                        <span class="page-link">3</span>
                    @endif
                </li>
            @endif

            {{-- Page ... --}}
            @if ($paginator->currentPage() > 3 && $paginator->currentPage() < $paginator->lastPage() - 2)
                <li class="page-item active">
                    <span class="page-link">{{ $paginator->currentPage() }}</span>
                </li>
            @endif

            {{-- Page 6 --}}
            @if ($paginator->lastPage() > 3 && $paginator->currentPage() == $paginator->lastPage() - 2)
                <li class="page-item {{ $paginator->currentPage() == $paginator->lastPage() -2 ? 'active' : '' }}">
                    @if ($paginator->currentPage() > 4 && $paginator->currentPage() == $paginator->lastPage() - 2)
                        <span class="page-link">{{ $paginator->lastPage()-2 }}</span>
                    @else
                        <span class="page-link">...</span>
                    @endif
                </li>
            @endif

            {{-- Last 7 --}}
            @if ($paginator->lastPage() > 3)
                <li class="page-item {{ $paginator->currentPage() == $paginator->lastPage() - 1 ? 'active' : '' }}">
                    @if ($paginator->currentPage() == $paginator->lastPage() - 1)
                        <span class="page-link">{{ $paginator->currentPage() }}</span>
                    @elseif ($paginator->currentPage() < $paginator->lastPage() - 2)
                        <span class="page-link">...</span>
                    @else
                <a class="page-link" href="{{ $paginator->url($paginator->lastPage() - 1) }}">{{$paginator->lastPage() - 1}}</a>
            @endif
            </li>
@endif

{{-- Last Page --}}
@if ($paginator->lastPage() > 3)
    <li class="page-item {{ $paginator->currentPage() == $paginator->lastPage() ? 'active' : '' }}">
        @if ($paginator->currentPage() == $paginator->lastPage())
            <span class="page-link">{{ $paginator->lastPage() }}</span>
        @else
            <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a>
        @endif
    </li>
@endif

{{-- Next Page Link --}}
@if ($paginator->hasMorePages())
    <li class="page-item">
        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">&rsaquo;</a>
    </li>
@else
    <li class="page-item disabled"><span class="page-link">&rsaquo;</span></li>
@endif
</ul>
</nav>
@endif
