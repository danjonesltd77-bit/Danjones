<!-- Start Header Area -->
<header class="header-area bg-white mb-4 rounded-10 border border-white" id="header-area">
    <div class="row align-items-center">
        <div class="col-md-6">
            <div class="left-header-content">
                <ul class="d-flex align-items-center ps-0 mb-0 list-unstyled justify-content-center justify-content-md-start">
                    <li class="d-xl-none">
                        <button class="header-burger-menu bg-transparent p-0 border-0 position-relative top-3" id="header-burger-menu">
                            <span class="border-1 d-block for-dark-burger" style="border-bottom: 1px solid #475569; height: 1px; width: 25px;"></span>
                            <span class="border-1 d-block for-dark-burger" style="border-bottom: 1px solid #475569; height: 1px; width: 25px; margin: 6px 0;"></span>
                            <span class="border-1 d-block for-dark-burger" style="border-bottom: 1px solid #475569; height: 1px; width: 25px;"></span>
                        </button>
                    </li>
                    <li>
                        <form class="src-form position-relative">
                            <input type="text" class="form-control" placeholder="Search transactions...">
                            <div class="src-btn position-absolute top-50 start-0 translate-middle-y bg-transparent p-0 border-0">
                                <span class="material-symbols-outlined">search</span>
                            </div>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-md-6">
            <div class="right-header-content mt-3 mt-md-0">
                <ul class="d-flex align-items-center justify-content-center justify-content-md-end ps-0 mb-0 list-unstyled">
                    
                    <li class="header-right-item light-dark-item">
                        <div class="light-dark">
                            <button class="switch-toggle dark-btn p-0 bg-transparent lh-0 border-0" id="switch-toggle">
                                <span class="dark"><i class="material-symbols-outlined">dark_mode</i></span> 
                                <span class="light"><i class="material-symbols-outlined">light_mode</i></span>
                            </button>
                        </div>
                    </li>

                    <li class="header-right-item">
                        <div class="dropdown admin-profile">
                            <div class="d-xxl-flex align-items-center bg-transparent border-0 text-start p-0 cursor dropdown-toggle" data-bs-toggle="dropdown">
                                <div class="flex-shrink-0 position-relative">
                                    <img class="rounded-circle admin-img-width-for-mobile" style="width: 40px; height: 40px;" src="{{ asset('assets/images/admin.png') }}" alt="admin">
                                    <span class="d-block bg-success-60 border border-2 border-white rounded-circle position-absolute end-0 bottom-0" style="width: 11px; height: 11px;"></span>
                                </div>
                                <div class="flex-grow-1 ms-3 d-none d-xxl-block">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h3 class="fw-semibold fs-16 mb-0">{{ auth()->user()->name }}</h3>
                                            <span class="fs-13 fw-normal text-secondary">Verified Account</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="dropdown-menu border-0 bg-white dropdown-menu-end">
                                <div class="d-flex align-items-center info">
                                    <div class="flex-shrink-0">
                                        <img class="rounded-circle admin-img-width-for-mobile" style="width: 40px; height: 40px;" src="{{ asset('assets/images/admin.png') }}" alt="admin">
                                    </div>
                                    <div class="flex-grow-1 ms-10">
                                        <h3 class="fw-medium fs-17 mb-0">{{ auth()->user()->name }}</h3>
                                        <span class="fs-15 fw-medium">{{ auth()->user()->email }}</span>
                                    </div>
                                </div>
                                <ul class="admin-link mb-0 list-unstyled">
                                    <li>
                                        <a class="dropdown-item admin-item-link d-flex align-items-center text-body" href="{{ route('profile.edit') }}">
                                            <i class="material-symbols-outlined">person</i>
                                            <span class="ms-2">My Profile</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item admin-item-link d-flex align-items-center text-body" href="{{ route('profile.edit') }}">
                                            <i class="material-symbols-outlined">settings</i>
                                            <span class="ms-2">Settings</span>
                                        </a>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}" id="logout-header-form" class="d-none">
                                            @csrf
                                        </form>
                                        <a class="dropdown-item admin-item-link d-flex align-items-center text-body" href="javascript:void(0);" onclick="event.preventDefault(); document.getElementById('logout-header-form').submit();">
                                            <i class="material-symbols-outlined">logout</i>
                                            <span class="ms-2">Logout</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
<!-- End Header Area -->
