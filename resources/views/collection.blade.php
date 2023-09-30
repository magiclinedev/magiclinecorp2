@section('title')
    Collection
@endsection

<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                {{-- FILTER --}}
                <div class="flex space-x-4 my-4">
                    {{-- category --}}
                    <div class="filter-dropdown">
                        <select id="categoryFilter" class="block w-52 py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Filter by Category">
                            <option value="">Categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->name }}">{{ $category->name }}</option>
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
                    <select id="bulk" class=" block w-52 mb-2 py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Filter by Company">
                        <option value="">Bulk Action</option>
                            <option>Delete Selected Item/s</option>
                    </select>
                </div> --}}

                <button name="bulkAction" id="bulkAction" class="hidden bg-red-500 block w-52 py-2 px-3 mb-2 border border-gray-300 text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" onclick="deleteSelectedMannequins()">
                    Delete All</button>

                {{-- TABLE --}}
                <table id="mannequinsTable" class="w-full table-auto border-collapse border">
                    <thead class="px-6 py-4 font-medium whitespace-nowrap text-white bg-gray-800 rounded-md">
                        <tr>
                            {{-- @can('super_admin', Auth::user())
                            <th class="px-4 py-2 border">
                                <input type="checkbox" id="selectAllCheckbox">
                            </th>
                            @endcan --}}
                            <th class="px-4 py-2 border">Image</th>
                            <th class="px-4 py-2 border">Item Reference</th>
                            <th class="px-4 py-2 border">Company</th>
                            <th class="px-4 py-2 border">Category</th>
                            <th class="px-4 py-2 border">Type</th>
                            <th class="px-4 py-2 border">Action Type</th>
                            <th class="px-4 py-2 border">Created at</th>
                            <th class="px-4 py-2 border">Action</th>
                        </tr>
                    </thead>
                </table>
                {{-- END TABLE --}}
            </div>
        </div>
        {{-- END main --}}
    </div>
    {{-- END content --}}

    {{--START scripts --}}
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    {{-- DATA TABLE --}}
    <script>
        $(document).ready(function() {
            var table = $('#mannequinsTable').DataTable({
                order: [[6, 'desc']],
                lengthChange: false,
                "dom": 'lrtip',
                processing: true,
                serverSide: true,
                deferRender: true,
                scrollY: false,
                pageLength: 10,
                ajax:{
                    url: '{{ route('collection') }}' ,
                    data: function (data) {
                        // Add additional filter data
                        data.category = $('#categoryFilter').val(); // Get the selected category value
                        data.company = $('#companyFilter').val();
                        data.search = $('#customSearchInput').val();

                        //added today
                        if (window.location.search.includes('date=today')) {
                            data.date = 'today';
                        }
                    },
                },
                deferLoading: (10, 100),
                columnDefs: [
                    {
                        targets: [6], // 6 is the index of the 'created_at' column (zero-based index)
                        visible: false,
                    },
                    {
                        targets: '_all',
                        className: 'px-2 py-2 border text-center',
                    },
                ],
                columns: [
                    {
                        data: 'image',
                        name: 'image',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, full, meta) {
                            if (type == 'display') {
                                if (data) {
                                    // Display the image as an <img> tag
                                    return '<img src="' + data + '" alt="Mannequin Image" class="w-16 h-16 object-contain" loading="lazy">';
                                } else {
                                    // Display a message if the image is not found
                                    return 'Image not found';
                                }
                            }
                            return data;

                        }
                    },
                    { data: 'itemref', name: 'itemref' },
                    { data: 'company', name: 'company' },
                    { data: 'category', name: 'category' },
                    { data: 'type', name: 'type' },
                    { data: 'addedBy', name: 'addedBy' },
                    { data: 'created_at', name: 'created_at' },

                    {
                        data:'action',
                        name: 'action',
                        orderable: false, searchable: false,
                    }
                ],
                // pagingType: 'full_numbers',
                language: {
                    emptyTable: 'No Data available',
                },
                searching: true,
            });

            // Check if the table is empty and reload if it is
            if (table.data().count() == 0) {
                table.ajax.reload();
            }

            // Category Filter
            // Add event listener for category filter
            $('#categoryFilter').on('change', function () {
                table.draw(); // Redraw the table to apply the filter
            });

            // Handle company filter change
            $('#companyFilter').on('change', function() {
                table.draw();
            });

            // Add event listener for custom search input
            $('#customSearchInput').on('keyup', function () {
                table.search(this.value).draw(); // This will send the search query to the server
            });

            // Trigger initial filter changes after DataTable initializes
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
    </script>
    {{--END scripts--}}
</x-app-layout>

