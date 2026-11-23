# System Architecture

## Tech Stack Rationale
- **PHP 8 + PDO:** Native multipart file handling (`$_FILES`), built-in `finfo` binary MIME inspection, and prepared statements against SQL injection.
- **MySQL:** Relational integrity between `visitors` and `photos` (`ON DELETE CASCADE`) with row-level locking (`FOR UPDATE`) for concurrent uploads.
- **HTML5 / CSS3 / Vanilla JS:** Zero build-step overhead; smooth CSS keyframe transitions and progress bar animations suitable for Smart TV browsers.

## Data Model

### `visitors` Table
| Column | Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `INT` | `PRIMARY KEY, AUTO_INCREMENT` | Unique visitor ID |
| `full_name` | `VARCHAR(100)` | `NOT NULL` | Display name shown on TV |
| `email` | `VARCHAR(150)` | `NOT NULL, UNIQUE` | Normalized identifier for returning visits |
| `consent_given` | `TINYINT(1)` | `NOT NULL, DEFAULT 1` | Explicit public display consent flag |
| `created_at` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | First visit timestamp |
| `last_visit_at` | `TIMESTAMP` | `ON UPDATE CURRENT_TIMESTAMP` | Most recent visit timestamp |

### `photos` Table
| Column | Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `INT` | `PRIMARY KEY, AUTO_INCREMENT` | Unique photo ID |
| `visitor_id` | `INT` | `FOREIGN KEY -> visitors(id)` | Owner of the photo |
| `file_name` | `VARCHAR(255)` | `NOT NULL` | Original uploaded filename |
| `file_path` | `VARCHAR(255)` | `NOT NULL` | Collision-safe relative path in `uploads/` |
| `mime_type` | `VARCHAR(50)` | `NOT NULL` | Verified MIME type (`image/jpeg`, etc.) |
| `file_size` | `INT` | `NOT NULL` | File size in bytes |
| `uploaded_at` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | Upload timestamp |

## Architecture Diagram

```text
+-----------------------+         POST /upload.php          +-----------------------+
|   Visitor Smartphone  |  ------------------------------>  |     PHP Backend       |
|      (index.php)      |    (Name, Email, Photos, Consent) | - Validate MIME/Size  |
+-----------------------+                                   | - DB Transaction Lock |
                                                            | - Save to /uploads/   |
                                                            +-----------+-----------+
                                                                        |
                                                                        v
+-----------------------+         GET /api_slides.php       +-----------------------+
|   Smart TV Display    |  <------------------------------  |     MySQL Database    |
|     (display.php)     |     (Polls every 3s for newest    | - visitors            |
| - Fullscreen Fade UI  |      visitor + historical photos) | - photos              |
+-----------------------+                                   +-----------------------+
```
