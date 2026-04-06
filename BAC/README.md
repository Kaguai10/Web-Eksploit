# Broken Access Control (BAC)

## Pengertian

Broken Access Control (BAC) adalah kerentanan keamanan yang terjadi ketika sistem gagal membatasi akses pengguna sesuai dengan hak dan peran yang seharusnya. Analogi sederhananya: seperti gedung perkantoran yang tidak memiliki penjaga resepsionis -- siapa pun bisa masuk ke ruangan mana pun tanpa kartu akses yang sesuai.

Ketika kontrol akses tidak diterapkan dengan benar, pengguna dapat melakukan tindakan yang seharusnya hanya dapat dilakukan oleh pengguna dengan hak lebih tinggi.

## Contoh Source Code yang Rentan BAC

### 1. IDOR pada PHP

**Kode Rentan:**
```php
<?php
session_start();

// Mengambil data profil berdasarkan parameter user_id
$user_id = $_GET['user_id'];

// BAHAYA: Tidak ada verifikasi apakah pengguna berhak mengakses data ini
$conn = new mysqli("localhost", "root", "", "app_db");
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = $conn->query($query);

$user = $result->fetch_assoc();
echo "Nama: " . $user['name'];
echo "Email: " . $user['email'];
?>
```

**Kode Aman:**
```php
<?php
session_start();

// Memastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_GET['user_id'];

// AMAN: Verifikasi bahwa pengguna hanya dapat mengakses datanya sendiri
if ($user_id != $_SESSION['user_id']) {
    http_response_code(403);
    echo "Akses ditolak. Anda tidak memiliki izin untuk mengakses data ini.";
    exit();
}

$conn = new mysqli("localhost", "root", "", "app_db");
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = $conn->query($query);

$user = $result->fetch_assoc();
echo "Nama: " . $user['name'];
echo "Email: " . $user['email'];
?>
```

### 2. Akses Halaman Admin pada Express.js (Node.js)

**Kode Rentan:**
```javascript
const express = require('express');
const app = express();

// BAHAYA: Tidak ada middleware untuk memverifikasi role pengguna
app.get('/admin/dashboard', (req, res) => {
    res.render('admin-dashboard');
});

app.get('/admin/users', (req, res) => {
    res.json({ users: ['user1', 'user2', 'user3'] });
});

app.listen(3000);
```

**Kode Aman:**
```javascript
const express = require('express');
const app = express();

// Middleware untuk verifikasi role admin
function requireAdmin(req, res, next) {
    if (!req.session || !req.session.user) {
        return res.status(401).json({ error: 'Unauthorized' });
    }
    
    if (req.session.user.role !== 'admin') {
        return res.status(403).json({ error: 'Forbidden: Admin access required' });
    }
    
    next();
}

// AMAN: Semua route admin dilindungi middleware
app.get('/admin/dashboard', requireAdmin, (req, res) => {
    res.render('admin-dashboard');
});

app.get('/admin/users', requireAdmin, (req, res) => {
    res.json({ users: ['user1', 'user2', 'user3'] });
});

app.listen(3000);
```

### 3. Horizontal Privilege Escalation pada Flask (Python)

**Kode Rentan:**
```python
from flask import Flask, request, session, jsonify

app = Flask(__name__)
app.secret_key = 'secret'

@app.route('/api/invoice/<invoice_id>')
def get_invoice(invoice_id):
    # BAHAYA: Tidak ada verifikasi kepemilikan invoice
    invoice = db.query("SELECT * FROM invoices WHERE id = ?", invoice_id)
    return jsonify(invoice)

if __name__ == "__main__":
    app.run(debug=True)
```

**Kode Aman:**
```python
from flask import Flask, request, session, jsonify

app = Flask(__name__)
app.secret_key = 'secret'

@app.route('/api/invoice/<invoice_id>')
def get_invoice(invoice_id):
    # AMAN: Verifikasi bahwa invoice milik pengguna yang sedang login
    invoice = db.query("SELECT * FROM invoices WHERE id = ? AND user_id = ?", 
                      invoice_id, session['user_id'])
    
    if not invoice:
        return jsonify({'error': 'Invoice not found or access denied'}), 403
    
    return jsonify(invoice)

if __name__ == "__main__":
    app.run(debug=True)
```

## Mekanisme Kerentanan

Aplikasi web yang aman harus memverifikasi dua hal setiap kali pengguna mengakses sumber daya:

1. **Autentikasi** -- Apakah pengguna sudah login?
2. **Otorisasi** -- Apakah pengguna memiliki hak untuk mengakses sumber daya ini?

BAC terjadi ketika langkah kedua tidak diperiksa atau diperiksa dengan tidak benar.

### Contoh Skenario

**Permintaan normal:**
```
GET /profile?user_id=5
```
**Hasil:** Data profil pengguna dengan ID 5 ditampilkan.

**Permintaan dengan manipulasi ID:**
```
GET /profile?user_id=6
```
**Hasil:** Data profil pengguna dengan ID 6 ditampilkan -- seharusnya tidak diizinkan.

Ketika pengguna dapat mengakses data pengguna lain hanya dengan mengubah parameter ID, ini mengindikasikan kerentanan Broken Access Control tipe IDOR.

## Dampak

Kerentanan ini dapat mengakibatkan:
- **Paparan data sensitif** -- Data pengguna lain dapat diakses oleh pihak yang tidak berwenang
- **Modifikasi data trái phép** -- Data dapat diubah atau dihapus oleh pengguna yang tidak memiliki hak
- **Peningkatan hak akses (Privilege Escalation)** -- Pengguna biasa dapat mengakses fungsi administratif

## Jenis Kerentanan

### IDOR (Insecure Direct Object Reference)
Terjadi ketika aplikasi menggunakan identifier (ID) yang dapat ditebak atau dimanipulasi untuk mengakses objek langsung.

**Contoh:**
```
GET /invoice/123
```
Penyerang mengubah angka ID untuk mengakses invoice milik pengguna lain.

### Forced Browsing
Penyerang dapat mengakses halaman atau fungsi tertentu hanya dengan menebak URL, tanpa perlu hak akses khusus.

**Contoh:**
Mengakses `/admin/config`, `/api/internal/users`, atau halaman tersembunyi lainnya tanpa autentikasi yang memadai.

### Vertical Privilege Escalation
Pengguna biasa dapat mengakses fungsi yang seharusnya hanya tersedia untuk admin.

**Contoh:**
- Mengakses `/admin/settings` tanpa role admin
- Menggunakan API endpoint `/api/users/delete` tanpa otorisasi

### Horizontal Privilege Escalation
Pengguna dapat mengakses data pengguna lain dengan level yang sama.

**Contoh:**
- Mengubah `user_id` di URL untuk melihat profil pengguna lain
- Mengubah `account_id` untuk melihat transaksi pengguna lain

## Kumpulan Payload dan Teknik Pengujian

### Payload IDOR

**Menguji akses data pengguna lain:**
```
GET /api/profile?user_id=1
GET /api/profile?user_id=2
GET /api/profile?user_id=admin
```

**Menguji akses invoice:**
```
GET /api/invoice/100
GET /api/invoice/101
GET /api/invoice/999
```

**Menguji dengan HTTP Method berbeda:**
```
GET /api/users/5
PUT /api/users/5
DELETE /api/users/5
```

### Payload Forced Browsing

**Menguji halaman administratif:**
```
GET /admin
GET /admin/dashboard
GET /admin/config
GET /admin/users
GET /api/admin/settings
GET /internal/api/users
GET /debug
GET /api/internal/config
```

**Menguji endpoint API tersembunyi:**
```
GET /api/v1/internal/users
GET /api/v2/admin/config
GET /api/debug/logs
GET /api/test
```

### Teknik Pengujian BAC

1. **Ubah ID objek** -- Ganti parameter ID dengan nilai lain
2. **Ganti HTTP method** -- Coba GET, POST, PUT, DELETE pada endpoint yang sama
3. **Akses URL langsung** -- Coba akses URL tanpa melalui alur normal
4. **Manipulasi role di token** -- Jika menggunakan JWT, coba ubah role
5. **Gunakan cookie pengguna lain** -- Tukar session ID dengan milik lain

## Tools untuk Pengujian BAC

### 1. Burp Suite

Burp Suite adalah alat utama untuk pengujian BAC:

- **Repeater** -- Memodifikasi parameter ID dan menganalisis respons
- **Intruder** -- Otomatisasi pengujian berbagai ID
- **Authorize** (Extension) -- Mendeteksi BAC secara otomatis
- **AutoRepeater** -- Otomatisasi pengujian akses kontrol

**Penggunaan Authorize Extension:**
```
1. Install Authorize dari BApp Store
2. Configure intercept rules
3. Login dengan user berbeda (low privilege)
4. Browse aplikasi dengan user tersebut
5. Authorize akan otomatis mendeteksi BAC
```

### 2. OWASP ZAP

OWASP ZAP memiliki fitur untuk mendeteksi BAC:

- **Access Control Testing** -- Membandingkan respons antar pengguna
- **Active Scan** -- Mendeteksi kerentanan akses kontrol
- **User Management** -- Simulasi berbagai role pengguna

### 3. Autorize (Burp Extension)

Autorize adalah extension Burp Suite khusus untuk mendeteksi BAC:

```bash
# Instalasi melalui BApp Store di Burp Suite
# Atau manual:
git clone https://github.com/Quitten/Autorize.git
```

### 4. Postman

Postman dapat digunakan untuk pengujian manual:

- Buat collection untuk setiap endpoint
- Gunakan environment variables untuk token berbeda
- Uji setiap endpoint dengan berbagai level akses

## Metode Deteksi

Untuk mengidentifikasi apakah sebuah aplikasi rentan BAC:

1. **Uji dengan mengubah ID objek** -- Ganti parameter ID dan lihat apakah data pengguna lain dapat diakses
2. **Akses URL administratif langsung** -- Coba akses URL admin tanpa login sebagai admin
3. **Bandingkan respons antar pengguna** -- Login dengan dua akun berbeda dan bandingkan akses
4. **Manipulasi HTTP method** -- Coba method berbeda pada endpoint yang sama
5. **Periksa kontrol akses di setiap fungsi** -- Pastikan setiap endpoint memverifikasi hak akses

## Metode Pencegahan

1. **Verifikasi hak akses di setiap permintaan** -- Jangan mengandalkan penyembunyian URL atau menu saja

2. **Gunakan kontrol akses berbasis peran (RBAC)** -- Tetapkan hak akses berdasarkan peran pengguna
   
   ```python
   def require_role(role):
       def decorator(f):
           @wraps(f)
           def decorated_function(*args, **kwargs):
               if session.get('role') != role:
                   return jsonify({'error': 'Forbidden'}), 403
               return f(*args, **kwargs)
           return decorated_function
       return decorator
   ```

3. **Validasi kepemilikan data** -- Pastikan pengguna hanya dapat mengakses data yang menjadi miliknya
   
   ```python
   # Selalu verifikasi kepemilikan
   invoice = db.query("SELECT * FROM invoices WHERE id = ? AND user_id = ?", 
                     invoice_id, session['user_id'])
   ```

4. **Terapkan prinsip least privilege** -- Berikan hak akses minimum yang diperlukan

5. **Gunakan indirect object references** -- Gunakan mapping ID yang tidak dapat ditebak
   
   ```python
   # Gunakan random reference ID
   invoice_ref = generate_random_token()
   cache.set(invoice_ref, real_invoice_id)
   ```

6. **Lakukan pengujian akses** -- Uji setiap fungsi dengan berbagai tingkat hak akses pengguna

7. **Log dan monitor akses yang gagal** -- Catat setiap percobaan akses yang ditolak

## Latihan Praktis

Silakan lanjutkan ke direktori `bac-challenge/` untuk latihan berbasis skenario.

## Referensi Lanjutan

- OWASP Top 10: Broken Access Control
- CWE-284: Improper Access Control
- PortSwigger: Access Control Vulnerabilities -- https://portswigger.net/web-security/access-control
- OWASP Access Control Cheat Sheet
