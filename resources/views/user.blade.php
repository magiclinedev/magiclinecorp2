@section('title')
    Users
@endsection

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>
    {{-- START MAIN --}}
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0">

            {{-- START Form for adding users --}}
            <div class="bg-white shadow-md rounded-lg p-4 md:w-2/5">
                <form method="POST" action="{{ route('users.add') }}" enctype="multipart/form-data">
                    @csrf
                    {{-- Name --}}
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" id="name" class="mt-1 p-2 border rounded-md w-full" required>
                    </div>
                    {{-- Username --}}
                    <div class="mb-4">
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="username" id="username" class="mt-1 p-2 border rounded-md w-full" required>
                    </div>
                    {{-- Email --}}
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" class="mt-1 p-2 border rounded-md w-full" required>
                    </div>
                    {{-- Password --}}
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" id="password" class="mt-1 p-2 border rounded-md w-full" required>
                    </div>
                    {{-- Confirm Pass --}}
                    <div class="mb-4">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 p-2 border rounded-md w-full" required>
                    </div>

                    {{-- ROLE --}}
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status" class="mt-1 p-2 border rounded-md w-full" required>
                            <option value="" disabled selected>Select a status</option>
                            <option value="1">Admin 1</option>
                            <option value="2">Admin 2</option>
                            <option value="3">Viewer</option>
                            <option value="4">Owner</option>
                        </select>
                    </div>

                    <div class="flex space-x-4">
                        {{-- COMPANY ACCESS --}}
                        <div id="companyAccessSection" class="flex-1">
                            <div class="mt-1">
                                <label class="block text-sm font-medium text-gray-700 mr-2">Companies</label>
                                {{-- Select all company/price access --}}
                                <label class="inline-flex items-center">
                                    <input type="checkbox" id="select-all-companies" class="rounded border-gray-300 font-medium text-indigo-600 shadow-sm focus:ring-indigo-500 mx-1">
                                    <span class="block text-sm font-medium text-gray-700 ml-2">Select All</span>
                                </label>

                                {{-- Companies --}}
                                @foreach ($companies as $company)
                                    <div class="w-auto">
                                        <label class="inline-flex">
                                            <input type="checkbox" name="company_ids[]" value="{{ $company->id }}" class="company-checkbox rounded border-gray-300 font-medium text-indigo-600 shadow-sm focus:ring-indigo-500 mx-1">
                                            <span class="block text-sm font-medium text-gray-700 mx-2">{{ $company->name }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Price Access --}}
                        <div id="companyAccessSection" class="flex-1">
                            <div class="mt-1">
                                <label class="block text-sm font-medium text-gray-700">Price Access</label>
                                <div id="selected-companies">
                                    <!-- Selected companies will be listed here dynamically -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <button type="submit" class="mt-4 mb-10 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Register
                        </button>
                    </div>
                </form>
            </div>
            {{-- END FORM --}}

            {{-- CompanyList --}}
            <div class="bg-white shadow-md rounded-lg p-4 w-full md:w-full">
                {{-- Searchbox --}}
                <div class="w-full">
                    <input id="customSearchInput" type="text" class="w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Search...">
                </div>
                {{-- table --}}
                <table id="usersTable" class="w-full border-collapse pt-6">
                    <thead class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap bg-gray-50 dark:text-white dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-2 border">Name</th>
                            <th class="px-4 py-2 border">Status</th>
                            <th class="px-4 py-2 border">Company</th>
                            <th class="px-4 py-2 border">Set By</th>
                            <th class="px-4 py-2 border">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr class="border">
                                <td class="px-4 py-2 border">{{ $user->name }}</td>
                                <td class="px-4 py-2 border" style="{{ $user->activeStatus == 0 ? 'color: red;' : '' }}">
                                    @if ($user->status == 1)
                                        Admin 1 / Super Admin
                                    @elseif ($user->status == 2)
                                        Admin 2
                                    @elseif ($user->status == 4)
                                        Owner
                                    @else
                                        Viewer
                                    @endif
                                </td>
                                <td class="px-4 py-2 border">
                                    @if ($user->status == 1 || $user->status == 4)
                                        All
                                    @else
                                        @foreach ($user->companies as $company)
                                            {{ $company->name }}
                                            @unless($loop->last)
                                            , {{-- Add a comma unless it's the last company --}}
                                            @endunless
                                        @endforeach
                                    @endif
                                </td>
                                <td class="px-4 py-2 border">{{ $user->addedBy }}</td>
                                <td class="px-4 py-2 border">
                                    {{-- If User is inactive --}}
                                    @if ($user->activeStatus == 0)
                                        {{-- <button class="btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </button> --}}
                                        <button class="btn-active" data-id="{{ $user->id }}" data-transfer-url="{{ route('users.restore', ['id' => $user->id]) }}">
                                            <i class="fas fa-check"></i> Active
                                        </button>
                                        <button class="btn-delete" data-id="{{ $user->id }}" data-transfer-url="{{ route('users.trash', ['id' => $user->id]) }}">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    {{-- else --}}
                                    @else
                                        {{-- <button class="btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </button> --}}
                                        <button class="btn-delete" data-id="{{ $user->id }}" data-transfer-url="{{ route('users.trash', ['id' => $user->id]) }}">
                                            <i class="fas fa-bullseye"></i> Deactivate
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- END table --}}

        </div>
    </div>
    {{-- END MAIN --}}

    {{-- START SCRIPTS --}}
    {{-- DATA TABLES --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#usersTable').DataTable({
                order: [[1, 'asc']],
                lengthChange: false,
                "dom": 'lrtip'
            });

             // Custom search input handler using input event
             $('#customSearchInput').keyup(function(){
                table.search( $(this).val() ).draw() ;
            })

        });
    </script>

    {{-- script for company checkbox and price access --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAllCheckbox = document.getElementById('select-all-companies');
            const companyCheckboxes = document.querySelectorAll('.company-checkbox');
            const selectedCompaniesDiv = document.getElementById('selected-companies');

            // Checkbox hide for admin 1 and owner
            const companyAccessSection = document.getElementById('companyAccessSection');
            const statusDropdown = document.getElementById('status');


            selectAllCheckbox.addEventListener('change', function ()
            {
                const isChecked = selectAllCheckbox.checked;

                companyCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                    handleSelectedCompany(checkbox);
                });
            });

            companyCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    handleSelectedCompany(checkbox);
                });
            });

            // WHEN COMPANY IS CHECKED IT WILL VIEW NEW CHECKBOX FOR PRICE ACCESS
            function handleSelectedCompany(checkbox) {
                const companyId = checkbox.value;
                const companyName = checkbox.nextElementSibling.textContent.trim();
                const selectedCompany = Array.from(selectedCompaniesDiv.children).find(el => el.querySelector('.text-gray-700').textContent == companyName);

                if (checkbox.checked) {
                    if (!selectedCompany) {
                        const newSelectedCompany = document.createElement('div');
                        newSelectedCompany.classList.add('flex', 'flex-col', 'mb-1'); // Use 'flex' and 'flex-col'
                        newSelectedCompany.innerHTML = `
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="selected_company_ids[]" value="${companyId}" class="selected-company-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" checked>
                                <span class="block text-sm font-medium text-gray-700 mx-2">${companyName}</span>
                            </label>
                        `;
                        selectedCompaniesDiv.appendChild(newSelectedCompany);
                    }
                } else {
                    if (selectedCompany) {
                        selectedCompaniesDiv.removeChild(selectedCompany);
                    }
                }
            }

            // FOR HIDING THE CHECKBOX WHEN ADMIN 1 or Owner is selected
                statusDropdown.addEventListener('change', function() {
                const selectedStatus = this.value;

                // Show/hide the sections based on the selected status
                if (selectedStatus === '1' || selectedStatus === '4') {
                    companyAccessSection.style.display = 'none';
                } else {
                    companyAccessSection.style.display = 'block';
                }
            });

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
                title: 'Invalid Input',
                text: '{{session('danger_message') }}',
                icon: 'error',
                timer: 3000,
                showCancelButton: false,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
            });
        @endif
    </script>
    {{-- Sweeet Alert for delete/restore --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deleteButtons = document.querySelectorAll('.btn-delete');
            const activeButtons = document.querySelectorAll('.btn-active');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const recordId = this.getAttribute('data-id');
                    const transferUrl = this.getAttribute('data-transfer-url');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'You are about to remove user!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Perform AJAX request to delete the record
                            axios.post(transferUrl)
                                .then(response => {
                                    if (response.data.success) {
                                        Swal.fire(
                                            'Deleted!',
                                            response.data.message, // Use the message from the server's response
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
            // RESTORE / ACTIVATE USER AGAIN
            activeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const recordId = this.getAttribute('data-id');
                    const transferUrl = this.getAttribute('data-transfer-url');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'You are about to restore a user.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Perform AJAX request to change the active status
                            axios.post(transferUrl)
                                .then(response => {
                                    if (response.data.success) {
                                        Swal.fire(
                                            'Changed!',
                                            'The user\'s status has been updated.',
                                            'success'
                                        ).then(() => {
                                            // Refresh the page after successful update
                                            window.location.reload();
                                        });
                                    }
                                })
                                .catch(error => {
                                    Swal.fire(
                                        'Error!',
                                        'An error occurred while updating the active status.',
                                        'error'
                                    );
                                });
                        }
                    });
                });
            });
        });
    </script>
    {{-- END SCRIPTS --}}

</x-app-layout>
