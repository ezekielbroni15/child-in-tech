<?php
// ============================================================
//  GET /get-tours.php
//  Returns JSON array of active upcoming tours with slot info
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/db/connect.php';

try {
    // Get active tours with registration count
    $stmt = $pdo->query("
        SELECT 
            t.id,
            t.tour_number,
            t.tour_date,
            t.time_start,
            t.time_end,
            t.location,
            t.max_slots,
            COUNT(r.id) AS registered_count
        FROM tours t
        LEFT JOIN registrations r ON r.tour_id = t.id
        WHERE t.is_active = 1
          AND t.tour_date >= CURDATE()
        GROUP BY t.id
        ORDER BY t.tour_date ASC
    ");

    $tours = $stmt->fetchAll();

    $result = array_map(function($tour) {
        $registered = (int)$tour['registered_count'];
        $max        = (int)$tour['max_slots'];
        $remaining  = max(0, $max - $registered);

        return [
            'id'           => (int)$tour['id'],
            'tour_number'  => $tour['tour_number'],
            'tour_date'    => $tour['tour_date'],
            'time_start'   => $tour['time_start'],
            'time_end'     => $tour['time_end'],
            'location'     => $tour['location'],
            'max_slots'    => $max,
            'registered'   => $registered,
            'remaining'    => $remaining,
            'is_full'      => $remaining === 0,
            'label'        => date('D, M j', strtotime($tour['tour_date']))
                              . ' — Tour ' . $tour['tour_number']
                              . ($remaining === 0 ? ' (FULL)' : ' · ' . $remaining . ' slots left'),
        ];
    }, $tours);

    echo json_encode(['success' => true, 'tours' => $result]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
