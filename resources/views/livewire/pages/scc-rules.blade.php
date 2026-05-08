<div class="scc-page">
    <section class="scc-page-hero">
        <div class="scc-eyebrow">Fuzzy Rules</div>
        <h1 class="mt-3 text-3xl font-semibold text-white">Basis Aturan Fuzzy</h1>
        <p class="mt-2 max-w-2xl text-sm text-slate-300">Tabel 25 aturan IF-THEN ditampilkan dalam surface gelap yang lebih kontras agar relasi antar kondisi error dan delta error lebih cepat dianalisis.</p>
    </section>
    <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <div class="scc-note">
            <div class="font-semibold text-white">Cara membaca tabel</div>
            <div class="mt-2">Pilih baris berdasarkan kategori <b>error (e)</b> dan kolom berdasarkan kategori <b>delta error (de)</b>. Titik pertemuan keduanya menunjukkan kategori output duty cycle PWM yang dipakai pengendali fuzzy.</div>
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="badge badge-error">NB</span>
                <span class="badge badge-warning">NS</span>
                <span class="badge badge-info">ZO</span>
                <span class="badge badge-warning">PS</span>
                <span class="badge badge-success">PB</span>
            </div>
            <div class="mt-3 text-xs text-slate-400">NB = Negatif Besar, NS = Negatif Kecil, ZO = Nol, PS = Positif Kecil, PB = Positif Besar.</div>
        </div>
        <div class="rounded-2xl border border-white/8 bg-slate-950/35 p-4">
            <div class="text-sm font-semibold text-white">Legenda warna output</div>
            <div class="mt-3 flex flex-wrap gap-3 text-sm">
                <span class="badge badge-error">NB: Duty sangat rendah</span>
                <span class="badge badge-warning">NS: Duty rendah</span>
                <span class="badge badge-info">ZO: Duty sedang</span>
                <span class="badge badge-warning">PS: Duty agak tinggi</span>
                <span class="badge badge-success">PB: Duty tinggi</span>
            </div>
        </div>
    </div>
    <x-card shadow>
        <p class="text-sm text-gray-400 mb-4">Baris = Error (e) | Kolom = Delta Error (de) | Isi = Output Duty Cycle</p>
        <div class="scc-table-wrap overflow-x-auto">
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
            Metode: fuzzy rule-based dengan output duty cycle diskrit
        </div>
    </x-card>
</div>
