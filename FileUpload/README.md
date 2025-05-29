# 🗂️ File Upload Vulnerability

## 📖 Apa Itu File Upload Vulnerability?

**File Upload Vulnerability** adalah kerentanan keamanan yang terjadi ketika aplikasi mengizinkan pengguna mengunggah file **tanpa validasi dan filter yang tepat**. Hal ini memungkinkan penyerang mengunggah file berbahaya seperti shell web (`.php`, `.jsp`, dll) dan mengeksekusinya di server.

---

## 📌 Definisi

File upload menjadi rentan ketika sistem:

- ❌ Tidak memverifikasi tipe atau ekstensi file dengan benar
- ❌ Menyimpan file di direktori yang dapat diakses secara publik
- ❌ Mengizinkan eksekusi file yang diunggah

Jika sistem tidak mengontrol proses upload dengan ketat, maka file berbahaya bisa dieksekusi dan menyebabkan **Remote Code Execution (RCE)** atau **pengambilalihan server**.

---

## 💡 Contoh Kasus

Beberapa serangan umum terkait file upload:

- 📂 Mengunggah file `.php` berisi shell, lalu mengaksesnya via URL untuk eksekusi.
- 🖼️ Menyembunyikan shell di balik ekstensi ganda: `shell.php.jpg`
- 🧾 Manipulasi Content-Type agar file dianggap aman.
- 🪤 Menyimpan file di direktori yang dapat dijalankan (`/uploads/` → eksekusi langsung).

---

## 🚨 Dampak

Kerentanan ini bisa menyebabkan:

- 💥 Remote Code Execution (RCE)
- 🕵️‍♂️ Pengambilalihan sistem
- 📦 Penyebaran malware/backdoor
- 🔓 Akses ke file internal dan informasi sensitif

---

## 🧩 Tipe Serangan Umum

### 1. 🐚 Web Shell Upload
Mengunggah file seperti `shell.php` yang berisi kode PHP berbahaya.

### 2. 🎭 File Name Bypass
Mengelabui sistem dengan nama file seperti `shell.php.jpg` atau `shell.pHp5`.

### 3. 🧬 Content-Type Spoofing
Mengirim file `.php` tapi mengatur header sebagai `image/jpeg`.

### 4. 🕳️ .htaccess Exploit
Mengunggah file `.htaccess` untuk memaksa server memproses file yang seharusnya tidak dieksekusi.

### 5. 📦 Deserialization / XSS / Malware via file upload
Menanamkan skrip berbahaya dalam file PDF, SVG, atau dokumen.

---

## 🧪 Jelajahi Lab

📁 `challenge/`  
Berisi latihan berbasis CTF untuk mengeksplorasi kerentanan file upload

---

## 📚 Pelajari Lebih Lanjut

- [🔗 OWASP File Upload Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html)
- [🔗 PayloadsAllTheThings – File Upload](https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/File%20Upload)
