<?php
// admin/search.php — AJAX name/ticket search for scan page
require_once 'auth.php';
require_once '../db/connect.php';

header('Content-Type: application/json');
$q = trim(filter_input(INPUT_GET, 'q', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

if (!$q) { echo json_encode(['results' => []]); exit; }

$stmt = $pdo->prepare("
    SELECT r.ticket_id, r.full_name, r.email, r.checked_in, t.tour_number
    FROM registrations r JOIN tours t ON t.id = r.tour_id
    WHERE r.full_name LIKE ? OR r.ticket_id LIKE ?
    LIMIT 10
");
$stmt->execute(["%$q%", "%$q%"]);
echo json_encode(['results' => $stmt->fetchAll()]);
