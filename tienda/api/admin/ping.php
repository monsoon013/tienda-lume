<?php
require_once __DIR__ . '/../../auth.php';

header('Content-Type: application/json; charset=utf-8');

requireAdmin();

echo json_encode(['ok' => true]);
?>