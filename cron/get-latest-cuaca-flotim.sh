#!/bin/bash

# Konfigurasi Direktori dan URL
API_URL="https://api.bmkg.go.id/publik/prakiraan-cuaca?&adm2=53.06"
OUTPUT_DIR="/home/geomette/www/data/flotim/"
OUTPUT_FILE="$OUTPUT_DIR/cuaca_flotim.json"
TEMP_FILE="$OUTPUT_DIR/cuaca_flotim_temp.json"

# Buat direktori cache jika belum ada
mkdir -p "$OUTPUT_DIR"

# Unduh data dari API BMKG ke file sementara menggunakan curl (-s = silent, -f = fail on server errors)
if curl -s -f "$API_URL" -o "$TEMP_FILE"; then
    # Pastikan file sementara tidak kosong dan valid ukurannya (> 100 bytes)
    if [ -s "$TEMP_FILE" ]; then
        mv "$TEMP_FILE" "$OUTPUT_FILE"
        echo "$(date '+%Y-%m-%d %H:%M:%S') - SUKSES: Data prakiraan cuaca Flores Timur berhasil diperbarui."
    else
        echo "$(date '+%Y-%m-%d %H:%M:%S') - PERINGATAN: File unduhan kosong, cache lama dipertahankan."
        rm -f "$TEMP_FILE"
    fi
else
    echo "$(date '+%Y-%m-%d %H:%M:%S') - ERROR: Gagal terhubung ke API BMKG, cache lama dipertahankan."
    rm -f "$TEMP_FILE"
fi
