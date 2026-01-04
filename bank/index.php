<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banking System - Home</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="hero">
            <h1>Banking System</h1>
            <p>Secure and reliable money transfer platform with complete transaction history</p>
            <a href="transfer.php" class="btn-primary">Start Transferring Money →</a>
        </div>
        
        <div class="features">
            <div class="feature-card">
                <h3>Secure Transactions</h3>
                <p>All transfers are protected with bank-level security and encryption.</p>
            </div>
            
            <div class="feature-card">
                <h3>Instant Processing</h3>
                <p>Transfers are processed immediately between accounts.</p>
            </div>
            
            <div class="feature-card">
                <h3> Full History</h3>
                <p>Complete record of all transactions with timestamps.</p>
            </div>
        </div>
        
        <div class="card">
            <h2>How to Use the System</h2>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; text-align: center;">
                <div>
                    <div style="font-size: 30px; margin-bottom: 10px;">1️⃣</div>
                    <h4>Select Accounts</h4>
                    <p>Choose sender and receiver</p>
                </div>
                <div>
                    <div style="font-size: 30px; margin-bottom: 10px;">2️⃣</div>
                    <h4>Enter Amount</h4>
                    <p>Specify transfer amount</p>
                </div>
                <div>
                    <div style="font-size: 30px; margin-bottom: 10px;">3️⃣</div>
                    <h4>Confirm</h4>
                    <p>Review and confirm</p>
                </div>
                <div>
                    <div style="font-size: 30px; margin-bottom: 10px;">4️⃣</div>
                    <h4>Complete</h4>
                    <p>Get instant receipt</p>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>Banking System &copy; <?php echo date('Y'); ?></p>
        </div>
    </div>
</body>
</html>
