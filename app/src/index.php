<?php
require_once 'config.php';

$error = null;
$success = null;
$entries = [];

// Establish database connection using values from the static config file.
$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $db_user, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    $error = "Database connection failed: " . htmlspecialchars($e->getMessage());
    $pdo = null;
}

// Bootstrap schema on first run.
if ($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS guestbook_entries (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            name       VARCHAR(120)  NOT NULL,
            message    TEXT          NOT NULL,
            created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

// Handle form submission.
if ($pdo && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name !== '' && $message !== '') {
        $stmt = $pdo->prepare(
            "INSERT INTO guestbook_entries (name, message) VALUES (?, ?)"
        );
        $stmt->execute([$name, $message]);
        $success = "Entry added — thanks, " . htmlspecialchars($name) . "!";
    } else {
        $error = "Both name and message are required.";
    }
}

// Fetch all entries, newest first.
if ($pdo) {
    $entries = $pdo->query(
        "SELECT name, message, created_at FROM guestbook_entries ORDER BY created_at DESC"
    )->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legacy Guestbook</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #0f1117;
            color: #e2e8f0;
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .container {
            max-width: 720px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #f8f9fc;
        }

        header p {
            margin-top: .4rem;
            color: #94a3b8;
            font-size: .95rem;
        }

        .badge {
            display: inline-block;
            margin-top: .75rem;
            padding: .25rem .75rem;
            border-radius: 9999px;
            background: rgba(99, 102, 241, .15);
            border: 1px solid rgba(99, 102, 241, .4);
            color: #a5b4fc;
            font-size: .75rem;
            letter-spacing: .04em;
        }

        .alert {
            padding: .875rem 1.25rem;
            border-radius: .5rem;
            margin-bottom: 1.5rem;
            font-size: .9rem;
        }

        .alert-error {
            background: rgba(239, 68, 68, .12);
            border: 1px solid rgba(239, 68, 68, .35);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(34, 197, 94, .12);
            border: 1px solid rgba(34, 197, 94, .35);
            color: #86efac;
        }

        .card {
            background: #1e2230;
            border: 1px solid #2d3349;
            border-radius: .75rem;
            padding: 1.75rem;
            margin-bottom: 1.5rem;
        }

        .card h2 {
            font-size: 1.1rem;
            margin-bottom: 1.25rem;
            color: #c7d2fe;
        }

        label {
            display: block;
            font-size: .85rem;
            color: #94a3b8;
            margin-bottom: .35rem;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: .65rem .9rem;
            background: #0f1117;
            border: 1px solid #2d3349;
            border-radius: .5rem;
            color: #e2e8f0;
            font-size: .95rem;
            margin-bottom: 1rem;
            transition: border-color .15s;
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #6366f1;
        }

        textarea {
            resize: vertical;
            min-height: 90px;
        }

        button {
            background: #6366f1;
            color: #fff;
            border: none;
            padding: .65rem 1.5rem;
            border-radius: .5rem;
            font-size: .95rem;
            cursor: pointer;
            font-weight: 600;
            transition: background .15s;
        }

        button:hover {
            background: #4f46e5;
        }

        .entry {
            border-top: 1px solid #2d3349;
            padding: 1.1rem 0;
        }

        .entry:first-child {
            border-top: none;
            padding-top: 0;
        }

        .entry-name {
            font-weight: 600;
            color: #c7d2fe;
        }

        .entry-date {
            font-size: .78rem;
            color: #64748b;
            margin-left: .5rem;
        }

        .entry-msg {
            margin-top: .35rem;
            color: #cbd5e1;
            line-height: 1.55;
        }

        .empty {
            text-align: center;
            color: #475569;
            padding: 1.5rem 0;
            font-style: italic;
        }

        .db-info {
            margin-top: 2rem;
            padding: .75rem 1rem;
            background: rgba(15, 17, 23, .6);
            border: 1px dashed #2d3349;
            border-radius: .5rem;
            font-size: .78rem;
            color: #475569;
            font-family: monospace;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>Legacy Guestbook</h1>
            <p>A classic PHP app — source unchanged, onboarded via Score config shim</p>
            <span class="badge">score-php-legacy-onboarding-demo</span>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>Leave a message</h2>
            <form method="POST" action="">
                <label for="name">Your name</label>
                <input id="name" type="text" name="name" placeholder="Ada Lovelace" required>
                <label for="message">Message</label>
                <textarea id="message" name="message" placeholder="Score makes platform engineering legible."
                    required></textarea>
                <button type="submit">Sign the guestbook</button>
            </form>
        </div>

        <div class="card">
            <h2>Entries (<?= count($entries) ?>)</h2>
            <?php if (empty($entries)): ?>
                <p class="empty">No entries yet — be the first to sign!</p>
            <?php else: ?>
                <?php foreach ($entries as $e): ?>
                    <div class="entry">
                        <span class="entry-name"><?= htmlspecialchars($e['name']) ?></span>
                        <span class="entry-date"><?= htmlspecialchars($e['created_at']) ?></span>
                        <p class="entry-msg"><?= nl2br(htmlspecialchars($e['message'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="db-info">
            Connected to: <strong><?= htmlspecialchars($db_host) ?>:<?= htmlspecialchars($db_port) ?></strong>
            / db: <strong><?= htmlspecialchars($db_name) ?></strong>
            / user: <strong><?= htmlspecialchars($db_user) ?></strong>
        </div>
    </div>
</body>

</html>