<!--
====================================
————————— LEFT SIDEBAR —————————————
====================================
-->
<aside class="left-sidebar bg-sidebar">
    <div id="sidebar" class="sidebar sidebar-with-footer">
        <!-- Aplication Brand -->
        <div class="app-brand">
            <a href="{{ route('dashboard') }}">
                {{-- <svg class="brand-icon" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid" width="30"
                    height="33" viewBox="0 0 30 33">
                    <g fill="none" fill-rule="evenodd">
                        <path class="logo-fill-blue" fill="#7DBCFF" d="M0 4v25l8 4V0zM22 4v25l8 4V0z" />
                        <path class="logo-fill-white" fill="#FFF" d="M11 4v25l8 4V0z" />
                    </g>
                </svg> --}}
                <img src="{{ asset('image/logo/walkie-talkie-logo.png') }}" alt="SPM-TELECOM" class="my-2"
                    style="width: 30px; height: 60px;">
                <span class="text-white brand-name"><strong>TELECOM</strong></span>
            </a>
        </div>
        <!-- begin sidebar scrollbar -->
        <div class="sidebar-scrollbar">

            <!-- sidebar menu -->
            <ul class="nav sidebar-inner" id="sidebar-menu">

                <li class="has-sub {{ request()->is('dashboard*') ? 'active' : '' }}">
                    <a class="sidenav-item-link" href="/dashboard">
                        <i class="fa-solid fa-chart-simple"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
               
                </li>

                @php
                    $url = Request::segment(1);
                    $component = ['slider', 'service', 'about', 'team','contact'];
                    $blog = ['post', 'tag', 'category'];
                    $product = ['product', 'model', 'category'];
                    $home = ['brand'];
                @endphp
                

                <li class="kh-battambang has-sub @if (in_array($url, $product)) expand active @endif">
                    <a class="sidenav-item-link" href="javascript:void(0)" data-toggle="collapse" data-target="#products"
                        aria-expanded="false" aria-controls="products">
                        <i class="fa-solid fa-walkie-talkie"></i>
                        <span class="nav-text">PRODUCTS</span> <b class="caret"></b>
                    </a>
                    <ul class="collapse @if (in_array($url, $product)) show @endif" id="products"
                        data-parent="#sidebar-menu">
                        <div class="sub-menu">

                            {{-- <li class="{{ request()->is('product/all') ? 'active' : '' }}">
                                <a class="sidenav-item-link" href="{{ route('all.product') }}">
                                    <i class="fa-solid fa-caret-right {{ request()->is('product/all') ? 'fa-beat' : '' }}"></i>
                                    <span class="nav-text">បញ្ជីសម្ភារៈ</span>

                                </a>
                            </li> --}}

                            <li class="{{ request()->is('product/model*') ? 'active' : '' }}">
                                <a class="sidenav-item-link" href="{{ route('model.show') }}">
                                    <i class="fa-solid fa-caret-right {{ request()->is('product/model*') ? 'fa-beat' : '' }}"></i>
                                    <span class="nav-text">បញ្ជីសម្ភារៈ</span>

                                </a>
                            </li>

                            <li class="{{ request()->is('product/stock-in*') ? 'active' : '' }}">
                                <a class="sidenav-item-link" href="{{ route('stockin.index') }}">
                                    <i class="fa-solid fa-caret-right {{ request()->is('product/stock-in*') ? 'fa-beat' : '' }}"></i>
                                    <span class="nav-text">នាំចូល</span>

                                </a>
                            </li>

                            <li class="{{ request()->is('product/stock-out*') ? 'active' : '' }}">
                                <a class="sidenav-item-link" href="{{ route('stockout.index') }}">
                                    <i class="fa-solid fa-caret-right {{ request()->is('product/stock-out*') ? 'fa-beat' : '' }}"></i>
                                    <span class="nav-text">ប្រគល់</span>

                                </a>
                            </li>

                            <li class="{{ request()->is('product/borrow*') ? 'active' : '' }}">
                                <a class="sidenav-item-link" href="{{ route('borrow.index') }}">
                                    <i class="fa-solid fa-caret-right {{ request()->is('product/borrow*') ? 'fa-beat' : '' }}"></i>
                                    <span class="nav-text">ខ្ចី/សង</span>

                                </a>
                            </li>
                        </div>
                    </ul>
                </li>
        </div>
        
    </div>
</aside>
