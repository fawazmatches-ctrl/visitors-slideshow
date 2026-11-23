<?php
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

$fullName = trim($_POST['full_name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$consent = isset($_POST['consent']) && $_POST['consent'] === '1';

// 1. Input & Consent Validation
if (empty($fullName) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Please provide a valid name and email address.']);
    exit;
}

if (!$consent) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Explicit consent is required before displaying photos publicly.']);
    exit;
}

$hasNewFiles = isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0]);
$maxBytes = MAX_UPLOAD_SIZE_MB * 1024 * 1024;
$allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$uploadDir = __DIR__ . '/uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

try {
    $pdo->beginTransaction();

    // 2. Identify Returning Visitor via normalized Email
    $stmt = $pdo->prepare("SELECT id, full_name FROM visitors WHERE email = ? FOR UPDATE");
    $stmt->execute([$email]);
    $visitor = $stmt->fetch();

    $isReturning = false;
    if ($visitor) {
        $isReturning = true;
        $visitorId = $visitor['id'];
        // Update last_visit_at so the TV display immediately switches back to this visitor
        $updateStmt = $pdo->prepare("UPDATE visitors SET full_name = ?, consent_given = 1, last_visit_at = CURRENT_TIMESTAMP WHERE id = ?");
        $updateStmt->execute([$fullName, $visitorId]);
    } else {
        // First-time visitors must upload at least 1 photo
        if (!$hasNewFiles) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'First-time visitors must select at least one photo to upload.']);
            exit;
        }
        $insertStmt = $pdo->prepare("INSERT INTO visitors (full_name, email, consent_given) VALUES (?, ?, 1)");
        $insertStmt->execute([$fullName, $email]);
        $visitorId = $pdo->lastInsertId();
    }

    // 3. Process New Photos (if any were selected)
    $uploadedCount = 0;
    $errors = [];

    if ($hasNewFiles) {
        $fileCount = count($_FILES['photos']['name']);
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        for ($i = 0; $i < $fileCount; $i++) {
            $error = $_FILES['photos']['error'][$i];
            $tmpName = $_FILES['photos']['tmp_name'][$i];
            $origName = basename($_FILES['photos']['name'][$i]);
            $size = $_FILES['photos']['size'][$i];

            if ($error !== UPLOAD_ERR_OK) {
                $errors[] = "$origName failed to upload (Error code: $error).";
                continue;
            }

            if ($size > $maxBytes) {
                $errors[] = "$origName exceeds the " . MAX_UPLOAD_SIZE_MB . "MB size limit.";
                continue;
            }

            $detectedMime = $finfo->file($tmpName);
            if (!array_key_exists($detectedMime, $allowedMimes)) {
                $errors[] = "$origName is not a valid image (Allowed: JPG, PNG, WEBP).";
                continue;
            }

            $ext = $allowedMimes[$detectedMime];
            $safeFilename = sprintf('v%d_%s_%s.%s', $visitorId, date('YmdHis'), bin2hex(random_bytes(8)), $ext);
            $destination = $uploadDir . $safeFilename;
            $relativePath = 'uploads/' . $safeFilename;

            if (move_uploaded_file($tmpName, $destination)) {
                $photoStmt = $pdo->prepare("INSERT INTO photos (visitor_id, file_name, file_path, mime_type, file_size) VALUES (?, ?, ?, ?, ?)");
                $photoStmt->execute([$visitorId, $origName, $relativePath, $detectedMime, $size]);
                $uploadedCount++;
            } else {
                $errors[] = "Server could not save $origName.";
            }
        }

        if ($uploadedCount === 0 && !$isReturning) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'No valid photos were uploaded.',
                'details' => $errors
            ]);
            exit;
        }
    }

    // Check total existing + new photos for this visitor
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM photos WHERE visitor_id = ?");
    $countStmt->execute([$visitorId]);
    $totalPhotos = (int)$countStmt->fetch()['total'];

    if ($totalPhotos === 0) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'No valid photos found for your profile. Please select at least one valid image.',
            'details' => $errors
        ]);
        exit;
    }

    $pdo->commit();

    if ($isReturning && $uploadedCount === 0) {
        $msg = "Welcome back, $fullName! Playing your $totalPhotos previous photo(s) on the display!";
    } elseif ($isReturning) {
        $msg = "Welcome back, $fullName! Uploaded $uploadedCount new photo(s). Playing all $totalPhotos of your photos on the display!";
    } else {
        $msg = "Welcome, $fullName! Uploaded $uploadedCount photo(s). Your slideshow is now live on the display!";
    }

    echo json_encode([
        'status' => 'success',
        'is_returning' => $isReturning,
        'visitor_id' => $visitorId,
        'visitor_name' => $fullName,
        'uploaded_now' => $uploadedCount,
        'total_visitor_photos' => $totalPhotos,
        'warnings' => $errors,
        'message' => $msg
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
?>