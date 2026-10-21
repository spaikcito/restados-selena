<?php
// Only explicit public endpoints may execute. Configuration and source stay private.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$routes = ['/' => 'index.php', '/index.php' => 'index.php', '/verificador.php' => 'verificador.php', '/api.php' => 'api.php', '/api_lotoven.php' => 'api_lotoven.php', '/api_loteriadehoy.php' => 'api_loteriadehoy.php', '/imagen.php' => 'imagen.php', '/historial.php' => 'historial.php'];
$file = $routes[$path] ?? null;
if (!$file) { http_response_code(404); header('Content-Type: text/plain; charset=utf-8'); echo 'Página no encontrada'; exit; }
header('X-Content-Type-Options: nosniff');
require dirname(__DIR__) . '/' . $file;
