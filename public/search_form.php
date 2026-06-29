<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Users</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Search Users</h2>
    <form method="POST" autocomplete="off">
        <div class="form-group">
            <input type="text" class="form-control" name="query" placeholder="Enter username or user ID" required>
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>
    <?php if (!empty($results)) { ?>
        <h4 class="mt-4">Results:</h4>
        <ul class="list-group">
        <?php foreach ($results as $user) { ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($user['username']) ?> (ID: <?= (int)$user['user_id'] ?>)
                <a href="view_profile.php?user_id=<?= (int)$user['user_id'] ?>" class="btn btn-sm btn-info">View Profile</a>
            </li>
        <?php } ?>
        </ul>
    <?php } ?>
    <a href="dashboard.php" class="btn btn-link mt-3">Back to Dashboard</a>
</div>
</body>
</html>
