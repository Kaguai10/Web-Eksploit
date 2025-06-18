# 🌐 Server-Side Request Forgery (SSRF)

Selamat datang di **Server-Side Request Forgery (SSRF)** — salah satu kerentanan web yang semakin populer dan sering muncul dalam CTF maupun insiden nyata. Di sini, kamu akan belajar bagaimana SSRF bekerja, area umum tempat SSRF ditemukan, dan payload dasar untuk mendeteksinya.

---

## 🧠 Apa Itu SSRF?

**SSRF** adalah kerentanan yang terjadi ketika server web menerima URL atau alamat dari input pengguna, lalu membuat **permintaan HTTP** berdasarkan input tersebut **tanpa validasi yang cukup**.

Dengan mengeksploitasi SSRF, penyerang bisa:

- Mengakses layanan internal yang tidak boleh diakses langsung dari luar
- Mengambil data sensitif (misalnya metadata cloud AWS)
- Melakukan scanning port internal
- Menjadikan server sebagai proxy untuk melakukan request berbahaya

---

## 📍 Di Mana SSRF Sering Ditemukan?

SSRF biasanya ditemukan pada fitur yang memungkinkan pengguna memberikan URL atau alamat untuk diambil kontennya oleh server:

| Lokasi Fitur        | Contoh Kasus                                                                 |
|---------------------|------------------------------------------------------------------------------|
| 🖼️ Avatar Fetcher     | Upload gambar profil via URL: `url=https://site.com/avatar.png`             |
| 🔗 URL Preview       | Menampilkan isi dari sebuah tautan yang dikirim user                        |
| 📦 Webhook Tester    | Aplikasi menyediakan test webhook untuk developer                           |
| 📄 PDF Converter     | Aplikasi mengambil halaman dari URL untuk dikonversi menjadi PDF             |
| 📡 Proxy/API Client  | Aplikasi sebagai perantara permintaan ke API dari domain tertentu            |

---

## 💥 Payload Dasar untuk Uji SSRF

Berikut adalah payload dasar dan umum yang digunakan untuk mendeteksi kemungkinan adanya SSRF:

### 🔍 Akses ke localhost

```

[http://127.0.0.1](http://127.0.0.1)
[http://localhost](http://localhost)

```

### 🏠 Akses ke metadata cloud (contoh AWS)

```

[http://169.254.169.254/latest/meta-data/](http://169.254.169.254/latest/meta-data/)

```

### 🔀 Akses dengan protokol berbeda (jika didukung)

```

file:///etc/passwd
gopher://127.0.0.1
ftp\://127.0.0.1

```

### 🧪 Deteksi Blind SSRF (tidak ada respon langsung)

Gunakan alat seperti Burp Collaborator, webhook.site, atau request-bin:

```

[http://your-custom-domain.burpcollaborator.net](http://your-custom-domain.burpcollaborator.net)

```

Jika server memproses URL tersebut, maka kamu akan melihat log di server kamu — ini membuktikan SSRF **blind** berhasil.

---

## 🔎 Tips Mengidentifikasi SSRF

- Input URL dikirim ke server dan ditampilkan kembali? Coba ganti URL-nya.
- Coba arahkan ke IP lokal (`127.0.0.1`) dan lihat respon berbeda.
- Respon lebih lambat saat mengakses domain tertentu? Bisa jadi itu SSRF.
- Tidak ada respon? Uji dengan domain milik sendiri (blind SSRF).
