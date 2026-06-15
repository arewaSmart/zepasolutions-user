 <!-- app-header -->
 <header class="app-header">
     <!-- Start::main-header-container -->
     <div class="main-header-container container-fluid">
         <!-- Start::header-content-left -->
         <div class="header-content-left">
             <!-- Start::header-element -->
             <div class="header-element">
                 <div class="horizontal-logo">
                     <a href="{{ route('dashboard') }}" class="header-logo">
                         <img src="{{ asset('assets/images/brand-logos/logo.png') }}" alt="logo" class="desktop-logo">
                         <img src="{{ asset('assets/images/brand-logos/logo.png') }}" alt="logo"
                             class="toggle-logo">
                         <img src="{{ asset('assets/images/brand-logos/logo.png') }}" alt="logo"
                             class="toggle-dark">
                         <img src="{{ asset('assets/images/brand-logos/logo.png') }}" alt="logo"
                             class="desktop-white">
                         <img src="{{ asset('assets/images/brand-logos/logo.png') }}" alt="logo"
                             class="toggle-white">
                     </a>
                 </div>
             </div>
             <!-- End::header-element -->
             <!-- Start::header-element -->
             <div class="header-element">
                 <!-- Start::header-link -->
                 <a aria-label="Hide Sidebar"
                     class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle"
                     data-bs-toggle="sidebar" href="javascript:void(0);"><span></span></a>
                 <!-- End::header-link -->
             </div>
             <!-- End::header-element -->
         </div>
         <!-- End::header-content-left -->
         <!-- Start::header-content-right -->
         <div class="header-content-right">
             <!-- End::header-element -->
             <!-- Start::header-element -->
             <div class="header-element notifications-dropdown">
                 <!-- Start::header-link|dropdown-toggle -->
                 <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-toggle="dropdown"
                     data-bs-auto-close="outside" id="messageDropdown" aria-expanded="false">
                     <i class="bx bx-bell header-link-icon"></i>
                     <span class="badge bg-danger rounded-pill header-icon-badge pulse pulse-secondary"
                         id="notification-icon-badge">{{ $notifyCount }}</span>
                 </a>
                 <!-- End::header-link|dropdown-toggle -->
                 <!-- Start::main-header-dropdown -->
                 <div class="main-header-dropdown dropdown-menu dropdown-menu-end" data-popper-placement="none">
                     <div class="p-3">
                         <div class="d-flex align-items-center justify-content-between">
                             <p class="mb-0 fs-17 fw-semibold">Notifications</p>
                             <span class="badge bg-danger-transparent" id="notifiation-data">{{ $notifyCount }}
                                 Unread</span>
                         </div>
                     </div>
                     <div class="dropdown-divider"></div>
                     <ul class="list-unstyled mb-0" id="header-notification-scroll">
                         @if ($notifyCount != 0 && $notificationsEnabled)
                             <audio src="{{ asset('assets/audio/notification.mp3') }}" autoplay="autoplay"></audio>
                         @endif
                         @if ($notifications->count() != 0)
                             @foreach ($notifications as $data)
                                 <li class="dropdown-item">
                                     <div class="d-flex align-items-start">
                                         <div class="pe-2">
                                             @if ($data->message_title == 'Account Has Been Verified')
                                                 <span class="avatar avatar-md bg-primary-transparent avatar-rounded"><i
                                                         class="ti ti-user-check fs-18"></i></span>
                                             @else
                                                 <span class="avatar avatar-md bg-primary-transparent avatar-rounded"><i
                                                         class="ti ti-bell fs-18"></i></span>
                                             @endif
                                         </div>
                                         <div class="flex-grow-1 d-flex align-items-center justify-content-between">
                                             <div>
                                                 <p class="mb-0 fw-semibold">{{ $data->message_title }}</p>
                                                 <span
                                                     class="text-muted fw-normal fs-12 header-notification-text">{{ $data->messages }}</span>
                                             </div>
                                             <div>
                                             </div>
                                         </div>
                                     </div>
                                 </li>
                             @endforeach
                         @else
                             <div class="p-5 empty-item1">
                                 <div class="text-center">
                                     <span class="avatar avatar-xl avatar-rounded bg-secondary-transparent">
                                         <i class="ri-notification-off-line fs-2"></i>
                                     </span>
                                     <h6 class="fw-semibold mt-3">No New Notifications</h6>
                                 </div>
                             </div>
                         @endif
                     </ul>
                     @if ($notifyCount != 0)
                         <div class="p-3 empty-header-item1 border-top">
                             <div class="d-grid">
                                 <a id="read" href="#" class="btn btn-primary">Mark as Read</a>
                                 <p style="display:none" id="done" class="text-danger text-center">Marked Read</p>
                             </div>
                         </div>
                     @endif
                 </div>
                 <!-- End::main-header-dropdown -->
             </div>
             <!-- End::header-element -->
             <!-- Start::header-element -->

             <div class="header-element">
                 <!-- Start::header-link|dropdown-toggle -->
                 <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile"
                     data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                     <div class="d-flex align-items-center">
                         <div class="me-sm-2 me-0">
                             @if (Auth::user()->profile_pic != '')
                                 <img alt="img" width="32" height="32" class="rounded-circle"
                                     src="{{ 'data:image/;base64,' . Auth::user()->profile_pic }}">
                             @else
                                 <img alt="img" width="32" height="32" class="rounded-circle"
                                     src="{{ asset('assets/images/zepa-logo.jpg') }}" alt="">
                             @endif
                         </div>
                         <div class="d-sm-block d-none">
                             <p class="fw-semibold mb-0 lh-1">{{ substr(Auth::user()->first_name, 0, 10) }}</p>
                             <span class="op-7 fw-normal d-block fs-11">{{ ucwords(Auth::user()->role) }}
                                 Account</span>
                         </div>
                     </div>
                 </a>

                 <!-- End::header-link|dropdown-toggle -->
                 <ul class="main-header-dropdown dropdown-menu pt-0 overflow-hidden header-profile-dropdown dropdown-menu-end"
                     aria-labelledby="mainHeaderProfile">
                     <li>
                         <a class="dropdown-item d-flex align-items-center" href="{{ route('profile.edit') }}">
                             <i class="ti ti-settings fs-18 me-2 op-7"></i>
                             <span>Settings</span>
                         </a>
                     </li>
                     <li>
                         <a class="dropdown-item d-flex align-items-center" href="{{ route('support') }}">
                             <i class="ti ti-headset fs-18 me-2 op-7"></i>
                             <span>Support</span>
                         </a>
                     </li>
                     <li>
                         <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                             @csrf
                             <button type="submit" class="dropdown-item d-flex align-items-center">
                                 <i class="ti ti-logout fs-18 me-2 op-7" style="margin-left:2px;"></i>
                                 <span>Log Out</span>
                             </button>
                         </form>
                     </li>
                 </ul>

             </div>
             <!-- End::header-element -->
         </div>
         <!-- End::header-content-right -->
     </div>
     <!-- End::main-header-container -->
 </header>
