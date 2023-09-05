@section('title')
    Audit Trail
@endsection

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Audit Trail') }}
        </h2>
    </x-slot>

    {{-- LOGIN LIST TABLE --}}
    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-3 lg:px-8">
            <div class="bg-white shadow-md rounded-lg auto px-4 py-6">

                <div class="flex space-x-4 mb-4">
                    {{-- Searchbox --}}
                    <div class="w-full mb-4">
                        <input id="customSearchInput" type="text" class="w-full px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Search...">
                    </div>
                </div>

                <table id="auditTrailTable" class="w-full table-auto border-collapse border">
                    <thead class="px-6 py-4 font-medium whitespace-nowrap text-white bg-gray-800 rounded-md">
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold uppercase tracking-wider">User ID</th>
                            <th class="px-5 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold uppercase tracking-wider">Activity</th>
                            <th class="px-5 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold uppercase tracking-wider">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($audits as $audit)
                        <tr>
                            <td class="px-4 py-2 border">{{ $audit->name }}</td>
                            <td class="px-4 py-2 border">{{ $audit->activity }}</td>
                            <td class="px-4 py-2 border">{{ $audit->created_at }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="mt-4">
                    {{-- all records --}}
                    @if ($audits instanceof Illuminate\Pagination\LengthAwarePaginator)
                        <a href="{{ route('audit-trail', ['showAll' => 1]) }}" class="text-blue-500 hover:underline">
                            View All Records
                        </a>
                    {{-- last 30 audit trail record (default)  --}}
                    @else
                        <a href="{{ route('audit-trail') }}" class="text-blue-500 hover:underline">
                            Paginated View
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- datables --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#auditTrailTable').DataTable({
                order: [[2, 'desc']], // Order by timestamp column (index 3) in descending order,
                lengthChange: false, // Disable the "Show x entries" dropdown
                "dom": 'lrtip'
            });

             // Custom search input handler using input event
            $('#customSearchInput').keyup(function(){
                table.search( $(this).val() ).draw() ;
            })

        });
    </script>
</x-app-layout>
