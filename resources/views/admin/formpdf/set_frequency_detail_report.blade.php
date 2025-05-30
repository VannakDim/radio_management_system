@extends('frontend.layout.web')

@section('style')
@endsection

@section('content')
    <div class="container" style="padding: 0 20px">
        <div class="row" style="margin-bottom: 20px; padding:0 20px;">
            <img src="{{asset('/image/leterhead/head.png')}}" alt="" style="width: 100%; height: auto;">
        </div>
        <h5 class="battambang text-center mb-3">តារាងរបាយការណ៍ផ្លាស់ប្តូប្រេកង់វិទ្យុទាក់ទង ប្រចាំត្រីមាសទី២ ឆ្នាំ២០២៥</h5>
        {{-- <h6 class="battambang text-center mb-5">( អង្គភាព: {{ $set_frequency->unit }})</h6> --}}
        
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center" width=20>ល.រ</th>
                    <th style="width: 30%">អង្គភាព</th>
                    <th style="width: 20%">ចំនួនវិទ្យុ</th>
                    <th style="width: 40%">ចំនួនតាមម៉ូដែល</th>
                    {{-- <th>ផ្សេងៗ</th> --}}
                </tr>
            </thead>
            <tbody>
                {{-- {{ $details }} --}}
                @php
                    $sortedDetails = $details->sortBy('unit_id');
                @endphp
                @foreach ($sortedDetails as $record)
                    <tr>
                        <td class="text-center">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $record->unit }}</td>
                        <td>{{ $record->product_count }}</td>
                        <td>
                            @foreach (collect($record->products)->groupBy('model') as $model => $products)
                                <strong>{{ $model }} ({{ $products->sum('count') }})</strong><br>
                            @endforeach
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td class="text-center" colspan="2"><strong>សរុប</strong></td>
                    <td colspan="2"><strong>{{ $sortedDetails->sum('product_count') }} គ្រឿង</strong></td>
                    
                </tr>
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
                {{-- <p class="battambang text-center
            ">{{ $set_frequency->name }}</p> --}}
            </div>
            <div class="col-sm-3">
                <p class="battambang text-center
            ">ឌឹម វណ្ណៈ</p>
            </div>

        </div>
        {{-- {{ $set_frequency->created_at->timezone('Asia/Phnom_Penh')->format('d-m-Y H:i:s') }} --}}
    </div>
@endsection
