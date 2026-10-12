<?php
// =========================================================================
// GENERATE PDF PRAKIRAAN CUACA DESA (PORTRAIT + IKON + WITA)
// =========================================================================
require 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// Tangkap parameter
$kode_kecamatan = $_GET['id'] ?? '';
$nama_kecamatan = $_GET['nama'] ?? 'Tidak Diketahui';

if (empty($kode_kecamatan) || !preg_match('/^[0-9\.]+$/', $kode_kecamatan)) {
    die("Kode kecamatan tidak valid.");
}

$json_file = $_SERVER['DOCUMENT_ROOT'] . "/data/flotim/kec/cuaca_{$kode_kecamatan}.json";
if (!file_exists($json_file)) {
    die("File JSON cuaca untuk kecamatan ini tidak ditemukan.");
}

$json_content = file_get_contents($json_file);
$parsed_data = json_decode($json_content, true);
$desa_list = $parsed_data['data'] ?? [];

$days_map = [];

foreach ($desa_list as $desa) {
    $nama_desa = $desa['lokasi']['desa'] ?? $desa['lokasi']['kelurahan'] ?? 'Unknown';
    $cuaca_days = $desa['cuaca'] ?? [];
    
    foreach ($cuaca_days as $day_slots) {
        if (is_array($day_slots)) {
            foreach ($day_slots as $slot) {
                $local_dt = $slot['local_datetime'] ?? '';
                if ($local_dt) {
                    $date_part = date('Y-m-d', strtotime($local_dt));
                    $time_part = date('H:i', strtotime($local_dt));
                    
                    $days_map[$date_part]['times'][$time_part] = true;
                    $days_map[$date_part]['data'][$nama_desa][$time_part] = $slot;
                }
            }
        }
    }
}

foreach ($days_map as $date => &$val) {
    if (isset($val['times'])) ksort($val['times']);
}
unset($val);
ksort($days_map);

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 7.5px; color: #333; }
        h3 { text-align: center; margin-bottom: 2px; color: #1e293b; font-size: 12px; }
        p { text-align: center; margin-top: 0; font-size: 9px; color: #64748b; margin-bottom: 10px; }
        h4 { background-color: #e2e8f0; padding: 5px; margin-bottom: 6px; font-size: 9px; color: #0f172a; border-left: 3px solid #198754; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 3px 4px; text-align: center; vertical-align: middle; }
        th { background-color: #198754; color: white; font-weight: bold; font-size: 7px; }
        .text-start { text-align: left; }
        .desa-col { font-weight: bold; background-color: #f8fafc; text-align: left; width: 90px; font-size: 7.5px; }
        .temp { font-weight: bold; color: #0f172a; font-size: 8px; }
        .desc { color: #475569; font-size: 6px; }
        .wind { color: #64748b; font-size: 5.5px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>';

$total_days = count($days_map);
$current_day = 0;

foreach ($days_map as $date_str => $day_info) {
    $current_day++;
    $formatted_date = date('D, d M Y', strtotime($date_str));
    $times = array_keys($day_info['times']);
    $desa_data = $day_info['data'];

    $break_class = ($current_day < $total_days) ? 'page-break' : '';

    $html .= '<div class="' . $break_class . '">';
    $html .= '<h3>Prakiraan Cuaca Tingkat Desa</h3>';
    $html .= '<p>Kecamatan ' . htmlspecialchars($nama_kecamatan) . ', Kabupaten Flores Timur</p>';
    $html .= '<h4>Tanggal: ' . $formatted_date . '</h4>';
    $html .= '<table>
                <thead>
                    <tr>
                        <th class="text-start">Desa / Kelurahan</th>';
    
    foreach ($times as $t) {
        $html .= '<th>' . $t . '<br><span style="font-size: 5.5px; font-weight: normal;">WITA</span></th>';
    }
    
    $html .= '     </tr>
                </thead>
                <tbody>';

    foreach ($desa_data as $nama_desa => $time_slots) {
        $html .= '<tr>
                    <td class="desa-col">' . htmlspecialchars($nama_desa) . '</td>';
        
        foreach ($times as $t) {
            if (isset($time_slots[$t])) {
                $slot = $time_slots[$t];
                $suhu = $slot['t'] ?? '-';
                $desc = $slot['weather_desc'] ?? '-';
                $ws = $slot['ws'] ?? '-';
                $wd = $slot['wd'] ?? '-';
                $img = $slot['image'] ?? '';

                $html .= '<td>';
                if (!empty($img)) {
                    $html .= '<img src="' . htmlspecialchars($img) . '" width="14" height="14" style="display: block; margin: 0 auto 1px auto;" />';
                }
                $html .= '<div class="temp">' . $suhu . '&deg;</div>
                            <div class="desc">' . htmlspecialchars($desc) . '</div>
                            <div class="wind">' . $wd . ' ' . $ws . 'm/s</div>
                          </td>';
            } else {
                $html .= '<td>-</td>';
            }
        }
        $html .= '</tr>';
    }

    $html .= '    </tbody>
              </table>';
    $html .= '</div>';
}

$html .= '</body>
</html>';

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Format nama file berdasarkan kecamatan
$nama_file = "prakiraan_cuaca_" . strtolower(str_replace(' ', '_', $nama_kecamatan)) . ".pdf";
$dompdf->stream($nama_file, ["Attachment" => false]);
exit;