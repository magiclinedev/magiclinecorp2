@section('title')
    Collection
@endsection

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Product') }}
            </h2>
            {{-- Admin Buttons(Add Products, Type, Category) --}}
            <div class="flex items-center space-x-2">
                {{-- Access for buttons --}}
                @can('admin_access', Auth::user())
                    <a href="{{ route('collection.add') }}" class="text-gray-800 hover:text-gray-600">
                        <i class="fas fa-plus-circle"></i> Add Product
                    </a>
                    <a href="{{ route('collection.category') }}" class="text-gray-800 hover:text-gray-600">
                        <i class="fas fa-folder-plus"></i> Add Category
                    </a>
                    <a href="{{ route('collection.type') }}" class="text-gray-800 hover:text-gray-600">
                        <i class="fas fa-tags"></i> Add Type
                    </a>
                @endcan

                {{-- Trashcan Button --}}
                @can('super_admin', Auth::user())
                    @if ($mannequins->contains('activeStatus', 0))
                        <div class="ml-2">
                            <a href="{{ route('collection.trashcan') }}" class="text-gray-800 hover:text-gray-600">
                                <i class="fas fa-trash-alt"></i> Trash
                                <span class="badge">{{ $mannequins->where('activeStatus', 0)->count() }}</span>
                            </a>
                        </div>
                    @endif
                @endcan

            </div>
        </div>
    </x-slot>
    {{-- START content --}}
    <div class="container mx-auto">
        {{-- START main --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-4">
            <div class="p-6 text-gray-900">
                <div class="flex items-center mb-2">
                    <h1 class="text-2xl font-bold"><i class="fas fa-list-alt"></i> Product List</h1>
                </div>

                {{-- Add this line to include the CSRF token for DataTables AJAX requests --}}
                {{-- <meta name="csrf-token" content="{{ csrf_token() }}"> --}}
                <div class="overflow-x-auto">

                    {{-- FILTER --}}
                    <div class="flex space-x-4 my-4">
                        {{-- category --}}
                        <div class="filter-dropdown">
                            <select id="categoryFilter" class="block w-52 py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Filter by Category">
                                <option value="">Categories </option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->name }}"> {{ $category->name }} </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- company --}}
                        <div class="filter-dropdown">
                            <select id="companyFilter" class="block w-52 py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Filter by Company">
                                <option value="">All Companies</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->name }}" @if ($companyName == $company->name) selected @endif>{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Searchbox --}}
                        <div class="w-full">
                            <input id="customSearchInput" type="text" class="w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Search...">
                        </div>
                    </div>
                    {{-- BUTTON FOR DELETING SELECTED CHECKBOXES --}}
                    {{-- <div class="filter-dropdown">
                        <select id="bulkAction" class="hidden block w-52 mb-2 py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Filter by Company">
                            <option value="">Bulk Action</option>
                                <option>Delete Selected Item/s</option>
                        </select>
                    </div> --}}
                    <button id="bulkAction" class="hidden bg-red-500 block w-52 py-2 px-3 mb-2 border border-gray-300 text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        Delete All
                    </button>

                    {{-- TABLE --}}
                    <table id="mannequinsTable" class="w-full table-auto border-collapse border">
                        <thead class="px-6 py-4 font-medium whitespace-nowrap text-white bg-gray-800 rounded-md">
                            <tr>
                                @can('super_admin', Auth::user())
                                <th class="px-4 py-2 border">
                                    <input type="checkbox" id="selectAllCheckbox">
                                </th>
                                @endcan
                                <th class="px-4 py-2 border">Image</th>
                                <th class="px-4 py-2 border">Item Reference</th>
                                <th class="px-4 py-2 border">Company</th>
                                <th class="px-4 py-2 border">Category</th>
                                <th class="px-4 py-2 border">Type</th>
                                <th class="px-4 py-2 border">Action By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mannequins as $mannequin)
                                @if ($mannequin->activeStatus != 0)
                                    <tr data-item-id="{{ $mannequin->id }}" class="border">
                                        {{-- Checkbox --}}
                                        @can('super_admin', Auth::user())
                                        <td class="px-7 py-2 border">
                                            <!-- Add the checkbox input here -->
                                            <input type="checkbox" class="row-checkbox center pb-4" data-item-id="{{ $mannequin->id }}" >
                                        </td>
                                        @endcan
                                        {{-- Images --}}
                                        @php
                                            // Cache the image URL for a limited time (e.g., 1 hour)
                                            $imageCacheKey = 'image_' . $mannequin->id;
                                            $imageUrl = Cache::remember($imageCacheKey, now()->addHours(1), function () use ($mannequin) {
                                                // Split the image paths string into an array
                                                $imagePaths = explode(',', $mannequin->images);
                                                // Get the first image path from the array
                                                $firstImagePath = $imagePaths[0] ?? null;

                                                if (Storage::disk('dropbox')->exists($firstImagePath)) {
                                                    return Storage::disk('dropbox')->url($firstImagePath);
                                                } else {
                                                    return null;
                                                }
                                            });
                                        @endphp

                                        <td class="px-7 py-2 border">
                                            @if ($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="Mannequin Image" class="w-16 h-16 object-contain" loading="lazy">

                                            @else
                                                <p>Image not found</p>
                                            @endif
                                        </td>

                                        <td class="px-7 py-2 border itemref-cell">
                                            {{-- ITEM REF --}}
                                            <span class="itemref-text">{{ $mannequin->itemref }}</span>

                                            {{-- HOVER to show read, update, and delete --}}
                                            <div class="action-buttons">

                                                <a href="{{ route('collection.view_prod', ['encryptedId' => Crypt::encrypt($mannequin->id)]) }}" class="btn-view">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                {{-- Admin --}}
                                                @can('admin_access', Auth::user())
                                                <a href="{{ route('collection.edit', ['id' => $mannequin->id]) }}" class="btn-view">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                @endcan
                                                @can('super_admin',Auth::user())
                                                <button class="btn-delete" data-id="{{ $mannequin->id }}" data-transfer-url="{{ route('collection.trash', ['id' => $mannequin->id]) }}">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </button>
                                                @endcan
                                            </div>
                                        </td>
                                        <td class="px-7 py-2 border">{{ $mannequin->company }}</td>
                                        <td class="px-7 py-2 border">{{ $mannequin->category }}</td>
                                        <td class="px-7 py-2 border">{{ $mannequin->type }}</td>
                                        <td class="px-7 py-2 border">{{ $mannequin->addedBy }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    {{-- END TABLE --}}

                </div>
            </div>
        </div>
        {{-- END main --}}
    </div>
    {{-- END content --}}

    {{--START scripts --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#mannequinsTable').DataTable({
                lengthChange: false,
                "dom": 'lrtip'
            });

            // Handle "select all" checkbox
            $('#selectAllCheckbox').on('change', function() {
                var isChecked = this.checked;
                $('td input.row-checkbox').each(function() {
                    this.checked = isChecked;
                });

                // Show/hide the "Delete All" button based on the checked status
                $('#bulkAction').toggleClass('hidden', !isChecked);
            });

            // Listen for checkbox changes
            $('td input.row-checkbox').on('change', function() {
                var anyChecked = $('td input.row-checkbox:checked').length > 0;
                $('#bulkAction').toggleClass('hidden', !anyChecked);

                // Check/uncheck the "Select All" checkbox based on the checked status
                var allCheckboxesChecked = $('td input.row-checkbox').length == $('td input.row-checkbox:checked').length;
                $('#selectAllCheckbox').prop('checked', allCheckboxesChecked);
            });

            // Handle "Delete All" button click
            $('#bulkAction').on('click', function() {
                console.log('Button clicked');

                var selectedIds = [];
                $('td input.row-checkbox:checked').each(function() {
                    selectedIds.push($(this).data('item-id'));
                });
                console.log('Selected IDs:', selectedIds);

                if (selectedIds.length > 0) {
                    $.ajax({
                        url: '{{ route('collection.trashMultiple') }}',
                        method: 'POST',
                        data: { ids: selectedIds },
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(data, textStatus, jqXHR) {
                            console.log('AJAX Request URL:', this.url);
                            console.log('AJAX Request Method:', this.type);
                            console.log('AJAX Request Headers:', this.headers);
                            console.log('AJAX Request Data:', this.data);

                            console.log('Response URL:', jqXHR.responseURL); // Log the response URL
                            console.log('Response Status:', textStatus); // Log the response status
                            console.log('Response Data:', data); // Log the response data

                            if (data.success) {
                                console.log('Response:', data.data);
                                // Refresh the page or update the table as needed
                                location.reload();
                            }
                        }
                    });
                }
            });

            //Category Filter
            $('#categoryFilter').on('change', function() {
                var category = $(this).val();

                if ({{ Auth::user()->status }} === 1 || {{ Auth::user()->status }} === 4) {
                    // Admin 1 or Owner(4) filter logic
                    table.column(4) // Category column index (0-based)
                        .search(category)
                        .draw();
                } else {
                    // Other users filter logic
                    table.column(3) // Category column index (0-based)
                        .search(category)
                        .draw();
                }
            });

            // Handle company filter change
            $('#companyFilter').on('change', function() {
                var company = $(this).val();

                if ({{ Auth::user()->status }} === 1 || {{ Auth::user()->status }} === 4) {
                    // Admin 1 or Owner(4) filter logic
                    table.column(3) // Company column index (0-based)
                        .search(company)
                        .draw();
                } else {
                    // Other users filter logic
                    table.column(2) // Company column index for non-admin users (0-based)
                        .search(company)
                        .draw();
                }
            });

            // Custom search input handler using input event
            $('#customSearchInput').keyup(function(){
                table.search( $(this).val() ).draw() ;
            });

            // Trigger initial filter changes after DataTable initializes(from dashboard)
            $('#categoryFilter').trigger('change');
            $('#companyFilter').trigger('change');
        });
    </script>

    {{-- sweet alert --}}
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
                text: '{{session('danger_message') }}',
                icon: 'error',
                timer: 3000,
                showCancelButton: false,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
            });
        @endif

        // FOR DELETE
        document.addEventListener('DOMContentLoaded', () => {
            const deleteButtons = document.querySelectorAll('.btn-delete');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const recordId = this.getAttribute('data-id');
                    const transferUrl = this.getAttribute('data-transfer-url');

                    console.log('recordId:', recordId);
                    console.log('transferUrl:', transferUrl);

                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'You won\'t be able to revert this!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Perform AJAX request to delete the record
                            axios.post(transferUrl, {
                                ids: [recordId] // Send the array of IDs here
                            })
                            .then(response => {
                                if (response.data.success) {
                                    Swal.fire(
                                        'Deleted!',
                                        'Your record has been deleted.',
                                        'success'
                                    ).then(() => {
                                        // Refresh the page after successful deletion
                                        window.location.reload();
                                    });
                                }
                            })
                            .catch(error => {
                                Swal.fire(
                                    'Error!',
                                    'An error occurred while deleting the record.',
                                    'error'
                                );
                            });
                        }
                    });
                });
            });
        });
    </script>
    {{--END scripts--}}
</x-app-layout>

