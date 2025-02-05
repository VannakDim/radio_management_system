@extends('frontend.layout.web')

@section('content')
    <div class="container" style="padding: 0 20px">
        <div class="row" style="margin-bottom: 20px">
            <img src="{{asset('/image/leterhead/head.png')}}" alt="" width="100%">
        </div>
        <h5 class="battambang text-center mb-3" style="font-weight: bold;">លិខិតនាំចូល</h5>
        <h6 class="battambang text-center mb-5">({{$stockIn->invoice_no}})</h6>
        
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center" width=90>ល.រ</th>
                    <th style="width: 30%">ក្រុមហ៊ុន</th>
                    <th style="width: 20%">ម៉ូដែល</th>
                    <th style="width: 20%">ចំនួន</th>
                    <th>ផ្សេងៗ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($stockIn->detail as $key => $item)
                    <tr>
                        <td class="text-center">{{ $key + 1 }}</td>
                        <td>{{ $item->product->brand->brand_name }}</td>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- @if ($stockIn->details->count() > 0)
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
                    @foreach ($stockIn->details as $key => $item)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->product->type }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif --}}

        <div class="row" style="margin: 50px 0 5px">
            <div class="col-sm-12">
                <p class="battambang text-right
            ">ធ្វើនៅ ក្រុងតាខ្មៅ ថ្ងៃ<span
                        style="padding-right: 150px"></span>ខែ<span style="padding-right: 90px"></span>ឆ្នាំ<span
                        style="padding-right: 80px"></span>ពស ២៥៦__</p>
            </div>
        </div>

        <div class="row" style="margin: 0 0 50px">

            <div class="col-sm-12">
                <p class="battambang text-right
            ">ត្រូវនឹងថ្ងៃទី<span
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
            "><strong>អ្នកផ្គត់ផ្គង់</strong></p>
            </div>
        </div>

        <div class="row" style="margin-top: 100px">
            <div class="col-sm-6">
                <p class="battambang
            "></p>
            </div>
            <div class="col-sm-3">
                <p class="battambang text-center
            ">{{ $stockIn->user->name }}</p>
            </div>
            <div class="col-sm-3">
                <p class="battambang text-center
            ">{{ $stockIn->supplier }}</p>
            </div>

        </div>
        {{ $stockIn->created_at }}
    </div>
@endsection
