<?php
// Returns the two result tables as an HTML fragment for a valid token.
// POST token=<64 hex chars>. Responses: 200 HTML, 400 bad format, 403 unknown token, 500 error.

// Adjust if you put the config somewhere else (must be outside the web root).
define('WCT_VIEWER_CONFIG', __DIR__ . '/../../../ivo/wct-viewer-config.php');

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function fail($status, $message)
{
    http_response_code($status);
    echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    exit;
}

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail(405, 'Use POST.');
}

$token = isset($_POST['token']) ? trim((string) $_POST['token']) : '';
if (!preg_match('/^[0-9a-fA-F]{64}$/', $token)) {
    fail(400, 'Token must be exactly 64 hexadecimal characters.');
}

$config = require WCT_VIEWER_CONFIG;

$valid = false;
foreach ($config['tokens'] as $allowed) {
    if (hash_equals(strtolower((string) $allowed), strtolower($token))) {
        $valid = true; // no early break, so timing doesn't reveal which entry matched
    }
}
if (!$valid) {
    usleep(500000); // slow down guessing
    fail(403, 'Invalid token.');
}

$queries = [
    'pretplatnik (partner)' => <<<'SQL'
SELECT 
'' AS 'pretplatnik (partner)',
p.id_pretplatnik AS sifraKupac,
NULL AS parentId,
p.pret_ime AS naziv,
p.pret_odjel AS imeOdjel,
p.pret_email AS email,
p.pret_oib AS oib,
p.pret_adresa AS adresa,
p.pret_grad AS mjesto,
p.pret_ptt AS ptt,
p.pret_tel AS telefon,
p.pret_status AS aktivan
FROM pretplatnik p
WHERE p.id_pretplatnik >= 140450 AND p.id_pretplatnik <= 142499
SQL
    ,
    'korisnik (kontakt)' => <<<'SQL'
SELECT
'' AS 'korisnik(kontakt)',
-- pk.sifra_kupca AS sifraKupac,
pk.kor_pretplatnik AS parentId,
-- pk.kor_ime as naziv,
-- pk.kor_odjel AS imeOdjel,
-- pk.kor_email AS email,
NULL AS oib,
-- pk.kor_adresa AS adresa,
-- pk.kor_mjesto AS mjesto,
-- pk.kor_ptt AS ptt,
-- pk.kor_tel AS telefon,
pk.kor_status AS aktivan,
pk.kor_username AS username,
pk.kor_password AS password,
CASE WHEN ppR.pretplata_korisnik IS NOT NULL THEN 1 ELSE 0 END AS rrifPretplata,
ppR.pretplata_godina AS godinaRrifPretplate,
CASE WHEN ppP.pretplata_korisnik IS NOT NULL THEN 1 ELSE 0 END AS pipPretplata,
ppP.pretplata_godina AS godinaPipPretplate
FROM pret_korisnik pk
LEFT JOIN pret_pretplata ppR ON pk.kor_id = ppR.pretplata_korisnik AND ppR.pretplata_casopis = 1
LEFT JOIN pret_pretplata ppP ON pk.kor_id = ppP.pretplata_korisnik AND ppP.pretplata_casopis = 3
WHERE pk.kor_pretplatnik >= 140450 AND pk.kor_pretplatnik <= 142499
SQL
    ,
];

try {
    $db = $config['db'];
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $db['host'], $db['port'], $db['dbname'], $db['charset']);
    $pdo = new PDO($dsn, $db['user'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $out = '<p class="stamp">Loaded ' . h(date('Y-m-d H:i:s')) . '</p>';
    foreach ($queries as $title => $sql) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $columns = [];
        for ($i = 0; $i < $stmt->columnCount(); $i++) {
            $meta = $stmt->getColumnMeta($i);
            $columns[] = $meta['name'];
        }
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);

        $out .= '<h2>' . h($title) . ' <small>(' . count($rows) . ' rows)</small></h2>';
        $out .= '<div class="scroll"><table><thead><tr>';
        foreach ($columns as $name) {
            $out .= '<th>' . h($name) . '</th>';
        }
        $out .= '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $out .= '<tr>';
            foreach ($row as $value) {
                $out .= $value === null ? '<td class="null">NULL</td>' : '<td>' . h($value) . '</td>';
            }
            $out .= '</tr>';
        }
        if (!$rows) {
            $out .= '<tr><td class="empty" colspan="' . count($columns) . '">No rows</td></tr>';
        }
        $out .= '</tbody></table></div>';
    }
    echo $out;
} catch (PDOException $e) {
    error_log('wct-viewer: ' . $e->getMessage());
    fail(500, 'Database error, see the server error log.');
}
