<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart TV Slideshow Display - Visitor Photo Wall</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="tv-body">
    <div id="slideshowContainer" class="tv-container">
        <div id="emptyState" class="tv-empty">
            <h2>Waiting for Visitor Photos...</h2>
            <p>Scan or visit the check-in page to upload your photos!</p>
        </div>

        <img id="slideImage" class="tv-image hidden" src="" alt="Visitor Photo" draggable="false">

        <div id="captionOverlay" class="tv-overlay hidden">
            <h2 id="visitorName">Visitor Name</h2>
            <p id="slideMeta">Slide 1 of 1</p>
            <div class="progress-bar-bg">
                <div id="progressBar" class="progress-bar-fill"></div>
            </div>
        </div>
    </div>

<script src="assets/js/slideshow.js?v=3"></script></body>
</html>