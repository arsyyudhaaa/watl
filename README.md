# Web Development - Stamet Gewayantana

Repositori ini berisi berkas sumber untuk sistem web Stasiun Meteorologi Gewayantana, Flores Timur. Sistem ini menyajikan informasi cuaca wilayah, perairan, dan pelabuhan yang diperbarui secara otomatis menggunakan skrip *cron job*.

---

## 📂 Struktur Direktori

```text
root/
├── cron/
│   ├── get-latest-cuaca-perairan-flotim.sh
│   ├── get-latest-cuaca-pelabuhan-flotim.sh
│   ├── get-latest-cuaca-flotim.sh
│   └── get-latest-cuaca-flotim-kec.sh
└── www/
    ├── data/
    ├── includes/
    ├── index.php
    ├── detail_cuaca.php
    └── *.php
```
- Unggah seluruh direktori includes/ dan semua file .php ke folder root web server (direktori www/ atau public_html/).
- Buat folder data/ di dalam direktori www/ untuk menampung data unduhan otomatis:
```bash
  mkdir -p www/data
  chmod -R 755 www/data
```

## Otomasi Data (cron/)
- Jalankan skrip shell di dalam folder cron/ secara berkala menggunakan crontab pada server. Akses editor crontab:
  ```bash
  crontab -e
  ```
- Tambahkan baris berikut (sesuaikan /path/to/ dengan lokasi absolut direktori di server):
  ```bash
  # Otomasi Unduh Data Cuaca Stamet Gewayantana
  9 * * * * /path/to/cron/get-latest-cuaca-perairan-flotim.sh
  9 * * * * /path/to/cron/get-latest-cuaca-pelabuhan-flotim.sh
  10 * * * * /path/to/cron/get-latest-cuaca-flotim.sh
  11 * * * * /path/to/cron/get-latest-cuaca-flotim-kec.sh
  ```
  Jadwal Eksekusi Skrip:
  * Menit ke-9: Cuaca Perairan & Cuaca Pelabuhan Flores Timur
  * Menit ke-10: Cuaca Wilayah Kabupaten Flores Timur
  * Menit ke-11: Cuaca Tingkat Kecamatan Flores Timur

 


