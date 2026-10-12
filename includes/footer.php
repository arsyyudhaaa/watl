<!-- Footer -->
<footer class="bg-dark text-white pt-5 pb-4 mt-auto">
    <div class="container">
        <div class="row gy-4">
            <!-- Kolom 1: Profil -->
            <div class="col-lg-5 col-md-12 pe-lg-4">
                <h5 class="fw-bold mb-3">Stasiun Meteorologi Gewayantana</h5>
                <p class="small text-light" style="opacity: 0.85; line-height: 1.6; text-align: justify;">
                    Unit Pelaksana Teknis Badan Meteorologi, Klimatologi, dan Geofisika yang menyediakan informasi cuaca penerbangan dan publik secara akurat and real-time untuk wilayah Flores Timur dan sekitarnya.
                </p>
            </div>

            <!-- Kolom 2: Sosial Media -->
            <div class="col-lg-3 col-md-5">
                <h5 class="fw-bold mb-3">Follow Us</h5>
                <div class="d-flex gap-3">
                    <a href="#" class="text-white fs-4" style="opacity: 0.8; transition: 0.3s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-white fs-4" style="opacity: 0.8; transition: 0.3s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="text-white fs-4" style="opacity: 0.8; transition: 0.3s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white fs-4" style="opacity: 0.8; transition: 0.3s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

            <!-- Kolom 3: Kontak -->
            <div class="col-lg-4 col-md-7">
                <h5 class="fw-bold mb-3">Kontak Kami</h5>
                <ul class="list-unstyled small text-light" style="opacity: 0.85; line-height: 1.8;">
                    <li class="mb-2 d-flex">
                        <i class="bi bi-geo-alt-fill me-2 mt-1 text-primary"></i>
                        <span>Jl. Soekarno-Hatta No.76, Kec. Ile Mandiri, Flores Timur, NTT</span>
                    </li>
                    <li class="mb-2 d-flex align-items-center">
                        <i class="bi bi-telephone-fill me-2 text-primary"></i>
                        <span>081339076013</span>
                    </li>
                    <li class="mb-2 d-flex align-items-center">
                        <i class="bi bi-envelope-fill me-2 text-primary"></i>
                        <span>stamet.gewayantana@bmkg.go.id</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Garis Pembatas & Copyright -->
        <hr class="mt-4 mb-3 border-secondary">
        <div class="row align-items-center small text-light" style="opacity: 0.7;">
            <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                &copy; <?= date('Y'); ?> Stasiun Meteorologi Gewayantana.
            </div>
            <div class="col-md-6 text-center text-md-end">
                Badan Meteorologi, Klimatologi, dan Geofisika
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>