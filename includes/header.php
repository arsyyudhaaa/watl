<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMKG | Stamet Gewayantana</title>
    <link rel="icon" type="image/x-icon" href="/asset/icon-bmkg.webp">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body { 
            background-color: #f4f5f7; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
        }

        .navbar-brand { font-size: 1.2rem; }
        .nav-link { font-weight: 500; color: #495057 !important; }
        .nav-link:hover { color: #0d6efd !important; }
        .dropdown-item:hover { background-color: #f8f9fa; color: #0d6efd; }

        .weather-section-container,
        .maritime-section-container,
        .earthquake-section-container,
        .activity-section-container {
            max-width: 1200px;
            margin: 0 auto 3rem auto; 
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .weather-section-container { margin-top: 2rem; border-top: 4px solid #0d6efd; }
        .maritime-section-container { border-top: 4px solid #0dcaf0; } 
        .earthquake-section-container { border-top: 4px solid #dc3545; }
        .activity-section-container { border-top: 4px solid #ffc107; }

        .weather-header, .earthquake-header, .maritime-header {
            background-color: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: 1.2rem 1.5rem;
        }

        .grid-border-bottom { border-bottom: 1px solid #e0e0e0; }
        .grid-border-right { border-right: 1px solid #e0e0e0; }
        
        .time-tabs { min-width: 150px; background: #fafafa; }
        .time-tabs .nav-link {
            border-radius: 0;
            color: #555;
            text-align: center;
            padding: 1rem 0.5rem;
            border-right: 1px solid #e0e0e0;
            border-bottom: 1px solid #e0e0e0;
            background: #fafafa;
        }
        .time-tabs .nav-link.active {
            background: white;
            border-right: 3px solid #0d6efd;
            font-weight: bold;
            color: #0d6efd;
        }
        .time-text-large { font-size: 1.25rem; font-weight: 700; line-height: 1; }
        .time-text-small { font-size: 0.75rem; text-transform: uppercase; color: #666; margin-top: 2px; }

        .weather-cell { padding: 1rem 1.2rem; position: relative; }
        .weather-cell:hover { background-color: #f9f9f9; }
        .temp-large { font-size: 1.8rem; font-weight: 600; color: #222; }
        .weather-icon { width: 35px; height: 35px; object-fit: contain; }
        
        .shakemap-img {
            width: 50%;
            height: auto;
            margin: 0 auto;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.2s;
            display: block;
        }
        .shakemap-img:hover {
            opacity: 0.9;
            transform: scale(1.05);
        }

        /* Responsive UI */
        @media (max-width: 767.98px) {
            .time-tabs { border-right: none !important; border-bottom: 2px solid #e0e0e0; }
            .time-tabs .nav {
                flex-direction: row !important; flex-wrap: nowrap;
                overflow-x: auto; overflow-y: hidden;
                -webkit-overflow-scrolling: touch; scrollbar-width: none; 
            }
            .time-tabs .nav::-webkit-scrollbar { display: none; }
            .time-tabs .nav-link { min-width: 100px; padding: 0.75rem 0.5rem; border-right: 1px solid #e0e0e0; border-bottom: none !important; }
            .time-tabs .nav-link.active { border-bottom: 3px solid #0d6efd !important; }
            .time-text-large { font-size: 1rem; }
            .temp-large { font-size: 1.25rem; }
            .weather-icon { width: 25px; height: 25px; }
            .weather-cell { padding: 0.75rem 0.5rem; }
            .shakemap-img { width: 90%; }
        }
    </style>
</head>
<body>

    <!-- ==========================================
         HEADER / NAVBAR
    =========================================== -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container" style="max-width: 1200px;">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="/asset/bmkg-logo.webp" alt="Logo BMKG" class="me-3" style="height: 50px; width: auto;">
                <div class="d-flex flex-column justify-content-center">
                    <span class="text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">BADAN METEOROLOGI, KLIMATOLOGI, DAN GEOFISIKA</span>
                    <strong class="text-dark" style="font-size: 1.1rem; line-height: 1.2;">Stasiun Meteorologi Gewayantana - Flores Timur</strong>
                </div>
            </a>
            
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu" aria-controls="mainMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="mainMenu">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item dropdown me-2">
                        <a class="nav-link dropdown-toggle" href="#" id="profilDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Profil</a>
                        <ul class="dropdown-menu border-0 shadow" aria-labelledby="profilDropdown">
                            <li><a class="dropdown-item" href="#">Visi</a></li>
                            <li><a class="dropdown-item" href="#">Misi</a></li>
                            <li><a class="dropdown-item" href="#">Sejarah</a></li>
                            <li><a class="dropdown-item" href="pegawai.php">Pegawai</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown me-2">
                        <a class="nav-link dropdown-toggle" href="#" id="cuacaDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Cuaca</a>
                        <ul class="dropdown-menu border-0 shadow" aria-labelledby="cuacaDropdown">
                            <li><a class="dropdown-item" href="detail_cuaca.php">Prakiraan Cuaca</a></li>
                            <li><a class="dropdown-item" href="#">Peringatan Dini</a></li>
                            <li><a class="dropdown-item" href="sigmet_ntt.php">Sigmet NTT</a></li>
                            <li><a class="dropdown-item" href="detail_perairan.php">Informasi Tinggi Gelombang</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="gempaDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Gempa Bumi</a>
                        <ul class="dropdown-menu border-0 shadow dropdown-menu-end" aria-labelledby="gempaDropdown">
                            <li><a class="dropdown-item" href="#">Gempa Bumi Terkini</a></li>
                            <li><a class="dropdown-item" href="#">Gempa Bumi Dirasakan</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
