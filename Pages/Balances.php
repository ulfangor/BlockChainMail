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
    <title>BlockChainMail</title>
    <link rel="stylesheet" href="../Styles/accounts.css">
    <link rel="stylesheet" href="../Styles/balances.css">
</head>
<body>
    <h1>Addresses & Balances</h1>
    
    <div class="balances-container">
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