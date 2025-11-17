<?php
session_start();
include_once 'election.php';

$election = new Election();
$current_election = $election->getCurrentElection();

if (!$current_election) {
    die("No active election found. Please contact administrator.");
}

$_SESSION['election_id'] = $current_election['election_id'];

$screen = isset($_GET['screen']) ? $_GET['screen'] : 'login';
$valid_screens = ['login', 'verification', 'ballot', 'results', 'admin'];

if (!in_array($screen, $valid_screens)) {
    $screen = 'login';
}

if ($screen === 'admin' && !isset($_SESSION['admin_logged_in'])) {
    $screen = 'login';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Student Council Elections</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php if (in_array($screen, ['login', 'verification', 'ballot'])): ?>
        <div class="progress-container">
            <div class="progress-bar" id="progressBar"></div>
            <div class="progress-step <?php echo $screen === 'login' ? 'active' : ''; ?>">
                <div class="progress-circle">1</div>
                <div class="progress-label">Login</div>
            </div>
            <div class="progress-step <?php echo $screen === 'verification' ? 'active' : ''; ?>">
                <div class="progress-circle">2</div>
                <div class="progress-label">Verify</div>
            </div>
            <div class="progress-step <?php echo $screen === 'ballot' ? 'active' : ''; ?>">
                <div class="progress-circle">3</div>
                <div class="progress-label">Vote</div>
            </div>
        </div>
        <?php endif; ?>

        <?php
        switch ($screen) {
            case 'login':
                include 'pages/login.php';
                break;
            case 'verification':
                include 'pages/verification.php';
                break;
            case 'ballot':
                include 'pages/ballot.php';
                break;
            case 'results':
                include 'pages/results.php';
                break;
            case 'admin':
                include 'pages/admin.php';
                break;
            default:
                include 'pages/login.php';
        }
        ?>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <div id="adminLoginModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Administrator Login</h2>
            </div>
            <form action="process.php" method="POST">
                <div class="form-group">
                    <label for="admin_username">Username</label>
                    <input type="text" name="admin_username" id="admin_username" required>
                </div>
                <div class="form-group">
                    <label for="admin_password">Password</label>
                    <input type="password" name="admin_password" id="admin_password" required>
                </div>
                <?php if (isset($_SESSION['admin_error'])): ?>
                    <div class="error-message"><?php echo $_SESSION['admin_error']; unset($_SESSION['admin_error']); ?></div>
                <?php endif; ?>
                <div class="modal-actions">
                    <button type="button" id="cancelAdminLogin" class="btn btn-outline">Cancel</button>
                    <button type="submit" name="admin_login" class="btn">Login</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>