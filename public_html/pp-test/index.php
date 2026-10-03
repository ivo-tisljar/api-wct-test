<?php header('Cache-Control: no-store'); ?>
<!doctype html>
<html lang="hr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>WCT test data</title>
<style>
  body { font-family: system-ui, sans-serif; margin: 1.5rem; color: #222; }
  form { display: flex; gap: .5rem; flex-wrap: wrap; align-items: center; }
  input[type=password] { font-family: monospace; width: 36rem; max-width: 100%; padding: .4rem; }
  button { padding: .4rem 1rem; }
  #message { margin: .75rem 0; }
  #message.error { color: #b00020; }
  .stamp { color: #666; font-size: .9rem; }
  h2 small { font-weight: normal; color: #666; font-size: .8em; }
  .scroll { overflow-x: auto; margin-bottom: 2rem; }
  table { border-collapse: collapse; font-size: .85rem; }
  th, td { border: 1px solid #ccc; padding: .25rem .5rem; text-align: left; white-space: nowrap; }
  th { background: #f0f0f0; position: sticky; top: 0; }
  tbody tr:nth-child(even) { background: #fafafa; }
  td.null { color: #999; font-style: italic; }
  td.empty { color: #666; text-align: center; }
</style>
</head>
<body>
<h1>WCT test data</h1>
<form id="form" autocomplete="off">
  <label for="token">Token</label>
  <input type="password" id="token" name="token" maxlength="64" required
         pattern="[0-9a-fA-F]{64}" title="64 hexadecimal characters" spellcheck="false">
  <button type="submit" id="refresh">Refresh</button>
</form>
<div id="message"></div>
<div id="tables"></div>

<script>
(function () {
  var form = document.getElementById('form');
  var input = document.getElementById('token');
  var button = document.getElementById('refresh');
  var message = document.getElementById('message');
  var tables = document.getElementById('tables');

  function show(text, isError) {
    message.textContent = text;
    message.className = isError ? 'error' : '';
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var token = input.value.trim();
    if (!/^[0-9a-fA-F]{64}$/.test(token)) {
      show('Token must be exactly 64 hexadecimal characters.', true);
      return;
    }
    button.disabled = true;
    show('Loading...', false);
    fetch('tables.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'token=' + encodeURIComponent(token),
      credentials: 'same-origin',
      cache: 'no-store'
    }).then(function (res) {
      return res.text().then(function (body) {
        if (!res.ok) { throw new Error(body || ('HTTP ' + res.status)); }
        tables.innerHTML = body; // server output is fully escaped
        show('', false);
      });
    }).catch(function (err) {
      show(err.message, true); // previous tables are left as they were
    }).then(function () {
      button.disabled = false;
    });
  });
})();
</script>
</body>
</html>
