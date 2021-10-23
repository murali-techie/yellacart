<a href="{{ route('wishlists.index') }}" class="d-flex align-items-center text-reset" style="position: relative;">
    <i class="la la-heart-o la-2x opacity-80" style="font-size: 1.125rem !important;"></i>
     @if(Auth::check())
            <span class="badge badge-primary badge-inline badge-pill" style="position: absolute;left: auto;top: -7px;right: -7px;background-color: #f00;">{{ count(Auth::user()->wishlists)}}</span>
        @else
            <span class="badge badge-primary badge-inline badge-pill" style="position: absolute;left: auto;top: -7px;right: -7px;background-color: #f00;">0</span>
        @endif
     {{-- <span class="flex-grow-1 ml-1">
        @if(Auth::check())
            <span class="badge badge-primary badge-inline badge-pill">{{ count(Auth::user()->wishlists)}}</span>
        @else
            <span class="badge badge-primary badge-inline badge-pill">0</span>
        @endif
        <span class="nav-box-text d-none  opacity-70">{{translate('Wishlist')}}</span>
    </span> --}}
</a>
