<?php
// =========================================================================
// DETAIL PRAKIRAAN CUACA KABUPATEN FLORES TIMUR (BENTUK TABEL PER HARI)
// =========================================================================
$json_file = $_SERVER['DOCUMENT_ROOT'] . '/data/flotim/cuaca_flotim.json';
$days_map = [];
$kecamatan_ids = []; // Array bantuan untuk menyimpan kode adm3 per kecamatan

if (file_exists($json_file)) {
    $json_content = file_get_contents($json_file);
    $parsed_data = json_decode($json_content, true);
    $kecamatan_list = $parsed_data['data'] ?? [];

    foreach ($kecamatan_list as $kec) {
        $nama_kec = $kec['lokasi']['kecamatan'] ?? 'Unknown';
        
        // Simpan kode adm3 ke dalam array berdasarkan nama kecamatan
        $kecamatan_ids[$nama_kec] = $kec['lokasi']['adm3'] ?? $kec['lokasi']['id'] ?? '';
        
        $cuaca_days = $kec['cuaca'] ?? [];
        
        foreach ($cuaca_days as $day_slots) {
            if (is_array($day_slots)) {
                foreach ($day_slots as $slot) {
                    $local_dt = $slot['local_datetime'] ?? '';
                    if ($local_dt) {
                        $date_part = date('Y-m-d', strtotime($local_dt));
                        $time_part = date('H:i', strtotime($local_dt));
                        
                        $days_map[$date_part]['times'][$time_part] = true;
                        $days_map[$date_part]['data'][$nama_kec][$time_part] = $slot;
                    }
                }
            }
        }
    }
    
    // Urutkan slot waktu untuk setiap hari
    foreach ($days_map as $date => &$val) {
        if (isset($val['times'])) {
            ksort($val['times']);
        }
    }
    unset($val);
    ksort($days_map); // Urutkan tanggal secara kronologis
}

// Panggil Header modular
include 'includes/header.php';
?>

<style>
    .weather-cell-content {
        font-size: 0.8rem;
    }
    .table th, .table td {
        white-space: nowrap;
        vertical-align: middle;
    }
</style>

    <div class="container my-4" style="max-width: 1300px;">
        <!-- Header Halaman Detail -->
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm flex-wrap gap-2">
            <div>
                <h4 class="fw-bold text-dark mb-1">
                    <i class="bi bi-cloud-sun-fill text-primary me-2"></i> Detail Prakiraan Cuaca Per Kecamatan
                </h4>
                <p class="text-muted small mb-0">Kabupaten Flores Timur - Disajikan dalam bentuk tabel matriks per hari.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="download_prakicu_flotim.php" target="_blank" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Unduh PDF
                </a>
                <a href="index.php" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
                </a>
            </div>
        </div>

        <?php if (!empty($days_map)): ?>
            <!-- Nav Tabs untuk Setiap Hari -->
            <ul class="nav nav-tabs mb-3" id="weatherDayTabs" role="tablist">
                <?php 
                $tab_idx = 0;
                foreach ($days_map as $date_str => $day_info): 
                    $is_active = ($tab_idx === 0) ? 'active' : '';
                    $tab_id = 'day-tab-' . $tab_idx;
                    $pane_id = 'day-pane-' . $tab_idx;
                    $formatted_date = date('D, d M Y', strtotime($date_str));
                ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold <?= $is_active ?>" id="<?= $tab_id ?>" data-bs-toggle="tab" data-bs-target="#<?= $pane_id ?>" type="button" role="tab">
                        <i class="bi bi-calendar-date me-1"></i> <?= htmlspecialchars($formatted_date) ?>
                    </button>
                </li>
                <?php 
                    $tab_idx++;
                endforeach; 
                ?>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="weatherDayTabsContent">
                <?php 
                $pane_idx = 0;
                foreach ($days_map as $date_str => $day_info): 
                    $is_active_pane = ($pane_idx === 0) ? 'show active' : '';
                    $pane_id = 'day-pane-' . $pane_idx;
                    $times = array_keys($day_info['times']);
                    $kecamatan_data = $day_info['data'];
                ?>
                <div class="tab-pane fade <?= $is_active_pane ?>" id="<?= $pane_id ?>" role="tabpanel">
                    <div class="card shadow-sm border-0">
                        <div class="card-body bg-white p-3">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th style="min-width: 180px;" class="text-start ps-3">Kecamatan</th>
                                            <?php foreach ($times as $t): ?>
                                                <th style="min-width: 110px;"><?= htmlspecialchars($t) ?> WITA</th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($kecamatan_data)): ?>
                                            <?php foreach ($kecamatan_data as $nama_kec => $time_slots): ?>
                                                <tr>
                                                    <td class="fw-bold text-dark ps-3 bg-light">
                                                        <?php 
                                                            // Memanggil ID yang sudah disimpan di array pembantu
                                                            $kode_adm3 = $kecamatan_ids[$nama_kec] ?? ''; 
                                                        ?>
                                                        <a href="detail_cuaca_desa.php?id=<?= htmlspecialchars($kode_adm3) ?>&nama=<?= urlencode($nama_kec) ?>" class="text-decoration-none text-dark d-block" style="transition: 0.2s;" onmouseover="this.style.color='#0d6efd'" onmouseout="this.style.color='#212529'">
                                                            <i class="bi bi-geo-alt text-danger me-1"></i> <?= htmlspecialchars($nama_kec) ?>
                                                        </a>
                                                    </td>
                                                    <?php foreach ($times as $t): ?>
                                                        <td class="text-center">
                                                            <?php if (isset($time_slots[$t])): 
                                                                $slot = $time_slots[$t];
                                                                $suhu = $slot['t'] ?? '-';
                                                                $desc = $slot['weather_desc'] ?? '-';
                                                                $img = $slot['image'] ?? '';
                                                                $ws = $slot['ws'] ?? '-';
                                                                $wd = $slot['wd'] ?? '-';
                                                            ?>
                                                                <div class="d-flex flex-column align-items-center weather-cell-content py-1">
                                                                    <?php if (!empty($img)): ?>
                                                                        <img src="<?= htmlspecialchars($img) ?>" alt="Cuaca" style="width: 28px; height: 28px;" class="mb-1">
                                                                    <?php endif; ?>
                                                                    <span class="fw-bold text-dark"><?= htmlspecialchars($suhu) ?>&deg;C</span>
                                                                    <span class="text-muted text-truncate" style="max-width: 100px; font-size: 0.7rem;" title="<?= htmlspecialchars($desc) ?>"><?= htmlspecialchars($desc) ?></span>
                                                                    <span class="text-secondary" style="font-size: 0.65rem;"><i class="bi bi-wind"></i> <?= htmlspecialchars($wd) ?> <?= htmlspecialchars($ws) ?> m/s</span>
                                                                </div>
                                                            <?php else: ?>
                                                                <span class="text-muted small">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="<?= count($times) + 1 ?>" class="text-center text-muted p-4">Tidak ada data tersedia untuk tanggal ini.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                    $pane_idx++;
                endforeach; 
                ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">Data file JSON cuaca Flores Timur (`cuaca_flotim.json`) tidak ditemukan atau kosong.</div>
        <?php endif; ?>
    </div>

<?php 
// Panggil Footer modular
include 'includes/footer.php'; 
?>