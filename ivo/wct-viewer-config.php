<?php
// Configuration for the WCT test-data viewer.
// Keep this file OUTSIDE the web root and readable only by the web server user:
//   chown root:www-data wct-viewer-config.php && chmod 640 wct-viewer-config.php

return [
    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'dbname'   => 'rrifhr_wct_test',
        'user'     => 'rrifhr_pp_viewer',
        'password' => '4E202919BB6D6D3A8126FDE0224BCB5E8A2C74E1424B8F81C049CB5346F30FEF',      // password of the read-only 'viewer'@'localhost' user
        'charset'  => 'utf8mb4',
    ],

    // Tokens allowed to view the tables: exactly 64 hex characters each.
    // Generate one with:  openssl rand -hex 32
    'tokens' => [
         'Client app team' => '7D4D97534ECF545B2AB04DA346A52EC01D685110C32CE8B65B39D145D69122AF',
    ],
];
