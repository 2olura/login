<?php
require 'config.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $from_acc = $_POST['from_acc'];
    $to_acc = $_POST['to_acc'];
    $amount = $_POST['amount'];
    
    if(empty($from_acc) || empty($to_acc) || empty($amount)) {
        $error = "All fields are required";
    } elseif($from_acc == $to_acc) {
        $error = "Cannot transfer to same account";
    } elseif($amount <= 0) {
        $error = "Amount must be greater than 0";
    } else {
        try {
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("SELECT balance FROM accounts WHERE id = ?");
            $stmt->execute([$from_acc]);
            $sender = $stmt->fetch();
            
            if(!$sender) {
                throw new Exception("Sender account not found");
            }
            
            if($sender['balance'] < $amount) {
                throw new Exception("Insufficient balance");
            }
            
            $stmt = $conn->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$amount, $from_acc]);
            
            $stmt = $conn->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
            $stmt->execute([$amount, $to_acc]);
            
            $stmt = $conn->prepare("INSERT INTO transactions (from_acc, to_acc, amount) VALUES (?, ?, ?)");
            $stmt->execute([$from_acc, $to_acc, $amount]);
            
            $conn->commit();
            
            $success = "Transfer completed successfully!";
            
        } catch(Exception $e) {
            $conn->rollBack();
            $error = "Transfer failed: " . $e->getMessage();
        }
    }
}

$accounts = $conn->query("SELECT * FROM accounts")->fetchAll();

$transactions = $conn->query("
    SELECT t.*, 
           a1.name as from_name, 
           a2.name as to_name 
    FROM transactions t
    LEFT JOIN accounts a1 ON t.from_acc = a1.id
    LEFT JOIN accounts a2 ON t.to_acc = a2.id
    ORDER BY t.created_at DESC 
    LIMIT 10
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Money Transfer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header-nav">
            <h1> Money Transfer System</h1>
            <a href="index.php" class="back-link">← Back to Home</a>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-error"> <?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success"> <?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Make a Transfer</h2>
            <form method="POST">
                <div class="form-group">
                    <label>From Account:</label>
                    <select name="from_acc" required>
                        <option value="">Select Sender Account</option>
                        <?php foreach($accounts as $acc): ?>
                        <option value="<?php echo $acc['id']; ?>">
                            <?php echo $acc['name']; ?> ($<?php echo number_format($acc['balance'], 2); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>To Account:</label>
                    <select name="to_acc" required>
                        <option value="">Select Receiver Account</option>
                        <?php foreach($accounts as $acc): ?>
                        <option value="<?php echo $acc['id']; ?>">
                            <?php echo $acc['name']; ?> ($<?php echo number_format($acc['balance'], 2); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Amount ($):</label>
                    <input type="number" name="amount" step="0.01" min="0.01" placeholder="Enter amount" required>
                </div>
                
                <button type="submit" class="btn">Transfer Money</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Accounts</h2>
            <div class="accounts-grid">
                <?php foreach($accounts as $acc): ?>
                <div class="account-card">
                    <div class="account-name"><?php echo $acc['name']; ?></div>
                    <div class="account-balance">$<?php echo number_format($acc['balance'], 2); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="card">
            <h2>Transaction History</h2>
            <div class="transactions-list">
                <?php if(empty($transactions)): ?>
                    <div class="empty-state">
                        <p>No transactions yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach($transactions as $t): ?>
                    <div class="transaction-item">
                        <div>
                            <div class="transaction-accounts">
                                <?php echo $t['from_name']; ?> → <?php echo $t['to_name']; ?>
                            </div>
                            <div class="transaction-date">
                                <?php echo date('Y-m-d H:i:s', strtotime($t['created_at'])); ?>
                            </div>
                        </div>
                        <div class="transaction-amount">
                            $<?php echo number_format($t['amount'], 2); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="footer">
            <p>Banking System &copy; <?php echo date('Y'); ?></p>
        </div>
    </div>
</body>
</html>
