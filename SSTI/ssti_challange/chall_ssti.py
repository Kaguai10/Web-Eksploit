from flask import Flask, request, render_template_string, send_from_directory, abort
import random, datetime, os

app = Flask(__name__)

DATA_DIR = os.path.join(os.path.dirname(__file__), 'data')
MUSIC_FILENAME = 'love.mp3'

TEMPLATES = [
"""Dear __CRUSH__,

Ada sesuatu yang ingin kukatakan padamu __CRUSH__, sesuatu yang sudah lama terpendam di dalam hatiku. Setiap kali aku melihatmu, setiap kali kita bercakap-cakap, aku merasa ada koneksi yang dalam antara kita. Kamu mungkin tidak menyadarinya, tapi aku selalu menantikan momen-momen kecil itu, saat-saat ketika hanya kita berdua.

Aku kagum dengan caramu membawa dirimu, caramu melihat dunia dengan mata yang begitu penuh optimisme dan keceriaan. Setiap kali aku mendengar tawamu, rasanya seakan dunia berhenti sejenak, dan hanya ada kamu di dalamnya. Mungkin ini terdengar klise, tapi aku yakin bahwa perasaan ini nyata. Aku ingin kamu tahu, bahwa aku sangat menghargai setiap detik yang kita habiskan bersama, dan aku berharap, suatu hari, kamu akan merasakan hal yang sama seperti yang aku rasakan.

Aku tidak tahu bagaimana masa depan kita, tapi satu hal yang pasti, aku ingin kamu selalu menjadi bagian dari hidupku. Dan mungkin, suatu hari nanti, kamu akan melihat betapa dalam perasaanku padamu. Semangat untuk mu, aku berharap suatu hari nanti kita akan bersama __CRUSH__

Dengan tulus,
__ME__""",

"""Kepada __CRUSH__,

Aku tidak tahu bagaimana memulai surat ini, karena perasaanku begitu besar dan sulit diungkapkan dengan kata-kata. Namun, aku akan mencoba. Sejak pertama kali bertemu denganmu __CRUSH__, hatiku sudah terasa berbeda. Ada sesuatu dalam dirimu yang membuatku selalu ingin tahu lebih banyak, selalu ingin lebih dekat. Setiap senyummu, caramu berbicara, hingga tatapan matamu membuat hatiku bergetar dengan cara yang tidak pernah kurasakan sebelumnya.

Aku bukan seseorang yang mudah menyatakan perasaan, tetapi denganmu, rasanya aku tak bisa menahannya lagi. Aku berharap, mungkin, di antara segala keceriaan yang kita bagi bersama, kamu juga merasakan hal yang sama. Aku tidak berharap banyak, hanya berharap kamu bisa mengerti bahwa perasaan ini tulus dan nyata. Kamu telah membuat hari-hariku lebih indah, dan aku berharap bisa terus berada di sampingmu __CRUSH__, membuat hari-harimu juga lebih berarti.

Terima kasih telah menjadi bagian dari hidupku, walau mungkin kamu tidak menyadarinya. Jika kamu memberikan kesempatan, aku akan dengan senang hati memberikan yang terbaik untukmu. Karena bagiku, kamu lebih dari sekadar Teman biasa, kamu adalah seseorang yang sangat istimewa.

Salam sayang,
__ME__""",

"""Untuk __CRUSH__, yang selalu mengisi hatiku

Aku selalu bingung harus memulai dari mana, tapi mungkin yang terbaik adalah memulai dengan kejujuran. Aku sudah menyimpan perasaan ini cukup lama, dan aku tidak ingin lagi hanya diam dalam ketidakpastian. Jadi, dengan segala keberanian yang kumiliki, aku menulis surat ini untuk memberitahumu betapa aku menyukaimu __CRUSH__.

Setiap kali aku bersamamu, aku merasakan kebahagiaan yang sulit dijelaskan. Entah mengapa, hal-hal sederhana seperti berjalan bersama, berbicara tentang hal-hal acak, hingga hanya duduk diam di sampingmu, semuanya terasa istimewa. Aku tahu ini mungkin terdengar berlebihan, tapi itulah yang kurasakan.

Aku tidak tahu bagaimana kamu akan menanggapi surat ini, dan aku siap menerima apa pun jawabannya. Namun, satu hal yang aku tahu, aku tidak bisa terus menyimpan perasaan ini sendirian. Aku hanya berharap kamu bisa memahami, dan jika memungkinkan, memberikan kesempatan bagi kita untuk mengenal satu sama lain lebih dalam. I always hope that you, __CRUSH__, can always be a part of my life.

Hormatku,
__ME__""",

"""Kepada __CRUSH__ yang selalu membuatku terpesona,

Ada sebuah rasa yang tak bisa kusembunyikan lagi, sebuah perasaan yang perlahan-lahan tumbuh setiap kali aku berada di dekatmu __CRUSH__. Mungkin ini terdengar seperti kisah dalam novel cinta, tapi aku yakin bahwa apa yang kurasakan untukmu jauh lebih nyata daripada sekadar kata-kata.

Aku mencintai caramu tersenyum, seakan dunia ini hanya milikmu seorang. Aku mencintai setiap detik percakapan kita, meskipun kadang hanya hal-hal kecil yang kita bicarakan. Ada keindahan dalam kesederhanaan itu, dan entah bagaimana, setiap kali aku mengingatnya, hatiku terasa hangat.

Mungkin aku belum pernah menyatakan perasaan ini dengan jelas, tapi aku ingin kamu tahu bahwa aku sangat menghargai keberadaanmu dalam hidupku. __CRUSH__ Kamu adalah alasan di balik senyumku setiap hari, dan aku berharap, suatu hari nanti, aku bisa menjadi alasan di balik senyummu juga.

Dengan segenap hatiku,
__ME__""",

"""Hai __CRUSH__,

Mungkin ini terdengar aneh, tapi setiap kali aku melihatmu, aku merasa seperti menemukan sesuatu yang hilang dalam hidupku. __CRUSH__, Kamu membawa kebahagiaan yang selama ini aku cari, meskipun mungkin kamu tidak menyadarinya.

Aku selalu mengagumi caramu menjalani hari-harimu. Kamu begitu penuh semangat dan tidak pernah ragu untuk berbagi keceriaan dengan orang lain. Itu adalah salah satu hal yang membuatku jatuh cinta padamu. Dan aku ingin kamu tahu, bahwa aku siap memberikan seluruh perhatianku untukmu, jika kamu menginginkannya.

Aku tidak tahu bagaimana kamu akan merespons surat ini, tapi satu hal yang pasti, aku sangat beruntung bisa mengenalmu. Aku berharap kamu bisa memberiku kesempatan untuk mengenalmu lebih dalam lagi. And I always hope that you, __CRUSH__, can always be a part of my life.

Salam hangat,
__ME__"""
]

PAGE = """<!doctype html>
<html>
  <head>
    <meta charset='utf-8'>
    <title>Template: Surat Cinta</title>
    <style>
      body { font-family: Arial, sans-serif; max-width:780px; margin:30px auto; color:#222; padding:12px; background:#fff0f5; }
      form { background:#fff; padding:16px; border-radius:8px; box-shadow:0 6px 18px rgba(0,0,0,0.06); }
      label { display:block; margin-top:8px; font-weight:600; }
      input[type=text] { width:100%; padding:8px; margin-top:6px; border-radius:6px; border:1px solid #ccc; }
      input[type=submit] { margin-top:12px; padding:10px 16px; border-radius:8px; border:none; cursor:pointer; background:#b0306a; color:#fff; }
      .result { margin-top:18px; }
      .card { background:#fff; padding:16px; border-radius:10px; box-shadow:0 6px 18px rgba(0,0,0,0.06); margin-bottom:12px; }
      pre { white-space:pre-wrap; font-family: Georgia, serif; font-size:16px; line-height:1.6; }
    </style>
  </head>
  <body>
    <audio id="bgm" src="/music" autoplay loop></audio>
    <h2>Buat Surat Cinta</h2>
    <form id="letterForm">
      <label>Nama Gebetan</label>
      <input type="text" id="crush" placeholder="Masukkan nama gebetan..." required>
      <label>Nama Anda</label>
      <input type="text" id="me" placeholder="Masukkan nama anda..." required>
      <input type="submit" value="Buat Surat">
    </form>
    <div id="resultArea"></div>
    <script>
      const form = document.getElementById('letterForm');
      form.addEventListener('submit', async e => {
        e.preventDefault();
        const data = new URLSearchParams();
        data.append('crush', document.getElementById('crush').value);
        data.append('me', document.getElementById('me').value);
        const res = await fetch('/generate', {
          method:'POST',
          headers:{'Content-Type':'application/x-www-form-urlencoded'},
          body: data.toString()
        });
        const j = await res.json();
        document.getElementById('resultArea').innerHTML = '<div class="card"><div style="font-size:14px;color:#666;margin-bottom:8px;">Hasil untuk: <strong>'+j.crush_display+'</strong> — '+j.when+'</div><pre>'+j.body+'</pre></div>';
      });
    </script>
  </body>
</html>"""

@app.route('/music')
def music():
    path = os.path.join(DATA_DIR, MUSIC_FILENAME)
    if not os.path.exists(path):
        abort(404)
    return send_from_directory(DATA_DIR, MUSIC_FILENAME)

@app.route('/generate', methods=['POST'])
def generate():
    crush = request.form.get('crush','Sayang')
    me = request.form.get('me','Aku')
    template = random.choice(TEMPLATES)
    try:
        body = render_template_string(template.replace('__CRUSH__', crush).replace('__ME__', me))
    except Exception as e:
        body = f'[Error render template: {e}]'
    return {
        'crush_display': crush,
        'when': datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
        'body': body
    }

@app.route('/')
def index():
    return PAGE

if __name__ == '__main__':
    app.run(host='0.0.0.0', debug=True)
