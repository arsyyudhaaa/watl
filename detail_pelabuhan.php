<?php
// =========================================================================
// PENGAMBILAN DETAIL DATA CUACA PELABUHAN (Format Kartu Horizontal Bergeser)
// =========================================================================
$pelabuhan_files = [
    $_SERVER['DOCUMENT_ROOT'] . '/data/flotim/XU004.json', // Pelabuhan Larantuka[cite: 5]
    $_SERVER['DOCUMENT_ROOT'] . '/data/flotim/XU005.json', // Pelabuhan Adonara
    $_SERVER['DOCUMENT_ROOT'] . '/data/flotim/XU006.json'  // Pelabuhan Waiwerang
];

$all_pelabuhan_data = [];
$current_time = time(); 

foreach ($pelabuhan_files as $file) {
    if (file_exists($file)) {
        $json_content = file_get_contents($file);
        $parsed_data = json_decode($json_content, true);

        $nama_pelabuhan = $parsed_data['name'] ?? 'Unknown Pelabuhan';
        $kode_pelabuhan = $parsed_data['code'] ?? ''; // Mengambil kode pelabuhan untuk link PDF & Infografis dinamis[cite: 5]
        $valid_slots = [];

        // Menggabungkan forecast_day1 dan forecast_day2-4 sesuai struktur JSON pelabuhan
        $all_forecasts = [];
        if (isset($parsed_data['forecast_day1']) && is_array($parsed_data['forecast_day1'])) {
            $all_forecasts = array_merge($all_forecasts, $parsed_data['forecast_day1']);
        }
        if (isset($parsed_data['forecast_day2-4']) && is_array($parsed_data['forecast_day2-4'])) {
            $all_forecasts = array_merge($all_forecasts, $parsed_data['forecast_day2-4']);
        }

        $index_slot = 0;
        foreach ($all_forecasts as $slot) {
            $slot_time = strtotime($slot['time']);
            
            // Ambil data mulai dari jam saat ini hingga waktu terakhir yang tersedia di JSON
            if ($slot_time >= $current_time) {
                // Konversi waktu UTC ke WITA (+8 jam)
                $waktu_ts = strtotime($slot['time'] . ' +8 hours');
                $format_waktu = date('D, d M, H:i', $waktu_ts); // Contoh: Sen, 31 Agu, 22:00
                
                // Label keterangan waktu relatif
                if ($index_slot == 0) {
                    $badge_waktu = '<span class="badge bg-primary text-white" style="font-size: 0.65rem;">Sekarang</span>';
                } else {
                    $badge_waktu = '<span class="text-muted" style="font-size: 0.7rem;">' . $index_slot . ' jam lagi</span>';
                }

                $gelombang_kat = $slot['wave_cat'] ?? '-';
                $gelombang_m = $slot['wave_height'] ?? '-';
                $cuaca = $slot['weather'] ?? '-';
                $visibilitas = isset($slot['visibility']) ? $slot['visibility'] . ' km' : '-';
                $tides = isset($slot['tides']) ? $slot['tides'] . ' m' : '-';
                
                $angin_arah = $slot['wind_from'] ?? '-';
                $angin_kecepatan = (isset($slot['wind_speed']) && isset($slot['wind_gust'])) ? $slot['wind_speed'] . ' - ' . $slot['wind_gust'] . ' Knot' : '-';
                
                $arus_kecepatan = isset($slot['current_speed']) ? $slot['current_speed'] . ' Knot' : '-';
                $arus_tujuan = $slot['current_to'] ?? '-';
                
                $suhu = isset($slot['temp_avg']) ? $slot['temp_avg'] : '-';
                $kelembapan = isset($slot['rh_avg']) ? $slot['rh_avg'] . '%' : '-';

                $valid_slots[] = [
                    'waktu_str' => $format_waktu,
                    'badge' => $badge_waktu,
                    'cuaca' => $cuaca,
                    'gelombang_kat' => $gelombang_kat,
                    'gelombang_m' => $gelombang_m,
                    'visibilitas' => $visibilitas,
                    'tides' => $tides,
                    'angin_arah' => $angin_arah,
                    'angin_kecepatan' => $angin_kecepatan,
                    'arus_kecepatan' => $arus_kecepatan,
                    'arus_tujuan' => $arus_tujuan,
                    'suhu' => $suhu,
                    'rh' => $kelembapan
                ];
                $index_slot++;
            }
        }

        $all_pelabuhan_data[] = [
            'nama' => $nama_pelabuhan,
            'code' => $kode_pelabuhan,
            'slots' => $valid_slots
        ];
    }
}

// Panggil Header modular
include 'includes/header.php';
?>

<style>
    .horizontal-scroll-container {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        gap: 1rem;
        padding-bottom: 0.75rem;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f1f5f9;
    }
    .horizontal-scroll-container::-webkit-scrollbar {
        height: 6px;
    }
    .horizontal-scroll-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .horizontal-scroll-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .forecast-card {
        min-width: 210px;
        max-width: 210px;
        flex: 0 0 auto;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        transition: transform 0.2s;
    }
    .forecast-card:hover {
        border-color: #cbd5e1;
    }
</style>

    <div class="container my-4" style="max-width: 1200px;">
        <!-- Header Halaman Detail -->
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <h4 class="fw-bold text-dark mb-1">
                    <i class="bi bi-geo-alt-fill text-danger me-2"></i> Detail Prakiraan Cuaca Pelabuhan
                </h4>
                <p class="text-muted small mb-0">Menampilkan prakiraan lengkap per jam dari waktu saat ini hingga selesai secara mendatar.</p>
            </div>
            <a href="index.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
            </a>
        </div>

        <!-- Daftar Wilayah Pelabuhan -->
        <?php if (!empty($all_pelabuhan_data)): ?>
            <?php foreach ($all_pelabuhan_data as $pelabuhan): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title fw-bold mb-0 text-dark">
                        <i class="bi bi-water text-danger me-2"></i><?= htmlspecialchars($pelabuhan['nama']) ?>
                    </h5>
                    <?php if (!empty($pelabuhan['code'])): ?>
                    <div class="d-flex gap-2">
                        <a href="https://maritim.bmkg.go.id/marine2026-data/doc/cuaca/pelabuhan/<?= htmlspecialchars($pelabuhan['code']) ?>.pdf" 
                           target="_blank" 
                           class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Unduh PDF
                        </a>
                        <a href="https://maritim.bmkg.go.id/marine2026-data/doc/cuaca/pelabuhan/<?= htmlspecialchars($pelabuhan['code']) ?>.png" 
                           target="_blank" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-image me-1"></i> Unduh Infografis
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-body bg-light p-3">
                    <?php if (!empty($pelabuhan['slots'])): ?>
                        <div class="horizontal-scroll-container">
                            <?php foreach ($pelabuhan['slots'] as $slot): ?>
                            <div class="forecast-card p-3 shadow-sm">
                                <!-- Header Waktu -->
                                <div class="d-flex justify-content-between align-items-start mb-2 border-bottom pb-2">
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.8rem;"><?= htmlspecialchars($slot['waktu_str']) ?> WITA</div>
                                        <div class="mt-1"><?= $slot['badge'] ?></div>
                                    </div>
                                </div>

                                <!-- Cuaca & Visibilitas -->
                                <div class="text-center my-2 py-1 bg-light rounded">
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 0.85rem;"><?= htmlspecialchars($slot['cuaca']) ?></div>
                                    <div class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-eye me-1"></i>Visibilitas: <?= htmlspecialchars($slot['visibilitas']) ?></div>
                                </div>

                                <!-- Gelombang-->
                                <div class="small mb-2 pt-1 border-top">
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.75rem;">
                                        <span><i class="bi bi-water me-1"></i> Gelombang</span>
                                    </div>
                                    <div class="d-flex justify-content-between fw-semibold text-dark mb-1" style="font-size: 0.75rem;">
                                        <span><?= htmlspecialchars($slot['gelombang_m']) ?> m</span>
                                        <span><?= htmlspecialchars($slot['gelombang_kat']) ?></span>
                                    </div>
                                </div>

                                <!-- Pasut (Tides) -->
                                <div class="small mb-2 pt-1 border-top">
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.75rem;">
                                        <span>Pasut</span>
                                    </div>
                                    <div class="d-flex justify-content-between fw-semibold text-dark mb-1" style="font-size: 0.75rem;">
                                        <span class="text-primary"><?= htmlspecialchars($slot['tides']) ?></span>
                                    </div>
                                </div>

                                <!-- Informasi Arus Laut -->
                                <div class="small border-top pt-2">
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.75rem;">
                                        <span><i class="bi bi-compass me-1"></i> Arus Laut</span>
                                    </div>
                                    <div class="d-flex justify-content-between fw-semibold text-dark mb-1" style="font-size: 0.75rem;">
                                        <span><?= htmlspecialchars($slot['arus_kecepatan']) ?></span>
                                        <span>ke <?= htmlspecialchars($slot['arus_tujuan']) ?></span>
                                    </div>
                                </div>
                                
                                <!-- Informasi Angin -->
                                <div class="small mb-2 pt-1 border-top">
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.75rem;">
                                        <span><i class="bi bi-wind me-1"></i> Angin</span>
                                    </div>
                                    <div class="d-flex justify-content-between fw-semibold text-dark mb-1" style="font-size: 0.75rem;">
                                        <span>Dari <?= htmlspecialchars($slot['angin_arah']) ?></span>
                                        <span><?= htmlspecialchars($slot['angin_kecepatan']) ?></span>
                                    </div>
                                </div>

                                <!-- Informasi Parameter (Suhu/RH) -->
                                <div class="small border-top pt-2">
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.75rem;">
                                        <span>Parameter</span>
                                    </div>
                                    <div class="d-flex justify-content-between fw-semibold text-dark mb-1" style="font-size: 0.75rem;">
                                        <span><i class="bi bi-thermometer-half me-1"></i><?= htmlspecialchars($slot['suhu']) ?> &deg;</span>
                                        <span><i class="bi bi-droplet me-1"></i><?= htmlspecialchars($slot['rh']) ?></span>
                                    </div>
                                </div>

                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted small p-3">
                            Tidak ada data prakiraan tersedia.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning text-center">Data file JSON pelabuhan tidak ditemukan di direktori.</div>
            </div>
        <?php endif; ?>
    </div>

<?php 
// Panggil Footer modular
include 'includes/footer.php'; 
?>