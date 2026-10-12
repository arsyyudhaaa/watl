# watl
Development web of Stamet Gewayantana

1. Folder cron, berisi file .sh untuk otomasi download data yang akan ditampilkan di web
- get-latest-cuaca-perairan-flotim.sh tiap menit ke-9
- get-latest-cuaca-pelabuhan-flotim.sh tiap ke-9
- get-latest-cuaca-flotim.sh tiap menit ke-10
- get-latest-cuaca-flotim-kec.sh tiap menit ke-11

2. Buat folder data di bawah folder www.

3. Folder Includes dan semua file .php diupload ke folder www di server.

Topologi direktorinya:
www
|_data
|_includes
|_index.php
|_detail_cuaca.php
|_semua file .php


