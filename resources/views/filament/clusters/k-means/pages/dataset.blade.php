<x-filament::page>
    <div class="space-y-6">
        <form wire:submit.prevent="submit" class="space-y-6">
            {{ $this->form }}
            
            <div class="flex gap-4 mt-6">
              <x-filament::button type="submit">Upload</x-filament::button>
              <x-filament::button type="button" wire:click="lanjutkan" :disabled="!$isUploaded">
                Lanjutkan
            </x-filament::button>
            </div>
        </form>

        @if ($errors->any())
            <div class="p-4 bg-red-100 text-red-700 rounded">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if (session('success'))
            <div class="p-4 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if($rawRows)
            <div class="mt-8">
                <h3 class="font-bold mb-2">Data Mentah dari Excel/CSV</h3>
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200 mb-6">
                        <thead>
                            <tr>
                                @foreach($header as $h)
                                    <th class="px-2 py-1 bg-gray-50 text-xs text-gray-500">{{ $h }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rawRows as $row)
                                <tr>
                                    @foreach($header as $h)
                                        <td class="px-2 py-1 text-xs">{{ $row[$h] ?? '-' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($normalizedRows)
            <div class="mt-8">
                <h3 class="font-bold mb-2">Data Setelah Normalisasi (Min-Max)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                @foreach($header as $h)
                                    <th class="px-2 py-1 bg-gray-50 text-xs text-gray-500">{{ $h }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($normalizedRows as $row)
                                <tr>
                                    @foreach($header as $h)
                                        <td class="px-2 py-1 text-xs">{{ $row[$h] ?? '-' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="bg-gray-50 rounded-xl p-6 mt-8">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Petunjuk Upload:</h3>
            <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                <li>File harus dalam format Excel (.xlsx) atau CSV</li>
                <li>Baris pertama adalah nama kolom</li>
                <li>Kolom pertama biasanya nama, sisanya numerik</li>
                <li>Nilai numerik akan dinormalisasi otomatis (Min-Max)</li>
            </ul>
        </div>
    </div>
</x-filament::page>