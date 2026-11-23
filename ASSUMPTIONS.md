# Architectural & Product Assumptions

Where the assignment prompt left open-ended design choices, the following decisions were made to ensure a reliable, secure, and user-friendly multi-visitor Smart TV slideshow system:

1. **Returning Visitor Identification (Normalized Email Matching):**
   - *Gap:* How is a returning visitor reliably distinguished from a new visitor without requiring passwords or account registration?
   - *Decision:* Visitors enter their email address alongside their full name at check-in. Emails are normalized (`strtolower(trim($email))`) and matched against a `UNIQUE` index in the `visitors` table. When a returning email is detected, the system updates `last_visit_at`, flags the session as returning, and links any newly uploaded photos to the existing `visitor_id`.

2. **Privacy, Public Display Consent & Retention:**
   - *Gap:* What constitutes valid consent for displaying personal photos publicly, and how is unconsented media prevented from appearing?
   - *Decision:* Check-in requires an explicit, unchecked-by-default **Public Display Consent** checkbox (`consent = 1`). Both frontend validation and backend enforcement (`upload.php`) reject submissions lacking consent with HTTP `400`. Additionally, `api_slides.php` filters strictly by `WHERE v.consent_given = 1` as a database-level safeguard.

3. **Returning Visitor Check-In Without Re-Uploading Photos:**
   - *Gap:* What if a returning visitor wants to replay their existing gallery on the display without uploading new photos?
   - *Decision:* First-time visitors must upload at least one valid photo, whereas returning visitors may submit the check-in form with zero new files. Checking in updates `last_visit_at`, immediately switching the Smart TV display back to their stored photo gallery.

4. **Slideshow Playlist Scope & Minimal Smart TV Overlay:**
   - *Gap:* Should the display mix multiple visitors' photos together, and what metadata should appear on screen?
   - *Decision:* The slideshow strictly isolates and loops **only the currently active visitor's photos** (combining newly uploaded photos with any historical photos from previous visits) so the spotlight stays personal to the active visitor. The Smart TV overlay displays only the **visitor's full name** and **slide counter** (`Slide X of Y`), keeping the screen uncluttered.

5. **Multi-Input Manual Slide Navigation:**
   - *Gap:* How can users manually browse through slides on different display devices?
   - *Decision:* Alongside automatic timed slide rotation (`SLIDE_DURATION_SECONDS`), `display.php` supports left/right **touchscreen swipes**, **mouse click-and-drag gestures**, **two-finger laptop trackpad horizontal swipes** (`wheel` `deltaX` with cooldown debounce), and **Left/Right keyboard arrow keys**, resetting the auto-advance timer on each manual interaction.

6. **File Validation, Size Limits & Simultaneous Upload Concurrency:**
   - *Gap:* How are malicious files blocked and race conditions prevented when multiple visitors submit simultaneously?
   - *Decision:* Every file's actual binary signature is verified server-side using PHP `finfo(FILEINFO_MIME_TYPE)` (allowing only `image/jpeg`, `image/png`, and `image/webp`) and capped at `MAX_UPLOAD_SIZE_MB` (default `5MB`). Visitor lookups and inserts run inside a MySQL transaction with row-level locking (`SELECT ... FOR UPDATE`), and saved files are renamed using `visitorId + timestamp + bin2hex(random_bytes(8))` to eliminate filename collisions.