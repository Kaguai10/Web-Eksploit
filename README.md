<div align="center">
  <img src="https://readme-typing-svg.herokuapp.com?size=30&color=FFA500&center=true&vCenter=true&width=650&lines=💀+WEB+EXPLOIT">
  <img src="https://user-images.githubusercontent.com/73097560/115834477-dbab4500-a447-11eb-908a-139a6edaec5c.gif"></p>
</div>



Modul ini dirancang untuk mempelajari berbagai jenis kerentanan keamanan pada aplikasi web secara terstruktur. Setiap bab membahas satu jenis kerentanan, mulai dari konsep dasar, mekanisme serangan, contoh source code yang rentan, kumpulan payload, tools pengujian, hingga metode pencegahan.

## Struktur Modul

| Modul | Topik | Deskripsi Singkat | Latihan Praktis |
|-------|-------|-------------------|-----------------|
| [BAC](./BAC/) | Broken Access Control | Kerentanan pada kontrol akses yang memungkinkan pengguna mengakses sumber daya di luar haknya | [bac-challenge](./BAC/bac-challenge/) |
| [Command Injection](./command_injection/) | Eksekusi Perintah Sistem | Penyisipan perintah sistem operasi melalui input yang tidak divalidasi | [Challange](./command_injection/Challange/) |
| [Cookie & JWT](./Cookie-JWT/) | Keamanan Sesi dan Token | Kerentanan pada mekanisme penyimpanan dan transmisi informasi sesi | [lab-Challanges](./Cookie-JWT/lab-Challanges/) |
| [File Upload](./FileUpload/) | Unggahan File Berbahaya | Eksploitasi fitur unggah file tanpa validasi yang memadai | [Challange](./FileUpload/Challange/) |
| [LFI & Path Traversal](./LFI-Traversal/) | Akses File Lokal | Pembacaan file sensitif melalui manipulasi path file | [challange](./LFI-Traversal/challange/) |
| [SQL Injection](./SQLi/) | Injeksi Perintah SQL | Penyisipan perintah SQL melalui input pengguna | [sqli-challange](./SQLi/sqli-challange/) |
| [SSRF](./SSRF/) | Server-Side Request Forgery | Memanfaatkan server untuk membuat permintaan HTTP ke sumber daya internal | [Challange](./SSRF/challange) |
| [SSTI](./SSTI/) | Server-Side Template Injection | Eksploitasi template engine untuk eksekusi kode di server | [ssti_challange](./SSTI/ssti_challange/) |
| [XSS](./XSS/) | Cross-Site Scripting | Penyisipan skrip berbahaya ke halaman web | [Challange](./XSS/Challange/) |

## Isi Setiap Modul

Setiap modul kerentanan mencakup:

1. **Pengertian** -- Penjelasan konsep kerentanan dengan analogi yang mudah dipahami
2. **Contoh Source Code** -- Contoh kode yang rentan dan cara memperbaikinya (PHP, Python, Node.js, Java)
3. **Mekanisme Kerentanan** -- Penjelasan bagaimana serangan bekerja
4. **Dampak** -- Konsekuensi jika kerentanan dieksploitasi
5. **Jenis-jenis Serangan** -- Kategori dan variasi kerentanan
6. **Kumpulan Payload** -- Daftar payload untuk pengujian:
   - Payload deteksi dasar
   - Payload eksploitasi
   - Payload bypass filter
   - Payload untuk skenario khusus
7. **Tools Pengujian** -- Alat-alat yang berguna dengan cara penggunaan:
   - Alat otomatis (SQLMap, Commix, XSStrike, dll)
   - Burp Suite dan OWASP ZAP
   - Alat khusus lainnya
8. **Metode Deteksi** -- Cara mengidentifikasi kerentanan
9. **Metode Pencegahan** -- Best practices dan contoh kode aman
10. **Referensi Lanjutan** -- Link ke dokumentasi OWASP, CWE, dan PortSwigger

## Cara Menggunakan Modul

1. Mulai dari bab yang sesuai dengan topik yang ingin dipelajari
2. Pahami konsep dasar dan mekanisme serangan
3. Pelajari contoh source code yang rentan dan cara memperbaikinya
4. Pelajari kumpulan payload dan skenario serangan
5. Pahami tools pengujian yang dapat digunakan
6. Pahami metode pencegahan yang direkomendasikan
7. Gunakan latihan praktis (jika tersedia) untuk menguji pemahaman

## Prasyarat

- Pemahaman dasar tentang HTTP, HTML, dan bahasa pemrograman web
- Pengetahuan dasar tentang cara kerja server dan database
- Lingkungan pengujian yang aman (disarankan menggunakan mesin virtual atau lingkungan terisolasi)

## Peringatan

Modul ini ditujukan untuk **tujuan edukasi dan pengujian yang sah**. Jangan gunakan pengetahuan dari modul ini untuk melakukan serangan terhadap sistem tanpa izin tertulis dari pemilik sistem.

## Lisensi

Modul ini dibuat untuk tujuan pembelajaran keamanan web. 🙏🏻
