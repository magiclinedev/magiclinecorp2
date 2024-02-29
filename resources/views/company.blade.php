@section('title')
    Company
@endsection

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add company') }}
        </h2>
    </x-slot>
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0">

            {{-- Add COmpany --}}
            <div class="bg-white shadow-md rounded-lg p-4 w-auto md:w-1/3">
                <form action="{{ route('company.add') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('POST') <!-- Add this line to specify the method -->
                    <label for="company" class="block font-bold mb-2">Company</label>
                    <input type="text" name="company" id="company" class="w-full border rounded-md py-2 px-3" placeholder="Enter company name">

                    <label for="images" class="block font-bold mt-4 mb-2">Images</label>
                    <input type="file" name="images" id="images" class="w-full border rounded-md py-2 px-3">

                    <button type="submit" class="mt-4 bg-gray-800 text-white rounded-md py-2 px-4 font-semibold hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition ease-in-out duration-150">
                        Add Company
                    </button>
                </form>
            </div>

            {{-- CompanyList --}}
            <div class="bg-white shadow-md rounded-lg p-4 w-full md:w-full">
                {{-- search box --}}
                <input id="customSearchInput" type="text" class="w-full px-4 py-2 border rounded-md shadow-sm mb-4" placeholder="Search...">
                {{-- Table --}}
                <table id="companiesTable" class="w-full table-auto border-collapse border">
                    <thead class="px-6 py-4 font-medium whitespace-nowrap text-white bg-gray-800 rounded-md">
                        <tr>
                            <th class="px-4 py-2 border">Logo</th>
                            <th class="px-4 py-2 border">Name</th>
                            <th class="px-4 py-2 border">Added By</th>
                            <th class="px-4 py-2 border">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($company as $company)
                            <tr class="border">
                                <td class="px-4 py-2 border">
                                    @php
                                        $cacheKey = 'company_image_' . $company->id; // Use a unique key for each company
                                        $imageURL = cache()->remember($cacheKey, now()->addHours(24), function () use ($company) {
                                            return Storage::disk('dropbox')->url($company->images);
                                        });
                                    @endphp
                                    <img src="{{ $imageURL }}" alt="Company logo" width="100">
                                </td>
                                <td class="px-4 py-2 border">{{ $company->name }}</td>
                                <td class="px-4 py-2 border">{{ $company->addedBy }}</td>
                                <td class="px-4 py-2 border">
                                    <button class="btn-delete" data-id="{{ $company->id }}" data-transfer-url="{{ route('company.trash', ['id' => $company->id]) }}">
                                        {{--  --}}
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    {{-- START SCRIPTS --}}
    {{-- DATA TABLES --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#companiesTable').DataTable({
                lengthChange: false,
                "dom": 'lrtip',
                processing: true,
                "autoWidth": false,
                scrollX: true,
                scrollCollapse: true,
            });

             // Custom search input handler using input event
             $('#customSearchInput').keyup(function(){
                table.search( $(this).val() ).draw() ;
            })

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
    {{-- Sweeet Alert for delete --}}
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
