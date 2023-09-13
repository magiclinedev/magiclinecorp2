@section('title')
    Collection
@endsection

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Product') }}
        </h2>
        <div class="breadcrumbs mt-4 mb-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-gray-500">
                    <li>
                        <a href="{{ route('collection') }}" class="hover:text-gray-700">Collection</a>
                    </li>
                    <li class="px-2">
                        <i class="fa fa-caret-right"></i>
                    </li>
                    <li class="font-semibold">
                        <span class="whitespace-nowrap">Product Add</span>
                    </li>
                </ol>
            </nav>
        </div>
    </x-slot>

    {{-- IMG VIEW CONTAINER --}}
    <style>
        .image-container {
            display: inline-block;
            position: relative;
            margin: 5px;
        }

        .remove-image {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: rgba(255, 255, 255, 0.8);
            cursor: pointer;
            padding: 4px;
            border-radius: 50%;
            color: #FF0000;
            font-weight: bold;
            font-size: 16px;
            line-height: 1;
        }

        .image-preview {
            max-width: 100px;
            max-height: 100px;
            border: 1px solid #ccc;
            border-radius: 5px;
            overflow: hidden;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>

    <div class="container mx-auto px-4 py-8">
        <form action="{{ route('collection.store') }}" method="POST"  enctype="multipart/form-data" class="bg-white shadow-md rounded-lg px-8 py-6">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-4">

                {{-- Purchase order --}}
                <div class="col-span-2 sm:col-span-1">
                    <label for="po" class="block font-bold mb-2">PO</label>
                    <input type="text" name="po" value="{{ old('po') }}" id="po" class="w-full border rounded-md py-2 px-3" placeholder="Enter PO number">
                </div>
                {{-- ITEM REF--}}
                <div class="col-span-2 sm:col-span-1">
                    <label for="itemRef" class="block font-bold mb-2">Item Reference</label>
                    <input type="text" name="itemRef" id="itemRef" value="{{ old('itemRef') }}" class="w-full border rounded-md py-2 px-3" placeholder="Enter Item Ref">
                </div>
                {{-- COMPANIES --}}
                <div class="col-span-2 sm:col-span-1">
                    <label for="company" class="block font-bold mb-2">Company</label>
                    <div class="col-span-2 sm:col-span-1 flex items-center">
                        <select name="company" id="company" class="w-full border rounded-md py-2 px-3">
                            @foreach ($companies as $company)
                                <option value="{{ $company->name }}" {{ old('company') == $company->name ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                {{-- CATEGORY --}}
                <div class="col-span-2 sm:col-span-1">
                    <label for="category" class="block font-bold mb-2">Category</label>
                    <div class="col-span-2 sm:col-span-1 flex items-center">
                        <select name="category" id="category" class="w-full border rounded-md py-2 px-3">
                            @foreach ($categories as $category)
                                <option value="{{ $category->name }}" {{ old('category') == $category->name ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                {{-- TYPE --}}
                <div class="col-span-2 sm:col-span-1">
                    <label for="type" class="block font-bold mb-2">Type</label>
                    <div class="col-span-2 sm:col-span-1 flex items-center">
                        <select name="type" id="type" class="w-full border rounded-md py-2 px-3">
                            @foreach ($types as $type)
                                <option value="{{ $type->name }}" {{ old('type') == $type->name ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                {{-- PRICE --}}
                <div class="col-span-2 sm:col-span-1">
                    <label for="price" class="block font-bold mb-2">Price</label>
                    <input type="number" name="price" value="{{ old('price') }}" id="price" class="w-full border rounded-md py-2 px-3" placeholder="Enter Price">
                </div>
                {{-- DESCRIPTION --}}
                <div class="col-span-2">
                    <label for="description" class="block font-bold mb-2">Description</label>
                    <div class="relative w-full border rounded-md py-2 px-3">
                        <div id="quill-editor" class="editor-style"></div>
                    </div>
                    <textarea name="description" id="description" class="hidden"></textarea>
                </div>

                {{-- UPLOAD FILES --}}
                {{-- images --}}
                <div class="col-span-2">
                    <label for="images" class="block font-bold mb-2">Images <i class="text-sm text-gray-600">(Maximum upload size 2MB per image)</i></label>
                    <input type="file" name="images[]" id="images" class="w-full border rounded-md py-2 px-3" multiple>
                    <div id="image-preview" class="mt-3">
                        {{-- Placeholder for image preview --}}
                    </div>
                </div>
                {{-- file --}}
                <div class="col-span-2">
                    <label for="file" class="block font-bold mb-2">File <i class="text-sm text-gray-600">(Maximum upload size 2MB)</i></label>
                    <input type="file" name="file" id="file" class="w-full border rounded-md py-2 px-3">
                </div>
                {{-- PDF --}}
                <div class="col-span-2">
                    <label for="pdf" class="block font-bold mb-2">PDF <i class="text-sm text-gray-600">(Maximum upload size 2MB)</i></label>
                    <input type="file" name="pdf" id="pdf" class="w-full border rounded-md py-2 px-3">
                </div>

            </div>
            <button type="submit" class="mt-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Add Product
            </button>
        </form>
    </div>
    {{-- SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        @if(session('success_message'))
            Swal.fire({
                title: 'Done!',
                text: '{{ session('success_message') }}',
                icon: 'success',
                timer: 3000,
                showCancelButton: false,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Close'
            });
            @elseif(session('danger_message'))
                Swal.fire({
                    title: 'Error!',
                    html: `{!! implode('<br>', $errors->all()) !!}`,
                    icon: 'error',
                    timer: 6000,
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                });
            @endif

    </script>

    {{-- Description Quill --}}
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        var quill = new Quill('#quill-editor', {
            theme: 'snow', // Snow is a prebuilt theme with a clean interface
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'], // Include the formatting options you want
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['link', 'image', 'video'],
                    ['clean']
                ]
            },
            placeholder: 'Enter Description'
        });

        // Retrieve the old input data for the description field
        var oldDescription = {!! json_encode(old('description')) !!};

        // Set the content of the Quill editor with old input data if it exists
        if (oldDescription) {
            quill.clipboard.dangerouslyPasteHTML(oldDescription);
        }

        // Sync the content of Quill editor with the hidden textarea
        quill.on('text-change', function() {
            var editorContent = document.querySelector('#quill-editor .ql-editor').innerHTML;
            document.querySelector('#description').value = editorContent;
        });
    </script>

    {{-- image holder --}}
    <script>
        document.getElementById("images").addEventListener("change", function (event) {
            const imagePreview = document.getElementById("image-preview");
            imagePreview.innerHTML = ""; // Clear previous preview

            const files = event.target.files;
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const reader = new FileReader();

                reader.onload = function (e) {
                    const imageContainer = document.createElement("div");
                    imageContainer.className = "image-container";

                    const imagePreviewDiv = document.createElement("div");
                    imagePreviewDiv.className = "image-preview";

                    const image = document.createElement("img");
                    image.src = e.target.result;
                    image.alt = "Preview";

                    const removeButton = document.createElement("span");
                    removeButton.className = "remove-image";
                    removeButton.innerHTML = "&times;";
                    removeButton.addEventListener("click", function () {
                        imageContainer.remove();
                    });

                    imagePreviewDiv.appendChild(image);
                    imageContainer.appendChild(imagePreviewDiv);
                    imageContainer.appendChild(removeButton);
                    imagePreview.appendChild(imageContainer);
                };

                reader.readAsDataURL(file);
            }
        });
    </script>
</x-app-layout>
