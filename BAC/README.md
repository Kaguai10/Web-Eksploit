# 🔐 Broken Access Control (BAC)

## 📖 Apa Itu Broken Access Control?

**Broken Access Control (BAC)** adalah sebuah kerentanan keamanan yang terjadi ketika sistem gagal menerapkan kontrol akses dengan tepat, sehingga memungkinkan pengguna yang tidak berwenang untuk **mengakses**, **memodifikasi**, atau **menghapus** data yang seharusnya dilindungi.

### 📌 Definisi
BAC merujuk pada kondisi di mana sistem tidak membatasi akses pengguna sesuai dengan hak dan peran mereka. Ketika kontrol akses tidak diberlakukan dengan benar, pengguna dapat melakukan aksi yang seharusnya tidak mereka miliki.

### 💡 Contoh Kasus
Seorang pengguna biasa dapat:
- Mengakses halaman `/admin/dashboard` tanpa otorisasi.
- Menghapus data milik pengguna lain.
- Mengubah `user_id` di URL seperti `GET /profile?user=2` untuk melihat data user lain.

### 🚨 Dampak
- **Kebocoran data sensitif**
- **Penghapusan atau modifikasi data oleh pihak tidak berwenang**
- **Privilege escalation** (peningkatan hak akses)

### 🧩 Tipe Kerentanan Umum

- **IDOR (Insecure Direct Object Reference)**  
  Penyerang memanfaatkan ID objek yang mudah ditebak, seperti `GET /invoice/123`, untuk mengakses data orang lain tanpa otorisasi yang semestinya.

- **Forced Browsing**
  Pengguna bisa langsung mengakses URL tertentu tanpa harus login atau tanpa hak akses khusus, misalnya `/admin/config`.

---

Setelah memahami dasar ini, kamu bisa menjelajahi:

- 🧪 `challenges/`: Soal latihan berbasis CTF.
- 📚 dan untuk pengetahuan lebih mendalam, baca lebih lanjut di sini
