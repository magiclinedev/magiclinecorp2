@section('title')
Collection
@endsection

<x-app-layout>
    {{-- Magnifier Box CSS --}}
    <style>
        .img-magnifier-container {
          position:relative;
        }

        .img-magnifier-glass {
          position: absolute;
          border: 0px solid #000;
          border-radius: 50%;
          background-color: #FBFBFD;

          cursor: none;
          /*Set the size of the magnifier glass:*/
          width: 250px;
          height: 250px;
        }
    </style>
    <script src="{{ asset('js/magnifier.js') }}"></script>

    {{-- FOR IMAGES --}}
    @php
        // Split the image paths string into an array
        $imagePaths = explode(',', $mannequin->images);
        $imageCacheKey = 'image_' . $mannequin->id;
        $imageUrl = Cache::remember($imageCacheKey, now()->addHours(1), function () use ($imagePaths) {
            foreach ($imagePaths as $imagePath) {
                if (!Storage::disk('dropbox')->exists($imagePath)) {
                    return null; // If any image doesn't exist, return null
                }
            }

            // If all images exist, return the URL of the first image
            return Storage::disk('dropbox')->url($imagePaths[0]);
        });

        // Split the image paths string into an array
        $imagePaths = explode(',', $mannequin->images);
        $imageCacheKey = 'images_' . $mannequin->id;
        $imageUrls = Cache::remember($imageCacheKey, now()->addHours(1), function () use ($imagePaths) {
            $imageUrls = [];

            foreach ($imagePaths as $imagePath) {
                if (Storage::disk('dropbox')->exists($imagePath)) {
                    $imageUrls[] = Storage::disk('dropbox')->url($imagePath);
                }
            }

            return $imageUrls;
        });
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('View Product') }}
        </h2>
        <div class="breadcrumbs mt-4 mb-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-gray-500">
                    @can('users', Auth::user())
                    <li>
                        <a href="javascript:history.go(-1);" class="hover:text-gray-700">Go Back</a>
                    </li>
                    @endcan
                    @can('owner', Auth::user())
                    <li>
                        <a href="{{ route('dashboard') }}" class="hover:text-gray-700">Dashboard</a>
                    </li>
                    @endcan
                    <li class="px-2">
                        <i class="fa fa-caret-right"></i>
                    </li>
                    <li class="font-semibold">
                        <span class="whitespace-nowrap">Product View</span>
                    </li>
                </ol>
            </nav>
        </div>
    </x-slot>

    {{-- START CONTENT --}}
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white shadow-md rounded-lg flex flex-col md:flex-row px-8 py-6">
            {{-- Display Images --}}
            <div class="w-full md:w-1/2 mb-4 md:mb-0 flex flex-col justify-center items-center border">
                {{-- MAIN Image --}}
                @if ($imageUrl)
                    <div class="w-10/12 md:w-8/12 img-magnifier-container relative">
                        <img id="mainImage" src="{{ $imageUrl }}" alt="Product Image" class="main-image w-full h-auto object-cover" loading="lazy">
                    </div>
                @else
                    <p>Image not found</p>
                @endif
                {{-- SELECT IMAGE --}}
                @if ($imageUrls)
                <div class="flex mt-1 space-x-1 overflow-hidden">
                    @foreach ($imageUrls as $index => $imagePath)
                        <div class="w-1/5 zoomable-image border" data-image-index="{{ $index }}">
                            <img src="{{ $imagePath }}" alt="Product Image" class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
                @else
                    <p>Image not found</p>
                @endif
            </div>
            {{-- DETAILS --}}
            <div class="md:w-1/2">
                <div class="p-4 leading-normal">
                    <div class="flex">
                        <div class="w-1/3 font-bold text-gray-700">Purchase Order:</div>
                        <div class="w-2/3">{{ $mannequin->po }}</div>
                    </div>
                    <div class="flex my-2">
                        <div class="w-1/3 font-bold text-gray-700">Item Reference:</div>
                        <div class="w-2/3">{{ $mannequin->itemref }}</div>
                    </div>
                    <div class="flex my-2">
                        <div class="w-1/3 font-bold text-gray-700">Company:</div>
                        <div class="w-2/3">{{ $mannequin->company }}</div>
                    </div>
                    <div class="flex my-2">
                        <div class="w-1/3 font-bold text-gray-700">Category:</div>
                        <div class="w-2/3">{{ $mannequin->category }}</div>
                    </div>
                    <div class="flex my-2">
                        <div class="w-1/3 font-bold text-gray-700">Type:</div>
                        <div class="w-2/3">{{ $mannequin->type }}</div>
                    </div>
                    {{-- PRICE --}}
                    @if ($canViewPrice)
                        <div class="flex my-2">
                            <div class="w-1/3 font-bold text-gray-700">Price:</div>
                            <div class="w-2/3">
                                    {{ $mannequin->price }}
                            </div>
                        </div>
                    @endif
                    {{-- DESCRIPTION --}}
                    <div class="mb-4">
                        <div class="w-1/3 font-bold text-gray-700">Description:</div>
                        <div class="max-h-full overflow-y-auto">{!! $mannequin->description !!}</div>
                    </div>
                    {{-- UPLOADS --}}
                    <div class="flex">
                        {{-- PDF --}}
                        {{-- <div class="w-1/3 font-bold text-gray-700">PDF:</div> --}}
                        <div class="w-2/3">
                            <a href="{{route('company.pdf', ['id' => Crypt::encrypt($mannequin->id)])}}" target="_blank">
                                <button class="bg-red-500 hover-bg-red-600 text-white px-3 py-1 rounded transition-all">
                                    Download PDF <i class="fa fa-download ml-2"></i>
                                </button>
                            </a>
                        </div>
                        {{-- COSTING/EXCEL FILES --}}
                        <div class="w-2/3">
                            @if ($fileUrls)
                                <a href="{{ $fileUrls }}" target="_blank">
                                    <button class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded transition-all">
                                        Download Costing <i class="fa fa-download ml-2"></i>
                                    </button>
                                </a>
                            @endif
                        </div>
                        {{-- 3D FIle --}}
                        <div class="w-2/3">
                            @if ($threeDUrls)
                                <a href="{{ $threeDUrls }}" target="_blank">
                                    <button class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded transition-all">
                                        Download 3D <i class="fa fa-download ml-2"></i>
                                    </button>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- END CONTENT --}}

    {{-- Scripts --}}
    {{-- Image Magnifier --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        //magnifier should disappear when outside border
        $(document).ready(function() {
            const imageThumbnails = $('.zoomable-image');
            const mainImage = $('#mainImage');
            let magnifierActive = false; // Keep track of magnifier statee

            imageThumbnails.on('click', function() {
                const imageIndex = $(this).data('image-index');
                const imagePath = $(this).find('img').attr('src');
                mainImage.attr('src', imagePath);

                // Disable the magnifier when switching to a new image
                disableMagnifier();
            });

            function disableMagnifier() {
                magnifierActive = false;
                const glass = $('.img-magnifier-glass'); // Store the magnifier glass element
                glass.hide();
            }

            // Add click handler for the main image to toggle the magnifier
            mainImage.on('click', function() {
                if (magnifierActive) {
                    disableMagnifier();
                } else {
                    magnifierActive = true;
                    setupMagnifier("mainImage", 2);
                }
            });

            // Add mouseleave event on the container of the main image to hide the magnifier
            mainImage.parent().on('mouseleave', function() {
                if (magnifierActive) {
                    disableMagnifier();
                }
            });
        });
    </script>
</x-app-layout>

