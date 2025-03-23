<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlockChainMail</title>
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select, input, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .buttons {
            margin-top: 20px;
        }
        button {
            padding: 10px 15px;
            margin-right: 10px;
            cursor: pointer;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
        .success {
            color: green;
            margin-top: 10px;
        }
        .transaction-list {
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <?php
    //include '../Pages/Header.php';
    
    // Fonction pour charger les comptes depuis le fichier JSON
    function loadAccounts() {
        $accountsFile = '../Data/accounts.json';
        if (file_exists($accountsFile)) {
            $accountsJson = file_get_contents($accountsFile);
            return json_decode($accountsJson, true);
        }
        return [];
    }
    
    // Fonction pour charger les transactions du mempool
    function loadMempool() {
        $mempoolFile = '../Data/mempool.json';
        if (file_exists($mempoolFile)) {
            $mempoolJson = file_get_contents($mempoolFile);
            return json_decode($mempoolJson, true);
        }
        return [];
    }
    
    // Fonction pour sauvegarder une transaction dans le mempool
    function saveTransaction($transaction) {
        $mempoolFile = '../Data/mempool.json';
        $mempool = loadMempool();
        
        // Générer le hash de la transaction
        $transactionData = $transaction['sender'] . $transaction['receiver'] . $transaction['amount'] . $transaction['fee'] . $transaction['message'];
        $transaction['hash'] = hash('sha256', $transactionData);
        
        // Ajouter la transaction au mempool
        $mempool[] = $transaction;
        
        // Sauvegarder le mempool mis à jour
        file_put_contents($mempoolFile, json_encode($mempool, JSON_PRETTY_PRINT));
        
        return $transaction['hash'];
    }
    
    // Variables pour les messages d'erreur et de succès
    $error = '';
    $success = '';
    
    // Traitement du formulaire de transaction
    if (isset($_POST['submit'])) {
        $sender = $_POST['sender'];
        $receiver = $_POST['receiver'];
        $amount = floatval($_POST['amount']);
        $fee = floatval($_POST['fee']);
        $message = $_POST['message'];
        
        // Vérifier que l'expéditeur et le destinataire sont différents
        if ($sender === $receiver) {
            $error = "L'expéditeur et le destinataire ne peuvent pas être identiques.";
        } else {
            // Charger les comptes
            $accounts = loadAccounts();
            
            // Trouver le compte de l'expéditeur
            $senderAccount = null;
            foreach ($accounts as $account) {
                if ($account['publicKey'] === $sender) {
                    $senderAccount = $account;
                    break;
                }
            }
            
            // Vérifier le solde de l'expéditeur
            if ($senderAccount && $senderAccount['balance'] >= ($amount + $fee)) {
                // Créer la transaction
                $transaction = [
                    'sender' => $sender,
                    'receiver' => $receiver,
                    'amount' => $amount,
                    'fee' => $fee,
                    'message' => $message,
                    'hash' => '' // Sera généré par la fonction saveTransaction
                ];
                
                // Sauvegarder la transaction dans le mempool
                $transactionHash = saveTransaction($transaction);
                $success = "Transaction créée avec succès! Hash: " . $transactionHash;
            } else {
                $error = "Solde insuffisant pour effectuer cette transaction.";
            }
        }
    }
    
    // Charger les comptes et le mempool pour l'affichage
    $accounts = loadAccounts();
    $mempool = loadMempool();
    ?>
    
    <div class="container">
        <h1>Mempool - Transactions en attente</h1>
        
        <div class="transaction-form">
            <h2>Effectuer une transaction</h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="sender">Expéditeur:</label>
                    <select name="sender" id="sender" required>
                        <option value="">Sélectionnez un expéditeur</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?php echo $account['publicKey']; ?>">
                                <?php echo $account['name'] . ' (' . $account['balance'] . ' coins)'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="receiver">Destinataire:</label>
                    <select name="receiver" id="receiver" required>
                        <option value="">Sélectionnez un destinataire</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?php echo $account['publicKey']; ?>">
                                <?php echo $account['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="amount">Montant:</label>
                    <input type="number" name="amount" id="amount" step="0.01" min="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="fee">Frais de transaction:</label>
                    <input type="number" name="fee" id="fee" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="message">Message (facultatif):</label>
                    <textarea name="message" id="message" rows="3"></textarea>
                </div>
                
                <div class="buttons">
                    <button type="submit" name="submit">Valider la transaction</button>
                    <button type="reset">Clear</button>
                </div>
            </form>
        </div>
        
        <div class="transaction-list">
            <h2>MEMPOOL - Transactions en attente</h2>
            
            <?php if (empty($mempool)): ?>
                <p>Aucune transaction en attente dans le mempool.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Expéditeur</th>
                            <th>Destinataire</th>
                            <th>Montant</th>
                            <th>Frais</th>
                            <th>Message</th>
                            <th>Hash</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mempool as $transaction): ?>
                            <tr>
                                <td>
                                    <?php 
                                    foreach ($accounts as $account) {
                                        if ($account['publicKey'] === $transaction['sender']) {
                                            echo $account['name'];
                                            break;
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    foreach ($accounts as $account) {
                                        if ($account['publicKey'] === $transaction['receiver']) {
                                            echo $account['name'];
                                            break;
                                        }
                                    }
                                    ?>
                                </td>
                                <td><?php echo $transaction['amount']; ?></td>
                                <td><?php echo $transaction['fee']; ?></td>
                                <td><?php echo $transaction['message']; ?></td>
                                <td><?php echo $transaction['hash']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>