<!DOCTYPE html>
<!-- Dashboard view: shows balance, quick actions, and recent transactions -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <!-- Greet the user. Always escape values from session to avoid XSS. -->
    <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h2>

    <!-- Show current balance. Use number_format for display and htmlspecialchars for safety. -->
    <div class="alert alert-info">
        <strong>Your Balance:</strong> Rs. <?= htmlspecialchars(number_format($balance, 2)) ?>
    </div>

    <!-- Quick navigation links for common actions. These are simple anchors to other public endpoints. -->
    <div class="mb-3">
        <a href="profile.php" class="btn btn-info">Profile</a>
        <a href="search.php" class="btn btn-secondary">Search Users</a>
        <a href="transfer.php" class="btn btn-success">Transfer Money</a>
        <a href="transaction_history.php" class="btn btn-warning">Transaction History</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <h4 class="mt-4">Recent Transactions</h4>
    <?php if (empty($transactions)): ?>
        <!-- Friendly placeholder when no transactions are available. -->
        <p class="text-muted">No transactions yet.</p>
    <?php else: ?>
        <!-- Table of recent transactions. Controller provides $transactions; view must escape values. -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr><th>Date</th><th>Type</th><th>User</th><th>Amount</th><th>Comment</th></tr>
            </thead>
            <tbody>
            <?php foreach ($transactions as $txn): ?>
                <tr>
                    <!-- Transaction date is rendered as provided; ensure DB stores safe formats. -->
                    <td><?= htmlspecialchars($txn['created_at']) ?></td>
                    <!-- Determine whether the transaction was sent or received by comparing IDs.
                         Casting to int defends against type-juggling surprises. -->
                    <td><?= (int)$txn['sender_id'] === (int)$_SESSION['user_id'] ? '<span class="text-danger">Sent</span>' : '<span class="text-success">Received</span>' ?></td>
                    <td>
                        <!-- Show counterparty username depending on direction of transfer. -->
                        <?php if ((int)$txn['sender_id'] === (int)$_SESSION['user_id']): ?>
                            <?= htmlspecialchars($txn['receiver_name']) ?>
                        <?php else: ?>
                            <?= htmlspecialchars($txn['sender_name']) ?>
                        <?php endif; ?>
                    </td>
                    <!-- Format amount for display and escape it. -->
                    <td>Rs. <?= htmlspecialchars(number_format($txn['amount'], 2)) ?></td>
                    <!-- Optional comment field; using null coalescing and escaping. -->
                    <td><?= htmlspecialchars($txn['comment'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
