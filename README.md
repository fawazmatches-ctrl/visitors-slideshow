# Visitor Photo Slideshow

A web application built with **PHP, MySQL, HTML, CSS, and Vanilla JavaScript** that lets event or venue visitors check in with their name, email, and photos, immediately triggering a real-time slideshow on a Smart TV or computer display—including historical photos for returning visitors.

## Features
- **Instant Slideshow Trigger:** As soon as an upload finishes, the Smart TV display (`display.php`) detects the new upload within 3 seconds and immediately showcases the visitor's photos.
- **Returning Visitor Recognition:** Visitors are matched via normalized email address. When a visitor returns, all their previous photos are automatically retrieved alongside their newly uploaded photos.
- **Privacy & Explicit Consent:** Mandatory opt-in checkbox enforced on both client and server (`upload.php`) before storing or displaying any photo.
- **Security & Concurrency Safe:** Uses `finfo` binary MIME inspection, file size enforcement, MySQL row-level transactions (`FOR UPDATE`), and cryptographically random filenames (`random_bytes`) to prevent overwrites during simultaneous uploads.

## Tech Stack
- **Backend:** PHP 8.x (PDO + `fileinfo` extension)
- **Database:** MySQL 8.0 / MariaDB
- **Frontend:** HTML5, CSS3, Vanilla JavaScript (Fetch API)

## Setup & Run Instructions
1. **Clone the repository:**
   ```bash
   git clone <your-public-repo-url>
   cd visitor-photo-slideshow
   ```
2. **Configure environment variables:**
   ```bash
   cp .env.example .env
   ```
   Edit `.env` with your local MySQL credentials (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).
3. **Create the MySQL database & tables:**
   Import `schema.sql` into your MySQL server (via phpMyAdmin or CLI):
   ```bash
   mysql -u root -p < schema.sql
   ```
4. **Ensure upload permissions:**
   Make sure the `uploads/` folder is writable by PHP.
5. **Run the application:**
   - Using PHP's built-in server:
     ```bash
     php -S localhost:8080
     ```
   - Or place the folder inside `htdocs/` (XAMPP) / `www/` (WAMP).
6. **Open the two interfaces:**
   - **Visitor Upload Form:** `http://localhost:8080/index.php`
   - **Smart TV Display Screen:** `http://localhost:8080/display.php`
