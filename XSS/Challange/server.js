const express = require('express');
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const app = express();
const port = process.env.PORT || 3000;

// In-memory stores
const activeTokens = new Set();
const sessions = new Map();

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

function genToken(len = 20) {
  return crypto.randomBytes(len).toString('hex');
}

app.get('/', (req, res) => {

  const indexPath = path.join(__dirname, 'public', 'index.html');
  let html = fs.readFileSync(indexPath, 'utf8');

  const sessionToken = genToken(12);
  const timeoutId = setTimeout(() => sessions.delete(sessionToken), 10 * 60 * 1000);
  sessions.set(sessionToken, { createdAt: Date.now(), timeoutId });

  const injectScript = `<script>window.__SESSION=${JSON.stringify(sessionToken)};</script>`;
  if (html.includes('<script src="/app.js"')) {
    html = html.replace('<script src="/app.js"', `${injectScript}\n<script src="/app.js"`);
  } else if (html.includes('</head>')) {
    html = html.replace('</head>', `${injectScript}\n</head>`);
  } else {
    html = injectScript + html;
  }

  res.setHeader('Content-Type', 'text/html; charset=utf-8');
  return res.send(html);
});

// POST /_genToken -> buat one-time token untuk /message
app.post('/_genToken', (req, res) => {
  const sessionToken = req.get('X-Session-Token') || req.body.sessionToken || req.query.sessionToken;
  if (!sessionToken || !sessions.has(sessionToken)) {
    return res.status(403).json({ error: 'Forbidden' });
  }

  const token = genToken(8);
  activeTokens.add(token);
  setTimeout(() => activeTokens.delete(token), 10000);

  return res.json({ token });
});

// GET /message -> hanya dengan token valid
app.get('/message', (req, res) => {
  const token = req.query.token || req.get('X-Token');
  if (!token || !activeTokens.has(token)) {
    return res.status(403).send('Forbidden');
  }
  activeTokens.delete(token);
  return res.json({ message: 'FLAG{XSS_c0ntrol_dengan_c0ns0l3}' });
});

// static files
app.use(express.static(path.join(__dirname, 'public')));

app.listen(port, () => {
  console.log(`Judol-CTF web running on http://localhost:${port}`);
});
