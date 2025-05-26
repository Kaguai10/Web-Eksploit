# 🔍 Local File Inclusion (LFI) & Path Traversal Exploitation


## 📂 Apa itu Local File Inclusion (LFI)?

**LFI** terjadi ketika aplikasi web menyisipkan file lokal dari server ke dalam proses eksekusi tanpa validasi input user. Ini bisa menyebabkan file rahasia terbaca atau kode berbahaya dijalankan.

### 📜 Contoh Kode PHP Rentan LFI:
```php
<?php
  $page = $_GET['page'];
  include($page);
?>
````

### 💣 Contoh Payload LFI:

```bash
?page=../../../../etc/passwd
?page=.....///.....///.....///.....///etc/passwd
?page=php://filter/convert.base64-encode/resource=index.php
```

### 🎯 Tujuan Serangan:

* Membaca file sensitif (`/etc/passwd`, `config.php`, dll)
* Melakukan **Remote Code Execution** dengan memanfaatkan log poisoning
* Menjalankan file upload berbahaya (jika combined dengan file upload vuln)
* Membaca session atau credential file

---

## 🗂️ Apa itu Path Traversal?

**Path Traversal** (Directory Traversal) terjadi ketika attacker memanipulasi input file path untuk mengakses file di luar direktori yang diizinkan, menggunakan karakter seperti `../`.

### 📜 Contoh Kode PHP Rentan Path Traversal:

```php
<?php
  $file = $_GET['file'];
  readfile("uploads/" . $file);
?>
```

### 💣 Contoh Payload Traversal:

```bash
?file=/upload/secret.txt
?file=/flag.txt
?file=../../../../etc/passwd
```

### 🎯 Tujuan Serangan:

* Mengakses file konfigurasi penting
* Membaca credential dan token
* Mengambil file yang tidak semestinya bisa diakses (contoh: `.env`, `wp-config.php`, `source code`)
* Menelusuri struktur direktori server

---

## ⚖️ Perbedaan LFI vs Path Traversal

| Aspek             | Local File Inclusion (LFI)                     | Path Traversal                                 |
| ----------------- | ---------------------------------------------- | ---------------------------------------------- |
| Tujuan            | Menyisipkan dan (kadang) mengeksekusi file     | Membaca file di luar direktori target          |
| Fungsi PHP rentan | `include()`, `require()`                       | `readfile()`, `fopen()`, `file_get_contents()` |
| Eksekusi          | Bisa menjalankan file (jika PHP)               | Tidak menjalankan, hanya membaca file          |
| Bahasa            | Umumnya di PHP                                 | Semua bahasa bisa rentan                       |
| Risiko tambahan   | Bisa digunakan untuk **Remote Code Execution** | Umumnya hanya digunakan untuk membaca file     |
