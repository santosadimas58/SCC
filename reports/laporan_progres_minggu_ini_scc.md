# Laporan Progres Minggu Ini - Project SCC Monitoring

Periode: 12-18 Mei 2026  
Project: SCC Monitoring berbasis Fuzzy Logic  
Status: Siap dipakai untuk demo/pemaparan progres

## 1. Ringkasan Progres

Pada minggu ini, pengembangan project SCC Monitoring difokuskan pada penguatan nilai demo dan analisis sistem. Aplikasi tidak hanya menampilkan monitoring Solar Charge Controller, tetapi mulai dilengkapi dengan interpretasi performa, simulasi manajemen beban DC, serta bahan validasi terhadap data eksternal.

Secara umum, progres utama minggu ini mencakup:

1. Penambahan fitur Load Management DC untuk mensimulasikan prioritas beban Lampu DC, Kipas DC, dan Pompa DC.
2. Penguatan halaman Analisis Performa untuk membaca tren SoC, duty cycle, daya panel, efisiensi, dan distribusi fase charging.
3. Integrasi hasil manajemen beban ke API, database, dashboard, dan tampilan Livewire.
4. Penyusunan laporan validasi simulasi SCC terhadap data NASA POWER sebagai bahan pendukung pemaparan.
5. Penambahan dan pembaruan pengujian otomatis untuk fuzzy charging, API SCC, dan load management.

## 2. Progres Fitur Aplikasi

### 2.1 Dashboard Monitoring SCC

Dashboard masih menjadi halaman utama demo. Sistem menampilkan kondisi real-time dari data SCC, meliputi tegangan panel, arus panel, tegangan baterai, arus baterai, SoC, duty cycle PWM, fase charging, dan label fuzzy.

Pembaruan minggu ini memperkuat dashboard agar lebih relevan untuk presentasi, khususnya dengan menampilkan konteks manajemen beban dan status energi. Dengan ini, dashboard tidak hanya menjawab "berapa nilai sensor saat ini", tetapi juga "apa dampaknya terhadap beban DC".

### 2.2 Analisis Performa

Halaman Analisis Performa dikembangkan untuk memberikan ringkasan teknis yang lebih mudah dipahami saat presentasi. Data yang dianalisis berasal dari data SCC terbaru.

Komponen analisis yang sudah tersedia:

- Jumlah sampel analisis, maksimal 200 data terbaru.
- Rata-rata SoC baterai.
- Rata-rata duty cycle PWM.
- Rata-rata daya panel berdasarkan rumus `Vpv x Ipv`.
- Estimasi efisiensi rata-rata berdasarkan `Pbat / Ppanel`.
- Daya panel maksimum.
- Distribusi fase charging: Bulk, Absorption, Float, dan Standby.
- Grafik hubungan duty cycle terhadap SoC.
- Interpretasi cepat untuk menjelaskan kondisi charging, tren duty, dan performa energi panel.

Nilai tambah fitur ini adalah presenter dapat menjelaskan performa sistem dari data, bukan hanya dari tampilan sensor mentah.

### 2.3 Load Management DC

Fitur baru yang paling signifikan minggu ini adalah Load Management DC. Fitur ini mensimulasikan bagaimana energi dari SCC dapat dipakai untuk mengatur beban DC berdasarkan kondisi baterai dan panel.

Beban yang dimodelkan:

- Lampu DC, prioritas 1, daya dasar 4 W.
- Kipas DC, prioritas 2, daya dasar 8 W.
- Pompa DC, prioritas 3, daya dasar 24 W.

Logika pengambilan keputusan:

- Sistem menghitung skor energi dari SoC, daya panel, tegangan panel, fase charging, dan label fuzzy.
- Jika skor tinggi, beban dapat menyala penuh.
- Jika skor menengah, sebagian beban dibatasi.
- Jika skor rendah, beban dimatikan untuk menjaga proteksi baterai.
- Pada fase Standby, beban prioritas rendah ditahan agar energi tetap diprioritaskan untuk keamanan sistem.

Output yang ditampilkan:

- Nama beban aktif.
- Status agregat beban: ON, LIMITED, atau OFF.
- Daya beban total.
- Arus beban.
- Net power atau selisih daya panel terhadap beban.
- Status surplus/defisit energi.
- Alasan keputusan sistem.
- Riwayat 50 data terbaru untuk melihat pola beban.

Fitur ini memperluas project dari sekadar monitoring charging menjadi simulasi sistem energi DC yang lebih utuh.

### 2.4 API dan Database

Endpoint API `POST /api/scc/data` tetap menjadi jalur masuk data dari simulator atau perangkat. Pada minggu ini, hasil evaluasi load management ikut dihitung dan disimpan ketika data SCC masuk.

Field tambahan yang sudah disiapkan pada tabel `scc_data`:

- `load_name`
- `load_status`
- `load_power`
- `load_current`
- `net_power`
- `load_reason`

Dengan adanya field ini, data beban tidak hanya dihitung untuk tampilan sesaat, tetapi dapat direkam dan dianalisis kembali.

### 2.5 Validasi Simulasi dengan NASA POWER

Project juga sudah memiliki bahan laporan validasi simulasi terhadap data NASA POWER. Laporan tersebut membandingkan daya panel simulasi dengan data irradiance publik NASA POWER.

Poin penting dari hasil validasi:

- Daya panel project dihitung dari `Vpv x Ipv`.
- Data NASA POWER dipakai sebagai referensi pola irradiance.
- Karena data project berada pada mode demo dan timestamp tertentu belum selalu mewakili jam matahari efektif, hasil validasi perlu dibaca sebagai pembanding pola, bukan kalibrasi absolut.
- Hal ini justru menjadi catatan metodologis yang penting untuk disampaikan saat pemaparan: sistem sudah dapat didemokan, tetapi validasi lapangan tetap membutuhkan data real pada jam siang atau sensor irradiance.

File pendukung yang sudah tersedia:

- `reports/laporan_validasi_simulasi_scc_nasa_power.html`
- `reports/laporan_validasi_simulasi_scc_nasa_power.pdf`
- `reports/laporan_validasi_simulasi_scc_nasa_power.docx`
- `reports/laporan_validasi_simulasi_scc_nasa_power.odt`

## 3. Pengujian

Pengujian otomatis sudah dijalankan pada 18 Mei 2026.

Hasil:

- 10 test passed.
- 37 assertions passed.
- Durasi pengujian sekitar 0,71 detik.

Cakupan pengujian yang relevan:

- Unit test fuzzy charge controller.
- Unit test load management controller.
- Feature test API SCC.
- Validasi token API SCC.
- Validasi bahwa API menghitung field fuzzy dan field load management sebelum menyimpan data.

Hasil ini menunjukkan bahwa fungsi inti fuzzy charging, manajemen beban, dan API masih berjalan sesuai ekspektasi setelah penambahan fitur.

## 4. Bukti Progres Teknis

Perubahan teknis minggu ini terlihat pada area berikut:

- `app/Services/Scc/LoadManagementController.php` untuk logika skor energi dan prioritas beban.
- `app/Livewire/Pages/SccLoadManagement.php` untuk halaman kontrol/manajemen beban DC.
- `resources/views/livewire/pages/scc-load-management.blade.php` untuk tampilan status, ringkasan, dan riwayat beban.
- `app/Livewire/Pages/SccAnalysis.php` untuk ringkasan performa dan interpretasi cepat.
- `resources/views/livewire/pages/scc-analysis.blade.php` untuk tampilan analisis performa.
- `app/Http/Controllers/Api/SccController.php` untuk integrasi hasil load management ke data API.
- `database/migrations/2026_05_12_000001_add_load_management_columns_to_scc_data_table.php` untuk penambahan kolom beban.
- `tests/Unit/LoadManagementControllerTest.php` dan `tests/Feature/SccDataApiTest.php` untuk validasi otomatis.
- `tools/generate_scc_validation_report.php` untuk pembuatan laporan validasi NASA POWER.

## 5. Batasan Saat Ini

Beberapa batasan yang perlu disampaikan secara transparan saat pemaparan:

1. Load Management DC masih berupa simulasi logika software, belum mengontrol relay atau hardware beban asli.
2. Data cuaca dan irradiance eksternal dipakai sebagai pembanding atau penguat narasi, bukan sebagai kalibrasi presisi terhadap panel fisik.
3. Validasi NASA POWER perlu data timestamp siang hari yang representatif agar hubungan irradiance dan daya panel lebih kuat.
4. Sistem masih membutuhkan uji lapangan menggunakan sensor aktual untuk memastikan akurasi pembacaan tegangan, arus, dan efisiensi converter.

## 6. Rencana Minggu Berikutnya

Rencana lanjutan yang disarankan:

1. Menyiapkan skenario demo yang konsisten dari kondisi baterai rendah, menengah, hingga penuh.
2. Menambahkan dataset siang hari agar validasi irradiance lebih representatif.
3. Menghubungkan Load Management DC ke rancangan hardware relay atau MOSFET driver.
4. Menambahkan export laporan analisis performa agar data bisa langsung dilampirkan pada laporan akhir.
5. Melakukan uji integrasi dengan simulator atau ESP32 secara end-to-end.
6. Merapikan narasi final untuk UAS: arsitektur sistem, fuzzy logic, hasil monitoring, analisis performa, dan keterbatasan validasi.

## 7. Alur Pemaparan yang Disarankan

1. Mulai dari latar belakang: SCC perlu monitoring dan kendali agar charging baterai lebih aman.
2. Jelaskan arsitektur: Panel Surya, Buck Converter, Baterai, ESP32/Simulator, API Laravel, Database, Dashboard.
3. Tampilkan dashboard: fokus pada sensor, SoC, duty cycle, dan fase charging.
4. Jelaskan fuzzy logic: input error, delta error, rule base, dan output duty cycle.
5. Masuk ke Analisis Performa: tunjukkan rata-rata SoC, duty, daya panel, efisiensi, dan distribusi fase.
6. Tampilkan Load Management DC: jelaskan prioritas Lampu, Kipas, Pompa, serta status ON/LIMITED/OFF.
7. Tampilkan laporan validasi NASA POWER: jelaskan bahwa validasi awal sudah dilakukan, tetapi masih perlu data lapangan yang lebih representatif.
8. Tutup dengan rencana lanjutan: integrasi hardware beban, validasi siang hari, dan pengujian end-to-end.

## 8. Naskah Singkat Pemaparan

Minggu ini, progres utama project SCC Monitoring adalah penguatan fitur analisis dan penambahan simulasi manajemen beban DC. Sebelumnya sistem sudah dapat menerima data SCC, menghitung fuzzy charging, menyimpan data, dan menampilkan dashboard monitoring. Pada pengembangan minggu ini, sistem mulai diarahkan agar tidak hanya menampilkan data sensor, tetapi juga memberi interpretasi terhadap performa energi.

Pada halaman Analisis Performa, sistem sudah dapat membaca data terbaru untuk menghitung rata-rata SoC, rata-rata duty cycle, rata-rata daya panel, estimasi efisiensi, daya panel maksimum, serta distribusi fase charging. Dengan fitur ini, pengguna dapat melihat apakah sistem lebih sering berada pada fase Bulk, Absorption, Float, atau Standby, dan bagaimana duty cycle berubah terhadap kondisi baterai.

Fitur baru berikutnya adalah Load Management DC. Fitur ini mensimulasikan tiga beban, yaitu Lampu DC, Kipas DC, dan Pompa DC. Sistem menentukan apakah beban menyala, dibatasi, atau dimatikan berdasarkan skor energi yang dihitung dari SoC, daya panel, tegangan panel, fase charging, dan label fuzzy. Tujuannya adalah menjaga agar beban tidak mengganggu proses charging dan proteksi baterai.

Selain itu, API juga sudah diperbarui agar setiap data SCC yang masuk langsung dihitung fuzzy charging dan manajemen bebannya. Hasilnya disimpan ke database, sehingga status beban bisa dianalisis kembali dari histori data. Pengujian otomatis juga sudah dijalankan, dengan hasil 10 test berhasil dan 37 assertion valid.

Sebagai pendukung laporan, project juga sudah memiliki laporan validasi terhadap data NASA POWER. Validasi ini menunjukkan bahwa data simulasi dapat dibandingkan dengan pola irradiance eksternal, meskipun saat ini masih perlu data timestamp siang hari atau sensor real agar validasi menjadi lebih kuat. Dengan demikian, status project saat ini sudah siap untuk pemaparan progres, terutama untuk menunjukkan dashboard monitoring, fuzzy charging, analisis performa, load management, dan batasan validasi yang masih akan dikembangkan.

## 9. Kesimpulan

Project SCC Monitoring minggu ini mengalami progres signifikan dari sisi analisis dan demonstrasi sistem. Aplikasi sudah lebih siap dipresentasikan karena memiliki alur yang lengkap: data SCC masuk melalui API, fuzzy controller menghitung fase dan duty cycle, dashboard menampilkan kondisi real-time, analisis performa membaca tren data, load management mensimulasikan prioritas beban DC, dan laporan validasi menyediakan pembanding eksternal.

Status akhir minggu ini: fitur inti berjalan, pengujian otomatis lulus, dan bahan pemaparan sudah siap untuk disusun menjadi slide presentasi.
