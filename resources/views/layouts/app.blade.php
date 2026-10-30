<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') | University Guest House Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    @yield('styles')
</head>
<body>
    <!-- Top University Banner -->
    <header class="navbar navbar-expand-lg navbar-dark navbar-university sticky-top px-3">
        <a class="navbar-brand d-flex align-items-center text-white" href="{{ route('dashboard') }}">
            <i class="bi bi-buildings-fill fs-3 text-warning me-2"></i>
            <div>
                <div class="fw-bold fs-6 lh-1">Dr. Y.S. Parmar University of Horticulture & Forestry</div>
                <small class="text-white-50" style="font-size: 0.72rem;">Nauni, Solan (H.P.) &bull; Guest House Portal</small>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto align-items-center">
                <!-- Fast Role Switcher (For Testing & Verification) -->
                <li class="nav-item dropdown me-3">
                    <button class="btn btn-sm btn-outline-warning dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-badge me-1"></i> Switch Demo Role
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><h6 class="dropdown-header">Switch Institutional Persona</h6></li>
                        <li>
                            <form action="{{ route('quick.login') }}" method="POST">
                                @csrf
                                <input type="hidden" name="role" value="system_admin">
                                <button type="submit" class="dropdown-item @if(auth()->user()->primary_role === 'system_admin') active @endif">
                                    <i class="bi bi-shield-lock text-danger me-2"></i> System Administrator
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('quick.login') }}" method="POST">
                                @csrf
                                <input type="hidden" name="role" value="guest_house_admin">
                                <button type="submit" class="dropdown-item @if(auth()->user()->primary_role === 'guest_house_admin') active @endif">
                                    <i class="bi bi-gear-fill text-primary me-2"></i> Guest House Admin
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('quick.login') }}" method="POST">
                                @csrf
                                <input type="hidden" name="role" value="guest_house_incharge">
                                <button type="submit" class="dropdown-item @if(auth()->user()->primary_role === 'guest_house_incharge') active @endif">
                                    <i class="bi bi-key-fill text-success me-2"></i> Guest House Incharge
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('quick.login') }}" method="POST">
                                @csrf
                                <input type="hidden" name="role" value="verifier">
                                <button type="submit" class="dropdown-item @if(auth()->user()->primary_role === 'verifier') active @endif">
                                    <i class="bi bi-check-circle text-info me-2"></i> Department Verifier
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('quick.login') }}" method="POST">
                                @csrf
                                <input type="hidden" name="role" value="approver">
                                <button type="submit" class="dropdown-item @if(auth()->user()->primary_role === 'approver') active @endif">
                                    <i class="bi bi-award text-warning me-2"></i> Dean / Approving Authority
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('quick.login') }}" method="POST">
                                @csrf
                                <input type="hidden" name="role" value="employee">
                                <button type="submit" class="dropdown-item @if(auth()->user()->primary_role === 'employee') active @endif">
                                    <i class="bi bi-person text-secondary me-2"></i> Faculty / Applicant
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('quick.login') }}" method="POST">
                                @csrf
                                <input type="hidden" name="role" value="accounts_finance">
                                <button type="submit" class="dropdown-item @if(auth()->user()->primary_role === 'accounts_finance') active @endif">
                                    <i class="bi bi-currency-rupee text-success me-2"></i> Accounts & Finance
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('quick.login') }}" method="POST">
                                @csrf
                                <input type="hidden" name="role" value="report_auditor">
                                <button type="submit" class="dropdown-item @if(auth()->user()->primary_role === 'report_auditor') active @endif">
                                    <i class="bi bi-file-earmark-bar-graph text-dark me-2"></i> Report Auditor
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>

                <!-- Logged in User Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-5 me-2"></i>
                        <div class="text-start d-none d-md-block">
                            <div class="fw-semibold lh-1">{{ auth()->user()->name }}</div>
                            <small class="text-white-50 text-uppercase" style="font-size: 0.68rem;">{{ str_replace('_', ' ', auth()->user()->primary_role) }}</small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="bi bi-person me-2"></i> My Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('bookings.track') }}"><i class="bi bi-search me-2"></i> Public Request Tracker</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar py-3">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>

                        <div class="nav-header">Guest House Bookings</div>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('bookings.create') ? 'active' : '' }}" href="{{ route('bookings.create') }}">
                                <i class="bi bi-calendar-plus me-2 text-success"></i> New Reservation
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('bookings.index') ? 'active' : '' }}" href="{{ route('bookings.index') }}">
                                <i class="bi bi-journal-text me-2"></i> Booking Register
                            </a>
                        </li>

                        @if(auth()->user()->hasRole(['verifier', 'approver', 'system_admin']))
                        <div class="nav-header">Institutional Workflow</div>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('workflow.pending') ? 'active' : '' }}" href="{{ route('workflow.pending') }}">
                                <i class="bi bi-hourglass-split me-2 text-warning"></i> Pending Verification / Approval
                            </a>
                        </li>
                        @endif

                        @if(auth()->user()->hasRole(['guest_house_incharge', 'guest_house_admin', 'system_admin']))
                        <div class="nav-header">Front Desk & Allocation</div>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('allocations.index') ? 'active' : '' }}" href="{{ route('allocations.index') }}">
                                <i class="bi bi-door-open me-2 text-primary"></i> Room Allocation
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('allocations.calendar') ? 'active' : '' }}" href="{{ route('allocations.calendar') }}">
                                <i class="bi bi-calendar3 me-2"></i> Availability Calendar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('operations.arrivals') ? 'active' : '' }}" href="{{ route('operations.arrivals') }}">
                                <i class="bi bi-box-arrow-in-right me-2 text-success"></i> Check-in Desk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('operations.active_stays') ? 'active' : '' }}" href="{{ route('operations.active_stays') }}">
                                <i class="bi bi-people-fill me-2 text-info"></i> Active Occupancy & Check-out
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('operations.blocks') ? 'active' : '' }}" href="{{ route('operations.blocks') }}">
                                <i class="bi bi-slash-circle me-2 text-danger"></i> Maintenance Blocks
                            </a>
                        </li>
                        @endif

                        @if(auth()->user()->hasRole(['accounts_finance', 'guest_house_incharge', 'guest_house_admin', 'system_admin']))
                        <div class="nav-header">Finance & Cashiering</div>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('billing.index') ? 'active' : '' }}" href="{{ route('billing.index') }}">
                                <i class="bi bi-receipt me-2 text-success"></i> Invoices & Receipts
                            </a>
                        </li>
                        @endif

                        @if(auth()->user()->hasRole(['report_auditor', 'accounts_finance', 'guest_house_admin', 'system_admin', 'guest_house_incharge']))
                        <div class="nav-header">Reports & Exports</div>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reports.bookings') ? 'active' : '' }}" href="{{ route('reports.bookings') }}">
                                <i class="bi bi-file-earmark-spreadsheet me-2"></i> Booking Register Report
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reports.availability') ? 'active' : '' }}" href="{{ route('reports.availability') }}">
                                <i class="bi bi-grid-3x3 me-2"></i> Room Occupancy Report
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reports.financial') ? 'active' : '' }}" href="{{ route('reports.financial') }}">
                                <i class="bi bi-cash-stack me-2"></i> Revenue & Billing Summary
                            </a>
                        </li>
                        @endif

                        @if(auth()->user()->hasRole(['guest_house_admin', 'system_admin']))
                        <div class="nav-header">Configuration Masters</div>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('masters.guesthouses.*') ? 'active' : '' }}" href="{{ route('masters.guesthouses.index') }}">
                                <i class="bi bi-houses me-2"></i> Guest House Master
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('masters.roomtypes.*') ? 'active' : '' }}" href="{{ route('masters.roomtypes.index') }}">
                                <i class="bi bi-tags me-2"></i> Room Type Master
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('masters.rooms.*') ? 'active' : '' }}" href="{{ route('masters.rooms.index') }}">
                                <i class="bi bi-door-closed me-2"></i> Rooms Master
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('masters.tariffs.*') ? 'active' : '' }}" href="{{ route('masters.tariffs.index') }}">
                                <i class="bi bi-currency-rupee me-2"></i> Category Tariffs Matrix
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('masters.taxes.*') ? 'active' : '' }}" href="{{ route('masters.taxes.index') }}">
                                <i class="bi bi-percent me-2"></i> Tax Master
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('masters.expenditure-heads.*') ? 'active' : '' }}" href="{{ route('masters.expenditure-heads.index') }}">
                                <i class="bi bi-briefcase me-2"></i> Expenditure Heads
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('masters.settings.*') ? 'active' : '' }}" href="{{ route('masters.settings.index') }}">
                                <i class="bi bi-sliders me-2"></i> Global Policies & Timings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('masters.users.*') ? 'active' : '' }}" href="{{ route('masters.users.index') }}">
                                <i class="bi bi-people me-2"></i> User & Role Accounts
                            </a>
                        </li>
                        @endif

                        @if(auth()->user()->hasRole(['system_admin', 'report_auditor']))
                        <div class="nav-header">System Oversight</div>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('oversight.audit') ? 'active' : '' }}" href="{{ route('oversight.audit') }}">
                                <i class="bi bi-shield-check me-2"></i> Immutable Audit Trail
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('oversight.notifications') ? 'active' : '' }}" href="{{ route('oversight.notifications') }}">
                                <i class="bi bi-envelope-paper me-2"></i> Notification Logs
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </nav>

            <!-- Main Page Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <!-- Alerts / Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i> {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <div class="fw-bold"><i class="bi bi-x-circle-fill me-2"></i> Please correct the following errors:</div>
                        <ul class="mb-0 mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>

