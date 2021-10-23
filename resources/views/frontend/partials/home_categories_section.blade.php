@php $home_categories = json_decode(get_setting('home_categories')); @endphp
@foreach ($home_categories as $key => $value)
    @php $category = \App\Category::find($value); @endphp
    <section class="mb-4">
        <div class="container">
            @if($loop->iteration === 3)
             {{-- Banner section 1 --}}
                @if (get_setting('home_banner2_images') != null)
                <div class="mb-4 mt-4">
                    <div class="container">
                        <div class="row gutters-10">
                            @php $banner_2_imags = json_decode(get_setting('home_banner2_images')); @endphp
                            @foreach ($banner_2_imags as $key => $value)
                                <div class="col-xl col-md-6" style="height: 600px;overflow: hidden;">
                                    <div class="mb-3 mb-lg-0">
                                        <a href="{{ json_decode(get_setting('home_banner2_links'), true)[$key] }}" class="text-reset">
                                            <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($banner_2_imags[$key]) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100" style="height: 100%;object-fit: cover;">
                                        </a>
                                    </div>
                                </div>
                                @break 1;
                            @endforeach
                            <div class="col-xl col-md-6">
                                @foreach ($banner_2_imags as $key => $value)
                                @if ($key > 0 && $key < 3)
                                    <div class="mb-3 mb-lg-0" style="height: 303px;overflow: hidden;padding: 5px;padding-top:0px">
                                        <a href="{{ json_decode(get_setting('home_banner2_links'), true)[$key] }}" class="text-reset">
                                            <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($banner_2_imags[$key]) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100" style="height: 100%;object-fit: cover;">
                                        </a>
                                    </div>
                                @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @elseif($loop->iteration === 5)
             {{-- Banner Section 2 --}}
                @if (get_setting('home_banner3_images') != null)
                <div class="mb-4">
                    <div class="container">
                        <div class="row gutters-10">
                            @php $banner_3_imags = json_decode(get_setting('home_banner3_images')); @endphp
                            @foreach ($banner_3_imags as $key => $value)
                                <div class="col-xl col-md-6">
                                    <div class="mb-3 mb-lg-0">
                                        <a href="{{ json_decode(get_setting('home_banner3_links'), true)[$key] }}" class="d-block text-reset">
                                            <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($banner_3_imags[$key]) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100">
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            @endif
            <div class="px-2 py-4 px-md-4 py-md-3">
                <div class="d-flex mb-3 align-items-baseline border-bottom">
                    <h3 class="h5 fw-700 mb-0">
                        <span class=" pb-3 d-inline-block">{{ $category->getTranslation('name') }}</span>
                    </h3>
                    <a href="{{ route('products.category', $category->slug) }}" class="ml-auto mr-0 btn btn-primary btn-sm shadow-md">{{ translate('View More') }}</a>
                </div>
                <div class="aiz-carousel gutters-10 half-outside-arrow" data-items="6" data-xl-items="5" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true'>
                    @foreach (get_cached_products($category->id) as $key => $product)
                        <div class="carousel-box">
                            @include('frontend.partials.product_box_1',['product' => $product])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endforeach
