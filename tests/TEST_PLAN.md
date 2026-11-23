# Manual & Automated Test Plan

## Automated Validation Script
Run `php tests/test_validation.php` from the project root to verify MIME detection, size limits, and email normalization logic.

## Manual Test Cases & Results

| Test ID | Scenario | Steps | Expected Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **TC-01** | First-Time Visitor Upload | Enter new name + email, check consent box, upload 2 JPGs. | Visitor created in DB; both photos saved to `uploads/` and immediately play on `display.php`. | **PASS** |
| **TC-02** | Returning Visitor Playback | Submit again using the **same email** with 1 new PNG photo. | `is_returning: true`; TV display plays the 1 new photo + 2 previous photos (3 total). | **PASS** |
| **TC-03** | Missing Consent Block | Fill name, email, and file, leave consent checkbox unchecked, submit. | Rejected with HTTP 400 error; no file saved to disk or DB. | **PASS** |
| **TC-04** | Oversized File Rejection | Attempt to upload an 8MB image (limit is 5MB). | Rejected with message indicating 5MB size limit exceeded. | **PASS** |
| **TC-05** | Spoofed File Extension | Rename a `.txt` or `.pdf` file to `fake.jpg` and upload. | `finfo` detects `text/plain` / `application/pdf` and rejects file. | **PASS** |
| **TC-06** | Simultaneous Multi-User Upload | Submit uploads from two browser tabs at the exact same moment with identical filenames (`photo.jpg`). | Both files saved with unique `random_bytes` names; zero overwrites. | **PASS** |
