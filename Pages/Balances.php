<?php
include "../Pages/Header.php";

// Function to get all accounts from JSON file
function getAccounts() {
    $filePath = "../Data/accounts.json";
    
    // Check if file exists
    if (!file_exists($filePath)) {
        return [];
    }
    
    // Read and decode JSON
    $jsonContent = file_get_contents($filePath);
    return json_decode($jsonContent, true) ?: [];
}

// Get all accounts
$accounts = getAccounts();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Viewer - BlockChainMail</title>
    <link rel="stylesheet" href="../Styles/accounts.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .balance-positive {
            color: green;
            font-weight: bold;
        }
        
        .balance-zero {
            color: #888;
        }
        
        .key-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .key-cell:hover {
            overflow: visible;
            white-space: normal;
            word-break: break-all;
        }
        
        .empty-message {
            text-align: center;
            padding: 20px;
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>
    <h1>Addresses & Balances</h1>
    
    <div class="container">
        <?php if (empty($accounts)): ?>
            <div class="empty-message">No accounts found in the system.</div>
        <?php else: ?>
            <table class="accounts-table" width="100%">
                <thead>
                    <tr>
                        <th>Address</th>
                        <th>Balance (in ChainMailCoins)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $account): ?>
                        <tr>
                            <td class="key-cell"><?php echo htmlspecialchars($account['publicKey']); ?></td>
                            <td text-align='right' class="<?php echo $account['balance']> 0 ? 'balance-positive' : 'balance-zero'; ?>">
                                <?php echo isset($account['balance']) ? htmlspecialchars($account['balance']) : '0'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
</body>
</html>