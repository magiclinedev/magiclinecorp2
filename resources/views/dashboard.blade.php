@section('title')
    Home
@endsection

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Magic Line') }}
        </h2>
    </x-slot>
    <style>
        /* Add this CSS to your stylesheet */
        .group {
            transition: all 0.3s ease; /* Add a smooth transition */
        }

        .group:hover {
            background-color: #ff0000; /* Change the background color on hover */
            transform: translateY(-10px); /* Move the element 10px to the right on hover */
        }

    </style>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-2 lg:px-2">
            <div class="flex flex-wrap ">

                {{-- ALL PRODUCT access admin 1 and owner--}}
                @can('super_admin', Auth::user())
                    <div class="w-full sm:w-1/5 p-4 ">
                        {{-- admin 1, 2 and viewer has href --}}
                        @can('users_access', Auth::user())
                            <a href="{{ route('collection')}}" class="block text-center relative overflow-hidden group">{{-- {{ route('collection', ['company' => $company->name]) }} --}}
                        @endcan
                        {{-- owner shows table below --}}
                        @can('owner', Auth::user())
                            <a href="" class="showAllProducts block text-center relative overflow-hidden group">
                        @endcan
                        <div class="pl-4 pr-4 py-4 font-medium whitespace-nowrap text-white bg-gray-800 rounded-md">
                            <div class="flex flex-col lg:flex-row items-center">
                                <div class="w-full lg:w-1/2 lg:p-2">
                                    <div class="relative bg-white rounded-md flex justify-center items-center p-2">
                                            <i class="fas fa-database fa-3x text-black"></i>
                                        </div>
                                    </div>

                                    <!-- Content container -->
                                    <div class="p-6 items-end flex justify-end">
                                        <div class="text-sm text-gray-500">
                                            <div class="text-right">
                                                <div class="text-2xl font-semibold text-white-800">
                                                    <span class="text-5xl text-800 text-white">
                                                        {{ $mannequins->where('activeStatus', 1)->count() }}
                                                    </span>
                                                </div>
                                                <div class="text-xs text-white-500">
                                                    <p class="text-white">All Products Available</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Pseudo-element for hover effect -->
                            <div class="absolute left-0 bottom-0 h-0 w-full bg-gradient-to-t from-gray-400 to-transparent transition-all duration-300 ease-in-out group-hover:h-full"></div>
                        </a>
                    </div>
                @endcan

                {{-- PRODUCT COUNT PER COMPANY --}}
                @foreach ($companies as $company)
                    <div class="w-full sm:w-1/5 p-4 ">
                        {{-- admin 1, 2 and viewer has href --}}
                        @can('users_access', Auth::user())
                            <a href="{{ route('collection', ['company' => $company->name, 'companySelected' => 'true']) }}" class="block text-center relative overflow-hidden group" data-company="{{ $company->name }}">{{-- {{ route('collection', ['company' => $company->name]) }} --}}
                        @endcan
                        {{-- owner shows table below --}}
                        @can('owner', Auth::user())
                            <a href="#" id="showTableButton" class="companyFilter show-table-button block text-center relative overflow-hidden group" data-company="{{ $company->name }}">{{-- {{ route('collection', ['company' => $company->name]) }} --}}
                        @endcan
                            <!-- Content for the first square -->
                            <div class="pl-4 pr-4 py-4 font-medium whitespace-nowrap text-white bg-gray-800 rounded-md">
                                <div class="flex flex-col lg:flex-row items-center">
                                    <div class="w-full lg:w-1/2 lg:p-2">
                                        <div class="relative bg-white rounded-md flex justify-center items-center">
                                            @php
                                                $cacheKey = 'company_image_' . $company->id; // Use a unique key for each company
                                                $imageURL = Cache::remember($cacheKey, now()->addHours(1), function () use ($company) {
                                                    return Storage::disk('dropbox')->url($company->images);
                                                });
                                            @endphp
                                            <img src="{{ $imageURL }}" alt="Company Image" class="w-16 h-16 object-contain">
                                        </div>
                                    </div>

                                    <!-- Content container -->
                                    <div class="p-6 items-end flex justify-end">
                                        <div class="text-sm text-gray-500">
                                            <div class="text-right">
                                                <div class="text-2xl font-semibold text-white-800">
                                                    <span class="text-5xl text-800 text-white">
                                                        {{ $mannequins->where('company', $company->name)->where('activeStatus', 1)->count() }}
                                                    </span>
                                                </div>
                                                <div class="text-xs text-white-500">
                                                    <p class="text-white">{{ $company->name }}'s Products</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Pseudo-element for hover effect -->
                            <div class="absolute left-0 bottom-0 h-0 w-full bg-gradient-to-t from-gray-400 to-transparent transition-all duration-300 ease-in-out group-hover:h-full"></div>
                        </a>
                    </div>
                @endforeach


                {{-- USERS --}}
                @can('super_admin', Auth::user())
                    <div class="w-full sm:w-1/5 p-4">
                        <a href="users" class="block text-center relative overflow-hidden group">
                            <!-- Content for the first square -->
                            <div class="pl-4 pr-4 py-4 font-medium whitespace-nowrap text-white bg-gray-800 rounded-md">
                                <div class="flex flex-col lg:flex-row items-center">
                                    <div class="w-full lg:w-1/2 lg:p-2">
                                        <div class="relative bg-white rounded-md flex justify-center items-center p-2">
                                            <i class="fas fa-users fa-3x text-black"></i>
                                        </div>
                                    </div>

                                    <!-- Content container -->
                                    <div class="p-6 items-end flex justify-end">
                                        <div class="text-sm text-gray-500">
                                            <div class="text-right">
                                                <div class="text-2xl font-semibold text-white-800">
                                                    <span class="text-5xl text-800 text-white">
                                                        {{ $users->count() }}
                                                    </span>
                                                </div>
                                                <div class="text-xs text-white-500">
                                                    <p class="text-white">Users Available</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Pseudo-element for hover effect -->
                            <div class="absolute left-0 bottom-0 h-0 w-full bg-gradient-to-t from-gray-400 to-transparent transition-all duration-300 ease-in-out group-hover:h-full"></div>
                        </a>
                    </div>
                @endcan

                {{-- ADDED PROD TODAY --}}
                @can('super_admin', Auth::user())
                    <div class="w-full sm:w-1/5 p-4">
                        {{-- admin 1, 2 and viewer has href --}}
                        @can('users_access', Auth::user())
                            <a href="{{ route('collection', ['date' => 'today']) }}" class="block text-center relative overflow-hidden group">
                        @endcan
                        {{-- owner shows table below --}}
                        @can('owner', Auth::user())
                            <a href="" class="showAddedTodayProducts block text-center relative overflow-hidden group">
                        @endcan
                            <!-- Content for the first square -->
                            <div class="pl-4 pr-4 py-4 font-medium whitespace-nowrap text-white bg-gray-800 rounded-md">
                                <div class="flex flex-col lg:flex-row items-center">
                                    <div class="w-full lg:w-1/2 lg:p-2">
                                        <div class="relative bg-white rounded-md flex justify-center items-center p-2">
                                            <i class="fas fa-plus fa-3x text-black"></i>
                                        </div>
                                    </div>

                                    <!-- Content container -->
                                    <div class="p-6 items-end flex justify-end">
                                        <div class="text-sm text-gray-500">
                                            <div class="text-right">
                                                <div class="text-2xl font-semibold text-white-800">
                                                    <span class="text-5xl text-800 text-white">
                                                        {{ $productsCreatedToday }}
                                                    </span>
                                                </div>
                                                <div class="text-xs text-white-500">
                                                    <p class="text-white">Added Products Today</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Pseudo-element for hover effect -->
                            <div class="absolute left-0 bottom-0 h-0 w-full bg-gradient-to-t from-gray-400 to-transparent transition-all duration-300 ease-in-out group-hover:h-full"></div>
                        </a>
                    </div>
                @endcan

                {{-- Updated PROD TODAY --}}
                @can('super_admin', Auth::user())
                    <div class="w-full sm:w-1/5 p-4">
                        {{-- admin 1, 2 and viewer has href --}}
                        @can('users_access', Auth::user())
                            <a href="{{ route('collection', ['date' => 'updatedToday']) }}" class="block text-center relative overflow-hidden group">
                        @endcan
                        {{-- owner shows table below --}}
                        @can('owner', Auth::user())
                            <a href="" class="showUpdatedTodayProducts block text-center relative overflow-hidden group">
                        @endcan
                            <!-- Content for the first square -->
                            <div class="pl-4 pr-4 py-4 font-medium whitespace-nowrap text-white bg-gray-800 rounded-md">
                                <div class="flex flex-col lg:flex-row items-center">
                                    <div class="w-full lg:w-1/2 lg:p-2">
                                        <div class="relative bg-white rounded-md flex justify-center items-center p-2">
                                            <i class="fas fa-edit fa-3x text-black"></i>
                                        </div>
                                    </div>

                                    <!-- Content container -->
                                    <div class="p-6 items-end flex justify-end">
                                        <div class="text-sm text-gray-500">
                                            <div class="text-right">
                                                <div class="text-2xl font-semibold text-white-800">
                                                    <span class="text-5xl text-800 text-white">
                                                        {{ $productsUpdatedToday }}
                                                    </span>
                                                </div>
                                                <div class="text-xs text-white-500">
                                                    <p class="text-white">Updated Products Today</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Pseudo-element for hover effect -->
                            <div class="absolute left-0 bottom-0 h-0 w-full bg-gradient-to-t from-gray-400 to-transparent transition-all duration-300 ease-in-out group-hover:h-full"></div>
                        </a>
                    </div>
                @endcan

                {{-- TABLE (for owner) --}}
                @can('owner', Auth::user())
                    <div id="tableContainer" class="bg-white shadow-sm sm:rounded-lg w-full p-4" style="display: none;">
                        <div class="flex flex-wrap space-x-4 my-4">
                            {{-- company FILTER --}}
                            <div class="filter-dropdown flex-1">
                                <select id="companyFilter" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Filter by Category">
                                    <option value="">All Companies</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->name }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- category FILTER --}}
                            <div class="filter-dropdown flex-1">
                                <select id="categoryFilter" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Filter by Company">
                                    <option value="">Categories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->name }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Searchbox --}}
                            <div class="w-1/2">
                                <input id="customSearchInput" type="text" class="w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Search...">
                            </div>
                        </div>
                        {{-- TABLE --}}
                        <table id="mannequinsTable" class="w-full table-auto border-collapse border">
                            <thead class="px-6 py-4 font-medium whitespace-nowrap text-white bg-gray-800 rounded-md">
                                <tr>
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
                @endcan
            </div>
        </div>
    </div>

    {{-- datables --}}
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#mannequinsTable').DataTable({
                // order: [[6, 'desc']],
                lengthChange: false,
                "dom": 'lrtip',
                processing: true,
                serverSide: true,
                deferRender: true,
                scrollX: true,
                "autoWidth": false,
                pageLength: 10,
                ajax:{
                    url: '{{ route('dashboard') }}',
                    data: function (data) {
                        // Add additional filter data
                        data.category = $('#categoryFilter').val(); // Get the selected category value
                        data.company = $('#companyFilter').val();
                        data.search = $('#customSearchInput').val();
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
            // When the company filter link is clicked
            $('.companyFilter').on('click', function(event) {
                event.preventDefault();
                var company = $(this).data('company');

                // Update the select element
                $('#companyFilter').val(company);

                // Trigger the DataTable's filtering logic
                table.search('').draw();
                table.column(2).search(company).draw();
            });

            // Add event listener for custom search input
            $('#customSearchInput').on('keyup', function () {
                table.search(this.value).draw(); // This will send the search query to the server
            });

            // Trigger initial filter changes after DataTable initializes
            $('#categoryFilter').trigger('change');
            $('#companyFilter').trigger('change');

            // Show All Products button
            $('.showAllProducts').on('click', function(event) {
                event.preventDefault();
                $('#tableContainer').show();
                $('#companyFilter').val('');
                $('#customSearchInput').val('');
                table.draw(); // Clear custom search input
                scrollToElement('tableContainer');
            });

            // added today
            $('.showAddedTodayProducts').on('click', function(event) {
                event.preventDefault();
                var dateFilter = 'today';
                $('#tableContainer').show();
                $('#companyFilter').val('');
                $('#customSearchInput').val('');
                table.draw(); // Clear custom search input
                scrollToElement('tableContainer');
            });

            // updated today
            $('.showUpdatedTodayProducts').on('click', function(event) {
                event.preventDefault();
                var dateFilter = 'updatedToday';
                $('#tableContainer').show();
                $('#companyFilter').val('');
                $('#customSearchInput').val('');
                table.draw(); // Clear custom search input
                scrollToElement('tableContainer');
            });

            // Show Table button
            $('.show-table-button').on('click', function(event) {
                event.preventDefault();
                $('#tableContainer').show();
                scrollToElement('tableContainer');
            });

            // Custom search input handler using input event
            $('#customSearchInput').keyup(function(){
            table.search( $(this).val() ).draw() ;
            })

            // Scroll function
            function scrollToElement(elementId) {
                const element = document.getElementById(elementId);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    </script>
</x-app-layout>
