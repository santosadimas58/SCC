<div class="scc-page">
    <section class="scc-page-hero">
        <div class="scc-eyebrow">Fuzzy Logic</div>
        <h1 class="mt-3 text-3xl font-semibold text-white">Fungsi Keanggotaan Fuzzy</h1>
        <p class="mt-2 max-w-2xl text-sm text-slate-300">Visualisasi kategori input dan output fuzzy disusun dengan istilah teknis bahasa Indonesia agar lebih mudah dipahami sebagai dokumentasi pengendali SCC.</p>
    </section>
    <div class="grid gap-6 xl:grid-cols-3">
        <div class="scc-note">
            <div class="font-semibold text-white">Input error tegangan (e)</div>
            <div class="mt-2">Menyatakan selisih antara tegangan referensi baterai dengan tegangan baterai aktual. Nilai ini menunjukkan apakah sistem perlu menaikkan atau menurunkan aksi kendali.</div>
        </div>
        <div class="scc-note">
            <div class="font-semibold text-white">Input delta error (de)</div>
            <div class="mt-2">Menyatakan perubahan error dari waktu sebelumnya ke waktu sekarang. Informasi ini membantu fuzzy logic membaca tren pengisian, bukan hanya kondisi sesaat.</div>
        </div>
        <div class="scc-note">
            <div class="font-semibold text-white">Output duty cycle PWM</div>
            <div class="mt-2">Duty cycle menentukan besar kecilnya aksi PWM pada konverter buck. Nilai kecil menahan pengisian, nilai besar mendorong pengisian lebih kuat.</div>
        </div>
    </div>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Input: Error Tegangan (e)" shadow>
            <p class="text-sm text-gray-400 mb-3">e = Vref - Vbat | Range: -3V sampai +3V</p>
            <div class="space-y-2 text-sm">
                @foreach([['NB','Negatif Besar','-3 s/d -2','badge-error'],['NS','Negatif Kecil','-3 s/d 0','badge-warning'],['ZO','Nol','-1 s/d +1','badge-info'],['PS','Positif Kecil','0 s/d +3','badge-warning'],['PB','Positif Besar','+2 s/d +3','badge-error']] as [$label,$name,$range,$color])
                <div class="flex items-center gap-3 rounded-2xl border border-white/8 bg-slate-950/35 px-4 py-3">
                    <span class="badge {{ $color }} w-10">{{ $label }}</span>
                    <span class="flex-1">{{ $name }}</span>
                    <span class="text-gray-400 text-xs">{{ $range }} V</span>
                </div>
                @endforeach
            </div>
        </x-card>
        <x-card title="Input: Delta Error (de)" shadow>
            <p class="text-sm text-gray-400 mb-3">de = e(t) - e(t-1) | Range: -1 sampai +1</p>
            <div class="space-y-2 text-sm">
                @foreach([['NB','Negatif Besar','-1 s/d -0.6','badge-error'],['NS','Negatif Kecil','-1 s/d 0','badge-warning'],['ZO','Nol','-0.3 s/d +0.3','badge-info'],['PS','Positif Kecil','0 s/d +1','badge-warning'],['PB','Positif Besar','+0.6 s/d +1','badge-error']] as [$label,$name,$range,$color])
                <div class="flex items-center gap-3 rounded-2xl border border-white/8 bg-slate-950/35 px-4 py-3">
                    <span class="badge {{ $color }} w-10">{{ $label }}</span>
                    <span class="flex-1">{{ $name }}</span>
                    <span class="text-gray-400 text-xs">{{ $range }}</span>
                </div>
                @endforeach
            </div>
        </x-card>
        <x-card title="Output: Duty Cycle PWM" shadow class="lg:col-span-2">
            <p class="text-sm text-gray-400 mb-3">Range output: 0% sampai 100%</p>
            <div class="grid grid-cols-2 gap-3 text-sm md:grid-cols-5">
                @foreach([['NB','0–10%','badge-error'],['NS','20–40%','badge-warning'],['ZO','45–55%','badge-info'],['PS','60–80%','badge-warning'],['PB','90–100%','badge-success']] as [$label,$range,$color])
                <div class="text-center p-3 rounded-2xl border border-white/10 bg-slate-950/35">
                    <span class="badge {{ $color }} mb-2">{{ $label }}</span>
                    <div class="text-xs text-gray-400">{{ $range }}</div>
                </div>
                @endforeach
            </div>
        </x-card>
        <x-card title="Contoh Interpretasi Sederhana" shadow class="lg:col-span-2">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-white/8 bg-slate-950/35 p-4 text-sm">
                    <div class="font-semibold text-white">Kasus 1</div>
                    <div class="mt-2 text-slate-300">Jika <b>e = PB</b> dan <b>de = ZO</b>, baterai masih jauh di bawah target. Sistem cenderung memberi duty cycle besar agar pengisian dipercepat.</div>
                </div>
                <div class="rounded-2xl border border-white/8 bg-slate-950/35 p-4 text-sm">
                    <div class="font-semibold text-white">Kasus 2</div>
                    <div class="mt-2 text-slate-300">Jika <b>e = ZO</b> dan <b>de = ZO</b>, tegangan mendekati target dan stabil. Sistem menjaga duty cycle pada tingkat menengah.</div>
                </div>
                <div class="rounded-2xl border border-white/8 bg-slate-950/35 p-4 text-sm">
                    <div class="font-semibold text-white">Kasus 3</div>
                    <div class="mt-2 text-slate-300">Jika <b>e = NB</b>, baterai sudah terlalu tinggi dibanding referensi. Duty cycle diturunkan agar pengisian tidak berlebih.</div>
                </div>
            </div>
        </x-card>
    </div>
</div>
