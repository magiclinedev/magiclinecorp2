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
        <form action="{{ route('collection.store') }}" method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded-lg px-8 py-6">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-3 gap-4">
                {{-- ITEM REF--}}
                <div class="col-span-3 sm:col-span-2 h-100">
                    <label for="itemRef" class="block font-bold">Item Reference</label>
                    <input type="text" name="itemRef" id="itemRef" value="{{ old('itemRef') }}" class="w-full border rounded-md py-2 px-3 mb-3" placeholder="Enter Item Ref">

                    {{-- Purchase order --}}
                    <label for="po" class="block font-bold">PO</label>
                    <input type="text" name="po" value="{{ old('po') }}" id="po" class="w-full border rounded-md py-2 px-3 mb-3" placeholder="Enter PO number">


                    {{-- COMPANIES --}}
                    <label for="company" class="block font-bold">Company</label>
                    <div class="col-span-2 sm:col-span-1 flex items-center">
                        <select name="company" id="company" class="w-full border rounded-md py-2 px-3 mb-3">
                            @foreach ($companies as $company)
                                <option value="{{ $company->name }}" {{ old('company') == $company->name ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- PRICE --}}
                    <label for="price" class="block font-bold">Price</label>
                    <input type="number" name="price" value="{{ old('price') }}" id="price" class="w-full border rounded-md py-2 px-3 mb-3" placeholder="Enter Price">

                    {{-- TYPE --}}
                    <label for="type" class="block font-bold">Type</label>
                    <div class="col-span-2 sm:col-span-1 flex items-center">
                        <select name="type" id="type" class="w-full border rounded-md py-2 px-3 ">
                            @foreach ($types as $type)
                                <option value="{{ $type->name }}" {{ old('type') == $type->name ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                {{-- CATEGORY --}}
                <div class="col-span-3 sm:col-span-1">
                    <label class="block font-bold">Category</label>
                    <div class=" border border-gray-300 rounded-md p-2 w-auto h-auto overflow-y-auto">
                        @foreach ($categories as $category)
                            <div class="flex flex-wrap">
                                <input type="checkbox" name="categories[]" value="{{ $category->name }}" id="{{ $category->name }}" class="mr-2">
                                <label for="{{ $category->name }}">{{ $category->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <br>
            <div class="grid grid-cols-2 gap-4">
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
                    {{-- <input type="file" name="images[]" id="images" class="w-full border rounded-md py-2 px-3" multiple> --}}
                    <input type="file"
                        name="images[]" id="images"
                        class="filepond"
                        multiple
                        data-max-file-size="2MB"
                        data-max-files="8" >

                    <!--<div id="image-preview" class="mt-3">
                        {{-- Placeholder for image preview --}}
                    </div>-->
                </div>
                {{-- file --}}
                <div class="col-span-2">
                    <label for="file" class="block font-bold mb-2">File <i class="text-sm text-gray-600">(Maximum upload size 2MB)</i></label>
                    <input type="file" name="file" id="file" class="w-full border rounded-md py-2 px-3">
                </div>
                {{-- PDF --}}
                <div class="col-span-2" id="pdfContainer">
                    <label for="pdf" class="block font-bold mb-2">PDF <i class="text-sm text-gray-600">(Maximum upload size 2MB).</i></label>

                    <i class="text-sm text-gray-600">Auto Generate PDF?</i>
                    <label class="switch">
                        <input type="checkbox" id="autoGeneratePDF" name="autoGeneratePDF">
                        <span class="slider round"></span>
                    </label>

                    <input type="file" name="pdf" id="pdfInput" class="w-full border rounded-md py-2 px-3">
                </div>

                {{-- REQUEST IMAGES --}}
                <div class="col-span-2">
                    <label for="reqImg[]" class="block font-bold mb-2">Request Images <i class="text-sm text-gray-600">(Maximum upload size 2MB)</i></label>
                    <input type="file" name="reqImg[]" id="reqImg[]" class="w-full border rounded-md py-2 px-3" multiple>
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
    {{-- IMAGE UPLOAD filepond--}}
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    <script>
        FilePond.registerPlugin(FilePondPluginImagePreview);

        const inputElement = document.querySelector('input[type="file"]');

        // Initialize FilePond with the desired configuration, including the imagePreview plugin
        const pond = FilePond.create(inputElement, {
            allowMultiple: true,
            revert: true,
            allowRemove: true,
            maxFileSize: '2MB',
            server: {
                process: {
                    url: '/dropbox-image', // Point this to your server endpoint for processing the upload
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' // Include CSRF token if needed
                    },
                    // Additional process options if needed
                },
                remove: {
                    url: '/remove-image', // Endpoint for deleting images
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    // Include a function to specify which file to remove
                    onRemove: (source, load, error) => {
                        // Retrieve the file path from the data attribute
                        const filePath = source.getAttribute('data-file-path');

                        // Send a request to your server with the file path
                        fetch('/remove-image', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ file_path: filePath }),
                        })
                            .then(response => response.json())
                            .then(data => {
                                // Handle the response from the server
                                if (data.success) {
                                    load();
                                } else {
                                    error('Error removing file');
                                }
                            })
                            .catch(error => {
                                error('Error removing file');
                            });
                    },
                },
            }
        });
        // Attach the file path to each FilePond file input element
        pond.on('addfile', (error, file) => {
            if (!error) {
                // The file has been added, set the file path data attribute
                file.source.setAttribute('data-file-path', file.serverId);
            }
        });
        // Hook into the addfile event to validate file size
        pond.on('addfile', (error, file) => {
            if (error || file.fileSize > 2 * 1024 * 1024) { // Check if the file size exceeds 2MB
                Swal.fire({
                    title: 'Error!',
                    text: 'File size exceeds the 2MB limit. Please select a smaller file.',
                    icon: 'error',
                    timer: 6000,
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                });
                pond.removeFile(file); // Remove the invalid file from FilePond
            }
            else {
                    // Add a cancel button to the file item
                    const cancelBtn = document.createElement('button');
                    cancelBtn.textContent = 'Cancel Upload';
                    cancelBtn.className = 'filepond--action';
                    cancelBtn.addEventListener('click', () => {
                        pond.removeFile(file);
                    });

                    file.info.appendChild(cancelBtn);
                }
        });
    </script>
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
