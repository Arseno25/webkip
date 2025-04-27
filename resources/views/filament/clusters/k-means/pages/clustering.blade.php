<!-- resources/views/filament/clusters/k-means/pages/clustering.blade.php -->
<x-filament::page>
    <div class="space-y-6">
        @if(!empty($dataWithCluster))
            <div class="overflow-x-auto" style="background: transparent;">
                <table class="w-full divide-y">
                    <thead>
                        <tr>
                            @foreach($header as $h)
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ $h }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($dataWithCluster as $row)
                            <tr>
                                @foreach($header as $key)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $row[$key] ?? '-' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <p class="text-sm text-yellow-700">
                    Belum ada hasil clustering. Silakan proses K-Means terlebih dahulu.
                </p>
            </div>
        @endif
    </div>
</x-filament::page>