<div class="scc-page">
    <section class="scc-page-hero">
        <div class="scc-eyebrow">Project Brief</div>
        <h1 class="mt-3 text-3xl font-semibold text-white">Tentang Project</h1>
        <p class="mt-2 max-w-2xl text-sm text-slate-300">Ringkasan project Solar Charge Controller berbasis Fuzzy Logic dengan tampilan dokumentasi yang lebih profesional dan seragam dengan modul monitoring.</p>
    </section>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        <x-card title="Latar Belakang" shadow>
            <div class="space-y-3 text-sm">
                <p>Project ini merupakan implementasi <b>Solar Charge Controller (SCC)</b> berbasis <b>Fuzzy Logic Mamdani</b> untuk kebutuhan akademik pada bidang elektronika daya dan sistem kendali.</p>
                <p>Permasalahan utama pada pengisian baterai panel surya adalah menjaga agar proses pengisian tetap efisien, stabil, dan aman terhadap kondisi <b>overcharge</b> maupun pengisian yang terlalu lemah saat kondisi panel berubah.</p>
            </div>
        </x-card>

        <x-card title="Tujuan Sistem" shadow>
            <div class="space-y-3 text-sm">
                <p>Tujuan sistem adalah menghasilkan pengendali charging yang adaptif terhadap perubahan tegangan baterai dan dinamika error, sehingga duty cycle PWM dapat disesuaikan secara lebih halus dibanding kendali konvensional berbasis threshold tetap.</p>
                <p>Sistem monitoring web mendukung tujuan ini dengan menyediakan visualisasi data real-time, histori pengukuran, analisis fuzzy, dan export dataset.</p>
            </div>
        </x-card>

        <x-card title="Cara Kerja Sistem" shadow>
            <div class="space-y-3 text-sm">
                <p>Panel surya menjadi sumber energi utama yang dihubungkan ke baterai melalui <b>buck converter</b>. Mikrokontroler membaca parameter listrik utama seperti tegangan panel, arus panel, tegangan baterai, arus baterai, state of charge, dan duty cycle PWM.</p>
                <p>Nilai <b>error (e)</b> dan <b>delta error (de)</b> menjadi masukan ke sistem fuzzy. Berdasarkan basis aturan, sistem menentukan besar duty cycle PWM untuk mengatur proses charging pada fase <b>Bulk</b>, <b>Absorption</b>, dan <b>Float</b>.</p>
            </div>
        </x-card>

        <x-card title="Peran Fuzzy Logic" shadow>
            <div class="space-y-3 text-sm">
                <p>Fuzzy logic dipakai karena perilaku pengisian baterai tidak selalu linear. Dengan representasi linguistik seperti NB, NS, ZO, PS, dan PB, sistem dapat membuat keputusan yang lebih fleksibel saat error kecil, besar, atau sedang berubah.</p>
                <p>Metode inferensi yang digunakan adalah <b>Mamdani</b> dan hasil akhirnya didefuzzifikasi menggunakan metode <b>Centroid of Area</b>.</p>
            </div>
        </x-card>

        <x-card title="Parameter Monitoring" shadow>
            <div class="scc-info-list text-sm">
                <div><span class="text-gray-400">Tegangan panel surya</span><span class="font-bold">Vpv</span></div>
                <div><span class="text-gray-400">Arus panel surya</span><span class="font-bold">Ipv</span></div>
                <div><span class="text-gray-400">Tegangan baterai</span><span class="font-bold">Vbat</span></div>
                <div><span class="text-gray-400">Arus baterai</span><span class="font-bold">Ibat</span></div>
                <div><span class="text-gray-400">State of Charge</span><span class="font-bold">SoC</span></div>
                <div><span class="text-gray-400">Duty cycle PWM</span><span class="font-bold">Duty Cycle</span></div>
                <div><span class="text-gray-400">Fase charging</span><span class="font-bold">Bulk / Absorption / Float</span></div>
                <div><span class="text-gray-400">Label fuzzy</span><span class="font-bold">e dan de</span></div>
            </div>
        </x-card>

        <x-card title="Manfaat Sistem" shadow>
            <div class="space-y-3 text-sm">
                <p>Sistem ini bermanfaat sebagai media evaluasi performa pengisian baterai, validasi rule fuzzy, dokumentasi eksperimen, dan sarana presentasi hasil monitoring kepada dosen atau penguji.</p>
                <p>Dari sisi akademik, dashboard ini membantu menjembatani antara implementasi kendali di perangkat keras dan analisis data di sisi perangkat lunak.</p>
            </div>
        </x-card>

        <x-card title="Arsitektur Sistem" shadow>
            <div class="space-y-3 text-sm">
                <p><b>Panel Surya</b> mengisi <b>baterai</b> melalui <b>buck converter</b> yang dikendalikan PWM. <b>ESP32</b> membaca sensor, menghitung parameter fuzzy, lalu mengirim data ke backend Laravel melalui API.</p>
                <p>Laravel menyimpan data ke database, sedangkan Livewire dan Blade menampilkan data tersebut dalam halaman monitoring, histori, analisis fuzzy, dan export.</p>
            </div>
        </x-card>

        <x-card title="Spesifikasi Implementasi" shadow class="lg:col-span-2">
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="scc-info-list text-sm">
                    <div><span class="text-gray-400">Mikrokontroler</span><span class="font-bold">ESP32</span></div>
                    <div><span class="text-gray-400">Panel Surya</span><span class="font-bold">10Wp Monocrystalline</span></div>
                    <div><span class="text-gray-400">Baterai</span><span class="font-bold">Lead-Acid 12V</span></div>
                    <div><span class="text-gray-400">Topologi Konverter</span><span class="font-bold">Buck Converter</span></div>
                </div>
                <div class="scc-info-list text-sm">
                    <div><span class="text-gray-400">Metode Fuzzy</span><span class="font-bold">Mamdani</span></div>
                    <div><span class="text-gray-400">Defuzzifikasi</span><span class="font-bold">Centroid of Area</span></div>
                    <div><span class="text-gray-400">Jumlah Aturan</span><span class="font-bold">25 aturan</span></div>
                    <div><span class="text-gray-400">Frekuensi PWM</span><span class="font-bold">20 kHz</span></div>
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
