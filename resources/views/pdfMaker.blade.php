<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
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
    <div class="container mx-auto">
        <div class="grid grid-flow-col grid-rows-2 grid-cols-4 gap-8 border">

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
    </div>

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
