<div class="card card-table-border-none" id="recent-orders">
    <div class="card-header justify-content-between">
        <h2 class="badge badge-{{ $badge }}"><strong style="color: white">{{ $title }}</strong></h2>
        @isset($filter)
            <div class="row">
                {{ $filter }}
            </div>
        @endisset
    </div>
    <div class="card-body pt-0 pb-5">
        <table class="table card-table table-responsive table-responsive-large" style="width:100%">
            <thead>
                {{ $header }}
            </thead>
            <tbody>
                {{ $body }}
            </tbody>
        </table>
        @isset($pagination)
            <div id="{{ $pagination }}"></div>
        @endisset
    </div>
</div>
