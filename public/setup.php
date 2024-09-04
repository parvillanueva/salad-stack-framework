<?php
// Check if the setup is already complete
if (file_exists(__DIR__ . '/../.env') && getenv('IS_SET') === 'true') {
    header('Location: /');
    exit;
}

$errors = [];
$successMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $dbHost = $_POST['db_host'] ?? '';
    $dbName = $_POST['db_name'] ?? '';
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';
    $siteTitle = $_POST['site_title'] ?? '';
    $siteDescription = $_POST['site_description'] ?? '';
    $adminUser = $_POST['admin_user'] ?? '';
    $adminPass = $_POST['admin_pass'] ?? '';
    $adminPassConfirm = $_POST['admin_pass_confirm'] ?? '';

    // Validate passwords
    if ($adminPass !== $adminPassConfirm) {
        $errors[] = "Passwords do not match!";
    }

    try {
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connectionStatus = "Database connection successful!";

    } catch (PDOException $e) {            
        $errors[] = "Database connection failed: " . $e->getMessage();

    }
    if (empty($errors)) {
        // Save configuration to .env file
        $envContent = "DB_HOST=$dbHost\n";
        $envContent .= "DB_NAME=$dbName\n";
        $envContent .= "DB_USER=$dbUser\n";
        $envContent .= "DB_PASS=$dbPass\n";
        $envContent .= "SITE_TITLE=\"$siteTitle\"\n";
        $envContent .= "SITE_DESCRIPTION=\"$siteDescription\"\n";
        $envContent .= "ADMIN_USER=\"$adminUser\"\n";
        $envContent .= "ADMIN_PASS=\"$adminPass\"\n";
        $envContent .= "IS_SET=true\n";
    
        file_put_contents(__DIR__ . '/../.env', $envContent);
    
        header('Location: /setup');
        exit;

    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup</title>
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .error {
            color: #dc3545;
        }
        .success {
            color: #198754;
        }
    </style>
</head>
<body>
    <div class="container">    
        <center>
            <h2>Install Salad</h2>
            <p class="mb-4">Setup your site by filling out the required details below.</p>
        </center>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php elseif ($successMessage): ?>
            <div class="alert alert-success">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>
        <form action="setup.php" method="post">
            
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label for="site_title" class="form-label">Site Title:</label>
                </div>
                <div class="col-sm-8">
                    <input type="text" id="site_title" name="site_title" class="form-control" value="<?php echo htmlspecialchars($siteTitle ?? ''); ?>" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label for="site_description" class="form-label">Site Description:</label>
                </div>
                <div class="col-sm-8">
                    <textarea id="site_description" name="site_description" class="form-control" required><?php echo htmlspecialchars($siteDescription ?? ''); ?></textarea>
                </div>
            </div>
            <hr>
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label for="db_host" class="form-label">MYSQL Host:</label>
                </div>
                <div class="col-sm-8">
                    <input type="text" id="db_host" name="db_host" class="form-control" value="<?php echo htmlspecialchars($dbHost ?? ''); ?>" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label for="db_name" class="form-label">MYSQL Database Name:</label>
                </div>
                <div class="col-sm-8">
                    <input type="text" id="db_name" name="db_name" class="form-control" value="<?php echo htmlspecialchars($dbName ?? ''); ?>" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label for="db_user" class="form-label">MYSQL User:</label>
                </div>
                <div class="col-sm-8">
                    <input type="text" id="db_user" name="db_user" class="form-control" value="<?php echo htmlspecialchars($dbUser ?? ''); ?>" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label for="db_pass" class="form-label">MYSQL Password:</label>
                </div>
                <div class="col-sm-8">
                    <input type="password" id="db_pass" name="db_pass" class="form-control" required>
                </div>
            </div>
            <hr>
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label for="admin_user" class="form-label">Admin Email:</label>
                </div>
                <div class="col-sm-8">
                    <input type="email" id="admin_user" name="admin_user" class="form-control" value="<?php echo htmlspecialchars($adminUser ?? ''); ?>" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label for="admin_pass" class="form-label">Admin Password:</label>
                </div>
                <div class="col-sm-8">
                    <input type="password" id="admin_pass" name="admin_pass" class="form-control" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label for="admin_pass_confirm" class="form-label">Confirm Admin Password:</label>
                </div>
                <div class="col-sm-8">
                    <input type="password" id="admin_pass_confirm" name="admin_pass_confirm" class="form-control" required>
                </div>
            </div>
            <hr>
            <center><button type="submit" class="btn btn-primary">Save Configuration</button></center>
        </form>
    </div>
    <script src="./js/bootstrap.bundle.min.js"></script>
</body>
</html>