@extends('frontend.layout.web')

@section('style')
    <style>
        @font-face {
            font-family: 'battambang';
            src: url('{{ public_path('font/KhmerOSBattambang-Regular.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        .battambang {
            font-family: 'battambang', sans-serif !important;
            word-spacing: 0;
        }
    </style>
@endsection

@section('content')
<div class="container" style="padding: 0 20px">
    <div class="row" style="margin-bottom: 20px">
        <img src="/image/leterhead/head.png" alt="" width="90%">
    </div>
    <h4 class="battambang text-center my-5" style="font-weight: bold;">បញ្ចីប្រគល់វិទ្យុទាក់ទង</h4>
    {{-- <p class="battambang"><strong>អ្នកទទួល:</strong> {{ $stockOut->receiver }}</p>
    <p class="battambang"><strong>អ្នកប្រគល់:</strong> {{ $stockOut->user->name }}</p>
    <p><strong>Date:</strong> {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}</p> --}}

    <table class="table table-bordered">
        <thead>
            <tr>
                <th width=90>ល.រ</th>
                <th>ម៉ូដែល</th>
                <th>ចំនួន</th>
                <th>ប្រភេទ</th>
                <th>ផ្សេងៗ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stockOut->stockOutDetails as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->product->type }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="row" style="margin: 50px 0 5px">
        <div class="col-sm-12">
            <p class="battambang text-right
            ">ធ្វើនៅ ក្រុងតាខ្មៅ ថ្ងៃ<span style="padding-right: 150px"></span>ខែ<span style="padding-right: 90px"></span>ឆ្នាំ<span style="padding-right: 80px"></span>ពស ២៥៦__</p>
        </div>
    </div>

    <div class="row" style="margin: 0 0 50px">
        
        <div class="col-sm-12">
            <p class="battambang text-right
            ">ត្រូវនឹងថ្ងៃទី<span style="padding-right: 100px"></span>ខែ<span style="padding-right: 80px"></span>ឆ្នាំ២០២__</p>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <p class="battambang
            "></p>
        </div>
        <div class="col-sm-3">
            <p class="battambang text-center
            "><strong>អ្នកទទួល</strong></p>
        </div>
        <div class="col-sm-3">
            <p class="battambang text-center
            "><strong>អ្នកប្រគល់</strong></p>
        </div>
    </div>

    <div class="row" style="margin-top: 100px">
        <div class="col-sm-6">
            <p class="battambang
            "></p>
        </div>
        <div class="col-sm-3">
            <p class="battambang text-center
            ">{{ $stockOut->receiver }}</p>
        </div>
        <div class="col-sm-3">
            <p class="battambang text-center
            ">{{ $stockOut->user->name }}</p>
        </div>
        
    </div>
</div>
@endsection
