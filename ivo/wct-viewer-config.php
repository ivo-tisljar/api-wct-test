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
        'password' => '4E2029E6D6DB8F81C049CB5319BB46F303A8126FDE0224BCB58A2C74E1424FEF',      // password of the read-only 'viewer'@'localhost' user
        'charset'  => 'utf8mb4',
    ],

    // Tokens allowed to view the tables: exactly 64 hex characters each.
    // Generate one with:  openssl rand -hex 32
    'tokens' => [
        // 'Client app team' => '7D491220C3D685D97534EC01A52CE8B65B39D145D6115B2AB04DA3462ECF54AF',
    ],
];
