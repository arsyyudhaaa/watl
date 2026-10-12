#!/bin/bash

# Menentukan direktori penyimpanan file JSON
OUTPUT_DIR="/home/geomette/www/data/flotim/kec"
mkdir -p "$OUTPUT_DIR"

echo "Memulai proses unduh data cuaca desa Kabupaten Flores Timur..."

# Looping dari 01 hingga 19 (argumen -w pada seq otomatis menambahkan angka 0 di depan angka satuan)
for i in $(seq -w 1 19); do
    KODE_KEC="53.06.$i"
    URL="https://api.bmkg.go.id/publik/prakiraan-cuaca?&adm3=$KODE_KEC"
    FILE_OUTPUT="$OUTPUT_DIR/cuaca_$KODE_KEC.json"
    
    echo "Mengunduh: $URL"
    # Menggunakan curl dalam mode silent (-s) dan mengarahkan output ke file (-o)
    curl -s -o "$FILE_OUTPUT" "$URL"
    
    # Memberikan jeda 1 detik untuk menghindari rate limit/pemblokiran dari server BMKG
    sleep 1
done

echo "Proses selesai! Seluruh file JSON telah tersimpan di direktori: $OUTPUT_DIR"