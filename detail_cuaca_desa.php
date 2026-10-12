<?php
// =========================================================================
// DETAIL PRAKIRAAN CUACA TINGKAT DESA 
// =========================================================================

// Tangkap parameter dari URL
$kode_kecamatan = $_GET['id'] ?? '';
$nama_kecamatan = $_GET['nama'] ?? 'Tidak Diketahui';

// Validasi ID untuk mencegah eksekusi file tidak sah
if (empty($kode_kecamatan) || !preg_match('/^[0-9\.]+$/', $kode_kecamatan)) {
    die("Kode kecamatan tidak valid.");
}

$json_file = $_SERVER['DOCUMENT_ROOT'] . "/data/flotim/kec/cuaca_{$kode_kecamatan}.json";
$days_map = [];

if (file_exists($json_file)) {
    $json_content = file_get_contents($json_file);
    $parsed_data = json_decode($json_content, true);
    $desa_list = $parsed_data['data'] ?? [];

    foreach ($desa_list as $desa) {
        // BMKG biasanya menyimpan nama desa di field 'desa' atau 'kelurahan'
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
    
    // Urutkan slot waktu per hari
    foreach ($days_map as $date => &$val) {
        if (isset($val['times'])) ksort($val['times']);
    }
    unset($val);
    ksort($days_map);
}

// Panggil Header modular
include 'includes/header.php';
?>

<style>
    .weather-cell-content { font-size: 0.8rem; }
    .table th, .table td { white-space: nowrap; vertical-align: middle; }
</style>

<div class="container my-4" style="max-width: 1300px;">
    <!-- Header Halaman -->
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-map-fill text-success me-2"></i> Prakiraan Cuaca Tingkat Desa
            </h4>
            <p class="text-muted small mb-0">Kecamatan <?= htmlspecialchars($nama_kecamatan) ?>, Kabupaten Flores Timur.</p>
        </div>
        <div class="d-flex gap-2">
            <!-- Tombol Unduh PDF -->
            <a href="download_prakicu_flotim_desa.php?id=<?= htmlspecialchars($kode_kecamatan) ?>&nama=<?= urlencode($nama_kecamatan) ?>" target="_blank" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i> Unduh PDF
            </a>
            <a href="detail_cuaca.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kecamatan
            </a>
        </div>
    </div>

    <?php if (!empty($days_map)): ?>
        <ul class="nav nav-tabs mb-3" id="weatherDayTabs" role="tablist">
            <?php 
            $tab_idx = 0;
            foreach ($days_map as $date_str => $day_info): 
                $is_active = ($tab_idx === 0) ? 'active' : '';
                $tab_id = 'day-tab-' . $tab_idx;
                $pane_id = 'day-pane-' . $tab_idx;
            ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold <?= $is_active ?>" id="<?= $tab_id ?>" data-bs-toggle="tab" data-bs-target="#<?= $pane_id ?>" type="button" role="tab">
                    <i class="bi bi-calendar-date me-1"></i> <?= date('D, d M Y', strtotime($date_str)) ?>
                </button>
            </li>
            <?php $tab_idx++; endforeach; ?>
        </ul>

        <div class="tab-content" id="weatherDayTabsContent">
            <?php 
            $pane_idx = 0;
            foreach ($days_map as $date_str => $day_info): 
                $is_active_pane = ($pane_idx === 0) ? 'show active' : '';
                $pane_id = 'day-pane-' . $pane_idx;
                $times = array_keys($day_info['times']);
                $desa_data = $day_info['data'];
            ?>
            <div class="tab-pane fade <?= $is_active_pane ?>" id="<?= $pane_id ?>" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-body bg-white p-3">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead class="table-success text-center">
                                    <tr>
                                        <th style="min-width: 180px;" class="text-start ps-3">Desa / Kelurahan</th>
                                        <?php foreach ($times as $t): ?>
                                            <th style="min-width: 110px;"><?= htmlspecialchars($t) ?> WITA</th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($desa_data as $nama_desa => $time_slots): ?>
                                        <tr>
                                            <td class="fw-bold text-dark ps-3 bg-light">
                                                <i class="bi bi-house-door text-success me-1"></i> <?= htmlspecialchars($nama_desa) ?>
                                            </td>
                                            <?php foreach ($times as $t): ?>
                                                <td class="text-center">
                                                    <?php if (isset($time_slots[$t])): 
                                                        $slot = $time_slots[$t];
                                                        $img = $slot['image'] ?? '';
                                                    ?>
                                                        <div class="d-flex flex-column align-items-center weather-cell-content py-1">
                                                            <?php if (!empty($img)): ?>
                                                                <img src="<?= htmlspecialchars($img) ?>" alt="Cuaca" style="width: 28px; height: 28px;" class="mb-1">
                                                            <?php endif; ?>
                                                            <span class="fw-bold text-dark"><?= htmlspecialchars($slot['t'] ?? '-') ?>&deg;C</span>
                                                            <span class="text-muted text-truncate" style="max-width: 100px; font-size: 0.7rem;" title="<?= htmlspecialchars($slot['weather_desc'] ?? '-') ?>"><?= htmlspecialchars($slot['weather_desc'] ?? '-') ?></span>
                                                            <span class="text-secondary" style="font-size: 0.65rem;">
                                                                <i class="bi bi-wind"></i> <?= htmlspecialchars($slot['wd'] ?? '-') ?> <?= htmlspecialchars($slot['ws'] ?? '-') ?> m/s
                                                            </span>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted small">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php $pane_idx++; endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">Data file JSON cuaca tingkat desa tidak ditemukan atau kosong.</div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>