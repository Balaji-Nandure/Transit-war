<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5" style="max-width: 800px;">
    <h2>Transaction History</h2>
    <div class="alert alert-info">
        <strong>Your Balance:</strong> Rs. <?= htmlspecialchars(number_format($balance, 2)) ?>
    </div>

    <?php if (empty($transactions)): ?>
        <p class="text-muted">No transactions found.</p>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Amount</th>
                    <th>Comment</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($transactions as $txn): ?>
                <tr>
                    <td><?= (int)$txn['transaction_id'] ?></td>
                    <td><?= htmlspecialchars($txn['created_at']) ?></td>
                    <td>
                        <?php if ((int)$txn['sender_id'] === (int)$_SESSION['user_id']): ?>
                            <span class="badge badge-danger">Sent</span>
                        <?php else: ?>
                            <span class="badge badge-success">Received</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($txn['sender_name']) ?></td>
                    <td><?= htmlspecialchars($txn['receiver_name']) ?></td>
                    <td>Rs. <?= htmlspecialchars(number_format($txn['amount'], 2)) ?></td>
                    <td><?= htmlspecialchars($txn['comment'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-link mt-3">Back to Dashboard</a>
</div>
</body>
</html>
