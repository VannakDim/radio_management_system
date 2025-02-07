@extends('frontend.layout.web')

@section('style')
@endsection

@section('content')
    <div class="container">
        <div class="row" style="margin-bottom: 20px; padding:0 20px;">
            <img src="{{asset('/image/leterhead/head.png')}}" alt="" style="width: 100%; height: auto;">
        </div>
        <h5 class="battambang text-center my-5" style="font-weight: bold;">លិខិតសងវិទ្យុទាក់ទង</h5>

        <table class="table table-bordered">
            <thead>
                <tr class="battambang">
                    <th class="text-center" width=90>ល.រ</th>
                    <th style="width: 30%">លេខសម្គាល់</th>
                    <th style="width: 20%">ម៉ូដែល</th>
                    <th style="width: 20%">ប្រភេទ</th>
                    <th>ផ្សេងៗ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($borrow->details as $key => $item)
                    <tr>
                        <td class="text-center">{{ sprintf('%02d', $key + 1) }}</td>
                        <td>{{ $item->product->PID }}</td>
                        <td>{{ $item->product->model->name }}</td>
                        <td>{{ $item->product->model->type }}</td>
                        <td></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if ($borrow->accessory->count() > 0)
            <h6 class="battambang text-center my-5" style="font-weight: bold;">និងឧបករណ៍ភ្ជាប់បន្ថែម</h6>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center" width=90>ល.រ</th>
                        <th style="width: 30%">ម៉ូដែល</th>
                        <th style="width: 20%">ប្រភេទ</th>
                        <th style="width: 20%">ចំនួន</th>
                        <th>ផ្សេងៗ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($borrow->accessory as $key => $item)
                        <tr>
                            <td class="text-center">{{ sprintf('%02d', $key + 1) }}</td>
                            <td>{{ $item->model->name }}</td>
                            <td>{{ $item->model->type }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="row" style="margin: 50px 0 50px">

            <div class="col-sm-12">
                <p class="battambang text-right
            ">ក្រុងតាខ្មៅ​ ថ្ងៃទី<span
                        style="padding-right: 100px"></span>ខែ<span style="padding-right: 80px"></span>ឆ្នាំ២០២__</p>
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
