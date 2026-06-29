<!DOCTYPE html>
<!-- Document type: informs browser to render in standards mode -->
<html lang="en">
<head>
    <!-- Character encoding: ensures correct text rendering -->
    <meta charset="UTF-8">
    <!-- Responsive viewport for mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Page title shown in browser tab -->
    <title>Search Users</title>
    <!-- Local stylesheet for project-specific styling -->
    <link rel="stylesheet" href="styles.css">
    <!-- Use Bootstrap CDN for quick, consistent UI components -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <!-- Main heading for the page -->
    <h2>Search Users</h2>

    <!-- Search form posts back to this same endpoint (search.php).
         autocomplete="off" reduces browser autofill for privacy. -->
    <form method="POST" autocomplete="off">
        <div class="form-group">
            <!-- Text input for username or numeric user ID. 'required' enforces client-side presence. -->
            <input type="text" class="form-control" name="query" placeholder="Enter username or user ID" required>
        </div>
        <!-- Submit button triggers POST to server to perform the search. -->
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <?php if (!empty($results)) { ?>
        <!-- Results header shown only when there are matches returned by controller -->
        <h4 class="mt-4">Results:</h4>
        <ul class="list-group">
        <?php foreach ($results as $user) { ?>
            <!-- Each result shows a sanitized username and an integer-cast ID. -->
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($user['username']) ?> (ID: <?= (int)$user['user_id'] ?>)
                <!-- Link to view another user's profile; user_id is cast to int to
                     avoid reflected input and to ensure the URL contains a number. -->
                <a href="view_profile.php?user_id=<?= (int)$user['user_id'] ?>" class="btn btn-sm btn-info">View Profile</a>
            </li>
        <?php } ?>
        </ul>
    <?php } ?>

    <!-- Navigation back to the authenticated user's dashboard -->
    <a href="dashboard.php" class="btn btn-link mt-3">Back to Dashboard</a>
</div>
</body>
</html>
