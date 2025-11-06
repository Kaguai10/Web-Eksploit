# 💉 SQL Injection (SQLi)

Selamat datang di **SQL Injection (SQLi)** — salah satu kerentanan paling umum dan berbahaya dalam dunia keamanan web. Di sini, kamu akan mempelajari bagaimana serangan ini bekerja, di mana biasanya ditemukan, serta bagaimana mengidentifikasinya melalui payload sederhana.

---

## 🧠 Apa Itu SQL Injection?

**SQL Injection** adalah teknik serangan yang memanfaatkan celah pada input pengguna yang langsung dimasukkan ke dalam perintah SQL tanpa validasi yang baik. Dengan menyisipkan kode SQL berbahaya, penyerang bisa:

- Membypass autentikasi (login tanpa password)
- Melihat seluruh isi tabel (termasuk data sensitif)
- Mengubah atau menghapus data
- Dalam kasus tertentu, bahkan mengendalikan server

---

## 📍 Di Mana SQL Injection Sering Ditemukan?

SQLi bisa muncul di berbagai titik input dalam aplikasi web, seperti:

| Lokasi            | Contoh Kasus                                                                 |
|-------------------|------------------------------------------------------------------------------|
| 🧾 Form Login      | `username` dan `password` langsung digunakan dalam query SQL                |
| 🔍 Kolom Pencarian | Input pencarian produk dimasukkan ke query tanpa filter                     |
| 🌐 URL Parameter   | `/produk.php?id=1` → query seperti `SELECT * FROM produk WHERE id = 1`      |
| 🍪 Cookie/Header   | Nilai dari cookie langsung dimasukkan ke query tanpa sanitasi               |

Jika input dari pengguna tidak disaring atau diamankan, semua titik di atas bisa menjadi pintu masuk SQL Injection.

---

## 💥 Payload Dasar untuk Uji SQLi

Berikut beberapa payload dasar yang biasa digunakan untuk menguji apakah sebuah input rentan terhadap SQLi:

### 💉 Payload Basic

```sql
'
"
'--
'#
//
/*
SLEEP(3)#
' OR 1=1 --
' ORDER BY 1--
' UNION SELECT 1,2,3--


