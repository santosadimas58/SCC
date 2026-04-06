<div>
    <x-header title="Rule Base Fuzzy" subtitle="Tabel 25 aturan IF-THEN untuk kendali SCC" separator />
    <x-card shadow>
        <p class="text-sm text-gray-400 mb-4">Baris = Error (e) | Kolom = Delta Error (de) | Isi = Output Duty Cycle</p>
        <div class="overflow-x-auto">
            <table class="table table-bordered w-full text-sm text-center">
                <thead>
                    <tr class="bg-base-300">
                        <th class="border border-base-300">e \ de</th>
                        @foreach(['NB','NS','ZO','PS','PB'] as $de)
                        <th class="border border-base-300">{{ $de }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                    $rules = [
                        'NB' => ['NB'=>'PB','NS'=>'PB','ZO'=>'PB','PS'=>'PS','PB'=>'ZO'],
                        'NS' => ['NB'=>'PB','NS'=>'PS','ZO'=>'PS','PS'=>'ZO','PB'=>'NS'],
                        'ZO' => ['NB'=>'PS','NS'=>'PS','ZO'=>'ZO','PS'=>'NS','PB'=>'NS'],
                        'PS' => ['NB'=>'PS','NS'=>'ZO','ZO'=>'NS','PS'=>'NS','PB'=>'NB'],
                        'PB' => ['NB'=>'ZO','NS'=>'NS','ZO'=>'NB','PS'=>'NB','PB'=>'NB'],
                    ];
                    $colors = ['NB'=>'badge-error','NS'=>'badge-warning','ZO'=>'badge-info','PS'=>'badge-warning','PB'=>'badge-success'];
                    @endphp
                    @foreach($rules as $e => $row)
                    <tr>
                        <td class="border border-base-300 font-bold bg-base-200">{{ $e }}</td>
                        @foreach(['NB','NS','ZO','PS','PB'] as $de)
                        <td class="border border-base-300">
                            <span class="badge badge-sm {{ $colors[$row[$de]] }}">{{ $row[$de] }}</span>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 text-xs text-gray-400">
            Metode Inferensi: Mamdani | Defuzzifikasi: Centroid of Area (CoA)
        </div>
    </x-card>
</div>
