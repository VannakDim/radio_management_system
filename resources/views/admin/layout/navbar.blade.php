<!-- Header -->
<header class="main-header " id="header">
    <nav class="navbar navbar-static-top navbar-expand-lg">
        <!-- Sidebar toggle button -->
        <button id="sidebar-toggler" class="sidebar-toggle">
            <span class="sr-only">Toggle navigation</span>
        </button>
        <!-- search form -->
        <div class="search-form">
            <!-- Show only the button on small screens, show full form on lg+ -->
            <div class="input-group">
            <button type="button" name="search" id="search-btn" class="btn btn-flat d-inline-block d-lg-none" onclick="window.location.href='{{ route('search.index') }}'">
                <i class="mdi mdi-magnify"></i>
            </button>
            <form action="{{ route('admin.search') }}" method="GET" class="d-none d-lg-flex w-100">
                <button type="button" name="search" id="search-btn-lg" class="btn btn-flat">
                <i class="mdi mdi-magnify"></i>
                </button>
                <input type="text" name="query" id="search-input" class="form-control"
                placeholder="'Search', 'name', 'units', 'PID' etc." autofocus autocomplete="off" value="{{ request('query') }}" />
            </form>
            </div>
        </div>

        <div class="navbar-right ">
            <ul class="nav navbar-nav">
                {{-- <li>
                    <a href="{{route('home')}}" class="px-3" target="_blank" rel="noopener noreferrer">
                        <i class="fa-solid fa-earth-americas" style="font-size: 1.5rem; color:rgba(138, 144, 157, 0.7);"></i>
                    </a>
                </li> --}}
                {{-- <li class="dropdown notifications-menu">
                    <button class="dropdown-toggle" data-toggle="dropdown">
                        <i class="mdi mdi-bell-outline"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li class="dropdown-header">You have 5 notifications</li>
                        <li>
                            <a href="#">
                                <i class="mdi mdi-account-plus"></i> New user registered
                                <span class=" font-size-12 d-inline-block float-right"><i
                                        class="mdi mdi-clock-outline"></i> 10 AM</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="mdi mdi-account-remove"></i> User deleted
                                <span class=" font-size-12 d-inline-block float-right"><i
                                        class="mdi mdi-clock-outline"></i> 07 AM</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="mdi mdi-chart-areaspline"></i> Sales report is ready
                                <span class=" font-size-12 d-inline-block float-right"><i
                                        class="mdi mdi-clock-outline"></i> 12 PM</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="mdi mdi-account-supervisor"></i> New client
                                <span class=" font-size-12 d-inline-block float-right"><i
                                        class="mdi mdi-clock-outline"></i> 10 AM</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="mdi mdi-server-network-off"></i> Server overloaded
                                <span class=" font-size-12 d-inline-block float-right"><i
                                        class="mdi mdi-clock-outline"></i> 05 AM</span>
                            </a>
                        </li>
                        <li class="dropdown-footer">
                            <a class="text-center" href="#"> View All </a>
                        </li>
                    </ul>
                </li> --}}
                <!-- User Account -->
                
                <li class="dropdown">
                    @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                        <a href="#" class="flex items-center px-4 py-3 nav-link" data-toggle="dropdown" aria-expanded="false" aria-haspopup="true" style="border: none; background: transparent;">
                            <div class="shrink-0 me-3">
                                <img class="size-10 rounded-full object-cover"
                                    src="{{ asset(Auth::user()->profile_photo_url) }}"
                                    alt="{{ Auth::user()->name }}" />
                            </div>
                        </a>
                    @endif

                    <ul class="dropdown-menu dropdown-menu-right dropdown-menu-end mt-2"
                        style="width: 250px; right: 0; left: auto; min-width: 220px;">
                        <!-- User image -->
                        <li class="dropdown-header text-center d-flex flex-column align-items-center">
                            <img src="{{ Auth::user()->profile_photo_url }}" class="img-circle mb-2"
                                alt="User Image" style="width:60px; height:60px; object-fit:cover; display:block; margin:auto;" />
                            <div class="d-inline-block text-center">
                                {{ Auth::user()->name }} <br>
                                <small class="pt-1">{{ Auth::user()->email }}</small>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a href="{{ route('profile.show') }}" class="dropdown-item">
                                <i class="mdi mdi-account"></i> My Profile
                            </a>
                        </li>
                        {{-- <li>
                            <a href="email-inbox.html" class="dropdown-item">
                                <i class="mdi mdi-email"></i> Message
                            </a>
                        </li>
                        <li>
                            <a href="#" class="dropdown-item">
                                <i class="mdi mdi-diamond-stone"></i> Projects
                            </a>
                        </li>
                        <li>
                            <a href="#" class="dropdown-item">
                                <i class="mdi mdi-settings"></i> Account Setting
                            </a>
                        </li> --}}
                        <li><hr class="dropdown-divider"></li>
                        <li class="dropdown-footer text-center">
                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf
                                <x-dropdown-link href="{{ route('logout') }}"
                                    @click.prevent="$root.submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </li>
                    </ul>
                </li>
                <style>
                /* Ensure dropdown aligns right on all screens */
                @media (max-width: 991.98px) {
                    .navbar .dropdown-menu.dropdown-menu-end,
                    .navbar .dropdown-menu.dropdown-menu-right {
                        right: 0 !important;
                        left: auto !important;
                        min-width: 220px;
                    }
                }
                @media (max-width: 575.98px) {
                    .navbar .dropdown-menu.dropdown-menu-end,
                    .navbar .dropdown-menu.dropdown-menu-right {
                        right: 10px !important;
                        left: auto !important;
                        min-width: 200px;
                    }
                }
                </style>

                {{-- </li> --}}
            </ul>
        </div>
    </nav>
</header>
<style>
#show-desktop button {
    font-size: 1.75rem !important;
    color: rgba(138, 144, 157, 0.7)!important;
}
</style>