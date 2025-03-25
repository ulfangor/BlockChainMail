<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlockChainMail</title>
    <link rel="stylesheet" href="../Styles/mempool.css">
</head>
<body>
    <?php
    
    session_start();
    ob_start();
    include '../Pages/Header.php';
    
    // Fonction pour charger les comptes depuis le fichier JSON
    function loadAccounts() {
        $accountsFile = '../Data/accounts.json';
        if (file_exists($accountsFile)) {
            $accountsJson = file_get_contents($accountsFile);
            return json_decode($accountsJson, true);
        }
        return [];
    }
    
    // Fonction pour sauvegarder les comptes dans le fichier JSON
    function saveAccounts($accounts) {
        $accountsFile = '../Data/accounts.json';
        file_put_contents($accountsFile, json_encode($accounts, JSON_PRETTY_PRINT));
    }
    
    // Fonction pour mettre à jour le solde d'un compte
    function updateAccountBalance($address, $amount) {
        $accounts = loadAccounts();
        foreach ($accounts as &$account) {
            if ($account['address'] === $address) {
                $account['balance'] -= $amount;
                break;
            }
        }
        saveAccounts($accounts);
    }
    
    // Fonction pour charger les transactions du mempool
    function loadMempool() {
        $mempoolFile = '../Data/mempool.json';
        if (file_exists($mempoolFile)) {
            $mempoolJson = file_get_contents($mempoolFile);
            return json_decode($mempoolJson, true) ?: [];
        }
        return [];
    }
    
    // Fonction pour sauvegarder le mempool
    function saveMempool($mempool) {
        $mempoolFile = '../Data/mempool.json';
        file_put_contents($mempoolFile, json_encode($mempool, JSON_PRETTY_PRINT));
    }
    
    // Fonction pour charger le bloc candidat
    function loadCandidateBlock() {
        $candidateFile = '../Data/candidate.json';
        if (file_exists($candidateFile)) {
            $candidateJson = file_get_contents($candidateFile);
            $candidateBlock = json_decode($candidateJson, true);
            
            // Vérifier si le bloc candidat a la structure attendue
            if (!is_array($candidateBlock) || empty($candidateBlock) || !isset($candidateBlock[0]['transactions'])) {
                // Structure incorrecte, créer un bloc par défaut
                $candidateBlock = [
                    [
                        "index" => "0",
                        "nonce" => "0",
                        "previous_hash" => "none",
                        "merkle_root" => "none",
                        "transactions" => [],
                        "block_hash" => "none"
                    ]
                ];
            }
        } else {
            // Si le fichier n'existe pas, créer un bloc candidat par défaut
            $candidateBlock = [
                [
                    "index" => "0",
                    "nonce" => "0",
                    "previous_hash" => "none",
                    "merkle_root" => "none",
                    "transactions" => [],
                    "block_hash" => "none"
                ]
            ];
        }
        
        saveCandidateBlock($candidateBlock);
        return $candidateBlock;
    }
    
    // Fonction pour sauvegarder le bloc candidat
    function saveCandidateBlock($candidateBlock) {
        $candidateFile = '../Data/candidate.json';
        file_put_contents($candidateFile, json_encode($candidateBlock, JSON_PRETTY_PRINT));
    }
    
    // Fonction pour sauvegarder une transaction dans le mempool
    function saveTransaction($transaction) {
        $mempool = loadMempool();
        
        // timestamp
        $transaction['timestamp'] = time();

        // hash de la transaction
        $transactionData = $transaction['sender'] . $transaction['receiver'] . $transaction['amount'] . $transaction['fee'] . $transaction['message'] . $transaction['timestamp'];
        $transaction['hash'] = hash('sha256', $transactionData);
        
        // Ajouter la transaction au mempool
        $mempool[] = $transaction;
        
        // Sauvegarder le mempool mis à jour
        saveMempool($mempool);
        
        // Mettre à jour le solde de l'expéditeur
        updateAccountBalance($transaction['sender'], ($transaction['amount'] + $transaction['fee']));
        
        return $transaction['hash'];
    }
    
    // Variables pour les messages d'erreur et de succès
    $error = '';
    $success = '';
    
    // Traitement du formulaire de transaction
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        $sender = $_POST['sender'];
        $receiver = $_POST['receiver'];
        $amount = floatval($_POST['amount']);
        $fee = floatval($_POST['fee']);
        $message = $_POST['message'];
        
        // Vérifier que l'expéditeur et le destinataire sont différents
        if ($sender === $receiver) {
            $_SESSION['error'] = "The sender and the receiver cannot be the same.";
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            // Charger les comptes
            $accounts = loadAccounts();
            
            // Trouver le compte de l'expéditeur
            $senderAccount = null;
            foreach ($accounts as $account) {
                if ($account['address'] === $sender) {
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

                // Store success message in session
                $success = "Transaction completed ! Hash: " . $transactionHash;
                $_SESSION['success'] = "Transaction completed ! Hash: " . $transactionHash;
                
                // Redirect to prevent resubmission
                header('Location: ' . $_SERVER['PHP_SELF']);
                ob_end_clean();
                exit();
            } else {
                $error = "Balance too low to complete this transaction";
                $_SESSION['error'] = "Balance too low to complete this transaction";
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    }

    // Retrieve and clear session messages
    $error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
    $success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
    unset($_SESSION['error']);
    unset($_SESSION['success']);
    
    // Traitement des actions sur les transactions du mempool
    if (isset($_POST['delete_from_mempool'])) {
        $transactionHash = $_POST['transaction_hash'];
        $mempool = loadMempool();
        
        // Trouver et supprimer la transaction
        foreach ($mempool as $key => $transaction) {
            if ($transaction['hash'] === $transactionHash) {
                // Rembourser l'expéditeur (annuler le débit)
                updateAccountBalance($transaction['sender'], -($transaction['amount'] + $transaction['fee']));
                unset($mempool[$key]);
                break;
            }
        }
        
        // Réindexer le tableau et sauvegarder
        $mempool = array_values($mempool);
        saveMempool($mempool);
        $success = "Transaction deleted from mempool.";
    }
    
    // Traitement de l'ajout d'une transaction au bloc candidat
    if (isset($_POST['add_to_candidate'])) {
        $transactionHash = $_POST['transaction_hash'];
        $mempool = loadMempool();
        $candidateBlock = loadCandidateBlock();
        
        // Trouver la transaction dans le mempool
        foreach ($mempool as $key => $transaction) {
            if ($transaction['hash'] === $transactionHash) {
                // Ajouter la transaction au bloc candidat
                $candidateBlock[0]['transactions'][] = $transaction;
                
                // Supprimer la transaction du mempool
                unset($mempool[$key]);
                break;
            }
        }
        
        // Réindexer le mempool et sauvegarder
        $mempool = array_values($mempool);
        saveMempool($mempool);
        
        // Sauvegarder le bloc candidat
        saveCandidateBlock($candidateBlock);
        $success = "Transaction added to candidate block";
    }
    
    // Traitement du retrait d'une transaction du bloc candidat
    if (isset($_POST['remove_from_candidate'])) {
        $transactionHash = $_POST['transaction_hash'];
        $candidateBlock = loadCandidateBlock();
        $mempool = loadMempool();
        
        // Trouver la transaction dans le bloc candidat
        foreach ($candidateBlock[0]['transactions'] as $key => $transaction) {
            if ($transaction['hash'] === $transactionHash) {
                // Ajouter la transaction au mempool
                $mempool[] = $transaction;
                
                // Supprimer la transaction du bloc candidat
                unset($candidateBlock[0]['transactions'][$key]);
                break;
            }
        }
        
        // Réindexer les transactions du bloc candidat et sauvegarder
        $candidateBlock[0]['transactions'] = array_values($candidateBlock[0]['transactions']);
        saveCandidateBlock($candidateBlock);
        
        // Sauvegarder le mempool
        saveMempool($mempool);
        $success = "Transaction removed from candidate block.";
    }
    
    // Charger les comptes, le mempool et le bloc candidat pour l'affichage
    $accounts = loadAccounts();
    $mempool = loadMempool();
    $candidateBlock = loadCandidateBlock();
    
    // Calcul des informations du bloc candidat
    $transactionCount = count($candidateBlock[0]['transactions']);
    $totalValue = 0;
    foreach ($candidateBlock[0]['transactions'] as $transaction) {
        $totalValue += $transaction['amount'];
    }

    ob_end_flush();
    ?>
    
    <div class="mempool-main-container">
        <h1>Transactions & Mempool</h1>
        
        <div class="transaction-form">
            <h2>Make a transaction</h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="sender">Sender:</label>
                    <select name="sender" id="sender" required>
                        <option value="">Select a sender</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?php echo $account['address']; ?>">
                                <?php echo $account['name'] . ' (' . $account['balance'] . ' CMC)'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="receiver">Receiver:</label>
                    <select name="receiver" id="receiver" required>
                        <option value="">Select a receiver</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?php echo $account['address']; ?>">
                                <?php echo $account['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="number" name="amount" id="amount" step="0.01" min="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="fee">Transaction fee:</label>
                    <input type="number" name="fee" id="fee" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="message">Message (optional):</label>
                    <textarea name="message" id="message" rows="3"></textarea>
                </div>
                
                <div class="buttons">
                    <button type="submit" name="submit" class="complete-btn">Complete transaction</button>
                    <button type="reset" class="clear-btn">Clear</button>
                </div>
            </form>
        </div>
        
        <div class="transaction-list-container">
            <h2>MEMPOOL</h2>
            
            <?php if (empty($mempool)): ?>
                <p>No transaction currently in mempool.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Receiver</th>
                            <th>Amount</th>
                            <th>Fee</th>
                            <th>Message</th>
                            <th>Timestamp</th>
                            <th>Hash</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mempool as $transaction): ?>
                            <tr>
                                <td>
                                    <?php 
                                    foreach ($accounts as $account) {
                                        if ($account['address'] === $transaction['sender']) {
                                            echo $account['name'] . ' ('.$account['address'] . ')';
                                            break;
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    foreach ($accounts as $account) {
                                        if ($account['address'] === $transaction['receiver']) {
                                            echo $account['name'] . ' ('.$account['address'] . ')';
                                            break;
                                        }
                                    }
                                    ?>
                                </td>
                                <td><?php echo $transaction['amount']; ?></td>
                                <td><?php echo $transaction['fee']; ?></td>
                                <td><?php echo $transaction['message']; ?></td>
                                <td><?php echo date('Y-m-d H:i:s', $transaction['timestamp']); ?></td>
                                <td class="key-cell"><?php echo $transaction['hash']; ?></td>
                                <td>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="transaction_hash" value="<?php echo $transaction['hash']; ?>">
                                        <button type="submit" name="add_to_candidate" class="add-btn">Add to block</button>
                                    </form>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="transaction_hash" value="<?php echo $transaction['hash']; ?>">
                                        <button type="submit" name="delete_from_mempool" class="delete-btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div class="candidate-block-container">
            <h2>Candidate Block</h2>
            
            <?php if (empty($candidateBlock[0]['transactions'])): ?>
                <p>No transaction currently in candidate block.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Receiver</th>
                            <th>Amount</th>
                            <th>Fee</th>
                            <th>Message</th>
                            <th>Timestamp</th>
                            <th>Hash</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($candidateBlock[0]['transactions'] as $transaction): ?>
                            <tr>
                                <td>
                                    <?php 
                                    foreach ($accounts as $account) {
                                        if ($account['address'] === $transaction['sender']) {
                                            echo $account['name'] . ' ('.$account['address'] . ')';
                                            break;
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    foreach ($accounts as $account) {
                                        if ($account['address'] === $transaction['receiver']) {
                                            echo $account['name'] . ' ('.$account['address'] . ')';
                                            break;
                                        }
                                    }
                                    ?>
                                </td>
                                <td><?php echo $transaction['amount']; ?></td>
                                <td><?php echo $transaction['fee']; ?></td>
                                <td><?php echo $transaction['message']; ?></td>
                                <td><?php echo date('Y-m-d H:i:s', $transaction['timestamp']); ?></td>
                                <td class="key-cell"><?php echo $transaction['hash']; ?></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="transaction_hash" value="<?php echo $transaction['hash']; ?>">
                                        <button type="submit" name="remove_from_candidate" class="remove-btn">Remove from block</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="info-box">
                    <div class="info-item"><strong>Index:</strong> <?php echo $candidateBlock[0]['index']; ?></div>
                    <div class="info-item"><strong>Transactions:</strong> <?php echo $transactionCount; ?></div>
                    <div class="info-item"><strong>Value:</strong> <?php echo $totalValue; ?> CMC</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>