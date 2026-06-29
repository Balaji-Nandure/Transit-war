<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Money</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Transfer Money</h2>
    <?php if (!empty($error)) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>'; ?>
    <?php if (!empty($success)) echo '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>'; ?>
    <div class="alert alert-info">
        <strong>Your Balance:</strong> Rs. <?= htmlspecialchars(number_format($balance, 2)) ?>
    </div>
    <form method="POST" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(SecurityService::generateCSRFToken()) ?>">
        <div class="form-group">
            <label for="receiver_id">Receiver User ID</label>
            <input type="number" class="form-control" name="receiver_id" value="<?= isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" class="form-control" name="amount" min="0.01" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="comment">Comment (optional)</label>
            <input type="text" class="form-control" name="comment">
        </div>
        <button type="submit" class="btn btn-primary">Transfer</button>
    </form>
    <a href="dashboard.php" class="btn btn-link mt-3">Back to Dashboard</a>
</div>
</body>
</html>
