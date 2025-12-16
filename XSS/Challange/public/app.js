const marqueeInner = document.getElementById('marqueeInner');
const sampleMessages = [
  'A*** telah menarik 12.00 koin!',
  'H*** koin habis, mendapat 0!',
  'F*** naik level VIP!',
  'Pemain D*** menang 50.000!',
  'JAGO123 mendapatkan jackpot palsu',
];
for (let i = 0; i < 20; i++) {
  const span = document.createElement('span');
  span.textContent = sampleMessages[i % sampleMessages.length];
  marqueeInner.appendChild(span);
}

(function spawnCoins() {
  const container = document.getElementById('coins');
  function makeCoin() {
    const c = document.createElement('div');
    c.className = 'coin';
    const startLeft = Math.random() * 100;
    c.style.left = startLeft + '%';
    c.style.top = '-60px';
    const scale = 0.7 + Math.random() * 0.9;
    c.style.width = (44 * scale) + 'px';
    c.style.height = (44 * scale) + 'px';
    container.appendChild(c);

    const duration = 4200 + Math.random() * 3200;
    const endX = startLeft + (Math.random() * 20 - 10);
    const keyframes = [
      { transform: `translate3d(0,0,0) rotate(${Math.random() * 180}deg) scale(${scale})`, opacity: 1 },
      { transform: `translate3d(${(endX - startLeft)}vw, ${window.innerHeight + 200}px,0) rotate(${720 + Math.random() * 720}deg) scale(${scale})`, opacity: 0.95 }
    ];
    c.animate(keyframes, { duration: duration, easing: 'cubic-bezier(.2,.9,.2,1)' });
    setTimeout(() => { c.remove(); }, duration + 50);
  }
  setInterval(makeCoin, 300);
})();

const reels = [document.getElementById('r1'), document.getElementById('r2'), document.getElementById('r3')];
const spinBtn = document.getElementById('spinBtn');
const status = document.getElementById('status');

function randomDigit() { return Math.floor(Math.random() * 10); }

let spinning = false;
spinBtn.addEventListener('click', async () => {
  if (spinning) return;
  spinning = true;
  status.textContent = 'Memutar...';

  const stopTimes = [800, 1200, 1600];
  for (let t = 0; t < stopTimes.length; t++) {
    const end = Date.now() + stopTimes[t];
    while (Date.now() < end) {
      reels[t].textContent = randomDigit();
      await new Promise(r => setTimeout(r, 50));
    }
  }

  if (reels[0].textContent === '7' && reels[1].textContent === '7' && reels[2].textContent === '7') {
    reels[2].textContent = (parseInt(reels[2].textContent) + 1) % 10;
  }

  spinning = false;
  status.textContent = 'Selesai';
  checkJackpot();
});

function getSessionTokenFromPage() {
  if (typeof window !== 'undefined' && window.__SESSION) return window.__SESSION;
  const m = document.querySelector('meta[name="session"]');
  if (m) return m.getAttribute('content');
  return null;
}

async function requestOneTimeToken(sessionToken) {
  if (!sessionToken) throw new Error('no-session');
  const resp = await fetch('/_genToken', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Session-Token': sessionToken
    },
    body: JSON.stringify({})
  });
  if (!resp.ok) throw new Error('genToken failed');
  const j = await resp.json();
  if (!j || !j.token) throw new Error('no-token');
  return j.token;
}

async function requestMessageWithToken(oneTimeToken) {
  if (!oneTimeToken) throw new Error('no-one-time-token');
  const url = `/message?token=${encodeURIComponent(oneTimeToken)}`;
  const resp = await fetch(url, { method: 'GET' });
  if (!resp.ok) throw new Error('message failed');
  return resp.json();
}

async function checkJackpot() {
  const v = reels.map(r => r.textContent).join(',');
  if (v === '7,7,7') {
    try {
      const sessionToken = getSessionTokenFromPage();
      if (!sessionToken) return alert('Tidak dapat mengambil session token.');

      const oneTime = await requestOneTimeToken(sessionToken);
      const j = await requestMessageWithToken(oneTime);

      if (j && j.message) {
        alert(j.message);
      } else {
        alert('Berhasil! (pesan server tidak tersedia).');
      }
    } catch (err) {
      console.error('checkJackpot error:', err);
      alert('Terjadi error saat meminta pesan dari server.');
    }
  }
}

const observer = new MutationObserver(() => { checkJackpot(); });
reels.forEach(r => observer.observe(r, { childList: true, characterData: true, subtree: true }));

window._judol = { reels, checkJackpot, getSessionTokenFromPage };
