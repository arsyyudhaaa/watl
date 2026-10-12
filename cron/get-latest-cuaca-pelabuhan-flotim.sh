#!/bin/bash

# ==========================================================
# Skrip Download Data JSON Cuaca Pelabuhan BMKG
# ==========================================================

# Tentukan folder tujuan (Bisa disesuaikan, misal: /var/www/html/data/pelabuhan)
OUTPUT_DIR="/home/geomette/www/data/flotim/"

# Membuat direktori jika belum ada
mkdir -p "$OUTPUT_DIR"

echo "Memulai proses download data cuaca pelabuhan BMKG..."
echo "Menyimpan ke direktori: $OUTPUT_DIR"
echo "------------------------------------------------------"

# 1. Download Pelabuhan Larantuka (XU004)
echo "1. Mendownload Pelabuhan Larantuka..."
curl -s -o "${OUTPUT_DIR}/XU004.json" "https://maritim.bmkg.go.id/marine2026-data/pelabuhan/XU004.json"
if [ $? -eq 0 ]; then
    echo "   [SUKSES] XU004.json berhasil disimpan."
else
    echo "   [GAGAL] Gagal mendownload XU004.json."
fi

# 2. Download Pelabuhan Adonara (XU005)
echo "2. Mendownload Pelabuhan Adonara..."
curl -s -o "${OUTPUT_DIR}/XU005.json" "https://maritim.bmkg.go.id/marine2026-data/pelabuhan/XU005.json"
if [ $? -eq 0 ]; then
    echo "   [SUKSES] XU005.json berhasil disimpan."
else
    echo "   [GAGAL] Gagal mendownload XU005.json."
fi

# 3. Download Pelabuhan Waiwerang (XU006)
echo "3. Mendownload Pelabuhan Waiwerang..."
curl -s -o "${OUTPUT_DIR}/XU006.json" "https://maritim.bmkg.go.id/marine2026-data/pelabuhan/XU006.json"
if [ $? -eq 0 ]; then
    echo "   [SUKSES] XU006.json berhasil disimpan."
else
    echo "   [GAGAL] Gagal mendownload XU006.json."
fi

echo "------------------------------------------------------"
echo "Selesai! Semua data JSON pelabuhan telah diperbarui pada $(date)."