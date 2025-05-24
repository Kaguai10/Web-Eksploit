# 🍪🔐 Cookie & JSON Web Token (JWT)

## 📖 Apa Itu Cookie dan JWT?

Dalam dunia web modern, **Cookie** dan **JWT (JSON Web Token)** adalah dua cara yang paling sering digunakan untuk menyimpan dan mengirimkan informasi antara klien (browser) dan server. Keduanya punya satu tujuan utama: **mengenali pengguna dan menjaga sesi** mereka tetap berjalan.

Namun, di balik kemudahan itu, tersimpan berbagai potensi celah keamanan yang bisa dimanfaatkan oleh penyerang jika developer tidak hati-hati.

### 📌 Definisi Singkat

- **Cookie**: Data kecil yang disimpan oleh browser dan dikirim secara otomatis ke server di setiap permintaan. Biasanya digunakan untuk menyimpan ID sesi, preferensi pengguna, atau bahkan token autentikasi.
- **JWT**: Token berbentuk string yang membawa informasi pengguna dalam format yang bisa dibaca (terencode), terdiri dari tiga bagian: header, payload, dan signature. JWT sering digunakan dalam aplikasi berbasis API dan SPA (Single Page Application) sebagai cara login tanpa menyimpan sesi di server.

---

## 💡 Contoh Kasus Nyata

Bayangkan kamu login ke sebuah situs.

- Jika menggunakan **Cookie**, server akan mengirimkan `Set-Cookie: session_id=xyz123`, dan browsermu akan menyimpannya. Setiap kali kamu membuka halaman lain, cookie itu ikut terkirim.
- Jika menggunakan **JWT**, setelah login kamu akan menerima token seperti `eyJhbGciOiJIUzI1NiIsIn...`, dan biasanya token ini disimpan di browser (misalnya di localStorage atau di cookie juga).

Yang jadi masalah adalah... ketika penyerang ikut bermain.

---

## 🚨 Potensi Kerentanan

| 🔍 Vektor | Penjelasan |
|----------|------------|
| 🎯 **XSS (Cross-Site Scripting)** | Jika ada celah XSS di situs web, penyerang bisa menyuntikkan script jahat dan mencuri cookie atau JWT milik pengguna lain. |
| 🧪 **Session Hijacking** | Token atau cookie yang dicuri bisa digunakan untuk menyamar sebagai korban dan mengambil alih sesi login mereka. |
| 🛠️ **Token Manipulation** | JWT yang tidak dilindungi dengan benar bisa dimodifikasi, misalnya mengganti `role: "user"` menjadi `role: "admin"`. |
| ⚠️ **Insecure Storage** | Menyimpan JWT di localStorage bisa berbahaya jika XSS berhasil dieksekusi, karena script bisa mengakses isi localStorage dengan mudah. |
| 🚪 **Privilege Escalation** | Penyerang bisa mengakses fitur tertentu hanya dengan mengubah isi cookie/JWT atau memanfaatkan celah ID yang bisa ditebak. |

---

## 🧩 Tipe Serangan Umum

| Tipe | Deskripsi |
|------|-----------|
| **Cookie Manipulation** | Mengedit nilai cookie di browser untuk mencoba mengakses fitur terlarang. |
| **None Algorithm Attack** | Menyerang JWT dengan memalsukan token dan menghapus signature jika server tidak memverifikasi dengan benar. |
| **Weak Secret Bruteforce** | Mencoba menebak `secret` dari JWT menggunakan wordlist umum dan tool seperti `jwt-cracker`. |
| **IDOR (Insecure Direct Object Reference)** | JWT atau cookie berisi user ID, dan penyerang menggantinya untuk melihat data milik pengguna lain. |

---

## 📚 Lanjut Belajar

Setelah memahami dasar di atas, kamu bisa eksplorasi lebih dalam melalui:

- 🧪 `cookie-jwt-chall/` — Eksperimen simulasi kerentanan cookie & JWT.
- 🧾 belajar lebih lanjut di sini
