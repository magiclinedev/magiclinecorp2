@section('title')
    Collection
@endsection

<x-app-layout>
    @php
        $imagePaths = explode(',', $mannequin->images);
        $imageUrls = [];
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Product') }}
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
    <style>
        .switch {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 24px;
        }

        .switch input {
        opacity: 0;
        width: 0;
        height: 0;
        }

        .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
        }

        .slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
        }

        input:checked + .slider {
        background-color: #2196F3;
        }

        input:focus + .slider {
        box-shadow: 0 0 1px #2196F3;
        }

        input:checked + .slider:before {
        -webkit-transform: translateX(16px);
        -ms-transform: translateX(16px);
        transform: translateX(16px);
        }

        /* Rounded sliders */
        .slider.round {
        border-radius: 34px;
        }

        .slider.round:before {
        border-radius: 50%;
        }
    </style>
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white shadow-md rounded-lg flex flex-col md:flex-row px-8 py-6">
            {{-- IMAGES --}}
            <div class="grid grid-cols-3 gap-4">
                @if ($imagePaths)
                    @foreach ($imagePaths as $imagePath)
                        @if (Storage::disk('dropbox')->exists($imagePath))
                            @php
                                $imageUrls[] = Storage::disk('dropbox')->url($imagePath);
                            @endphp
                            <div class="w-full relative">
                                <img src="{{ Storage::disk('dropbox')->url($imagePath) }}" alt="Photo" class="max-w-full h-auto" loading="lazy">
                                <input type="hidden" name="images[]" value="{{ $imagePath }}">
                                <button class="text-red-500 delete-image absolute top-0 right-0" data-image="{{ $imagePath }}">X</button>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>


            {{-- START FORM --}}
            <div class="md:w-1/2">
                <div class="p-4 leading-normal">
                    <form method="POST" action="{{ route('collection.update', ['id' => $mannequin->id]) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="deleted_images" value="[]">
                        {{-- Purchase Order --}}
                        <div class="flex">
                            <div class="w-1/3 font-bold font-bold">Purchase Order:</div>
                            <div class="w-2/3">
                                <input type="text" name="po" value="{{ $mannequin->po }}" class="border rounded p-1 w-full">
                            </div>
                        </div>
                        {{-- Item Reference --}}
                        <div class="flex">
                            <div class="w-1/3 font-bold font-bold">Item Reference:</div>
                            <div class="w-2/3">
                                <input type="text" name="itemref" value="{{ $mannequin->itemref }}" class="border rounded p-1 w-full">
                            </div>
                        </div>

                        {{-- Company --}}
                        <div class="col-span-2 sm:col-span-1">
                            <label for="company" class="block font-bold mb-2">Company:</label>
                            <div class="col-span-2 sm:col-span-1 flex items-center">
                                <select name="company" id="company" class="w-full border rounded-md py-2 px-3">
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->name }}" {{ $company->name == $mannequin->company ? 'selected' : '' }}>{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- CATEGORY --}}
                        <div class="col-span-2 sm:col-span-1">
                            <label for="category" class="block font-bold mb-2">Category:</label>
                            <div class="col-span-2 sm:col-span-1 flex items-center">
                                <select name="category" id="category" class="w-full border rounded-md py-2 px-3">
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->name }}" {{ $category->name == $mannequin->category ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- TYPE --}}
                        <div class="col-span-2 sm:col-span-1">
                            <label for="type" class="block font-bold mb-2">Type:</label>
                            <div class="col-span-2 sm:col-span-1 flex items-center">
                                <select name="type" id="type" class="w-full border rounded-md py-2 px-3">
                                    @foreach ($types as $type)
                                        <option value="{{ $type->name }}" {{ $type->name == $mannequin->type ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- PRICE --}}
                        @if ($canViewPrice)
                        <div class="col-span-2 sm:col-span-1">
                            <div class="w-1/3 font-bold font-bold">Price:</div>
                            <div class="col-span-2 sm:col-span-1 flex items-center">
                                <input type="text" name="price" value="{{ $mannequin->price }}" class="w-full border rounded-md py-2 px-3">
                            </div>
                        </div>
                        @endif

                        {{-- listed items wont save dots, number, or etc. --}}
                        {{-- DESCRIPTION --}}
                        <div class="col-span-2">
                            <label for="description" class="block font-bold mb-2">Description</label>
                            <div class="relative w-full border rounded-md py-2 px-3">
                                <div id="quill-editor" class="editor-style">{!! $mannequin->description !!}</div>
                            </div>
                            <textarea name="description" id="description" class="hidden">{!! $mannequin->description !!}</textarea>
                        </div>

                        {{-- UPLOAD FILES --}}
                        {{-- IMAGES --}}
                        <div class="w-full">
                            <label class="block font-bold mb-2">Images</label>
                            <div class="mt-2 items-center">
                                <input type="file" name="images[]" class="border rounded-lg p-2" multiple>
                            </div>
                        </div>

                        {{-- File --}}
                        <div class="mt-4">
                            <label class="block font-bold mb-2">Costing</label>
                            <div class="mt-2 flex items-center space-x-4">
                                <input type="file" name="file" class="border rounded-lg p-2">
                                @if ($mannequin->file)
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-500">Current File:</span>
                                        <span class="text-sm text-blue-600">{{ $mannequin->file }}</span>
                                    </div>
                                @else
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-500">Current File:</span>
                                        <span class="text-sm text-blue-600">No Current file</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- PDF --}}
                        <div class="mt-4">
                            <label class="block font-bold mb-2">PDF</label>
                            <i class="text-sm text-gray-600">Auto Generate PDF?</i>
                            <label class="switch">
                                <input type="checkbox" id="autoGeneratePDF" name="autoGeneratePDF" @if($mannequin->pdf == 'Auto') checked @endif>
                                <span class="slider round"></span>
                            </label>
                            <div class="mt-2 flex items-center space-x-4">
                                <input type="file" name="pdf" class="border rounded-lg p-2" id="pdfInput">
                                @if ($mannequin->pdf)
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-500">Current PDF:</span>
                                        <span class="text-sm text-blue-600">{{ $mannequin->pdf }}</span>
                                    </div>
                                @else
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-500">Current PDF:</span>
                                        <span class="text-sm text-blue-600">No PDF</span>
                                    </div>
                                @endif
                            </div>
                        </div>


                        {{-- REquest IMAGES --}}
                        <div class="mt-4">

                            {{-- Thumbnails --}}
                            <label class="block font-bold mb-2">Thumbnail:</label>
                            <div class="mt-2 items-center">
                                <input type="file" name="reqImg[]" class="border rounded-lg p-2" multiple>
                            </div>
                            @if ($mannequin->reqImg)
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-500">Current Thumbnail:</span>
                                    <span class="text-sm text-blue-600">{{ $mannequin->reqImg }}</span>
                                </div>
                            @else
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-500">Current Thumbnail:</span>
                                    <span class="text-sm text-blue-600">No Thumbnail</span>
                                </div>
                            @endif
                        </div>
                        <button type="submit" class="mt-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Save
                        </button>
                    </form>
                </div>
            </div>
            {{-- END form --}}
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{-- Sweet Alert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        $('form').submit(function(event) {
            event.preventDefault(); // Prevent the default form submission

            var form = this;

            // Get the current value of the description textarea
            var descriptionValue = $('#description').val();

            Swal.fire({
                title: 'Confirm Edit',
                text: 'Are you sure you want to save the changes?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, save changes!',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Restore the original description value before submitting the form
                    $('#description').val(descriptionValue);

                    // Proceed with the form submission
                    form.submit();
                }
            });
        });

        // Update Validation fail
        @if(session('danger_message'))
            Swal.fire({
            title: 'Error!',
            html: `{!! implode('<br>', $errors->all()) !!}`,
            icon: 'error',
            timer: 6000,
            showCancelButton: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
        });
        @elseif(session('success_message'))
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

        // Sync the content of Quill editor with the hidden textarea
        quill.on('text-change', function() {
            var editorContent = document.querySelector('#quill-editor .ql-editor').innerHTML;
            document.querySelector('#description').value = editorContent;
        });
    </script>

    {{-- DELETE IMAGE --}}
    <script>
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-image')) {
                const imageContainer = e.target.parentElement;
                const imagePath = imageContainer.querySelector('input[name="images[]"]').value;

                if (imagePath) {
                    // Remove the image container from the DOM
                    imageContainer.remove();
                    // Track the deleted image
                    const deletedImagesInput = document.querySelector('input[name="deleted_images"]');
                    let deletedImages = deletedImagesInput.value;
                    if (deletedImages === "[]") {
                        deletedImages = imagePath;
                    } else {
                        deletedImages += ',' + imagePath;
                    }
                    deletedImagesInput.value = deletedImages;
                }
            }
        });
    </script>

    {{-- remove input file for pdf if switch button checked --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Get references to the checkbox and input elements
            var checkbox = document.getElementById('autoGeneratePDF');
            var input = document.getElementById('pdfInput');
            var container = document.getElementById('pdfContainer');

            // Add event listener to the checkbox
            checkbox.addEventListener('change', function () {
                // Toggle the visibility of the input based on the checkbox state
                if (checkbox.checked) {
                    input.style.display = 'none';
                } else {
                    input.style.display = 'block';
                }
            });

            // Initial check to hide/show input based on the checkbox state
            if (checkbox.checked) {
                input.style.display = 'none';
            } else {
                input.style.display = 'block';
            }
        });
    </script>
</x-app-layout>
