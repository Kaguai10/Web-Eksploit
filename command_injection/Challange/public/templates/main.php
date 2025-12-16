<main class="panel">
  <form method="post" class="two-col">

    <div class="col">
      <label>Encode (Plain → Base64)</label>
      <textarea name="encode_text" rows="6"></textarea>
      <div class="row-right">
        <button name="do_encode" class="btn">Encode</button>
      </div>
      <label>Hasil Encode</label>
      <textarea readonly rows="3"><?= htmlspecialchars($enc) ?></textarea>
    </div>

    <div class="col">
      <label>Decode (Base64 → Plain)</label>
      <textarea name="decode_text" rows="6"></textarea>
      <div class="row-right">
        <button name="do_decode" class="btn green">Decode</button>
      </div>
      <label>Hasil Decode</label>
      <textarea readonly rows="3"><?= htmlspecialchars($dec) ?></textarea>
    </div>

  </form>
</main>
