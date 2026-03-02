<?php
// ============================================================
//  GET /calendar.php?ticket_id=CIT-20260307-0042
//  Downloads an .ics calendar file for the event
// ============================================================
require_once __DIR__ . '/db/connect.php';

$ticket_id = filter_input(INPUT_GET, 'ticket_id', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$ticket_id) {
    http_response_code(400);
    exit('Invalid request');
}

try {
    $stmt = $pdo->prepare("
        SELECT r.*, t.tour_number, t.tour_date, t.time_start, t.time_end, t.location
        FROM registrations r
        JOIN tours t ON t.id = r.tour_id
        WHERE r.ticket_id = ?
    ");
    $stmt->execute([$ticket_id]);
    $reg = $stmt->fetch();

    if (!$reg) {
        http_response_code(404);
        exit('Registration not found');
    }

    $dtStart  = date('Ymd', strtotime($reg['tour_date'])) . 'T'
              . str_replace(':', '', substr($reg['time_start'], 0, 5)) . '00';
    $dtEnd    = date('Ymd', strtotime($reg['tour_date'])) . 'T'
              . str_replace(':', '', substr($reg['time_end'],   0, 5)) . '00';
    $now      = gmdate('Ymd\THis\Z');
    $summary  = 'Innoventure Tour ' . $reg['tour_number'] . ' — Child In Tech';
    $desc     = 'Ticket ID: ' . $reg['ticket_id'] . '\nOne day of exploration at real tech companies!';

    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="innoventure-tour-' . $reg['tour_number'] . '.ics"');

    echo "BEGIN:VCALENDAR\r\n";
    echo "VERSION:2.0\r\n";
    echo "PRODID:-//Child In Tech//Innoventure Tour//EN\r\n";
    echo "BEGIN:VEVENT\r\n";
    echo "UID:" . $reg['ticket_id'] . "@childintech.com\r\n";
    echo "DTSTAMP:" . $now . "\r\n";
    echo "DTSTART:" . $dtStart . "\r\n";
    echo "DTEND:" . $dtEnd . "\r\n";
    echo "SUMMARY:" . $summary . "\r\n";
    echo "DESCRIPTION:" . $desc . "\r\n";
    echo "LOCATION:" . $reg['location'] . "\r\n";
    echo "STATUS:CONFIRMED\r\n";
    echo "END:VEVENT\r\n";
    echo "END:VCALENDAR\r\n";

} catch (PDOException $e) {
    http_response_code(500);
    exit('Error generating calendar file');
}
