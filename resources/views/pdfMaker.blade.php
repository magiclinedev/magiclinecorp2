<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>PDF</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
</head>
<style>
        @page {
        size: A4 landscape;
    }
    body {

        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
    }

    .container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background-color: #f9f9f9;
    }

    .two-column {

    }

    .column {
        flex: 1;
    }

    .product-image {
        width: 90%;
        flex-wrap: wrap;
    }

    .additional-images {
        display: flex;
        flex-wrap: wrap;
    }

    .additional-image {
        width: 37%;

    }

    .logo-images {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 10px;

    }

    .logo-image {
        max-width: 100px;
        max-height: 100px;
        width: 40%;

    }

    .info-container {
        flex: 1;
    }

    .item-reference {
        font-weight: bold;
        text-align: center;
        margin: 10px 0;
        font-size: 24px;
    }

    .company-label,
    .category-label,
    .type-label,
    .description-label {
        font-weight: bold;
    }

    .company-name,
    .category-name,
    .type-name,
    .description {
        margin-left: 10px;
    }
</style>
<body>
    {{-- FOR IMAGES --}}
    @php
    //    // Extract image paths and cache key
    //     $imagePaths = explode(',', $mannequin->images);
    //     $imageCacheKey = 'images_' . $mannequin->id;

    //     // Fetch image URLs from Dropbox disk and cache them
    //     $imageUrls = Cache::remember($imageCacheKey, now()->addHours(1), function () use ($imagePaths) {
    //         return array_filter(array_map(function ($imagePath) {
    //             return Storage::disk('dropbox')->exists($imagePath)
    //                 ? Storage::disk('dropbox')->url($imagePath)
    //                 : null;
    //         }, $imagePaths));
    //     });

        // Fetch the logo URL from Dropbox disk
        $imageLogo = Storage::disk('dropbox')->url($companyLogo);
    @endphp

    <table>
        <th>
            <tr>
                <td>
                    <img id="mainImage" src="{{ $imageLogo }}" alt="Product Image" class="logo-image">
                </td>
                <td></td>
                <td>
                    <div class="item-reference">{{ $mannequin->itemref }}</div>
                </td>
            </tr>
        </th>
        <tr class="border">
            <!-- Column 1 -->
            <td style="width: 40%; border-right: none;">
                <center>
                    <img id="mainImage" src="{{ $imageUrls[0] }}" alt="Product Image" class="product-image">

                    {{-- <img src="{{ asset('storage/' .  $companyLogo)}}" alt="Company logo" width="100"> --}}
                </center>
            </td>
            <td style="width: 30%;">
                <div class="additional-images">
                    @foreach ($imageUrls as $index => $imagePath)
                        @if ($index > 0)
                            <img id="mainImage" src="{{ $imagePath }}" class="additional-image" loading="lazy">
                        @endif
                    @endforeach
                </div>
            </td>

            <!-- Column 2 -->
            <td class="border two-column" style="width: 30%; margin-top: 0; text-align: left;">
                <div class="category-label">Category:</div>
                <div class="category-name">{{ $mannequin->category }}</div>

                <div class="description-label">Description:</div>
                <div class="description">{!! $mannequin->description !!}</div>
            </td>
        </tr>
    </table>


{{-- <div class="container">
    <div class="product-card">
        <div class="item-reference">{{ $mannequin->itemref }}</div>

        <center>
            <img id="mainImage" src="{{ $imageUrl }}" alt="Product Image" class="product-image">

            <div class="additional-images">
                @foreach ($imageUrls as $index => $imagePath)
                    @if ($index > 0)
                        <img id="mainImage" src="{{ $imagePath }}" class="additional-image" loading="lazy">
                    @endif
                @endforeach
            </div>
        </center>

        <div>
            <div class="company-label">Company:</div>
            <div class="company-name">{{ $mannequin->company }}</div>

            <div class="category-label">Category:</div>
            <div class="category-name">{{ $mannequin->category }}</div>

            <div class="type-label">Type:</div>
            <div class="type-name">{{ $mannequin->type }}</div>

            <div class="description-label">Description:</div>
            <div class="description">{!! $mannequin->description !!}</div>
        </div>
    </div>
    <script>
        // Initialize lazy loading for images within the description
        lazySizes.init();
    </script>
 </div> --}}
 {{-- <div class="container mx-auto">
    <div class="grid grid-cols-2 gap-8">
        <!-- Column 1 -->
        <div>
            <img id="mainImage" src="{{ $imageUrl }}" alt="Product Image" class="product-image">
                <div class="grid grid-cols-2 gap-2">
                @foreach ($imageUrls as $index => $imagePath)
                    <div>
                        <img id="mainImage" src="{{ $imagePath }}" alt="Product Image" class="w-40 h-40" loading="lazy">
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Column 2 -->
        <div>
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
            <div class="w-1/3 font-bold text-gray-700">Description:</div>
            <div class="max-h-full overflow-y-auto">{!! $mannequin->description !!}</div>
        </div>
    </div>
</div> --}}

{{--
<div class="container mx-auto">
    <div class="grid grid-flow-col grid-rows-2 grid-cols-4 gap-8 border">

        <div class="border p-2">
            <div class="col-span-4 row-span-1 border">
                {{ $mannequin->itemref }}
            </div>

            <img id="mainImage" src="{{ $imageUrl }}" alt="Product Image" class="object-cover w-65 h-60" loading="lazy">

            <div class="border col-span-4 row-span-1">
                <div class="grid grid-flow-col grid-rows-1 grid-cols-4 gap-4">
                    <div class="p-2">
                        @foreach ($imageUrls as $index => $imagePath)
                            <img id="mainImage" src="{{ $imagePath }}" class="w-20 h-20" loading="lazy">
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="border col-span-4">
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
                <div class="w-1/3 font-bold text-gray-700">Description:</div>
                <div class="max-h-full overflow-y-auto">{!! $mannequin->description !!}</div>
            </div>
        </div>
    </div>
</div> --}}


    {{-- <div class="container mx-auto">
        <div class="grid grid-flow-col grid-rows-2 grid-cols-4 gap-8">

            <div class="border">
            <img id="mainImage" src="{{ $imageUrl }}" alt="Product Image" class="object-cover w-70 h-70" loading="lazy">
            </div>

            <div class="col-start-2">
                <div class="grid grid-flow-col grid-rows-3 grid-cols-2 gap-2">
                    @foreach ($imageUrls as $index => $imagePath)
                        <div class="">
                            <img id="mainImage" src="{{ $imagePath }}" alt="Product Image" class=" w-40 h-40" loading="lazy">
                        </div>
                    @endforeach
                </div>
            </div>


            <div class="border col-start-3">
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
                <div class="w-1/3 font-bold text-gray-700">Description:</div>
                <div class="max-h-full overflow-y-auto">{!! $mannequin->description !!}</div>
            </div>

            <div class="col-span-4 row-span-1 border">
                {{ $mannequin->itemref }}
            </div>
        </div>
    </div> --}}
</body>
</html>
