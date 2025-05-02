@extends('frontend.layout.web')

@section('style')
@endsection

@section('content')
    <div class="container" style="padding: 0 20px">
        <div class="row" style="margin-bottom: 20px; padding:0 20px;">
            <img src="{{asset('/image/leterhead/head.png')}}" alt="" style="width: 100%; height: auto;">
        </div>
        <h5 class="battambang text-center mb-3" style="font-weight: bold;">របាយការណ៍ផ្លាស់ប្តូប្រេកង់វិទ្យុទាក់ទង</h5>
        {{-- <h6 class="battambang text-center mb-5">( អង្គភាព: {{ $set_frequency->unit }})</h6> --}}
        
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center" width=90>ល.រ</th>
                    <th style="width: 20%">អង្គភាព</th>
                    <th style="width: 30%">លេខសម្គាល់</th>
                    <th style="width: 20%">ចំនួនវិទ្យុ</th>
                    <th>ផ្សេងៗ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($details as $record)
                    <tr>
                        <td>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $record->unit }}</td>
                        <td>
                            @foreach (collect(json_decode($record->products))->groupBy('model') as $model => $products)
                                <strong>{{ $model }} ({{ $products->count() }})</strong><br>
                                @foreach ($products as $product)
                                    <span style="margin-left: 5px;">{{ $product->PID }}</span><br>
                                @endforeach
                                <div class="pt-4"></div>
                            @endforeach
                        </td>
                        <td>{{ $record->product_count }}</td>
                        <td></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        

        {{-- <div class="row" style="margin: 50px 0 5px">
            <div class="col-sm-12">
                <p class="battambang text-right
            ">ធ្វើនៅ ក្រុងតាខ្មៅ ថ្ងៃ<span
                        style="padding-right: 150px"></span>ខែ<span style="padding-right: 90px"></span>ឆ្នាំ<span
                        style="padding-right: 80px"></span>ពស ២៥៦__</p>
            </div>
        </div> --}}

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
            "><strong></strong></p>
            </div>
            <div class="col-sm-3">
                <p class="battambang text-center
            "><strong>ប្រធានផ្នែក</strong></p>
            </div>
        </div>

        <div class="row" style="margin-top: 100px">
            <div class="col-sm-6">
                <p class="battambang
            "></p>
            </div>
            <div class="col-sm-3">
                <p class="battambang text-center
            {{-- ">{{ $set_frequency->name }}</p> --}}
            </div>
            <div class="col-sm-3">
                <p class="battambang text-center
            {{-- ">{{ $set_frequency->user->name }}</p> --}}
            </div>

        </div>
        {{-- {{ $set_frequency->created_at->timezone('Asia/Phnom_Penh')->format('d-m-Y H:i:s') }} --}}
    </div>
@endsection
