<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>User Profile</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php else: ?>
        <?php if (!empty($profile['profile_image'])): ?>
            <img src="serve_image.php?file=<?= urlencode($profile['profile_image']) ?>" alt="Profile Image" class="rounded-circle mb-3" width="120" height="120">
        <?php endif; ?>
        <table class="table table-bordered">
            <tr><th>User ID</th><td><?= (int)$profile['user_id'] ?></td></tr>
            <tr><th>Username</th><td><?= htmlspecialchars($profile['username']) ?></td></tr>
            <tr><th>Biography</th><td><?= nl2br(htmlspecialchars($profile['bio'] ?? '')) ?></td></tr>
            <tr><th>Member Since</th><td><?= htmlspecialchars($profile['created_at']) ?></td></tr>
        </table>
        <a href="transfer.php?receiver_id=<?= (int)$profile['user_id'] ?>" class="btn btn-success">Send Money</a>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-link mt-3">Back to Dashboard</a>
</div>
</body>
</html>
