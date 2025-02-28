@extends('admin.layout.admin')
@section('main_body')
    <div class="content">
        {{-- <x-app-layout> --}}

            <div class="bg-white border rounded">
                <div class="row no-gutters">
                    <div class="col-lg-4 col-xl-3">
                        <div class="profile-content-left pt-5 pb-3 px-3 px-xl-5">
                            <div class="card text-center widget-profile px-0 border-0">
                                <div class="card-img mx-auto rounded-circle">
                                    <img src="{{ Auth::user()->profile_photo_url }}" alt="user image" width="100%">
                                </div>
                                <div class="card-body">
                                    <h4 class="py-2 text-dark">{{ Auth::user()->name }}</h4>
                                    <p>{{ Auth::user()->email }}</p>
                                    <a class="btn btn-primary btn-pill btn-lg my-4" href="#">Follow</a>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between ">
                                <div class="text-center pb-4">
                                    <h6 class="text-dark pb-2">1503</h6>
                                    <p>Friends</p>
                                </div>
                                <div class="text-center pb-4">
                                    <h6 class="text-dark pb-2">2905</h6>
                                    <p>Followers</p>
                                </div>
                                <div class="text-center pb-4">
                                    <h6 class="text-dark pb-2">1200</h6>
                                    <p>Following</p>
                                </div>
                            </div>
                            <hr class="w-100">
                            <div class="contact-info pt-4">
                                <h5 class="text-dark mb-1">Contact Information</h5>
                                <p class="text-dark font-weight-medium pt-4 mb-2">Email address</p>
                                <p>{{ Auth::user()->email }}</p>
                                <p class="text-dark font-weight-medium pt-4 mb-2">Phone Number</p>
                                <p>+99 9539 2641 31</p>
                                <p class="text-dark font-weight-medium pt-4 mb-2">Birthday</p>
                                <p>Nov 15, 1990</p>
                                <p class="text-dark font-weight-medium pt-4 mb-2">Social Profile</p>
                                <p class="pb-3 social-button">
                                    <a href="#" class="mb-1 btn btn-outline btn-twitter rounded-circle">
                                        <i class="mdi mdi-twitter"></i>
                                    </a>
                                    <a href="#" class="mb-1 btn btn-outline btn-linkedin rounded-circle">
                                        <i class="mdi mdi-linkedin"></i>
                                    </a>
                                    <a href="#" class="mb-1 btn btn-outline btn-facebook rounded-circle">
                                        <i class="mdi mdi-facebook"></i>
                                    </a>
                                    <a href="#" class="mb-1 btn btn-outline btn-skype rounded-circle">
                                        <i class="mdi mdi-skype"></i>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8 col-xl-9">
                        <div class="profile-content-right py-5">
                            <ul class="nav nav-tabs px-3 px-xl-5 nav-style-border" id="myTab" role="tablist">

                                <li class="nav-item">
                                    <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#profile"
                                        role="tab" aria-controls="profile" aria-selected="false">Profile</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" id="timeline-tab" data-toggle="tab" href="#timeline" role="tab"
                                        aria-controls="timeline" aria-selected="true">Timeline</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" id="settings-tab" data-toggle="tab" href="#settings" role="tab"
                                        aria-controls="settings" aria-selected="false">Settings</a>
                                </li>
                            </ul>
                            <div class="tab-content px-3 px-xl-5" id="myTabContent">
                                <div class="tab-pane fade" id="timeline" role="tabpanel" aria-labelledby="timeline-tab">
                                    <div class="media mt-5 profile-timeline-media">
                                        <div class="align-self-start iconbox-45 overflow-hidden mr-3">
                                            <img src="assets/img/user/u3.jpg" alt="Generic placeholder image">
                                        </div>
                                        <div class="media-body">
                                            <h6 class="mt-0 text-dark">Larissa Gebhardt</h6>
                                            <span>Designer</span>
                                            <span class="float-right">5 mins ago</span>
                                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                                                tempor
                                                incididunt
                                                ut labore et dolore
                                                magna aliqua. ut enim ad minim veniam quis nostrud exercitation ullamco
                                                laboris
                                                nisi ut
                                                aliquip.</p>
                                            <div class="d-inline-block rounded overflow-hidden mt-4 mr-0 mr-lg-4">
                                                <img src="assets/img/products/pa1.jpg" alt="Product">
                                            </div>
                                            <div class="d-inline-block rounded overflow-hidden mt-4 mr-0 mr-lg-4">
                                                <img src="assets/img/products/pa2.jpg" alt="Product">
                                            </div>
                                            <div class="d-inline-block rounded overflow-hidden mt-4 mr-0 mr-lg-4">
                                                <img src="assets/img/products/pa3.jpg" alt="Product">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="media mt-5 profile-timeline-media">
                                        <div class="align-self-start iconbox-45 overflow-hidden mr-3">
                                            <img src="assets/img/user/u4.jpg" alt="Generic placeholder image">
                                        </div>
                                        <div class="media-body">
                                            <h6 class="mt-0 text-dark">Walter Reuter</h6>
                                            <span>Designer</span>
                                            <span class="float-right">2 hrs ago</span>
                                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                                                tempor
                                                incididunt
                                                ut labore et dolore
                                                magna aliqua. ut enim ad minim veniam quis nostrud exercitation ullamco
                                                laboris
                                                nisi ut
                                                aliquip.</p>

                                        </div>
                                    </div>
                                    <div class="media mt-5 profile-timeline-media">
                                        <div class="align-self-start iconbox-45 overflow-hidden mr-3">
                                            <img src="assets/img/user/u7.jpg" alt="Generic placeholder image">
                                        </div>
                                        <div class="media-body">
                                            <h6 class="mt-0 text-dark">Albrecht Straub</h6>
                                            <span>Designer</span>
                                            <span class="float-right">5 days ago</span>
                                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                                                tempor
                                                incididunt
                                                ut labore et dolore
                                                magna aliqua. ut enim ad minim veniam quis nostrud exercitation ullamco
                                                laboris
                                                nisi ut
                                                aliquip.</p>
                                            <div class="d-inline-block rounded overflow-hidden mt-4 mr-0 mr-lg-4">
                                                <img src="assets/img/products/pa4.jpg" alt="Product">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="media mt-5 profile-timeline-media">
                                        <div class="align-self-start iconbox-45 overflow-hidden mr-3">
                                            <img src="assets/img/user/u8.jpg" alt="Generic placeholder image">
                                        </div>
                                        <div class="media-body">
                                            <h6 class="mt-0 text-dark">Selena Wagner</h6>
                                            <span>Designer</span>
                                            <span class="float-right">Mar 05, 2018</span>
                                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                                                tempor
                                                incididunt
                                                ut labore et dolore
                                                magna aliqua. ut enim ad minim veniam quis nostrud exercitation ullamco
                                                laboris
                                                nisi ut
                                                aliquip.</p>
                                            <div class="d-inline-block rounded overflow-hidden mt-4 mr-0 mr-lg-4">
                                                <img src="assets/img/products/pa5.jpg" alt="Product">
                                            </div>
                                            <div class="d-inline-block rounded overflow-hidden mt-4 mr-0 mr-lg-4">
                                                <img src="assets/img/products/pa6.jpg" alt="Product">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="tab-pane fade show active" id="profile" role="tabpanel"
                                    aria-labelledby="profile-tab">
                                    <div>
                                        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                                            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                                                @livewire('profile.update-profile-information-form')
                                                <x-section-border />
                                            @endif

                                            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                                                <div class="mt-10 sm:mt-0">
                                                    @livewire('profile.update-password-form')
                                                </div>

                                                <x-section-border />
                                            @endif

                                            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                                                <div class="mt-10 sm:mt-0">
                                                    @livewire('profile.two-factor-authentication-form')
                                                </div>

                                                <x-section-border />
                                            @endif

                                            <div class="mt-10 sm:mt-0">
                                                @livewire('profile.logout-other-browser-sessions-form')
                                            </div>

                                            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                                                <x-section-border />

                                                <div class="mt-10 sm:mt-0">
                                                    @livewire('profile.delete-user-form')
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="settings" role="tabpanel"
                                    aria-labelledby="settings-tab">...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {{-- </x-app-layout> --}}
    </div>
@endsection
