@section('title')
    Users
@endsection

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('User-Edit') }}
        </h2>
        <div class="breadcrumbs mt-4 mb-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-gray-500">
                    <li>
                        <a href="{{ route('users') }}" class="hover:text-gray-700">Users</a>

                    <li class="px-2">
                        <i class="fa fa-caret-right"></i>
                    </li>
                    <li class="font-semibold">
                        <span class="whitespace-nowrap">User Edit</span>
                    </li>
                </ol>
            </nav>
        </div>
    </x-slot>

    {{-- START Form for Editing users --}}
    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-3 lg:px-8">
            <div class="bg-white shadow-md rounded-lg auto px-4 py-6">
                <form method="POST" action="{{ route('users.update', ['id' => $user->id]) }}" enctype="multipart/form-data">
                    @csrf
                    {{-- Name --}}
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" id="name" value="{{ $user->name }}" class="mt-1 p-2 border rounded-md w-full" required>
                    </div>
                    {{-- Username --}}
                    <div class="mb-4">
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="username" id="username" value="{{ $user->username }}" class="mt-1 p-2 border rounded-md w-full" required>
                    </div>
                    {{-- Email --}}
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" value="{{ $user->email }}" class="mt-1 p-2 border rounded-md w-full" required>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status" class="mt-1 p-2 border rounded-md w-full" required>
                            <option value="" disabled>Select a status</option>
                            <option value="1" {{ $user->status == 1 ? 'selected' : '' }}>Admin 1</option>
                            <option value="2" {{ $user->status == 2 ? 'selected' : '' }}>Admin 2</option>
                            <option value="3" {{ $user->status == 3 ? 'selected' : '' }}>Viewer</option>
                            <option value="4" {{ $user->status == 4 ? 'selected' : '' }}>Owner</option>
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
                                            <input type="checkbox" name="company_ids[]" value="{{ $company->id }}" class="company-checkbox rounded border-gray-300 font-medium text-indigo-600 shadow-sm focus:ring-indigo-500 mx-1"
                                                {{ $user->companies->contains($company) ? 'checked' : '' }}>
                                            <span class="block text-sm font-medium text-gray-700 mx-2">{{ $company->name }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        {{-- Price Access --}}
                        <div id="priceAccessSection" class="flex-1">
                            <div class="mt-1">
                                <label class="block text-sm font-medium text-gray-700">Price Access</label>
                                <div id="selected-companies">
                                    @foreach ($user->companies as $company)
                                        <div class="w-auto">
                                            <label class="inline-flex">
                                                <input type="checkbox" name="selected_company_ids[]" value="{{ $company->id }}" class="selected-company-checkbox rounded border-gray-300 font-medium text-indigo-600 shadow-sm focus:ring-indigo-500 mx-1"
                                                    {{ $company->pivot->checkPrice ? 'checked' : '' }}>
                                                <span class="block text-sm font-medium text-gray-700 mx-2">{{ $company->name }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <button type="submit" class="mt-4 mb-4 inline-flex items-center px-5 py-3 bg-gray-800 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Update
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script>
        // COMPANY/PRICE ACCESS CHECKBOX SCRIPT
        document.addEventListener("DOMContentLoaded", function() {
            // Get references to the relevant elements
            const companyCheckboxes = document.querySelectorAll(".company-checkbox");
            const selectedCompaniesContainer = document.getElementById("selected-companies");
            const selectAllCompaniesCheckbox = document.getElementById("select-all-companies");

            // Helper function to add or remove a selected company in Price Access
            function toggleSelectedCompany(companyId, isChecked) {
                const selectedCompanyCheckbox = document.querySelector(
                    `.selected-company-checkbox[value="${companyId}"]`
                );

                if (selectedCompanyCheckbox) {
                    if (!isChecked) {
                        // If unchecked in Company Access, remove it from Price Access
                        selectedCompanyCheckbox.parentElement.parentElement.remove();
                    } else {
                        selectedCompanyCheckbox.checked = isChecked;
                    }
                } else if (isChecked) {
                    // Create a new selected company element if it doesn't exist
                    const company = document.querySelector(
                        `.company-checkbox[value="${companyId}"]`
                    );

                    if (company) {
                        const companyName = company.nextElementSibling.textContent.trim();

                        const newSelectedCompany = document.createElement('div');
                        newSelectedCompany.classList.add('w-auto');
                        newSelectedCompany.innerHTML = `
                            <label class="inline-flex">
                                <input type="checkbox" name="company_ids[]" value="${companyId}" class="selected-company-checkbox rounded border-gray-300 font-medium text-indigo-600 shadow-sm focus:ring-indigo-500 mx-1" checked>
                                <span class="block text-sm font-medium text-gray-700 mx-2">${companyName}</span>
                            </label>
                        `;

                        selectedCompaniesContainer.appendChild(newSelectedCompany);
                    }
                }
            }

            // Add event listeners to company checkboxes in Company Access
            companyCheckboxes.forEach(function(checkbox) {
                checkbox.addEventListener("change", function() {
                    const companyId = checkbox.value;
                    const isChecked = checkbox.checked;
                    toggleSelectedCompany(companyId, isChecked);
                });
            });

            // Add an event listener to the "Select All" checkbox in Company Access
            selectAllCompaniesCheckbox.addEventListener("change", function() {
                const isChecked = this.checked;

                // Update the state of all individual company checkboxes in Company Access
                companyCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                    const companyId = checkbox.value;
                    toggleSelectedCompany(companyId, isChecked);
                });
            });
        });
        // DROPDOWN
        // Get references to the select element and both sections
        const statusDropdown = document.getElementById('status');
        const companyAccessSection = document.getElementById('companyAccessSection');
        const priceAccessSection = document.getElementById('priceAccessSection');
        const selectedCompaniesDiv = document.getElementById('selected-companies');

        // Function to toggle the visibility of sections based on the selected status
        function toggleSections() {
            const selectedStatus = statusDropdown.value;

            // Check the selected status and hide/show the sections accordingly
            if (selectedStatus == '1' || selectedStatus == '4') {
                companyAccessSection.style.display = 'none';
                priceAccessSection.style.display = 'none';
            } else {
                companyAccessSection.style.display = 'block';
                priceAccessSection.style.display = 'block';
            }
        }
        // Add an event listener to the select element
        statusDropdown.addEventListener('change', toggleSections);

        // Trigger the change event initially to set the initial visibility
        statusDropdown.dispatchEvent(new Event('change'));
    </script>

</x-app-layout>

