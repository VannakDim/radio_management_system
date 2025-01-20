@extends('frontend.layout.web')

@section('style')
    {{-- <style>
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
    </style> --}}
@endsection

@section('content')
<div class="container" style="padding: 0 20px">
    <div class="row" style="margin-bottom: 20px">
        <img src="/image/leterhead/head.png" alt="" width="100%">
    </div>
    <h5 class="battambang text-center my-5" style="font-weight: bold;">បញ្ចីខ្ចីវិទ្យុទាក់ទង</h5>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th class="text-center" width=90>ល.រ</th>
                <th>លេខសម្គាល់</th>
                <th>ម៉ូដែល</th>
                <th>ប្រភេទ</th>
                <th>ផ្សេងៗ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($borrow->details as $key => $item)
                <tr>
                    <td class="text-center">{{ $key + 1 }}</td>
                    <td>{{ $item->product->PID }}</td>
                    <td>{{ $item->product->model->name }}</td>
                    <td>{{ $item->product->model->type }}</td>
                    <td></td>
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
            ">{{ $borrow->receiver }}</p>
        </div>
        <div class="col-sm-3">
            <p class="battambang text-center
            ">{{ $borrow->user->name }}</p>
        </div>
        
    </div>
    {{ $borrow->created_at }}
</div>
@endsection

