<div class="scc-page">
    <section class="scc-page-hero">
        <div class="scc-eyebrow">Fuzzy Logic</div>
        <h1 class="mt-3 text-3xl font-semibold text-white">Fungsi Keanggotaan Fuzzy</h1>
        <p class="mt-2 max-w-2xl text-sm text-slate-300">Kategori input dan output fuzzy disusun sebagai peta keputusan agar hubungan antara kondisi panel, kondisi baterai, error tegangan, perubahan error, dan duty cycle lebih cepat dipahami.</p>
    </section>

    <div class="scc-note">
        <div class="flex flex-wrap items-center gap-2">
            <span class="scc-fuzzy-chip">Demo Mode / Simulasi BMKG</span>
            <span>Cuaca/BMKG memengaruhi simulasi <b>Vpv</b> dan <b>Ipv</b>, bukan menjadi input fuzzy langsung.</span>
        </div>
        <div class="mt-2 text-sm text-slate-400">Keputusan fuzzy tetap berbasis error, delta error, dan kondisi charging, lalu dipetakan sebagai <b>fuzzy rule-based dengan output duty cycle diskrit</b>.</div>
        <div class="mt-2 text-sm text-slate-400">Load Management DC adalah simulasi proteksi beban berbasis <b>SoC</b> dan kondisi daya panel. Bagian ini tidak mengubah rule fuzzy charging dan belum mengontrol hardware asli.</div>
    </div>

    @php
        $errorSets = [
            ['label' => 'NB', 'name' => 'Negatif Besar', 'range' => '<= -0.60 V', 'tone' => 'danger', 'width' => 18],
            ['label' => 'NS', 'name' => 'Negatif Kecil', 'range' => '-0.60 s/d -0.20 V', 'tone' => 'warning', 'width' => 21],
            ['label' => 'ZO', 'name' => 'Nol', 'range' => '-0.20 s/d +0.20 V', 'tone' => 'info', 'width' => 22],
            ['label' => 'PS', 'name' => 'Positif Kecil', 'range' => '+0.20 s/d +0.60 V', 'tone' => 'warning', 'width' => 21],
            ['label' => 'PB', 'name' => 'Positif Besar', 'range' => '>= +0.60 V', 'tone' => 'success', 'width' => 18],
        ];
        $deltaSets = [
            ['label' => 'NB', 'name' => 'Turun Cepat', 'range' => '<= -0.20', 'tone' => 'danger', 'width' => 18],
            ['label' => 'NS', 'name' => 'Turun Pelan', 'range' => '-0.20 s/d -0.05', 'tone' => 'warning', 'width' => 21],
            ['label' => 'ZO', 'name' => 'Stabil', 'range' => '-0.05 s/d +0.05', 'tone' => 'info', 'width' => 22],
            ['label' => 'PS', 'name' => 'Naik Pelan', 'range' => '+0.05 s/d +0.20', 'tone' => 'warning', 'width' => 21],
            ['label' => 'PB', 'name' => 'Naik Cepat', 'range' => '>= +0.20', 'tone' => 'success', 'width' => 18],
        ];
        $outputSets = [
            ['label' => 'NB', 'value' => '5%', 'tone' => 'danger', 'position' => 5],
            ['label' => 'NS', 'value' => '22%', 'tone' => 'warning', 'position' => 22],
            ['label' => 'ZO', 'value' => '45%', 'tone' => 'info', 'position' => 45],
            ['label' => 'PS', 'value' => '70%', 'tone' => 'warning', 'position' => 70],
            ['label' => 'PB', 'value' => '92%', 'tone' => 'success', 'position' => 92],
        ];
    @endphp

    <div class="scc-fuzzy-overview">
        <div class="scc-fuzzy-concept">
            <div class="scc-fuzzy-concept-icon bg-blue-500/15 text-blue-200">
                <x-icon name="o-minus-circle" class="h-6 w-6" />
            </div>
            <div>
                <div class="font-semibold text-white">Error Tegangan</div>
                <div class="mt-1 text-sm text-slate-400">e = Vref - Vbat, menunjukkan jarak baterai terhadap target tegangan.</div>
            </div>
        </div>
        <div class="scc-fuzzy-concept">
            <div class="scc-fuzzy-concept-icon bg-violet-500/15 text-violet-200">
                <x-icon name="o-arrow-trending-up" class="h-6 w-6" />
            </div>
            <div>
                <div class="font-semibold text-white">Delta Error</div>
                <div class="mt-1 text-sm text-slate-400">de = e(t) - e(t-1), membaca arah perubahan kondisi charging.</div>
            </div>
        </div>
        <div class="scc-fuzzy-concept">
            <div class="scc-fuzzy-concept-icon bg-emerald-500/15 text-emerald-200">
                <x-icon name="o-cpu-chip" class="h-6 w-6" />
            </div>
            <div>
                <div class="font-semibold text-white">Duty Cycle PWM</div>
                <div class="mt-1 text-sm text-slate-400">Output fuzzy rule-based mengatur aksi PWM diskrit pada buck converter.</div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <x-card title="Input Fuzzy pada SCC" shadow>
            <div class="space-y-3 text-sm">
                <p>Input utama fuzzy saat ini adalah <b>error tegangan baterai</b> terhadap target charging dan <b>delta error</b> atau perubahan error antar pembacaan.</p>
                <p>Kondisi charging juga tercermin dari data listrik sistem seperti <b>Vbat</b>, <b>Vpv</b>, <b>Ipv</b>, <b>SoC</b>, dan fase charging. Data tersebut membantu sistem menentukan apakah energi panel cukup, baterai masih rendah, atau baterai sudah mendekati penuh.</p>
                <p>Cuaca/BMKG tidak menjadi input fuzzy langsung. BMKG dipakai sebagai konteks simulasi panel: perubahan cuaca memengaruhi estimasi <b>Vpv</b> dan <b>Ipv</b>, lalu fuzzy merespons perubahan nilai listrik tersebut.</p>
            </div>
        </x-card>

        <x-card title="Output Fuzzy" shadow>
            <div class="space-y-3 text-sm">
                <p>Sistem ini menggunakan <b>fuzzy rule-based dengan output duty cycle diskrit</b>. Output fuzzy berupa label keputusan duty cycle seperti NB, NS, ZO, PS, dan PB yang dipetakan ke nilai PWM tertentu.</p>
                <p>Nilai duty cycle PWM menentukan besar kecilnya aksi buck converter, sedangkan mode charging menunjukkan konteks operasinya: <b>Bulk</b>, <b>Absorption</b>, <b>Float</b>, atau <b>Standby</b>.</p>
                <p>Kontrol beban DC berada di luar fuzzy charging. Status <b>ON</b>, <b>LIMITED</b>, atau <b>OFF</b> ditentukan oleh proteksi sederhana dari SoC baterai dan kecukupan daya panel.</p>
                <p>Dengan output diskrit, keputusan sistem tetap mudah dijelaskan saat demo: duty dinaikkan, ditahan, diturunkan, atau dimatikan saat panel tidak cukup.</p>
            </div>
        </x-card>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <x-card title="Input: Error Tegangan (e)" shadow>
            <div class="scc-membership-card">
                <div class="scc-membership-formula">e = Vref - Vbat</div>
                <div class="scc-membership-axis">
                    @foreach($errorSets as $set)
                        <div class="scc-membership-segment scc-tone-{{ $set['tone'] }}" style="width: {{ $set['width'] }}%">
                            <span>{{ $set['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="scc-membership-list">
                    @foreach($errorSets as $set)
                        <div class="scc-membership-row">
                            <span class="scc-membership-badge scc-tone-{{ $set['tone'] }}">{{ $set['label'] }}</span>
                            <span class="font-semibold text-white">{{ $set['name'] }}</span>
                            <span class="text-sm text-slate-400">{{ $set['range'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-card>

        <x-card title="Input: Delta Error (de)" shadow>
            <div class="scc-membership-card">
                <div class="scc-membership-formula">de = e(t) - e(t-1)</div>
                <div class="scc-membership-axis">
                    @foreach($deltaSets as $set)
                        <div class="scc-membership-segment scc-tone-{{ $set['tone'] }}" style="width: {{ $set['width'] }}%">
                            <span>{{ $set['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="scc-membership-list">
                    @foreach($deltaSets as $set)
                        <div class="scc-membership-row">
                            <span class="scc-membership-badge scc-tone-{{ $set['tone'] }}">{{ $set['label'] }}</span>
                            <span class="font-semibold text-white">{{ $set['name'] }}</span>
                            <span class="text-sm text-slate-400">{{ $set['range'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-card>
    </div>

    <x-card title="Output: Duty Cycle PWM" shadow>
        <div class="scc-duty-scale">
            <div class="scc-duty-track">
                @foreach($outputSets as $set)
                    <div class="scc-duty-point scc-tone-{{ $set['tone'] }}" style="left: {{ $set['position'] }}%">
                        <span>{{ $set['label'] }}</span>
                        <b>{{ $set['value'] }}</b>
                    </div>
                @endforeach
            </div>
            <div class="scc-duty-caption">
                <span>0% · pengisian ditahan</span>
                <span>100% · pengisian didorong kuat</span>
            </div>
        </div>
    </x-card>

    <x-card title="Cara Kerja Rule" shadow>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="scc-interpret-card">
                <div class="scc-interpret-label">Baterai rendah</div>
                <div class="mt-2 text-sm text-slate-300">Jika baterai masih jauh dari target dan panel cukup kuat, rule fuzzy menaikkan duty cycle agar fase <b>Bulk</b> dapat mengisi baterai lebih cepat.</div>
            </div>
            <div class="scc-interpret-card">
                <div class="scc-interpret-label">Mendekati penuh</div>
                <div class="mt-2 text-sm text-slate-300">Jika baterai mendekati target, duty cycle ditahan atau diturunkan agar sistem masuk <b>Absorption</b> atau <b>Float</b> dengan lebih aman.</div>
            </div>
            <div class="scc-interpret-card">
                <div class="scc-interpret-label">Panel lemah</div>
                <div class="mt-2 text-sm text-slate-300">Jika panel melemah karena mendung atau hujan, duty cycle dibatasi. Bila energi panel tidak cukup, sistem masuk <b>Standby</b>.</div>
            </div>
            <div class="scc-interpret-card">
                <div class="scc-interpret-label">Transisi halus</div>
                <div class="mt-2 text-sm text-slate-300">Perubahan duty cycle dilakukan bertahap melalui label fuzzy agar charging tidak kasar seperti kontrol ON/OFF berbasis threshold tetap.</div>
            </div>
        </div>
    </x-card>

    <x-card title="Contoh Keputusan Fuzzy" shadow>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="scc-interpret-card">
                <div class="scc-interpret-label">Cerah + baterai rendah</div>
                <div class="mt-2 text-sm text-slate-300">Saat baterai rendah dan cuaca cerah membuat <b>Vpv/Ipv</b> kuat, sistem memilih <b>Bulk</b> dengan duty cycle tinggi.</div>
            </div>
            <div class="scc-interpret-card">
                <div class="scc-interpret-label">Berawan + baterai rendah</div>
                <div class="mt-2 text-sm text-slate-300">Saat baterai rendah tetapi panel berkurang karena berawan, duty cycle tetap dinaikkan namun dibatasi kemampuan panel.</div>
            </div>
            <div class="scc-interpret-card">
                <div class="scc-interpret-label">Baterai hampir penuh</div>
                <div class="mt-2 text-sm text-slate-300">Saat <b>Vbat</b> atau <b>SoC</b> mendekati penuh, sistem masuk <b>Absorption</b> atau <b>Float</b> dan duty cycle diturunkan.</div>
            </div>
            <div class="scc-interpret-card">
                <div class="scc-interpret-label">Panel tidak cukup</div>
                <div class="mt-2 text-sm text-slate-300">Saat <b>Vpv</b> tidak cukup untuk charging, sistem masuk <b>Standby</b> dan duty cycle menjadi <b>0%</b>.</div>
            </div>
        </div>
    </x-card>

    <x-card title="Skenario Demo" shadow>
        <div class="mb-4 text-sm text-slate-300">
            Skenario ini membantu menjelaskan bagaimana perubahan cuaca dan kondisi baterai memengaruhi keputusan fuzzy rule-based, output duty cycle diskrit, dan mode charging saat presentasi.
        </div>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="scc-interpret-card">
                <div class="scc-interpret-label">1. Cuaca cerah</div>
                <div class="mt-2 text-sm text-slate-300">Panel menghasilkan daya lebih tinggi, <b>Vpv</b> dan <b>Ipv</b> meningkat, baterai masih rendah, sistem masuk <b>Bulk</b>, dan duty cycle PWM tinggi.</div>
            </div>
            <div class="scc-interpret-card">
                <div class="scc-interpret-label">2. Cuaca berawan</div>
                <div class="mt-2 text-sm text-slate-300">Daya panel menurun, <b>Vpv/Ipv</b> turun, fuzzy tetap mencoba charging jika baterai rendah, tetapi duty cycle dinaikkan secara terbatas sesuai kemampuan panel.</div>
            </div>
            <div class="scc-interpret-card">
                <div class="scc-interpret-label">3. Baterai mendekati penuh</div>
                <div class="mt-2 text-sm text-slate-300">Tegangan baterai mendekati target, sistem masuk <b>Absorption</b> atau <b>Float</b>, lalu duty cycle diturunkan atau ditahan agar charging tidak agresif.</div>
            </div>
            <div class="scc-interpret-card">
                <div class="scc-interpret-label">4. Panel tidak cukup / hujan</div>
                <div class="mt-2 text-sm text-slate-300">Daya panel sangat rendah, charging tidak optimal, sistem masuk <b>Standby</b>, dan duty cycle menjadi <b>0%</b> atau sangat rendah.</div>
            </div>
        </div>
    </x-card>
</div>
