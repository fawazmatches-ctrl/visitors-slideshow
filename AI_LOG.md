# AI Collaboration & Decision Log

## Prompt 1: Initial Database & Project Architecture
- **Prompt:** *"Design a MySQL schema and PHP folder structure for a visitor photo slideshow where returning visitors also see their old photos."*
- **Outcome:** Generated `visitors` and `photos` tables with foreign keys, plus separate `index.php` (upload) and `display.php` (TV screen) views.

## Prompt 2: Catching AI Mistake #1 — Naive File Extension Validation
- **Prompt:** *"Write the PHP file upload handler for JPG, PNG, and WEBP images."*
- **AI Mistake:** The AI initially validated files using `pathinfo($filename, PATHINFO_EXTENSION)` and `$_FILES['photos']['type']`.
- **How I Noticed:** Both values come directly from the client browser and can be spoofed (e.g., renaming a `.php` script or `.exe` to `photo.jpg`), creating a remote code execution / security risk.
- **How I Fixed It:** Replaced extension/header checks with PHP's server-side `new finfo(FILEINFO_MIME_TYPE)` to inspect the actual binary magic bytes of the uploaded temporary file before moving it to `uploads/`.

## Prompt 3: Catching AI Mistake #2 — Filename Overwrites & Race Conditions During Concurrent Uploads
- **Prompt:** *"Ensure the upload script works safely when 5 visitors upload photos at the exact same second."*
- **AI Mistake:** The AI originally used `time() . '_' . $_FILES['photos']['name'][$i]` for saved filenames and ran a plain `SELECT` followed by `INSERT` without a transaction.
- **How I Noticed:** Two users uploading `IMG_001.jpg` within the same second would overwrite each other's photo on disk, and two simultaneous submissions from the same returning email could trigger a duplicate key SQL error.
- **How I Fixed It:** Added `bin2hex(random_bytes(8))` plus `$visitorId` to generate collision-proof filenames, and wrapped the visitor lookup/upsert in a MySQL transaction using `SELECT ... FOR UPDATE`.

## Prompt 4: Slideshow Interruption Logic on Smart TV Display
- **Prompt:** *"Write JavaScript for display.php so that when a new visitor uploads, their photos interrupt the current loop immediately instead of waiting for 20 old slides to finish."*
- **Outcome:** Implemented timestamp comparison (`newestTimestamp !== lastLatestUpload`) in `assets/js/slideshow.js` to reset `currentIndex = 0` and restart the slide timer immediately on new uploads.

## Prompt 5: Returning Visitor Query Design
- **Prompt:** *"Write the SQL query in api_slides.php to fetch all photos of the latest uploader (including past visits) first, followed by ambient photos from other visitors."*
- **Outcome:** Split the query into featured slides (`WHERE v.id = ? ORDER BY p.uploaded_at DESC`) merged ahead of ambient slides (`WHERE v.id != ? LIMIT 10`).

## Prompt 6: Catching AI Mistake #3 — Ambient Playlist Mixing, Returning Check-In & Swipe Gestures
- **Prompt:** *"Restrict the slideshow strictly to the active visitor's photos, allow returning visitors to replay their existing photos without uploading new ones, show only the visitor's name and slide numbers on the TV screen, and add touch/trackpad swipe controls."*
- **AI Mistake:** The AI's earlier query (`api_slides.php`) appended other visitors' ambient photos after the active visitor's photos, forced `<input type="file" required>` so returning visitors couldn't check in without new uploads, and omitted laptop trackpad `wheel` (`deltaX`) horizontal swipe handling.
- **How I Noticed:** During testing, switching between two visitors still showed other visitors' photos in the loop, blocked returning visitors who only wanted to replay old photos, and ignored two-finger trackpad swipes.
- **How I Fixed It:**
  - Updated `api_slides.php` to query exclusively `WHERE v.id = ?` ordered by `v.last_visit_at DESC`.
  - Removed `required` from the file input in `index.php` and updated `upload.php` to allow returning visitors to check in with 0 new files and immediately replay their past gallery.
  - Simplified `display.php` and `assets/js/slideshow.js` to display only the visitor's name and `Slide X of Y`, and added touch swipe, mouse drag, arrow-key, and debounced trackpad `wheel` (`deltaX`) navigation with CSS `overscroll-behavior-x: none`.