<div>
    <x-header title="Tentang Project" subtitle="Solar Charge Controller berbasis Fuzzy Logic" separator />
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <x-card title="Deskripsi Project" shadow>
            <div class="space-y-3 text-sm">
                <p>Project ini merupakan implementasi <b>Solar Charge Controller (SCC)</b> berbasis <b>Fuzzy Logic</b> untuk mata kuliah Seminar Teknik Elektro.</p>
                <p>SCC berfungsi mengatur pengisian baterai dari panel surya agar tidak terjadi <b>overcharge</b> maupun <b>overdischarge</b> dengan menggunakan algoritma Fuzzy Logic Mamdani.</p>
            </div>
        </x-card>

        <x-card title="Spesifikasi Sistem" shadow>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between border-b border-base-300 pb-1">
                    <span class="text-gray-400">Mikrokontroler</span><span class="font-bold">ESP32</span>
                </div>
                <div class="flex justify-between border-b border-base-300 pb-1">
                    <span class="text-gray-400">Panel Surya</span><span class="font-bold">10Wp Monocrystalline</span>
                </div>
                <div class="flex justify-between border-b border-base-300 pb-1">
                    <span class="text-gray-400">Baterai</span><span class="font-bold">Lead-Acid 12V</span>
                </div>
                <div class="flex justify-between border-b border-base-300 pb-1">
                    <span class="text-gray-400">Topologi Konverter</span><span class="font-bold">Buck Converter</span>
                </div>
                <div class="flex justify-between border-b border-base-300 pb-1">
                    <span class="text-gray-400">Metode Fuzzy</span><span class="font-bold">Mamdani</span>
                </div>
                <div class="flex justify-between border-b border-base-300 pb-1">
                    <span class="text-gray-400">Defuzzifikasi</span><span class="font-bold">Centroid of Area</span>
                </div>
                <div class="flex justify-between border-b border-base-300 pb-1">
                    <span class="text-gray-400">Jumlah Rule</span><span class="font-bold">25 Rule</span>
                </div>
                <div class="flex justify-between border-b border-base-300 pb-1">
                    <span class="text-gray-400">Frekuensi PWM</span><span class="font-bold">20 kHz</span>
                </div>
            </div>
        </x-card>

        <x-card title="Tech Stack Web" shadow class="lg:col-span-2">
            <div class="flex flex-wrap gap-2">
                <span class="badge badge-outline">Laravel 12</span>
                <span class="badge badge-outline">Livewire 4</span>
                <span class="badge badge-outline">MaryUI</span>
                <span class="badge badge-outline">SQLite</span>
                <span class="badge badge-outline">Chart.js</span>
                <span class="badge badge-outline">Tailwind CSS</span>
                <span class="badge badge-outline">Laravel Reverb</span>
            </div>
        </x-card>

    </div>
</div>
