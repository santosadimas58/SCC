<?php

$root = dirname(__DIR__);
$database = $root.'/scc_monitoring';
$nasaFile = $root.'/reports/nasa_power_20250511_20250515_setiabudhi.json';
$htmlFile = $root.'/reports/laporan_validasi_simulasi_scc_nasa_power.html';

$nasa = json_decode(file_get_contents($nasaFile), true, flags: JSON_THROW_ON_ERROR);
$parameters = $nasa['properties']['parameter'];
$db = new SQLite3($database, SQLITE3_OPEN_READONLY);

$result = $db->query(<<<'SQL'
    select
        strftime('%Y-%m-%d %H:00:00', created_at) as hour,
        count(*) as samples,
        round(avg(vpv * ipv), 2) as avg_ppanel,
        round(avg(vpv), 2) as avg_vpv,
        round(avg(ipv), 2) as avg_ipv,
        round(avg(vbat), 2) as avg_vbat,
        round(avg(ibat), 2) as avg_ibat,
        round(avg(soc), 2) as avg_soc,
        round(avg(duty_cycle), 2) as avg_duty,
        group_concat(distinct fase) as phases
    from scc_data
    group by hour
    order by hour
SQL);

$rows = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $projectTime = new DateTimeImmutable($row['hour'], new DateTimeZone('Asia/Jakarta'));
    $nasaReference = $projectTime
        ->setDate(2025, (int) $projectTime->format('m'), (int) $projectTime->format('d'))
        ->setTimezone(new DateTimeZone('UTC'));
    $key = $nasaReference->format('YmdH');
    $irradiance = valueOrNull($parameters['ALLSKY_SFC_SW_DWN'][$key] ?? null);
    $cloud = valueOrNull($parameters['CLOUD_AMT'][$key] ?? null);
    $temperature = valueOrNull($parameters['T2M'][$key] ?? null);
    $simPower = (float) $row['avg_ppanel'];

    $rows[] = [
        ...$row,
        'project_time' => $projectTime,
        'nasa_key' => $key,
        'nasa_local_time' => $nasaReference->setTimezone(new DateTimeZone('Asia/Jakarta')),
        'irradiance' => $irradiance,
        'cloud' => $cloud,
        'temperature' => $temperature,
        'pattern' => patternText($irradiance, $simPower),
    ];
}

$validRows = array_values(array_filter($rows, fn ($row) => $row['irradiance'] !== null));
$avgSimPower = average(array_column($validRows, 'avg_ppanel'));
$avgIrradiance = average(array_column($validRows, 'irradiance'));
$maxSimPower = max(array_map(fn ($row) => (float) $row['avg_ppanel'], $rows));
$maxIrradiance = max(array_map(fn ($row) => (float) ($row['irradiance'] ?? 0), $rows));
$matchingRows = array_filter($validRows, fn ($row) => str_contains($row['pattern'], 'Sesuai'));
$matchPercent = count($validRows) > 0 ? round(count($matchingRows) / count($validRows) * 100, 1) : 0.0;
$minProject = $rows[0]['project_time'] ?? null;
$maxProject = $rows[array_key_last($rows)]['project_time'] ?? null;

$html = '<!doctype html><html lang="id"><head><meta charset="utf-8"><title>Laporan Validasi Simulasi SCC</title><style>';
$html .= file_get_contents(__DIR__.'/report_style.css');
$html .= '</style></head><body>';
$html .= '<h1>Laporan Perbandingan Simulasi SCC dengan Data Irradiance NASA POWER</h1>';
$html .= '<p class="meta">Lokasi simulasi: Setiabudhi/Bandung sekitar latitude -6.86 dan longitude 107.59. Data project diambil dari tabel <code>scc_data</code>. Data pembanding memakai NASA POWER Hourly API parameter <code>ALLSKY_SFC_SW_DWN</code>, <code>CLOUD_AMT</code>, dan <code>T2M</code>.</p>';

$html .= '<h2>Ringkasan</h2><table><tbody>';
$html .= row('Jumlah record SCC', dbScalar($db, 'select count(*) from scc_data').' record');
$html .= row('Jumlah agregasi per jam', count($rows).' jam');
$html .= row('Rentang data project', ($minProject?->format('d M Y H:i') ?? '-').' WIB sampai '.($maxProject?->format('d M Y H:i') ?? '-').' WIB');
$html .= row('Rata-rata daya panel simulasi', number_format($avgSimPower, 2).' W');
$html .= row('Maksimum daya panel simulasi', number_format($maxSimPower, 2).' W');
$html .= row('Rata-rata irradiance NASA referensi', number_format($avgIrradiance, 2).' Wh/m² per jam');
$html .= row('Maksimum irradiance NASA referensi', number_format($maxIrradiance, 2).' Wh/m² per jam');
$html .= row('Kesesuaian pola jam yang tersedia', number_format($matchPercent, 1).'%');
$html .= row('Status demo project', 'BMKG_WEATHER_DEMO_MODE=true, sehingga simulator dapat menghasilkan daya panel untuk kebutuhan presentasi walaupun timestamp berada di luar jam efektif matahari.');
$html .= '</tbody></table>';

$html .= '<h2>Metode Perbandingan</h2>';
$html .= '<p>Project SCC ini menghasilkan data simulasi panel berupa <code>Vpv</code> dan <code>Ipv</code>. Daya panel simulasi dihitung dengan rumus <code>Ppanel = Vpv × Ipv</code>. Nilai tersebut dibandingkan dengan data radiasi matahari publik NASA POWER, yaitu <code>ALLSKY_SFC_SW_DWN</code>.</p>';
$html .= '<p>Karena data SCC di project adalah demo tahun 2026 dan NASA POWER belum menyediakan nilai irradiance hourly yang valid untuk tanggal tersebut, laporan ini memakai tanggal kalender yang sama pada tahun 2025 sebagai baseline historis. Perbandingan difokuskan pada pola, bukan kalibrasi absolut: saat irradiance tinggi, daya panel simulasi seharusnya tinggi; saat irradiance nol/rendah, daya panel seharusnya rendah.</p>';
$html .= '<p>Catatan penting: data project yang tersedia pada database saat laporan dibuat berada pada pukul 02:00-06:00 WIB. Pada jam tersebut, data NASA menunjukkan irradiance nol atau sangat rendah. Karena project sedang berjalan dalam mode demo, simulator masih dapat membuat daya panel puluhan watt agar dashboard presentasi tetap bergerak. Hal ini membuat perbandingan timestamp langsung menunjukkan ketidaksesuaian, tetapi sekaligus menjadi bukti batasan simulasi yang perlu dijelaskan saat UAS.</p>';

$html .= '<h2>Tabel Perbandingan per Jam</h2>';
$html .= '<p>Tabel dibuat ringkas agar mudah dibaca di dokumen. Detail fase, cloud, suhu, dan interpretasi ditulis setelah tabel.</p>';
$html .= '<table><thead><tr>';
$html .= '<th>No</th><th>Waktu Project</th><th>NASA Referensi</th><th>Sampel</th><th>Ppanel Simulasi</th><th>SoC</th><th>Duty</th><th>Irradiance NASA</th>';
$html .= '</tr></thead><tbody>';
foreach ($rows as $index => $row) {
    $html .= '<tr>';
    $html .= cell((string) ($index + 1));
    $html .= cell($row['project_time']->format('d M Y H:i').' WIB');
    $html .= cell($row['nasa_local_time']->format('d M Y H:i').' WIB');
    $html .= cell($row['samples']);
    $html .= cell(number_format((float) $row['avg_ppanel'], 2).' W');
    $html .= cell(number_format((float) $row['avg_soc'], 2).'%');
    $html .= cell(number_format((float) $row['avg_duty'], 2).'%');
    $html .= cell($row['irradiance'] !== null ? number_format($row['irradiance'], 2).' Wh/m²' : 'Tidak tersedia');
    $html .= '</tr>';
}
$html .= '</tbody></table>';

$html .= '<h2>Detail Interpretasi per Jam</h2><table><thead><tr>';
$html .= '<th>No</th><th>Parameter Project</th><th>Parameter NASA</th><th>Interpretasi</th>';
$html .= '</tr></thead><tbody>';
foreach ($rows as $index => $row) {
    $projectDetail = 'Vpv '.number_format((float) $row['avg_vpv'], 2).' V; Ipv '.number_format((float) $row['avg_ipv'], 2).' A; fase '.$row['phases'].'.';
    $nasaDetail = 'Cloud '.($row['cloud'] !== null ? number_format($row['cloud'], 2).'%' : '-').'; suhu '.($row['temperature'] !== null ? number_format($row['temperature'], 2).' °C' : '-').'.';

    $html .= '<tr>';
    $html .= cell((string) ($index + 1));
    $html .= cell($projectDetail);
    $html .= cell($nasaDetail);
    $html .= cell($row['pattern']);
    $html .= '</tr>';
}
$html .= '</tbody></table>';

$html .= '<h2>Analisis</h2>';
$html .= '<p>Secara umum, data project menunjukkan daya panel simulasi berada pada kisaran '.number_format($maxSimPower, 2).' W maksimum. Namun pada timestamp yang sama, NASA POWER menunjukkan irradiance nol atau rendah. Artinya, data demo yang sedang tersimpan belum cocok untuk disebut sebagai validasi waktu nyata terhadap radiasi matahari. Perbedaan ini muncul karena mode demo project sengaja mempertahankan simulasi panel agar dashboard tetap aktif untuk presentasi.</p>';
$html .= '<p>Nilai numerik antara NASA irradiance dan Ppanel simulasi tidak harus sama karena satuannya berbeda. NASA memberikan energi radiasi per luas permukaan horizontal, sedangkan project menghitung daya keluaran panel dari model simulasi tegangan dan arus. Oleh karena itu laporan ini membaca kesesuaian tren, bukan error absolut.</p>';
$html .= '<p>Jika ingin memperoleh validasi yang lebih kuat, data SCC perlu direkam pada jam siang lokal, misalnya pukul 08:00-15:00 WIB, atau demo mode perlu dimatikan agar logika siang/malam benar-benar menahan produksi panel pada malam hari.</p>';

$html .= '<h2>Keterbatasan</h2><ul>';
$html .= '<li>Data NASA yang dipakai adalah baseline historis 2025 karena data irradiance NASA untuk tanggal demo 2026 masih bernilai <code>-999</code> atau belum tersedia.</li>';
$html .= '<li>Simulasi project memakai tutupan awan BMKG dan logika siang/malam, tetapi pada saat laporan dibuat mode demo aktif sehingga pengaruh malam hari tidak dipakai secara ketat.</li>';
$html .= '<li>Perbandingan ini adalah plausibility check. Untuk validasi hardware penuh tetap diperlukan pengukuran real Vpv, Ipv, irradiance, suhu panel, dan orientasi panel.</li>';
$html .= '</ul>';

$html .= '<h2>Kesimpulan</h2>';
$html .= '<p>Perbandingan dengan NASA POWER menunjukkan bahwa data demo project belum valid sebagai pembacaan waktu nyata karena daya panel simulasi masih muncul pada jam ketika irradiance referensi rendah atau nol. Namun, hasil ini tetap berguna untuk laporan UAS karena menunjukkan batasan simulasi secara transparan. Project layak dipakai sebagai demonstrasi hubungan cuaca, potensi panel, fuzzy charging, dan manajemen beban DC, dengan catatan bahwa validasi lapangan tetap memerlukan data siang hari atau sensor irradiance real.</p>';

$html .= '<h2>Sumber Data</h2><ol>';
$html .= '<li>NASA POWER Hourly API: https://power.larc.nasa.gov/docs/services/api/temporal/hourly/</li>';
$html .= '<li>NASA POWER Solar FAQ: https://power.larc.nasa.gov/docs/faqs/solar/</li>';
$html .= '<li>NASA POWER Methodology CERES SYN1deg: https://power.larc.nasa.gov/docs/methodology/energy-fluxes/syn1deg/</li>';
$html .= '</ol>';
$html .= '</body></html>';

file_put_contents($htmlFile, $html);
echo $htmlFile.PHP_EOL;

function valueOrNull(mixed $value): ?float
{
    if (! is_numeric($value) || (float) $value <= -900.0) {
        return null;
    }

    return (float) $value;
}

function average(array $values): float
{
    $values = array_values(array_filter($values, fn ($value) => is_numeric($value)));

    return count($values) > 0 ? array_sum($values) / count($values) : 0.0;
}

function patternText(?float $irradiance, float $simPower): string
{
    if ($irradiance === null) {
        return 'Data NASA tidak tersedia.';
    }

    if ($irradiance >= 150.0 && $simPower >= 20.0) {
        return 'Sesuai: irradiance tersedia dan simulasi menghasilkan daya panel.';
    }

    if ($irradiance < 50.0 && $simPower < 10.0) {
        return 'Sesuai: irradiance rendah dan daya panel simulasi rendah.';
    }

    if ($irradiance < 50.0 && $simPower >= 10.0) {
        return 'Tidak sesuai: irradiance rendah/nol tetapi simulasi masih menghasilkan daya panel. Ini indikasi efek demo mode atau timestamp tidak representatif.';
    }

    if ($irradiance >= 150.0 && $simPower < 10.0) {
        return 'Perlu dicek: irradiance tinggi tetapi daya simulasi rendah.';
    }

    return 'Cukup sesuai: pola tidak bertentangan, tetapi nilai berada di area transisi.';
}

function row(string $label, string $value): string
{
    return '<tr><th>'.escape($label).'</th><td>'.escape($value).'</td></tr>';
}

function cell(mixed $value): string
{
    return '<td>'.escape((string) $value).'</td>';
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function dbScalar(SQLite3 $db, string $query): string
{
    return (string) $db->querySingle($query);
}
