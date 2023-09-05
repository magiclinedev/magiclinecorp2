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
                        <div class="px-6 py-4 font-medium text-gray-900 dark:text-black whitespace-nowrap bg-gray-50 dark:bg-gray-800 rounded-md">
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
                        <div class="px-6 py-4 font-medium text-gray-900 dark:text-black whitespace-nowrap bg-gray-50 dark:bg-gray-800 rounded-md">
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
                <div class="w-full sm:w-1/5 p-4">
                    <a href="users" class="block text-center relative overflow-hidden group">
                        <!-- Content for the first square -->
                        <div class="px-6 py-4 font-medium text-gray-900 dark:text-black whitespace-nowrap bg-gray-50 dark:bg-gray-800 rounded-md">
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

                        {{-- Trashcan Button --}}
                        @if ($mannequins->contains('activeStatus', 0))
                            <div class="pt-2">
                                <a href="{{ route('collection.trashcan') }}" class="text-gray-800 hover:text-gray-600">
                                    <i class="fas fa-trash-alt"></i> Trash
                                    <span class="badge">{{ $mannequins->where('activeStatus', 0)->count() }}</span>
                                </a>
                            </div>
                        @endif
                    </div>

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
                                                <button class="btn-delete"data-transfer-url="{{ route('collection.trash', ['id' => $mannequin->id]) }}">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </button>
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
                "dom": 'lrtip'
            });

            // Handle company filter(ON TOP OF TABLE)
            $('#companyFilter').on('change', function() {
                var company = $(this).val();
                table.column(3).search(company).draw();
            });

            // Handle category filter change(on table)
            $('#categoryFilter').on('change', function() {
                var category = $(this).val();
                table.column(4).search(category).draw();
            });

            // Show All Products button
            $('.showAllProducts').on('click', function(event) {
                event.preventDefault();
                $('#tableContainer').show();
                table.search('').columns().search('').draw();
                $('#companyFilter').val('');
                $('#customSearchInput').val(''); // Clear custom search input
                scrollToElement('tableContainer');
            });

              // Handle "select all" checkbox
              $('#selectAllCheckbox').on('change', function() {
                var isChecked = this.checked;
                $('td input.row-checkbox').each(function() {
                    this.checked = isChecked;
                });
            });

            // Company Filter links
            $('.companyFilter').on('click', function(event) {
                event.preventDefault();
                var company = $(this).data('company');
                table.search('').draw();
                table.column(3).search(company).draw();
                $('#companyFilter').val(company);
                $('#customSearchInput').val(''); // Clear custom search input
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
    {{-- Sweet Alert for Delete --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deleteButtons = document.querySelectorAll('.btn-delete');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const recordId = this.getAttribute('data-id');
                    const transferUrl = this.getAttribute('data-transfer-url');

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
                            axios.post(transferUrl)
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
</x-app-layout>
