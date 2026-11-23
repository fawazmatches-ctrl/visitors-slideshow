<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Check-In & Photo Upload</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Visitor Photo Wall</h1>
            <p>Upload your photos to appear live on the venue display screen.</p>
            <a href="display.php" target="_blank" class="btn-secondary">Open TV Slideshow Display &rarr;</a>
        </header>

        <main class="card">
            <div id="alertBox" class="alert hidden"></div>

            <form id="uploadForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="full_name">Full Name <span class="required">*</span></label>
                    <input type="text" id="full_name" name="full_name" required placeholder="e.g. Fawaz Matcheswala" maxlength="100">
                </div>

                <div class="form-group">
                    <label for="email">Email Address (Used to recognize returning visits) <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required placeholder="e.g. fawaz@example.com" maxlength="150">
                    <small class="hint">If you have visited before, your previous photos will automatically play alongside your new ones!</small>
                </div>

                <div class="form-group">
                    <label for="photos">Select Photos (JPG, PNG, WEBP — Max <?php echo MAX_UPLOAD_SIZE_MB; ?>MB each)</label>
                    <input type="file" id="photos" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple>
                    <small class="hint">Returning visitor? Leave this empty to replay your previous photos on the display!</small>
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" id="consent" name="consent" value="1" required>
                    <label for="consent">
                        <strong>Public Display Consent:</strong> I confirm I have permission to share these photos and consent to displaying them publicly on the venue slideshow screen. <span class="required">*</span>
                    </label>
                </div>

                <button type="submit" id="submitBtn" class="btn-primary">Upload &amp; Play on Display</button>
            </form>
        </main>
    </div>

    <script src="assets/js/upload.js"></script>
</body>
</html>
