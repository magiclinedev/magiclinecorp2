@section('title')
    Home
@endsection

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Magic Line') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-2 lg:px-2">
            <div class="flex flex-wrap ">

                {{-- ALL PRODUCT access admin 1 and owner--}}
                @can('super_admin', Auth::user())
                <div class="w-full sm:w-1/5 p-4 ">
                    <a href="#" class="showAllProducts block text-center relative overflow-hidden group">
                        <!-- Content for the first square -->
                        <div class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap bg-gray-50 dark:text-white dark:bg-gray-800 rounded-md">
                            <div class="relative">
                                <div class="absolute left-0 top-5 transform -translate-x-1/2 -translate-y-1/2 ml-7 w-20 h-20 rounded-md flex justify-center items-center">
                                    <div class="bg-white w-full h-full rounded-md flex justify-center items-center">
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
                    <a href="{{ route('collection', ['company' => $company->name]) }}" class="block text-center relative overflow-hidden group" data-company="{{ $company->name }}">{{-- {{ route('collection', ['company' => $company->name]) }} --}}
                    @endcan
                    {{-- owner shows table below --}}
                    @can('owner', Auth::user())
                    <a href="#" id="showTableButton" class="companyFilter show-table-button block text-center relative overflow-hidden group" data-company="{{ $company->name }}">{{-- {{ route('collection', ['company' => $company->name]) }} --}}
                    @endcan
                        <!-- Content for the first square -->
                        <div class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap bg-gray-50 dark:text-white dark:bg-gray-800 rounded-md">
                            <div class="relative">
                                <div class="absolute left-0 top-5 transform -translate-x-1/2 -translate-y-1/2 ml-7 w-20 h-20 rounded-md flex justify-center items-center">
                                    <div class="bg-white w-full h-full rounded-md flex justify-center items-center">
                                        <img src="{{ asset('storage/' . $company->images) }}" alt="Company Image" class="w-16 h-16 object-contain">
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
                <div class="w-full sm:w-1/5 p-4 ">
                    <a href="#" class="block text-center relative overflow-hidden group">
                        <!-- Content for the first square -->
                        <div class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap bg-gray-50 dark:text-white dark:bg-gray-800 rounded-md">
                            <div class="relative">
                                <div class="absolute left-0 top-5 transform -translate-x-1/2 -translate-y-1/2 ml-7 w-20 h-20 rounded-md flex justify-center items-center">
                                    <div class="bg-white w-full h-full rounded-md flex justify-center items-center">
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

                @can('owner', Auth::user())
                <div id="tableContainer" class="w-full sm:w-full p-4" style="display: none;">
                {{-- TABLE --}}
                <table id="mannequinsTable" class="w-full table-auto border-collapse border">
                    <thead class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap bg-gray-50 dark:text-white dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-2 border">
                                <input type="checkbox" id="selectAllCheckbox">
                            </th>
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
                                <tr class="border">
                                    <td class="px-4 py-2 border">
                                        <!-- Add the checkbox input here -->
                                        <input type="checkbox" class=" row-checkbox center pb-4">
                                    </td>
                                    <td class="px-4 py-2 border">
                                        @php
                                            // Split the image paths string into an array
                                            $imagePaths = explode(',', $mannequin->images);
                                            // Get the first image path from the array
                                            $firstImagePath = $imagePaths[0] ?? null;
                                        @endphp
                                        @if ($firstImagePath)
                                            <img src="{{ asset('storage/' . $firstImagePath) }}" alt="Mannequin Photo" width="100">
                                        @else
                                            No Image
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border itemref-cell">
                                        <span class="itemref-text">{{ $mannequin->itemref }}</span>
                                        {{-- HOVER to show read, update, and delete --}}
                                        <div class="action-buttons">

                                            <a href="{{ route('collection.view_prod', ['encryptedId' => Crypt::encrypt($mannequin->id)]) }}" class="btn-view">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            {{-- Admin --}}
                                            <a href="{{ route('collection.edit', ['id' => $mannequin->id]) }}" class="btn-view">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 border">{{ $mannequin->company }}</td>
                                    <td class="px-4 py-2 border">{{ $mannequin->category }}</td>
                                    <td class="px-4 py-2 border">{{ $mannequin->type }}</td>
                                    <td class="px-4 py-2 border">{{ $mannequin->addedBy }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                {{-- END TABLE --}}
                </div>
                @endcan
            </div>
        </div>
    </div>

    {{-- datables --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#mannequinsTable').DataTable({
                lengthChange: false,
            });

             // showAllProducts
             $('.showAllProducts').on('click', function(event) {
                event.preventDefault();
                $('#tableContainer').show(); // Show the table container

                // Clear existing search/filtering and draw the original table
                var table = $('#mannequinsTable').DataTable();
                table.search('').columns().search('').draw();
            });

            // Handle company filter change
            $('.companyFilter').on('click', function(event) {
                event.preventDefault();

                var company = $(this).data('company');

                // Clear existing search and apply new company filter
                table.search('').draw(); // Clear any previous search
                table.column(3) // Company column index (0-based)
                    .search(company)
                    .draw();
            });

            //Show table
            $('.show-table-button').on('click', function(event) {
                event.preventDefault();
                $('#tableContainer').show(); // Show the table container
            });
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
</x-app-layout>
