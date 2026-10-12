<?php
// =========================================================================
// HALAMAN INFORMASI SIGMET WILAYAH NTT / WAAA + PETA & INDIKATOR ARAH PERGERAKAN
// =========================================================================
$json_file = $_SERVER['DOCUMENT_ROOT'] . '/data/flotim/sigmet_ntt.json';
$sigmet_data = [];
$last_update = '-';

if (file_exists($json_file)) {
    $content = file_get_contents($json_file);
    $decoded = json_decode($content, true);
    $sigmet_data = $decoded['data'] ?? [];
    $last_update = $decoded['last_update'] ?? '-';
}

include 'includes/header.php';
?>

<!-- Tambahkan CSS Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<!-- Tambahkan Bootstrap Icons untuk ikon panah -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="container my-4" style="max-width: 1200px;">
    <!-- Header Halaman -->
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i> Peringatan Dini SIGMET Penerbangan
            </h4>
            <p class="text-muted small mb-0">Wilayah Nusa Tenggara Timur & Sekitarnya (FIR Ujung Pandang / WAAF)</p>
        </div>
        <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
                </a>
            </div>
    </div>

    <!-- Daftar SIGMET dalam bentuk Grid Card -->
    <?php if (!empty($sigmet_data)): ?>
        <div class="row row-cols-1 g-4">
            <?php foreach ($sigmet_data as $index => $sig): 
                $hazard = $sig['hazard'] ?? 'UNKNOWN';
                $qualifier = $sig['qualifier'] ?? 'UMUM';
                $base = $sig['base'] ?? 0;
                $top = $sig['top'] ?? 0;
                $dir = $sig['dir'] ?? '-';
                $spd = $sig['spd'] ?? '0';
                $raw = $sig['rawSigmet'] ?? '';
                $coords = $sig['coords'] ?? [];
                
                // Format Flight Level (Base & Top)
                $base_display = ($base == 0) ? 'SFL' : 'FL' . ($base / 100);
                $top_display  = 'FL' . ($top / 100);

                // Ekstraksi valid_from dan valid_to langsung dari rawSigmet
                $valid_from = '-';
                $valid_to = '-';
                if (preg_match('/VALID\s+([0-9]{6})\/([0-9]{6})/', $raw, $matches)) {
                    $valid_from = $matches[1];
                    $valid_to   = $matches[2];
                }
                
                // Warna badge berdasarkan jenis hazard
                $badge_bg = 'bg-primary';
                if ($hazard === 'VA') $badge_bg = 'bg-danger';
                elseif ($hazard === 'TS') $badge_bg = 'bg-warning text-dark';
                elseif ($hazard === 'TURB') $badge_bg = 'bg-info text-dark';
            ?>
            <div class="col">
                <div class="card border shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                        <span class="fw-bold text-dark">
                            <i class="bi bi-shield-exclamation text-danger me-1"></i> Series ID: <?= htmlspecialchars($sig['seriesId'] ?? '-') ?>
                        </span>
                        <span class="badge <?= $badge_bg ?>"><?= htmlspecialchars($hazard) ?> - <?= htmlspecialchars($qualifier) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- SISI KIRI: Detail Informasi & Raw Text -->
                            <div class="col-lg-7 d-flex flex-column justify-content-between">
                                <span class="badge bg-secondary">Pembaruan Terakhir: <?= htmlspecialchars($last_update) ?> UTC</span>
                                <ul class="list-unstyled small mb-3">
                                    <li class="mb-2 d-flex justify-content-between border-bottom pb-1">
                                        <span class="text-muted">Valid From (Mulai):</span>
                                        <span class="fw-semibold text-dark font-monospace"><?= htmlspecialchars($valid_from) ?> UTC</span>
                                    </li>
                                    <li class="mb-2 d-flex justify-content-between border-bottom pb-1">
                                        <span class="text-muted">Valid To (Selesai):</span>
                                        <span class="fw-semibold text-dark font-monospace"><?= htmlspecialchars($valid_to) ?> UTC</span>
                                    </li>
                                    <li class="mb-2 d-flex justify-content-between border-bottom pb-1">
                                        <span class="text-muted">Flight Level (Ketinggian):</span>
                                        <span class="fw-semibold text-dark"><?= $base_display ?> s.d <?= $top_display ?></span>
                                    </li>
                                    <li class="mb-2 d-flex justify-content-between">
                                        <span class="text-muted">Arah Pergerakan:</span>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($dir) ?></span>
                                    </li>
                                    <li class="mb-2 d-flex justify-content-between">
                                        <span class="text-muted">Kecepatan Pergerakan:</span>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($spd) ?> KT</span>
                                    </li>
                                </ul>

                                <!-- Accordion untuk Raw Text SIGMET -->
                                <div class="accordion" id="accordionRaw<?= $index ?>">
                                    <div class="accordion-item border-0 bg-light">
                                        <h2 class="accordion-header" id="heading<?= $index ?>">
                                            <button class="accordion-button collapsed py-1 px-2 small bg-light text-secondary shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>" aria-expanded="false">
                                                <i class="bi bi-code-square me-1"></i> Lihat Teks Mentah (Raw SIGMET)
                                            </button>
                                        </h2>
                                        <div id="collapse<?= $index ?>" class="accordion-collapse collapse" data-bs-parent="#accordionRaw<?= $index ?>">
                                            <div class="accordion-body p-2">
                                                <pre class="mb-0 text-dark" style="font-size: 0.7rem; white-space: pre-wrap; font-family: monospace;"><?= htmlspecialchars($raw) ?></pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SISI KANAN: Peta Area SIGMET -->
                            <div class="col-lg-5">
                                <div id="map-<?= $index ?>" class="rounded border h-100" style="min-height: 220px; width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Inisialisasi Peta Leaflet & Arah Panah untuk Kartu Ini -->
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    var coordsData = <?= json_encode($coords) ?>;
                    var moveDir = "<?= $dir ?>";
                    var moveSpd = "<?= $spd ?>";
                    var topfl   = "<?= $top_display?>";

                    if (coordsData && coordsData.length > 0) {
                        var latLngs = coordsData.map(function(c) {
                            return [c.lat, c.lon];
                        });

                        var map = L.map('map-<?= $index ?>', {
                            zoomControl: false,
                            attributionControl: false
                        });

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 18
                        }).addTo(map);

                        var polyColor = '<?= ($hazard === 'VA') ? '#dc3545' : (($hazard === 'TS') ? '#ffc107' : '#0d6efd') ?>';

                        var polygon = L.polygon(latLngs, {
                            color: polyColor,
                            fillColor: polyColor,
                            fillOpacity: 0.35,
                            weight: 2
                        }).addTo(map);

                        map.fitBounds(polygon.getBounds(), { padding: [10, 10] });

                        // Logika Penambahan Panah Pergerakan jika arah valid (bukan '-')
                        if (moveDir && moveDir !== '-' && moveDir !== 'STNR') {
                            var compassDegrees = {
                                'N': 0, 'NNE': 22.5, 'NE': 45, 'ENE': 67.5,
                                'E': 90, 'ESE': 112.5, 'SE': 135, 'SSE': 157.5,
                                'S': 180, 'SSW': 202.5, 'SW': 225, 'WSW': 247.5,
                                'W': 270, 'WNW': 292.5, 'NW': 315, 'NNW': 337.5
                            };

                            var rotationAngle = compassDegrees[moveDir] !== undefined ? compassDegrees[moveDir] : 0;
                            var centerPoint = polygon.getBounds().getCenter();

                            // Buat custom HTML icon panah dengan rotasi CSS
                            var arrowIcon = L.divIcon({
                                className: 'custom-arrow-icon',
                                html: '<div style="background: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.3); border: 2px solid ' + polyColor + ';">' +
                                      '<i class="bi bi-arrow-up-short" style="font-size: 20px; color: ' + polyColor + '; transform: rotate(' + rotationAngle + 'deg); display: inline-block; line-height: 1;"></i>' +
                                      '</div>',
                                iconSize: [30, 30],
                                iconAnchor: [15, 15]
                            });

                            L.marker(centerPoint, { icon: arrowIcon })
                             .bindTooltip(topfl+ " " + moveDir + " " + moveSpd + "KT", { permanent: true, direction: 'top', offset: [0, -10] })
                             .addTo(map);
                        }
                    }
                });
            </script>

            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Jika Tidak Ada SIGMET Aktif -->
        <div class="card shadow-sm border-0 text-center py-5">
            <div class="card-body">
                <i class="bi bi-check-circle-fill text-success display-4 mb-3"></i>
                <h5 class="fw-bold text-dark">Tidak Ada Peringatan SIGMET Aktif</h5>
                <p class="text-muted small mb-0">Saat ini tidak ada laporan cuaca ekstrem atau abu vulkanik yang melintasi wilayah Nusa Tenggara Timur.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Tambahkan JS Leaflet -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<?php include 'includes/footer.php'; ?>