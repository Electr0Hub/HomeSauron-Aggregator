<ul class="navbar-nav">
    <li class="nav-item">
        <a class="nav-link @if(url()->current() === route('dashboard.index')) active @endif" href="{{ route('dashboard.index') }}">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                <i class="fa-solid fa-folder"></i>
            </div>
            <span class="nav-link-text ms-1">Dashboard</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(url()->current() === route('cameras.index')) active @endif" href="{{ route('cameras.index') }}">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                <i class="fa-solid fa-list"></i>
            </div>
            <span class="nav-link-text ms-1">Cameras List</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(url()->current() === route('cameras.discover')) active @endif" href="{{ route('cameras.discover') }}">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <span class="nav-link-text ms-1">Discover Cameras</span>
        </a>
    </li>
</ul>
