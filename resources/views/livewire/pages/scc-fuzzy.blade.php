<div>
    <x-header title="Membership Function" subtitle="Fungsi keanggotaan Fuzzy Logic SCC" separator />
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card title="Input: Error Tegangan (e)" shadow>
            <p class="text-sm text-gray-400 mb-3">e = Vref - Vbat | Range: -3V sampai +3V</p>
            <div class="space-y-2 text-sm">
                @foreach([['NB','Negatif Besar','-3 s/d -2','badge-error'],['NS','Negatif Kecil','-3 s/d 0','badge-warning'],['ZO','Nol','-1 s/d +1','badge-info'],['PS','Positif Kecil','0 s/d +3','badge-warning'],['PB','Positif Besar','+2 s/d +3','badge-error']] as [$label,$name,$range,$color])
                <div class="flex items-center gap-3">
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
                <div class="flex items-center gap-3">
                    <span class="badge {{ $color }} w-10">{{ $label }}</span>
                    <span class="flex-1">{{ $name }}</span>
                    <span class="text-gray-400 text-xs">{{ $range }}</span>
                </div>
                @endforeach
            </div>
        </x-card>
        <x-card title="Output: Duty Cycle PWM" shadow class="lg:col-span-2">
            <p class="text-sm text-gray-400 mb-3">Range output: 0% sampai 100%</p>
            <div class="grid grid-cols-5 gap-2 text-sm">
                @foreach([['NB','0–10%','badge-error'],['NS','20–40%','badge-warning'],['ZO','45–55%','badge-info'],['PS','60–80%','badge-warning'],['PB','90–100%','badge-success']] as [$label,$range,$color])
                <div class="text-center p-3 rounded border border-base-300">
                    <span class="badge {{ $color }} mb-2">{{ $label }}</span>
                    <div class="text-xs text-gray-400">{{ $range }}</div>
                </div>
                @endforeach
            </div>
        </x-card>
    </div>
</div>
