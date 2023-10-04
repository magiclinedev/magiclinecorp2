@section('title')
    Collection
@endsection

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Trash') }}
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
                        <span class="whitespace-nowrap">Trash Can</span>
                    </li>
                </ol>
            </nav>
        </div>
    </x-slot>

    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-3 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-4">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center mb-2">
                        <h1 class="text-2xl font-bold">Deleted Products</h1>
                    </div>
                    <div class="flex items-center mb-2">
                    {{-- DELETE/TRASH ALL BUTTON --}}
                        <button name="bulkActionRestore" id="bulkActionRestore" class="hidden mr-2 bg-blue-500 block w-52 py-2 px-3 mb-2 border border-gray-300 text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <i class="fas fa-undo-alt"></i> Restore
                        </button>
                        <button name="bulkAction" id="bulkAction" class="hidden mr-2 bg-red-500 block w-52 py-2 px-3 mb-2 border border-gray-300 text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <i class="fas fa-trash-alt"></i> Permanently Delete
                        </button>
                    </div>

                    <table id="trashTable" class="w-full table-auto border-collapse border">
                        <thead class="px-6 py-4 font-medium whitespace-nowrap text-white bg-gray-800 rounded-md">
                            <tr>
                                <th class="px-4 py-2 border text-center">
                                    <div class="flex items-center justify-center">
                                        <input type="checkbox" id="selectAllCheckbox">
                                    </div>
                                </th>
                                <th class="px-4 py-2 border">Image</th>
                                <th class="px-4 py-2 border">Item Reference</th>
                                <th class="px-4 py-2 border">Company</th>
                                <th class="px-4 py-2 border">Category</th>
                                <th class="px-4 py-2 border">Type</th>
                                <th class="px-4 py-2 border">Deleted By</th>
                                <th class="px-4 py-2 border">Action</th>
                            </tr>
                        </thead>
                        {{-- <tbody>
                            @foreach ($mannequins as $mannequin)
                                <tr class="border">
                                    <td class="px-4 py-2 border">
                                        <!-- Add the checkbox input here-->
                                        <input type="checkbox" class=" row-checkbox center pb-4">
                                    </td>

                                    <!-- Images -->
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

                                    <td class="px-4 py-2 border itemref-cell">
                                        <span class="itemref-text">{{ $mannequin->itemref }}</span>
                                        <!-- HOVER to show read, update, and delete -->
                                        <div class="action-buttons">
                                            <a href="{{ route('collection.view_prod', ['id' => Crypt::encrypt($mannequin->id)]) }}" class="btn-view">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="#" class="btn-view restore-button" onclick="confirmRestore('{{ route('collection.restore', ['id' => $mannequin->id]) }}')">
                                                <i class="fas fa-check"></i> Restore
                                            </a>
                                            <button class="btn-delete" data-id="{{ $mannequin->id }}" data-transfer-url="{{ route('collection.delete', ['id' => $mannequin->id]) }}"
                                                onclick="confirmDelete(this)">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 border">{{ $mannequin->company }}</td>
                                    <td class="px-4 py-2 border">{{ $mannequin->category }}</td>
                                    <td class="px-4 py-2 border">{{ $mannequin->type }}</td>
                                    <td class="px-4 py-2 border">{{ $mannequin->addedBy }}</td>
                                </tr>
                            @endforeach
                        </tbody> --}}
                    </table>
                </div>
            </div>
        </div>
    </div>
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#trashTable').DataTable({
                lengthChange: false,
                searching: false,
                serverSide: true,
                proccessing: true,
            });
            // Handle "select all" checkbox
            $('#selectAllCheckbox').on('change', function() {
                var isChecked = this.checked;
                $('td input.row-checkbox').each(function() {
                    this.checked = isChecked;
                });
            });
        });
    </script> --}}
      {{--START scripts --}}
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            // DATATBLE
            var table = $('#trashTable').DataTable({
                order: [[6, 'asc']],
                lengthChange: false,
                "dom": 'lrtip',
                processing: true,
                "autoWidth": false,
                serverSide: true,
                deferRender: true,
                "scrollX": true,
                responsive: true,
                // pageLength: 10,
                ajax:{
                    url: '{{ route('collection.trashcan') }}' ,
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
                //   {
                //       targets: [7], // created at
                //       visible: false,
                //   },
                    {
                        targets: '_all',
                        className: 'px-2 py-2 border text-center',
                    },
                ],
                columns: [
                    {
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false, searchable: false,
                    },
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
                        },
                    },
                    { data: 'itemref', name: 'itemref',},
                    { data: 'company', name: 'company' },
                    { data: 'category', name: 'category' },
                    { data: 'type', name: 'type' },
                    { data: 'addedBy', name: 'addedBy' },
                //   { data: 'created_at', name: 'created_at' },

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

                // CHECKBOX
                initComplete: function () {
                    // Handle "Select All" checkbox
                    $('#selectAllCheckbox').on('change', function () {
                        var isChecked = $(this).prop('checked');
                        $('input.row-checkbox').prop('checked', isChecked);
                    });
                }
            });

            // Check if the table is empty and reload if it is
            if (table.data().count() == 0) {
                table.ajax.reload();
            }

            // Category Filter
            $('#categoryFilter').on('change', function () {
                table.draw();
            });

            // Handle company filter change
            $('#companyFilter').on('change', function() {
                table.draw();
            });

            // Add event listener for custom search input
            $('#customSearchInput').on('keyup', function () {
                table.search(this.value).draw();
            });

            // Trigger initial filter changes after DataTable initializes
            $('#categoryFilter').trigger('change');
            $('#companyFilter').trigger('change');

            // SELECTED COMPANY FROM DASHBOARD
            var companySelected = '{{ request()->input('companySelected') }}';
            if (companySelected === 'true') {
                // Get a reference to the company filter dropdown
                var companyDropdown = $('#companyDropdown');
                // Hide the company dropdown
                companyDropdown.hide();

                // TITLE CAHANGE TO COMPANY NAME
                var selectedCompany = '{{ request()->input('company') }}';
                // Update the heading with the selected company's name
                $('#pageTitle').text(selectedCompany);
            }

            //Handles individual row checkboxes
            $('#trashTable').on('change', 'input.row-checkbox', function () {
                var allChecked = $('input.row-checkbox:checked').length === $('input.row-checkbox').length;
                $('#selectAllCheckbox').prop('checked', allChecked);
            });

            // Handle the "Delete/Trash All" button click event
            $('#bulkAction').on('click', function () {
                var selectedIds = [];
                $('input.row-checkbox:checked').each(function () {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length > 0) {
                    Swal.fire({
                        title: "Are you sure?",
                        text: "Do you want to trash the selected items?",
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, trash them",
                        cancelButtonText: "No, cancel"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // User confirmed, proceed with the update
                            $.ajax({
                                url: '/collection/trash-multiple',
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    ids: selectedIds
                                },
                                success: function (response) {
                                    // Handle the response from the server, e.g., show a success message
                                    Swal.fire({
                                        title: "Items Updated",
                                        text: "Selected items have been trashed.",
                                        icon: "success"
                                    });

                                    // Reload the page after a short delay (e.g., 1 second)
                                    setTimeout(function () {
                                        location.reload();
                                    }, 1000); // 1000 milliseconds = 1 second

                                    // datatable reload
                                    table.ajax.reload();
                                },
                                error: function (error) {
                                    Swal.fire({
                                        title: "Error",
                                        text: "An error occurred while trashing items.",
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                }
                else {
                    // No checkboxes were selected, provide feedback to the user.
                    Swal.fire({
                        title: "No Items Selected",
                        text: "Please select items to trash.",
                        icon: "info"
                    });
                }
            });

            // Handles Restore all click evemt
            $('#bulkActionRestore').on('click', function () {
                var selectedIds = [];
                $('input.row-checkbox:checked').each(function () {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length > 0) {
                    Swal.fire({
                        title: "Are you sure?",
                        text: "Do you want to Restore the selected items?",
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, restore them",
                        cancelButtonText: "No, cancel"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // User confirmed, proceed with the update
                            $.ajax({
                                url: '/collection/restore-multiple',
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    ids: selectedIds
                                },
                                success: function (response) {
                                    // Handle the response from the server, e.g., show a success message
                                    Swal.fire({
                                        title: "Items Updated",
                                        text: "Selected items have been restored.",
                                        icon: "success"
                                    });

                                    // datatable reload
                                    table.ajax.reload();
                                },
                                error: function (error) {
                                    Swal.fire({
                                        title: "Error",
                                        text: "An error occurred while trashing items.",
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                }
                else {
                    // No checkboxes were selected, provide feedback to the user.
                    Swal.fire({
                        title: "No Items Selected",
                        text: "Please select items to trash.",
                        icon: "info"
                    });
                }
            });

            //Handles Trash/Delete Button show
            function updateBulkButtonVisibility() {
                var anyCheckboxChecked = $('input.row-checkbox:checked').length > 0;
                $('#bulkAction').toggleClass('hidden', !anyCheckboxChecked);
                $('#bulkActionRestore').toggleClass('hidden', !anyCheckboxChecked);
            }

            $('#selectAllCheckbox').on('change', function () {
                $('input.row-checkbox').prop('checked', $(this).prop('checked'));
                updateBulkButtonVisibility();
            });

            $('#trashTable').on('change', 'input.row-checkbox', updateBulkButtonVisibility);
        });
    </script>
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
                title: 'Done!',
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
