@extends('frontend.layout.web')

@section('style')
@endsection

@section('content')
    <div class="container" style="padding: 0 20px">
        <div class="row" style="margin-bottom: 20px; padding:0 20px;">
            <img src="{{asset('/image/leterhead/head.png')}}" alt="" style="width: 100%; height: auto;">
        </div>
        <h5 class="battambang text-center mb-3">តារាងរបាយការណ៍ផ្លាស់ប្តូប្រេកង់វិទ្យុទាក់ទង ប្រចាំ{{ $Label }}</h5>
        
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th style="width: 30%">Brand name</th>
                    <th style="width: 30%">Model</th>
                    <th style="width: 30%" class="d-none d-md-table-cell">ចំនួនវិទ្យុ</th>
                </tr>
            </thead>
            <tbody>
                {{-- {{ $radios }} --}}
                @php
                    $totalRadioCount = 0;
                @endphp
                @foreach ($radios as $record)
                    @if ($record->accessory == 0 && $record->product_count > 0)
                        @php
                            $totalRadioCount += $record->product_count;
                        @endphp
                        <tr>
                            <td>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $record->brand->brand_name }}</td>
                            <td>{{ $record->name }}</td>
                            <td>{{ $record->product_count }}</td>
                        </tr>
                    @endif
                @endforeach
                <tr>
                    <td colspan="3" class="text-right text-danger strong"><strong>សរុប</strong></td>
                    <td><strong class="badge badge-danger" style="font-size: 1rem;">{{ $totalRadioCount }}
                            គ្រឿង</strong></td>
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
