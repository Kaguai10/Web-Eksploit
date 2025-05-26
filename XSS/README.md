# 🛡️ XSS Cross-Site Scripting

## 🔍 Apa itu XSS?

**XSS (Cross-Site Scripting)** adalah jenis serangan terhadap aplikasi web yang memungkinkan penyerang menyisipkan skrip berbahaya ke dalam halaman web yang dilihat oleh pengguna lain. Skrip ini biasanya berupa JavaScript dan digunakan untuk mencuri informasi, melakukan redirect, atau memanipulasi tampilan web.

---

## 🎯 Tujuan Serangan XSS

XSS dapat digunakan untuk:
- Mencuri **cookie** pengguna (misalnya: sesi login)
- Melakukan **phishing** melalui tampilan palsu
- Memodifikasi **konten halaman web**
- Menyebarkan **malware**
- Melakukan **deface** situs

---

## 🚩 Jenis-jenis XSS

1. **Stored XSS (Persistent)**
   - Payload disimpan di server (misalnya di database atau komentar).
   - Akan dijalankan setiap kali halaman dimuat.

2. **Reflected XSS (Non-persistent)**
   - Payload berasal dari parameter URL/form.
   - Dieksekusi secara langsung saat halaman dirender.

3. **DOM-based XSS**
   - Dieksekusi di sisi klien (browser) akibat manipulasi DOM oleh JavaScript.

---

## 🔍 Di Mana Biasanya Ditemukan?

XSS sering ditemukan pada:
- Input form tanpa validasi
- Parameter URL
- Kolom komentar/chat
- Fitur pencarian
- Panel admin yang tidak di-filter
- JavaScript yang mengambil nilai dari `document.location`, `document.cookie`, `innerHTML`, dll

---

## 💥 Contoh Payload Dasar

Beberapa payload XSS dasar yang sering digunakan:

```html
<script>alert('XSS!')</script>
<img src=x onerror=alert('XSS')>
<a href="javascript:alert('XSS')">Click me</a>

