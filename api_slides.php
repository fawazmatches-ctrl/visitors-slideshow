<?php
header('Content-Type: application/json');
require_once 'config.php';

try {
    // Find the most recently checked-in or uploaded visitor (using last_visit_at)
    $latestStmt = $pdo->query("
        SELECT v.id, v.full_name, v.email, v.last_visit_at as latest_activity, COUNT(p.id) as photo_count
        FROM visitors v
        JOIN photos p ON v.id = p.visitor_id
        WHERE v.consent_given = 1
        GROUP BY v.id, v.full_name, v.email, v.last_visit_at
        ORDER BY v.last_visit_at DESC
        LIMIT 1
    ");
    $activeVisitor = $latestStmt->fetch();

    if (!$activeVisitor) {
        echo json_encode([
            'status' => 'empty',
            'slide_duration' => SLIDE_DURATION_SECONDS,
            'slides' => []
        ]);
        exit;
    }

    // Fetch ONLY this visitor's photos (current + previous visits)
    $activePhotosStmt = $pdo->prepare("
        SELECT p.id, p.file_path, p.uploaded_at, v.full_name, 1 as is_featured
        FROM photos p
        JOIN visitors v ON p.visitor_id = v.id
        WHERE v.id = ? AND v.consent_given = 1
        ORDER BY p.uploaded_at DESC
    ");
    $activePhotosStmt->execute([$activeVisitor['id']]);
    $slides = $activePhotosStmt->fetchAll();

    echo json_encode([
        'status' => 'ok',
        'slide_duration' => SLIDE_DURATION_SECONDS,
        'active_visitor' => [
            'id' => $activeVisitor['id'],
            'name' => $activeVisitor['full_name'],
            'latest_upload' => $activeVisitor['latest_activity'],
            'total_photos' => $activeVisitor['photo_count']
        ],
        'slides' => $slides
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>