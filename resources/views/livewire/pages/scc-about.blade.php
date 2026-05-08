<div class="scc-page">
    <section class="scc-page-hero">
        <div class="scc-eyebrow">Project Brief</div>
        <h1 class="mt-3 text-3xl font-semibold text-white">Tentang Project</h1>
        <p class="mt-2 max-w-2xl text-sm text-slate-300">Ringkasan project Solar Charge Controller berbasis fuzzy rule-based untuk menjaga proses charging baterai tetap adaptif terhadap perubahan daya panel dan kondisi baterai.</p>
    </section>

    <x-card title="Diagram Arsitektur Sistem" shadow>
        <div class="scc-architecture">
            <div class="scc-architecture-band">
                <div class="scc-architecture-label">Lapisan Perangkat</div>
                <div class="scc-architecture-row">
                    <div class="scc-architecture-node">
                        <div class="scc-architecture-icon bg-amber-500/15 text-amber-200">
                            <x-icon name="o-sun" class="h-6 w-6" />
                        </div>
                        <div>
                            <div class="scc-architecture-title">Panel Surya</div>
                            <div class="scc-architecture-copy">Sumber Vpv dan Ipv</div>
                        </div>
                    </div>
                    <div class="scc-architecture-connector">
                        <span>→</span>
                    </div>
                    <div class="scc-architecture-node">
                        <div class="scc-architecture-icon bg-blue-500/15 text-blue-200">
                            <x-icon name="o-cpu-chip" class="h-6 w-6" />
                        </div>
                        <div>
                            <div class="scc-architecture-title">Buck Converter</div>
                            <div class="scc-architecture-copy">Regulasi charging via PWM</div>
                        </div>
                    </div>
                    <div class="scc-architecture-connector">
                        <span>→</span>
                    </div>
                    <div class="scc-architecture-node">
                        <div class="scc-architecture-icon bg-emerald-500/15 text-emerald-200">
                            <x-icon name="o-battery-100" class="h-6 w-6" />
                        </div>
                        <div>
                            <div class="scc-architecture-title">Baterai 12V</div>
                            <div class="scc-architecture-copy">Vbat, Ibat, dan SoC</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="scc-architecture-down">
                <span>↓</span>
            </div>

            <div class="scc-architecture-band scc-architecture-band-soft">
                <div class="scc-architecture-label">Lapisan Kendali dan Monitoring</div>
                <div class="scc-architecture-row">
                    <div class="scc-architecture-node">
                        <div class="scc-architecture-icon bg-violet-500/15 text-violet-200">
                            <x-icon name="o-adjustments-horizontal" class="h-6 w-6" />
                        </div>
                        <div>
                            <div class="scc-architecture-title">ESP32 + Fuzzy</div>
                            <div class="scc-architecture-copy">Hitung e, de, fase, duty cycle</div>
                        </div>
                    </div>
                    <div class="scc-architecture-connector">
                        <span>→</span>
                    </div>
                    <div class="scc-architecture-node">
                        <div class="scc-architecture-icon bg-sky-500/15 text-sky-200">
                            <x-icon name="o-cloud-arrow-up" class="h-6 w-6" />
                        </div>
                        <div>
                            <div class="scc-architecture-title">Laravel API</div>
                            <div class="scc-architecture-copy">Validasi token dan simpan data</div>
                        </div>
                    </div>
                    <div class="scc-architecture-connector">
                        <span>→</span>
                    </div>
                    <div class="scc-architecture-node">
                        <div class="scc-architecture-icon bg-indigo-500/15 text-indigo-200">
                            <x-icon name="o-chart-bar" class="h-6 w-6" />
                        </div>
                        <div>
                            <div class="scc-architecture-title">Dashboard</div>
                            <div class="scc-architecture-copy">Real-time, histori, export</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-card>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        <x-card title="Latar Belakang" shadow>
            <div class="space-y-3 text-sm">
                <p>Project ini merupakan implementasi <b>Solar Charge Controller (SCC)</b> berbasis <b>fuzzy rule-based dengan output duty cycle diskrit</b> untuk kebutuhan akademik pada bidang elektronika daya dan sistem kendali.</p>
                <p>Permasalahan utama pada pengisian baterai panel surya adalah menjaga agar proses pengisian tetap efisien, stabil, dan aman terhadap kondisi <b>overcharge</b> maupun pengisian yang terlalu lemah saat daya panel berubah karena intensitas matahari.</p>
            </div>
        </x-card>

        <x-card title="Masalah Kontrol Konvensional" shadow>
            <div class="space-y-3 text-sm">
                <p>SCC konvensional yang berbasis threshold tetap cenderung mengambil keputusan dari batas ON/OFF atau nilai tegangan tertentu. Pendekatan ini sederhana, tetapi responsnya kurang adaptif saat kondisi panel dan baterai berubah cepat.</p>
                <p>Saat cuaca terik dan panel menghasilkan daya tinggi, charging bisa terlalu agresif jika baterai sudah mendekati penuh. Sebaliknya, saat mendung atau berawan, output panel turun sehingga charging bisa tidak optimal. Perpindahan mode charging juga dapat terasa kasar karena bergantung pada batas tetap.</p>
            </div>
        </x-card>

        <x-card title="Tujuan Sistem" shadow>
            <div class="space-y-3 text-sm">
                <p>Tujuan sistem adalah menghasilkan pengendali charging yang adaptif terhadap perubahan tegangan baterai dan dinamika error, sehingga duty cycle PWM dapat disesuaikan secara bertahap dibanding kendali konvensional berbasis threshold tetap.</p>
                <p>Sistem monitoring web mendukung tujuan ini dengan menyediakan visualisasi data real-time, histori pengukuran, analisis fuzzy, dan export dataset.</p>
            </div>
        </x-card>

        <x-card title="Cara Kerja Sistem" shadow>
            <div class="space-y-3 text-sm">
                <p>Panel surya menjadi sumber energi utama yang dihubungkan ke baterai melalui <b>buck converter</b>. Mikrokontroler membaca parameter listrik utama seperti tegangan panel, arus panel, tegangan baterai, arus baterai, state of charge, dan duty cycle PWM.</p>
                <p>Alur kendalinya adalah <b>cuaca / intensitas matahari</b> memengaruhi <b>Vpv</b> dan <b>Ipv</b> panel, lalu fuzzy membaca kondisi charging dari error tegangan dan perubahan error. Setelah itu duty cycle PWM disesuaikan agar output ke baterai lebih aman dan stabil.</p>
            </div>
        </x-card>

        <x-card title="Solusi Fuzzy Rule-Based" shadow>
            <div class="space-y-3 text-sm">
                <p>Fuzzy logic dipakai karena perilaku pengisian baterai tidak selalu linear. Dengan representasi linguistik seperti NB, NS, ZO, PS, dan PB, sistem dapat membuat keputusan yang lebih fleksibel saat error kecil, besar, atau sedang berubah.</p>
                <p>Sistem ini menggunakan <b>fuzzy rule-based dengan output duty cycle diskrit</b>. Rule fuzzy menentukan apakah aksi PWM perlu diturunkan, ditahan, atau dinaikkan secara bertahap pada fase <b>Bulk</b>, <b>Absorption</b>, <b>Float</b>, maupun <b>Standby</b>.</p>
            </div>
        </x-card>

        <x-card title="Hubungan BMKG dan Simulasi Panel" shadow>
            <div class="space-y-3 text-sm">
                <p>Data BMKG tidak langsung mengontrol baterai. Data cuaca digunakan sebagai konteks simulasi kondisi lingkungan yang memengaruhi potensi daya panel surya.</p>
                <p>Kondisi cerah menggambarkan potensi <b>Vpv</b> dan <b>Ipv</b> lebih tinggi, kondisi berawan membuat daya panel turun, sedangkan hujan atau mendung berat dapat membuat charging melemah atau masuk <b>Standby</b> karena energi panel belum cukup.</p>
            </div>
        </x-card>

        <x-card title="Demo Mode / Simulasi BMKG" shadow>
            <div class="space-y-3 text-sm">
                <p><b>Demo Mode</b> digunakan agar respon Solar Charge Controller terhadap perubahan cuaca dapat terlihat jelas saat presentasi tanpa harus menunggu perubahan cuaca nyata.</p>
                <p>BMKG digunakan sebagai konteks simulasi lingkungan, bukan sebagai pengontrol baterai secara langsung. Data cuaca membantu mensimulasikan perubahan <b>Vpv</b> dan <b>Ipv</b>, lalu sistem menampilkan bagaimana SCC merespons melalui fase charging dan duty cycle PWM.</p>
            </div>
        </x-card>

        <x-card title="Skenario Demo" shadow class="lg:col-span-2">
            <div class="mb-4 text-sm text-slate-300">
                Skenario ini digunakan untuk menunjukkan bagaimana perubahan cuaca dan kondisi baterai memengaruhi keputusan fuzzy rule-based, fase charging, dan duty cycle PWM.
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

        <x-card title="Konvensional vs Fuzzy" shadow class="lg:col-span-2">
            <div class="scc-table-wrap overflow-x-auto">
                <table class="table table-zebra w-full text-sm">
                    <thead>
                        <tr>
                            <th>Aspek</th>
                            <th>Kontrol Konvensional</th>
                            <th>Fuzzy Rule-Based</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Dasar keputusan</td>
                            <td>Threshold tetap atau logika ON/OFF.</td>
                            <td>Error tegangan, delta error, fase charging, dan rule fuzzy.</td>
                        </tr>
                        <tr>
                            <td>Respons saat panel kuat</td>
                            <td>Berisiko terlalu agresif jika baterai mendekati penuh.</td>
                            <td>Duty cycle dapat diturunkan bertahap saat baterai masuk Absorption atau Float.</td>
                        </tr>
                        <tr>
                            <td>Respons saat mendung</td>
                            <td>Charging bisa tidak optimal karena daya panel turun.</td>
                            <td>Sistem membaca kondisi panel dan baterai, lalu menyesuaikan PWM atau masuk Standby.</td>
                        </tr>
                        <tr>
                            <td>Perpindahan mode</td>
                            <td>Cenderung kasar karena berbasis batas tetap.</td>
                            <td>Lebih halus karena keputusan duty cycle dipetakan melalui rule fuzzy.</td>
                        </tr>
                    </tbody>
                </table>
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
                <div><span class="text-gray-400">Fase charging</span><span class="font-bold">Bulk / Absorption / Float / Standby</span></div>
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
                <p>Laravel menyimpan data ke database, sedangkan Livewire dan Blade menampilkan hubungan antara daya panel, kondisi baterai, keputusan fuzzy, histori, analisis performa, dan export dataset.</p>
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
                    <div><span class="text-gray-400">Metode Fuzzy</span><span class="font-bold">Rule-based diskrit</span></div>
                    <div><span class="text-gray-400">Output Kendali</span><span class="font-bold">Duty cycle PWM diskrit</span></div>
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
