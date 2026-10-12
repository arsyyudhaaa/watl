<?php
// =========================================================================
// 1. PEMBACAAN DATA JSON LOKAL (Prakiraan Cuaca Kabupaten Flores Timur)
// =========================================================================
$json_file = $_SERVER['DOCUMENT_ROOT'] . '/data/flotim/cuaca_flotim.json';
$data_kecamatan_list = [];

if (file_exists($json_file)) {
    $json_content = file_get_contents($json_file);
    $parsed_data = json_decode($json_content, true);
    
    if (isset($parsed_data['data']) && is_array($parsed_data['data'])) {
        $data_kecamatan_list = $parsed_data['data'];
    }
}

// =========================================================================
// 2. PENARIKAN DATA API GEMPA BUMI BMKG
// =========================================================================
$url_gempa = "https://data.bmkg.go.id/DataMKG/TEWS/autogempa.json";
$json_gempa = @file_get_contents($url_gempa); 
$gempa = [
    'Tanggal' => '-', 'Jam' => '-', 'Magnitude' => '-', 
    'Kedalaman' => '-', 'Wilayah' => 'Data tidak tersedia', 
    'Potensi' => '-', 'Shakemap' => '',
    'Lintang' => '-', 'Bujur' => '-', 'Dirasakan' => '-'
];

if ($json_gempa !== false) {
    $data_api_gempa = json_decode($json_gempa, true);
    if (isset($data_api_gempa['Infogempa']['gempa'])) {
        $gempa = $data_api_gempa['Infogempa']['gempa'];
    }
}

// =========================================================================
// 3. PENGAMBILAN DATA CUACA PERAIRAN
// =========================================================================
$perairan_files = [
    $_SERVER['DOCUMENT_ROOT'] . '/data/flotim/P.U.04.json',
    $_SERVER['DOCUMENT_ROOT'] . '/data/flotim/P.U.03.json',
    $_SERVER['DOCUMENT_ROOT'] . '/data/flotim/P.U.07.json',
    $_SERVER['DOCUMENT_ROOT'] . '/data/flotim/P.U.11.json'
];

$data_perairan = [];
$current_time = time(); 

foreach ($perairan_files as $file) {
    if (file_exists($file)) {
        $json_content = file_get_contents($file);
        $parsed_data = json_decode($json_content, true);

        if (isset($parsed_data['forecast_day1'])) {
            $nama_perairan = $parsed_data['name'] ?? 'Unknown';
            $forecast_data = $parsed_data['forecast_day1'];

            $selected_slot = null;
            $min_diff = null;

            foreach ($forecast_data as $slot) {
                $slot_time = strtotime($slot['time']);
                $diff = $slot_time - ($current_time); 

                if ($diff >= 0 && ($min_diff === null || $diff < $min_diff)) {
                    $min_diff = $diff;
                    $selected_slot = $slot;
                }
            }
            
            if (!$selected_slot && !empty($forecast_data)) {
                 $selected_slot = end($forecast_data);
            }

            if ($selected_slot) {
                $gelombang = $selected_slot['wave_height'] . ' m (' . $selected_slot['wave_cat'] . ')';
                $cuaca = $selected_slot['weather'];
                $angin = $selected_slot['wind_from'] . ', ' . $selected_slot['wind_speed'] . ' - ' . $selected_slot['wind_gust'] . ' knots';
                
                $waktu_valid = '-';
                if (isset($selected_slot['time'])) {
                    $waktu_ts = strtotime($selected_slot['time'] . ' +8 hours');
                    $waktu_valid = date('d M Y, H:i', $waktu_ts) . ' WITA';
                }

                $data_perairan[] = [
                    'nama' => $nama_perairan,
                    'gelombang' => $gelombang,
                    'cuaca' => $cuaca,
                    'angin' => $angin,
                    'waktu' => $waktu_valid
                ];
            }
        }
    }
}

// =========================================================================
// 4. PENGAMBILAN DATA CUACA PELABUHAN
// =========================================================================
$pelabuhan_files = [
    $_SERVER['DOCUMENT_ROOT'] . '/data/flotim/XU004.json',
    $_SERVER['DOCUMENT_ROOT'] . '/data/flotim/XU005.json',
    $_SERVER['DOCUMENT_ROOT'] . '/data/flotim/XU006.json'
];

$data_pelabuhan = [];

foreach ($pelabuhan_files as $file) {
    if (file_exists($file)) {
        $json_content = file_get_contents($file);
        $parsed_data = json_decode($json_content, true);

        if (isset($parsed_data['forecast_day1'])) {
            $nama_pelabuhan = $parsed_data['name'] ?? 'Unknown';
            $forecast_data = $parsed_data['forecast_day1'];

            $selected_slot = null;
            $min_diff = null;

            foreach ($forecast_data as $slot) {
                $slot_time = strtotime($slot['time']); 
                $diff = $slot_time - ($current_time); 

                if ($diff >= 0 && ($min_diff === null || $diff < $min_diff)) {
                    $min_diff = $diff;
                    $selected_slot = $slot;
                }
            }
            
            if (!$selected_slot && !empty($forecast_data)) {
                 $selected_slot = end($forecast_data);
            }

            if ($selected_slot) {
                $gelombang = isset($selected_slot['wave_height']) ? $selected_slot['wave_height'] . ' m (' . $selected_slot['wave_cat'] . ')' : '-';
                $cuaca = $selected_slot['weather'] ?? '-';
                $angin = isset($selected_slot['wind_from']) ? $selected_slot['wind_from'] . ', ' . $selected_slot['wind_speed'] . ' - ' . $selected_slot['wind_gust'] . ' knots' : '-';
                
                $waktu_valid = '-';
                if (isset($selected_slot['time'])) {
                    $waktu_ts = strtotime($selected_slot['time'] . ' +8 hours'); 
                    $waktu_valid = date('d M Y, H:i', $waktu_ts) . ' WITA';
                }

                $data_pelabuhan[] = [
                    'nama' => $nama_pelabuhan,
                    'gelombang' => $gelombang,
                    'cuaca' => $cuaca,
                    'angin' => $angin,
                    'waktu' => $waktu_valid
                ];
            }
        }
    }
}

// Pengolahan navigasi waktu cuaca kecamatan
$jumlah_slot = 6;
$list_slot_waktu = [];
if (!empty($data_kecamatan_list)) {
    $sampel_kec = $data_kecamatan_list[0];
    $days_array = [];
    if (is_array($sampel_kec) && isset($sampel_kec['cuaca'])) {
        $days_array = $sampel_kec['cuaca'];
    } elseif (is_object($sampel_kec) && isset($sampel_kec->cuaca)) {
        $days_array = $sampel_kec->cuaca;
    }
    
    $flat_sampel_slots = [];
    foreach ($days_array as $day_slots) {
        if (is_array($day_slots) || is_object($day_slots)) {
            foreach ($day_slots as $slot) {
                $flat_sampel_slots[] = $slot;
            }
        }
    }
    
    for ($i = 0; $i < $jumlah_slot; $i++) {
        if (isset($flat_sampel_slots[$i])) {
            $slot = $flat_sampel_slots[$i];
            $local_dt = is_array($slot) ? ($slot['local_datetime'] ?? '') : ($slot->local_datetime ?? '');
            
            if (!empty($local_dt)) {
                $timestamp = strtotime($local_dt);
                $list_slot_waktu[] = [
                    'jam' => date('H:i', $timestamp),
                    'tgl' => date('d M', $timestamp)
                ];
            }
        }
    }
}

// Panggil Header
include 'includes/header.php';
?>

    <!-- ==========================================
         SECTION PRAKIRAAN CUACA
    =========================================== -->
    <div class="weather-section-container rounded-3 overflow-hidden px-0">
        <div class="weather-header d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-cloud-sun-fill text-primary me-2"></i> Prakiraan Cuaca Kabupaten Flores Timur
            </h5>
        </div>
        <div class="bg-white">
            <div class="row g-0">
                <div class="col-12 col-md-2 time-tabs border-md-start grid-border-bottom">
                    <div class="nav flex-md-column nav-pills" id="v-pills-tab" role="tablist">
                        <?php foreach ($list_slot_waktu as $idx => $slot): 
                            $active_cls = ($idx === 0) ? 'active' : '';
                        ?>
                        <button class="nav-link <?= $active_cls ?> grid-border-bottom" id="v-pills-time<?= $idx ?>-tab" data-bs-toggle="pill" data-bs-target="#v-pills-time<?= $idx ?>" type="button" role="tab">
                            <div class="time-text-large"><?= htmlspecialchars($slot['jam']) ?> WITA</div>
                            <div class="time-text-small"><?= htmlspecialchars($slot['tgl']) ?></div>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-12 col-md-10 border-md-start border-md-0">
                    <div class="tab-content h-100" id="v-pills-tabContent">
                        <?php for ($slot_idx = 0; $slot_idx < $jumlah_slot; $slot_idx++): 
                            $active_pane = ($slot_idx === 0) ? 'show active' : '';
                        ?>
                        <div class="tab-pane fade <?= $active_pane ?> h-100" id="v-pills-time<?= $slot_idx ?>" role="tabpanel">
                            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-0 h-100">
                                <?php if (!empty($data_kecamatan_list)): ?>
                                    <?php foreach ($data_kecamatan_list as $kec): 
                                        $nama_kecamatan = 'Unknown';
                                        if (is_array($kec)) {
                                            $nama_kecamatan = $kec['lokasi']['kecamatan'] ?? ($kec['lokasi']->kecamatan ?? 'Unknown');
                                        } elseif (is_object($kec)) {
                                            $nama_kecamatan = $kec->lokasi['kecamatan'] ?? ($kec->lokasi->kecamatan ?? 'Unknown');
                                        }

                                        $kec_days = [];
                                        if (is_array($kec) && isset($kec['cuaca'])) {
                                            $kec_days = $kec['cuaca'];
                                        } elseif (is_object($kec) && isset($kec->cuaca)) {
                                            $kec_days = $kec->cuaca;
                                        }

                                        $flat_kec_slots = [];
                                        foreach ($kec_days as $day_slots) {
                                            if (is_array($day_slots) || is_object($day_slots)) {
                                                foreach ($day_slots as $slot) { $flat_kec_slots[] = $slot; }
                                            }
                                        }

                                        $suhu = '-'; $desc = 'Data tidak tersedia'; $img = '';
                                        if (isset($flat_kec_slots[$slot_idx])) {
                                            $slot_data = $flat_kec_slots[$slot_idx];
                                            if (is_array($slot_data)) {
                                                $suhu = $slot_data['t'] ?? '-';
                                                $desc = $slot_data['weather_desc'] ?? '-';
                                                $img = $slot_data['image'] ?? '';
                                            } elseif (is_object($slot_data)) {
                                                $suhu = $slot_data->t ?? '-';
                                                $desc = $slot_data->weather_desc ?? '-';
                                                $img = $slot_data->image ?? '';
                                            }
                                        }
                                    ?>
                                    <div class="col weather-cell grid-border-right grid-border-bottom">
                                        <h6 class="fw-bold mb-1 text-dark text-truncate" style="font-size: 0.85rem;"><?= htmlspecialchars($nama_kecamatan) ?></h6>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="temp-large"><?= htmlspecialchars($suhu) ?>&deg;</div>
                                            <?php if (!empty($img)): ?>
                                                <img src="<?= htmlspecialchars($img) ?>" class="weather-icon" alt="Ikon Cuaca">
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted text-truncate" style="font-size: 0.75rem;"><?= htmlspecialchars($desc) ?></div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12 p-5 text-center text-muted">Data file JSON belum tersedia.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Link Lihat Detail di bagian bawah section prakiraan cuaca -->
        <div class="bg-white p-3 text-end border-top">
            <a href="detail_cuaca.php" class="text-decoration-none small fw-bold text-primary">
                Lihat Detail <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>


    <!-- ==========================================
         SECTION CUACA MARITIM
    =========================================== -->
    <div class="maritime-section-container rounded-3 overflow-hidden px-0">
        <div class="maritime-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-water text-info me-2"></i> Informasi Cuaca Maritim
                </h5>
            </div>
        </div>
        <div class="p-4 bg-white">
            
            <!-- Grid Perairan (4 Kolom) -->
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">
                <i class="bi bi-tsunami me-1"></i> Prakiraan Cuaca Perairan
            </h6>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3 mb-2">
                <?php if (!empty($data_perairan)): ?>
                    <?php foreach ($data_perairan as $perairan): ?>
                    <div class="col">
                        <div class="card h-100 border shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="card-title fw-bold text-dark mb-3"><?= htmlspecialchars($perairan['nama']) ?></h6>
                                <div class="d-flex justify-content-between mb-2 small border-bottom pb-1">
                                    <span class="text-muted"><i class="bi bi-water me-1"></i> Gelombang</span>
                                    <span class="fw-semibold text-dark text-end"><?= htmlspecialchars($perairan['gelombang']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 small border-bottom pb-1">
                                    <span class="text-muted"><i class="bi bi-cloud-sun me-1"></i> Cuaca</span>
                                    <span class="fw-semibold text-dark text-end"><?= htmlspecialchars($perairan['cuaca']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 small border-bottom pb-1">
                                    <span class="text-muted"><i class="bi bi-wind me-1"></i> Angin</span>
                                    <span class="fw-semibold text-dark text-end"> dari <?= htmlspecialchars($perairan['angin']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span class="text-muted"><i class="bi bi-clock me-1"></i> Waktu</span>
                                    <span class="fw-semibold text-dark text-end"><?= htmlspecialchars($perairan['waktu']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 p-3 text-muted">Data cuaca perairan tidak tersedia.</div>
                <?php endif; ?>
            </div>

            <!-- Link Detail Perairan -->
            <div class="text-end mb-2"> <!-- Diubah dari mb-5 menjadi mb-2 -->
                <a href="detail_perairan.php" class="text-decoration-none small fw-bold text-primary">
                    Lihat Detail <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <!-- Grid Pelabuhan -->
            <h6 class="fw-bold mb-3 text-danger border-bottom pb-2 mt-3"> <!-- Tambahan mt-3 opsional jika butuh sedikit ruang dari teks "Lihat Detail" -->
                <i class="bi bi-geo-alt-fill me-1"></i> Prakiraan Cuaca Pelabuhan
            </h6>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mb-2">
                <?php if (!empty($data_pelabuhan)): ?>
                    <?php foreach ($data_pelabuhan as $pelabuhan): ?>
                    <div class="col">
                        <div class="card h-100 border shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="card-title fw-bold text-dark mb-3"><?= htmlspecialchars($pelabuhan['nama']) ?></h6>
                                <div class="d-flex justify-content-between mb-2 small border-bottom pb-1">
                                    <span class="text-muted"><i class="bi bi-water me-1"></i> Gelombang</span>
                                    <span class="fw-semibold text-dark text-end"><?= htmlspecialchars($pelabuhan['gelombang']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 small border-bottom pb-1">
                                    <span class="text-muted"><i class="bi bi-cloud-sun me-1"></i> Cuaca</span>
                                    <span class="fw-semibold text-dark text-end"><?= htmlspecialchars($pelabuhan['cuaca']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 small border-bottom pb-1">
                                    <span class="text-muted"><i class="bi bi-wind me-1"></i> Angin</span>
                                    <span class="fw-semibold text-dark text-end"> dari <?= htmlspecialchars($pelabuhan['angin']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span class="text-muted"><i class="bi bi-clock me-1"></i> Waktu</span>
                                    <span class="fw-semibold text-dark text-end"><?= htmlspecialchars($pelabuhan['waktu']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 p-3 text-muted">Data cuaca pelabuhan tidak tersedia. Pastikan file JSON pelabuhan sudah diunduh ke direktori /data/pelabuhan/.</div>
                <?php endif; ?>
            </div>

            <!-- Link Detail Pelabuhan -->
            <div class="text-end">
                <a href="detail_pelabuhan.php" class="text-decoration-none small fw-bold text-danger">
                    Lihat Detail <i class="bi bi-arrow-right"></i>
                </a>
            </div>

        </div>
    </div>


    <!-- ==========================================
         SECTION INFO GEMPA TERKINI 
    =========================================== -->
    <div class="earthquake-section-container rounded-3 overflow-hidden px-0">
        <div class="earthquake-header d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-activity text-danger me-2"></i> Gempa Bumi Terkini
            </h5>
        </div>
        <div class="p-4 bg-white">
            <div class="row align-items-center">
                
                <div class="col-md-7">
                    <span class="badge bg-danger mb-2"><?= htmlspecialchars($gempa['Tanggal']) ?> | <?= htmlspecialchars($gempa['Jam']) ?></span>
                    <div class="d-flex align-items-baseline mb-3">
                        <h2 class="text-danger fw-bold display-5 me-3 mb-0">M <?= htmlspecialchars($gempa['Magnitude']) ?></h2>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <strong class="text-dark text-truncate d-block fs-6"><?= htmlspecialchars($gempa['Wilayah']) ?></strong>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-sm-4">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <span class="text-muted small d-block">Kedalaman</span>
                                <strong class="text-dark fs-5"><?= htmlspecialchars($gempa['Kedalaman']) ?></strong>
                            </div>
                        </div>
                        <div class="col-6 col-sm-8">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <span class="text-muted small d-block">Koordinat:</span>
                                <strong class="text-dark fs-6 text-truncate d-block"><?= htmlspecialchars($gempa['Lintang']) ?> - <?= htmlspecialchars($gempa['Bujur']) ?></strong>
                            </div>
                        </div>
                    </div>

                    <?php 
                        $bg_potensi = (strpos(strtolower($gempa['Potensi']), 'tidak berpotensi tsunami') !== false) ? 'alert-success' : 'alert-danger';
                    ?>
                    <div class="alert <?= $bg_potensi ?> py-2 px-3 mb-0 small fw-bold text-center">
                        <i class="bi bi-info-circle-fill me-1"></i> <?= htmlspecialchars($gempa['Potensi']) ?>
                    </div>
                </div>

                <div class="col-md-5 text-center mt-4 mt-md-0">
                    <?php if(!empty($gempa['Shakemap'])): ?>
                        <img src="https://data.bmkg.go.id/DataMKG/TEWS/<?= htmlspecialchars($gempa['Shakemap']) ?>" 
                             class="shakemap-img shadow-sm" 
                             alt="Peta Guncangan Gempa BMKG"
                             data-bs-toggle="modal" 
                             data-bs-target="#shakemapModal">
                        <span class="d-block text-muted mt-2" style="font-size: 0.75rem;">Klik gambar untuk memperbesar</span>
                    <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center border rounded text-muted small p-4" style="height: 180px;">
                            Peta Guncangan Tidak Tersedia
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>


    <!-- ==========================================
         SECTION INFORMASI KEGIATAN
    =========================================== -->
    <div class="activity-section-container rounded-3 overflow-hidden px-0">
        <div class="weather-header d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-newspaper text-warning me-2"></i> Kegiatan Stasiun Meteorologi Gewayantana
            </h5>
        </div>
        <div class="p-4 bg-white">
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col">
                    <div class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <span class="badge bg-primary mb-2">28 Agustus 2026</span>
                            <h5 class="card-title fw-bold fs-6 text-dark">Sosialisasi Cuaca Ekstrem bagi Nelayan dan Petani Larantuka</h5>
                            <p class="card-text text-muted small">Stasiun Meteorologi Gewayantana memberikan edukasi pemanfaatan informasi cuaca maritim untuk keselamatan nelayan dan pengolahan sektor pertanian...</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 pb-3">
                            <a href="#" class="btn btn-sm btn-outline-primary w-100">Baca Selengkapnya</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <span class="badge bg-primary mb-2">15 Agustus 2026</span>
                            <h5 class="card-title fw-bold fs-6 text-dark">Kunjungan Edukasi Siswa Sekolah ke Kantor BMKG Gewayantana</h5>
                            <p class="card-text text-muted small">Mengenalkan peralatan meteorologi dan cara kerja pengamatan cuaca secara langsung guna meningkatkan kesadaran sadar bencana sejak dini...</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 pb-3">
                            <a href="#" class="btn btn-sm btn-outline-primary w-100">Baca Selengkapnya</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <span class="badge bg-primary mb-2">10 Agustus 2026</span>
                            <h5 class="card-title fw-bold fs-6 text-dark">Rapat Koordinasi Mitigasi Bencana Hidrometeorologi Daerah</h5>
                            <p class="card-text text-muted small">Koordinasi lintas instansi di Kabupaten Flores Timur dalam rangka memperkuat kesiapsiagaan menghadapi potensi cuaca ekstrem tahun ini...</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 pb-3">
                            <a href="#" class="btn btn-sm btn-outline-primary w-100">Baca Selengkapnya</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- BOOTSTRAP MODAL UNTUK GAMBAR SHAKEMAP -->
    <div class="modal fade" id="shakemapModal" tabindex="-1" aria-labelledby="shakemapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark text-white border-0">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="shakemapModalLabel"><i class="bi bi-map me-2"></i>Peta Guncangan Gempa (Shakemap)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-3">
                    <?php if(!empty($gempa['Shakemap'])): ?>
                        <img src="https://data.bmkg.go.id/DataMKG/TEWS/<?= htmlspecialchars($gempa['Shakemap']) ?>" class="img-fluid rounded" alt="Peta Guncangan Full Size">
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php 
// Panggil Footer
include 'includes/footer.php'; 
?>