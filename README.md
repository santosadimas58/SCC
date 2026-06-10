# SCC Monitoring

SCC Monitoring adalah aplikasi web untuk memantau Solar Charge Controller berbasis fuzzy logic. Sistem ini menampilkan data tegangan panel, arus panel, tegangan baterai, arus baterai, state of charge, duty cycle PWM, fase charging, serta label fuzzy secara real-time.

Project ini dibuat untuk kebutuhan demo/presentasi akademik pada topik elektronika daya, sistem kendali, dan monitoring perangkat berbasis web.

## Fitur Utama

- Dashboard monitoring SCC dengan status online/offline, ringkasan harian, metrik real-time, grafik Vbat, dan grafik SoC.
- Pengendali fuzzy logic Mamdani untuk menentukan duty cycle PWM dari error dan delta error.
- Fuzzifikasi menggunakan membership function overlap, inferensi min-max pada 25 aturan, dan defuzzifikasi centroid.
- Halaman rule base berisi 25 aturan fuzzy.
- Histori data dengan pencarian, filter fase, filter tanggal, sorting, pagination, dan export CSV.
- Halaman About dengan diagram arsitektur sistem.
- Seeder demo data untuk membuat dataset presentasi secara cepat.
- Tombol Reset Demo di dashboard untuk menghapus data SCC dan mengisi ulang dataset demo terbaru.
- Simulator Python untuk mengirim data SCC ke API seperti perangkat ESP32.

## Tech Stack

- Laravel 12
- Livewire 4
- MaryUI
- Tailwind CSS
- DaisyUI
- Chart.js
- SQLite untuk setup lokal default
- MySQL/Redis melalui Docker Compose

## Arsitektur Singkat

Alur sistem:

```text
Simulasi Panel/Baterai -> Laravel API + Fuzzy Mamdani -> Database -> Dashboard
```

Simulator mengirim data sensor ke endpoint API. Backend Laravel memvalidasi token, menentukan fase charging, menjalankan inferensi Mamdani, menyimpan data, lalu Livewire menampilkan hasilnya pada dashboard, histori, analisis fuzzy, dan export. Project ini merupakan simulasi software dan tidak mengendalikan hardware fisik.

## Akun Demo

Seeder membuat akun berikut:

```text
Email    : admin@example.com
Password : password
```

Seeder juga mempertahankan akun:

```text
Email    : st@techupi.id
Password : Ddw9889##
```

## Setup Lokal

1. Install dependency PHP dan JavaScript.

```bash
composer install
npm install
```

2. Siapkan file environment.

```bash
cp .env.example .env
php artisan key:generate
```

3. Jalankan migrasi dan seeder demo.

```bash
php artisan migrate --seed
```

4. Jalankan server Laravel dan Vite.

```bash
php artisan serve
npm run dev
```

5. Buka aplikasi.

```text
http://127.0.0.1:8000
```

## Setup Docker

Project menyediakan `docker-compose.yml` dan `makefile`.

```bash
make up
make artisan migrate --seed
make npm run dev
```

Aplikasi dapat diakses melalui:

```text
http://localhost:8088
```

## Demo Data

Seeder otomatis mengisi tabel `scc_data` dengan skenario demo yang mencakup fase:

- Bulk
- Absorption
- Float
- Standby

Untuk mengisi ulang data demo dari terminal:

```bash
php artisan db:seed --class=SccDemoDataSeeder
```

Untuk reset saat presentasi, login ke aplikasi lalu tekan tombol **Reset Demo** pada dashboard. Tombol ini akan menghapus seluruh data SCC dan membuat dataset demo baru dengan timestamp terbaru.

## Simulator SCC

Simulator Python mengirim payload ke endpoint API:

```text
POST /api/scc/data
```

Jalankan server Laravel terlebih dahulu, lalu jalankan:

```bash
python3 simulator.py
```

Token API dibaca dari `.env` melalui `SCC_API_TOKEN`. Nilai default pada `.env.example`:

```text
SCC_API_TOKEN=change-this-token
```

Jika token aktif, request API harus membawa header:

```text
X-SCC-Token: change-this-token
```

## Endpoint API

Kirim data sensor:

```http
POST /api/scc/data
X-SCC-Token: change-this-token
Content-Type: application/json

{
  "vpv": 19.0,
  "ipv": 2.4,
  "vbat": 12.0,
  "ibat": 2.1,
  "soc": 32.0
}
```

Backend akan menghitung field berikut:

- `duty_cycle`
- `fase`
- `label_e`
- `label_de`

Endpoint pembacaan:

```text
GET /api/scc/latest
GET /api/scc/history
```

## Pengujian

Jalankan test:

```bash
php artisan test
```

Atau melalui Composer script:

```bash
composer test
```

## Jalur Demo Presentasi

Urutan demo yang disarankan:

1. Login memakai akun demo.
2. Buka dashboard dan tekan Reset Demo agar data terlihat segar.
3. Jelaskan status alat, fase charging, grafik Vbat, grafik SoC, dan ringkasan harian.
4. Buka halaman Fuzzy Logic untuk menjelaskan membership function.
5. Buka Rule Base untuk menunjukkan 25 aturan IF-THEN.
6. Buka History untuk menunjukkan filter, sorting, dan data mentah.
7. Export CSV sebagai bukti data bisa dianalisis di luar aplikasi.
8. Buka About untuk menjelaskan arsitektur dari panel surya sampai dashboard.
