<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Profile</h2>
    <?php if (!empty($error)) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>'; ?>
    <?php if (!empty($success)) echo '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>'; ?>
    <form method="POST" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(SecurityService::generateCSRFToken()) ?>">
        <div class="form-group">
            <label>Username</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
        </div>
        <div class="form-group">
            <label for="bio">Biography</label>
            <textarea class="form-control" name="bio" rows="4"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label for="profile_image">Profile Image</label><br>
            <?php if (!empty($user['profile_image'])): ?>
                <img src="serve_image.php?file=<?= urlencode($user['profile_image']) ?>" alt="Profile Image" width="100"><br>
            <?php endif; ?>
            <input type="file" name="profile_image" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
            <small class="form-text text-muted">Allowed formats: JPG, PNG. Size must be between 200KB and 2MB.</small>
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
    <a href="dashboard.php" class="btn btn-link mt-3">Back to Dashboard</a>
</div>
</body>
</html>
