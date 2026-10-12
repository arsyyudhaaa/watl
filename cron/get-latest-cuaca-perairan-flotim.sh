#!/bin/bash

# ==========================================================
# Skrip Download Data JSON Cuaca Maritim BMKG
# ==========================================================

# Tentukan folder tujuan (Bisa disesuaikan dengan folder web server Anda, misal: /var/www/html/data/maritim)
OUTPUT_DIR="/home/geomette/www/data/flotim/"

# Membuat direktori jika belum ada
mkdir -p "$OUTPUT_DIR"

echo "Memulai proses download data cuaca maritim BMKG..."
echo "Menyimpan ke direktori: $OUTPUT_DIR"
echo "------------------------------------------------------"

# 1. Download Selat Flores - Lamakera (P.U.04)
echo "1. Mendownload Selat Flores - Lamakera..."
curl -s -o "${OUTPUT_DIR}/P.U.04.json" "https://maritim.bmkg.go.id/marine2026-data/perairan/P.U.04.json"
if [ $? -eq 0 ]; then
    echo "   [SUKSES] P.U.04.json berhasil disimpan."
else
    echo "   [GAGAL] Gagal mendownload P.U.04.json."
fi

# 2. Download Perairan Selatan Flores (P.U.07)
echo "2. Mendownload Perairan Selatan Flores..."
curl -s -o "${OUTPUT_DIR}/P.U.07.json" "https://maritim.bmkg.go.id/marine2026-data/perairan/P.U.07.json"
if [ $? -eq 0 ]; then
    echo "   [SUKSES] P.U.07.json berhasil disimpan."
else
    echo "   [GAGAL] Gagal mendownload P.U.07.json."
fi

# 3. Download Laut Sawu (P.U.11)
echo "3. Mendownload Laut Sawu..."
curl -s -o "${OUTPUT_DIR}/P.U.11.json" "https://maritim.bmkg.go.id/marine2026-data/perairan/P.U.11.json"
if [ $? -eq 0 ]; then
    echo "   [SUKSES] P.U.11.json berhasil disimpan."
else
    echo "   [GAGAL] Gagal mendownload P.U.11.json."
fi

# 4. Download Laut Sawu (P.U.11)
echo "3. Mendownload Perairan Utara Flores..."
curl -s -o "${OUTPUT_DIR}/P.U.03.json" "https://maritim.bmkg.go.id/marine2026-data/perairan/P.U.03.json"
if [ $? -eq 0 ]; then
    echo "   [SUKSES] P.U.03.json berhasil disimpan."
else
    echo "   [GAGAL] Gagal mendownload P.U.03.json."
fi

echo "------------------------------------------------------"
echo "Selesai! Semua data JSON perairan telah diperbarui pada $(date)."