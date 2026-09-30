<?php
// Allow both POST and htmx-driven navigational GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['action'])) {
    header("HTTP/1.1 403 Forbidden");
    exit("Direct entry access not permitted.");
}

$action = $_GET['action'] ?? '';

// Helper function to obfuscate emails for DB storage proof section
function obfuscateEmail($email) {
    $parts = explode('@', $email);
    if(count($parts) < 2) { return $email; }
    $name  = $parts[0];
    $domain = $parts[1];
    
    $obscuredName = (strlen($name) > 3) ? substr($name, 0, 3) . '***' : substr($name, 0, 1) . '***';
    $obscuredDomain = (strlen($domain) > 4) ? substr($domain, 0, 2) . '***' . substr($domain, -3) : $domain;
    
    return $obscuredName . '@' . $obscuredDomain;
}

switch ($action) {
    // Router
    case 'page':
        $pages = [
            'home' => __DIR__ . '/../client/home.html',
            'about' => __DIR__ . '/../client/about.html',
            'contact' => __DIR__ . '/../client/contact.html',
            'admin' => __DIR__ . '/../client/admin/dashboard.html',
        ];

        $page = $_GET['page'] ?? 'home';

        if (!isset($pages[$page])) {
            http_response_code(404);
            echo '<div class="alert alert-danger">Page not found.</div>';
            break;
        }

        readfile($pages[$page]);
        break;
    // Dynamic Timestamp Generation
    case 'get_year':
        echo date("Y");
        break;

    // Reactive Form Interaction Toggle
    case 'toggle_phone':
        $sales_call = $_POST['sales_call'] ?? '0';
        if ($sales_call === '1') {
            // Inputmode for mobile keypad and 10-digit strict regex validation
            echo '<div class="p-3 bg-light rounded border border-primary animate-fade-in mb-3">
                    <label for="callback-phone" class="form-label fw-semibold text-primary">Callback Phone Number (10 Digits)</label>
                    <input
                        type="tel"
                        id="callback-phone"
                        name="phone"
                        class="form-control"
                        placeholder="5551234567"
                        inputmode="numeric"
                        pattern="[0-9]{10}"
                        maxlength="10"
                        title="Please enter exactly 10 numeric digits with no spaces or dashes."
                        required
                    >
                </div>';
        }
        break;

    // Database Persistence Implementation
    case 'submit_lead':
        $name    = htmlspecialchars(trim($_POST['name'] ?? ''));
        $email   = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $message = htmlspecialchars(trim($_POST['message'] ?? ''));
        $sales   = isset($_POST['sales_call']) ? 1 : 0;
        // Strip unexpected non-digits on the server side
        $phone   = isset($_POST['phone']) ? preg_replace('/[^0-9]/', '', $_POST['phone']) : 'N/A';
        if (empty($phone)) { $phone = 'N/A'; }

        if (!$name || !$email || !$message) {
            echo '<div class="alert alert-danger mb-0">Error: Please provide all valid field parameters.</div>';
            exit;
        }

        try {
            // Establish target path matching folder structure
            $dbPath = __DIR__ . '/../db/threepager.db';
            $db = new PDO("sqlite:$dbPath");
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Construct table auto schema initialization
            $db->exec("CREATE TABLE IF NOT EXISTS leads (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT, email TEXT, message TEXT, phone TEXT, wants_call INTEGER, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            $stmt = $db->prepare("INSERT INTO leads (name, email, message, phone, wants_call) VALUES (:n, :e, :m, :p, :w)");
            $stmt->execute([':n' => $name, ':e' => $email, ':m' => $message, ':p' => $phone, ':w' => $sales]);

        } catch (PDOException $e) {
            echo '<div class="alert alert-danger mb-0">Database Processing Exception Intercepted.</div>';
            exit;
        }

        // Email dispatch trigger wrapper placeholder
        $to = "hello@yourmom.com";
        $subject = "New PoC Lead Captured: " . $name;
        $body = "Name: $name\nEmail: $email\nPhone: $phone\nSales Call Request: " . ($sales ? 'Yes' : 'No') . "\n\nMessage:\n$message";
        @mail($to, $subject, $body, "From: webserver@domain.com");

        echo '
            <div class="alert alert-success text-center p-4 shadow-sm mb-0">
                <h4 class="alert-heading fw-bold">🎉 Database Record Saved Successfully!</h4>
                <p class="mb-0">Thanks for experimenting with the architecture, <strong>' . $name . '</strong>. A confirmation message payload has been sent to the office admin.</p>
            </div>';
        break;



    // Admin Dashboard Mockup Teaser
    case 'admin_data':
        $requestedPage = filter_var(
            $_GET['p'] ?? 1,
            FILTER_VALIDATE_INT
        );
        $page = max(1, $requestedPage ?: 1);
        $limit = 25;
        $leads = [];
        $totalLeads = 0;
        $databaseError = false;

        try {
            $dbPath = __DIR__ . '/../db/threepager.db';
            $db = new PDO("sqlite:$dbPath");
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $totalLeads = (int) $db->query(
                'SELECT COUNT(*) FROM leads'
            )->fetchColumn();

            $totalPages = max(1, (int) ceil($totalLeads / $limit));
            $page = min($page, $totalPages);
            $offset = ($page - 1) * $limit;

            $stmt = $db->prepare('
                SELECT * FROM leads
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset
            ');
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $databaseError = true;
        }

        if ($databaseError) {
            http_response_code(500);
            echo '
                <div class="alert alert-danger mb-0">
                    Could not read the leads database. Check that PHP has PDO SQLite enabled and that the database is accessible.
                </div>';
            break;
        }

        if (empty($leads)) {
            echo '<div class="alert alert-warning">
                    No records found yet. Submit the contact form to see records here.
                </div>';
            break;
        }

        echo '
            <div class="table-responsive bg-white p-3 rounded border">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Obfuscated Email</th>
                        <th>Sales Call / Phone</th>
                        <th>Message</th>
                        <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($leads as $row) {
            echo '<tr>';
            echo '<td>' . (int) $row['id'] . '</td>';
            echo '<td>' . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td><code>' . htmlspecialchars(
                obfuscateEmail($row['email']),
                ENT_QUOTES,
                'UTF-8'
            ) . '</code></td>';
            $callDetails = (int) $row['wants_call'] === 1
                ? 'Yes (' . htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8') . ')'
                : 'No';
            echo '<td>' . $callDetails . '</td>';
            echo '<td class="text-break">' . nl2br(
                htmlspecialchars($row['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            ) . '</td>';
            echo '<td class="small text-muted">' .
                htmlspecialchars(date('Y-m-d', strtotime($row['created_at'])), ENT_QUOTES, 'UTF-8') .
                '</td>';
            echo '</tr>';
        }

        echo '
                </tbody>
            </table>
        </div>';

        // Pagination
        if ($totalPages > 1) {
            echo '
                <nav class="mt-3" aria-label="Dashboard pages">
                    <ul class="pagination justify-content-center">';

            for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++) {
            $active = $pageNumber === $page ? ' active' : '';
            echo '
                <li class="page-item' . $active . '">
                    <a
                        class="page-link" href="#"
                        hx-get="api/backend.php?action=admin_data&amp;p=' . $pageNumber . '"
                        hx-target="#dashboard-data"
                        hx-swap="innerHTML"'
                        . ($pageNumber === $page ? ' aria-current="page"' : '') . '>'
                        . $pageNumber . '
                    </a>
                </li>';
            }

            echo '
                </ul>
            </nav>';
        }
        break;
}
