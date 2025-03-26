<?php
// Fonction pour lire les fichiers JSON
function readJsonFile($path) {
    if (!file_exists($path)) {
        die("File not found: $path");
    }
    return json_decode(file_get_contents($path), true);
}

// Fonction de mise à jour des soldes des comptes
function updateAccountBalances($accounts, $transactions, $minerAddress, $coinbase) {
    // Créer une copie modifiable des comptes
    $updatedAccounts = $accounts;
    
    // Trouver l'index du mineur
    $minerIndex = array_search($minerAddress, array_column($updatedAccounts, 'address'));
    
    if ($minerIndex !== false) {
        // Ajouter la récompense de minage au solde du mineur
        if (!isset($updatedAccounts[$minerIndex]['balance'])) {
            $updatedAccounts[$minerIndex]['balance'] = 0;
        }
        $updatedAccounts[$minerIndex]['balance'] += $coinbase;
    }
    
    // Traiter les transactions (hors coinbase)
    foreach ($transactions as $transaction) {
        // Ignorer la transaction coinbase
        if ($transaction['sender'] === 'coinbase') continue;
        
        // Trouver l'index du destinataire
        $receiverIndex = array_search($transaction['receiver'], array_column($updatedAccounts, 'address'));
        
        if ($receiverIndex !== false) {
            // Ajouter le montant au solde du destinataire
            if (!isset($updatedAccounts[$receiverIndex]['balance'])) {
                $updatedAccounts[$receiverIndex]['balance'] = 0;
            }
            $updatedAccounts[$receiverIndex]['balance'] += $transaction['amount'];
        }
    }
    
    return $updatedAccounts;
}

// Lecture des fichiers JSON
$accounts = readJsonFile('../Data/accounts.json');
$stats = readJsonFile('../Data/stats.json')[0];
$candidateBlock = readJsonFile('../Data/candidate.json')[0];
$blocks = readJsonFile('../Data/blocks.json');

// Gestion de la requête de minage
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);
    
    // Mettre à jour les comptes avec les transactions
    $updatedAccounts = updateAccountBalances(
        $accounts, 
        $postData['transactions'], 
        $postData['miner'], 
        $stats['coinbase']
    );
    
    // Mettre à jour le fichier accounts.json
    file_put_contents('../Data/accounts.json', json_encode($updatedAccounts, JSON_PRETTY_PRINT));
    
    // Mettre à jour les statistiques
    $stats['blocks']++;
    $stats['value'] += array_reduce($postData['transactions'], function($sum, $tx) {
        return $sum + $tx['amount'];
    }, 0);
    
    // Écrire les stats mises à jour
    file_put_contents('../Data/stats.json', json_encode([$stats], JSON_PRETTY_PRINT));
    
    // Créer un nouveau bloc
    $newBlock = [
        'index' => count($blocks),
        'timestamp' => $postData['timestamp'],
        'merkle_root' => $postData['merkle_root'],
        'previous_hash' => $postData['previous_hash'],
        'nonce' => $postData['nonce'],
        'transactions' => $postData['transactions'],
        'block_hash' => $postData['block_hash'],
        'miner' => $postData['miner']
    ];

    // Ajouter le nouveau bloc
    $blocks[] = $newBlock;

    // Écrire le nouveau bloc dans blocks.json
    file_put_contents('../Data/blocks.json', json_encode($blocks, JSON_PRETTY_PRINT));

    // Réinitialiser le bloc candidat
    $candidateBlock['transactions'] = []; 
    $candidateBlock['index'] = $stats['blocks'];
    file_put_contents('../Data/candidate.json', json_encode([$candidateBlock], JSON_PRETTY_PRINT));
    

    echo json_encode(['status' => 'success']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Blockchain Mining Interface</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="container mx-auto grid grid-cols-3 gap-6">
        <!-- Mineur Selection -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Sélection du Mineur</h2>
            <select id="minerSelect" class="w-full p-2 border rounded">
                <?php 
                foreach ($accounts as $account) {
                    echo "<option value='{$account['address']}'>{$account['name']}</option>";
                }
                ?>
            </select>
        </div>

        <!-- Bloc Candidat -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Bloc Candidat</h2>
            <div id="candidateBlockInfo">
                <div class="mb-4">
                    <strong>Index:</strong> <?php echo $candidateBlock['index']; ?><br>
                    <strong>Transactions:</strong> <?php echo count($candidateBlock['transactions']); ?><br>
                    <strong>Total Value:</strong> <?php 
                        $totalValue = array_reduce($candidateBlock['transactions'], function($sum, $tx) {
                            return $sum + $tx['amount'];
                        }, 0);
                        echo $totalValue;
                    ?>
                </div>
                <hr class="my-4">
                <div>
                    <h3 class="font-bold mb-2">Transactions:</h3>
                    <div id="transactionsList">
                        <div class="bg-gray-100 p-2 rounded mb-2">
                            <strong>Coinbase Reward:</strong> <?php echo $stats['coinbase']; ?>
                        </div>
                        <?php 
                        foreach ($candidateBlock['transactions'] as $tx) {
                            echo "<div class='bg-gray-100 p-2 rounded mb-2'>";
                            echo "<strong>Sender:</strong> " . substr($tx['sender'], 0, 10) . "...<br>";
                            echo "<strong>Receiver:</strong> " . substr($tx['receiver'], 0, 10) . "...<br>";
                            echo "<strong>Amount:</strong> " . $tx['amount'] . "<br>";
                            echo "<strong>Fee:</strong> " . $tx['fee'];
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            <button id="mineBlockBtn" class="mt-4 w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600 disabled:opacity-50" 
                    <?php 
                    // Disable button if no transactions (except for genesis block)
                    if ($candidateBlock['index'] !== "0" && count($candidateBlock['transactions']) <= 1) {
                        echo 'disabled';
                    }
                    ?>>
                Mine this block
            </button>
        </div>

        <!-- Mining Progress -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Progression du Minage</h2>
            <div id="miningProgress" class="text-center">
                <div id="progressBar" class="w-full bg-gray-200 h-4 rounded">
                    <div id="progressBarFill" class="bg-green-500 h-4 rounded w-0"></div>
                </div>
                <p id="miningStatus" class="mt-4">En attente de minage...</p>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const minerSelect = document.getElementById('minerSelect');
        const mineBlockBtn = document.getElementById('mineBlockBtn');
        const progressBar = document.getElementById('progressBarFill');
        const miningStatus = document.getElementById('miningStatus');

        mineBlockBtn.addEventListener('click', async () => {
            const startTime = Date.now();
            const minerAddress = minerSelect.value;

            const candidateBlock = <?php echo json_encode($candidateBlock); ?>;
            const previousBlocks = <?php echo json_encode($blocks); ?>;
            const stats = <?php echo json_encode($stats); ?>;

            // Compute Merkle Root (simplified)
            const computeMerkleRoot = (transactions) => {
                let hashes = transactions.map(tx => tx.hash);
                while (hashes.length > 1) {
                    const newLevel = [];
                    for (let i = 0; i < hashes.length; i += 2) {
                        const hash1 = hashes[i];
                        const hash2 = i + 1 < hashes.length ? hashes[i + 1] : hash1;
                        const combinedHash = CryptoJS.SHA256(hash1 + hash2).toString();
                        newLevel.push(combinedHash);
                    }
                    hashes = newLevel;
                }
                return hashes[0] || 'none';
            };

            const merkleRoot = computeMerkleRoot(candidateBlock.transactions);
            const previousHash = previousBlocks.length > 0 ? previousBlocks[previousBlocks.length - 1].block_hash : 'none';

            // Mining simulation
            const mine = () => {
                return new Promise((resolve, reject) => {
                    let nonce = 0;
                    const difficulty = '000'; // 3 leading zeros

                    const updateProgress = (percent) => {
                        progressBar.style.width = `${percent}%`;
                        miningStatus.textContent = `Mining... (Nonce: ${nonce})`;
                    };

                    const miningInterval = setInterval(() => {
                        const blockData = `${candidateBlock.index}${candidateBlock.timestamp}${merkleRoot}${previousHash}${nonce}`;
                        const blockHash = CryptoJS.SHA256(blockData).toString();

                        updateProgress((nonce % 1000) / 10);

                        if (blockHash.startsWith(difficulty)) {
                            clearInterval(miningInterval);
                            resolve({ blockHash, nonce });
                        }
                        nonce++;

                        // Ajout d'une limite pour éviter une boucle infinie
                        if (nonce > 100000) {
                            clearInterval(miningInterval);
                            reject(new Error('Mining took too long'));
                        }
                    }, 10);
                });
            };

            try {
                miningStatus.textContent = 'Mining started...';
                const miningResult = await mine();
                const endTime = Date.now();
                const miningDuration = (endTime - startTime) / 1000;

                // Prepare new block
                const newBlock = {
                    index: candidateBlock.index,
                    timestamp: Math.floor(startTime / 1000),
                    merkle_root: merkleRoot,
                    previous_hash: previousHash,
                    nonce: miningResult.nonce,
                    transactions: candidateBlock.transactions,
                    block_hash: miningResult.blockHash,
                    miner: minerAddress
                };

                // Send block to server
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(newBlock)
                });

                const result = await response.json();

                if (result.status === 'success') {
                    progressBar.style.width = '100%';
                    miningStatus.textContent = `Block mined successfully in ${miningDuration.toFixed(2)} seconds!`;
                    alert(`Block mined successfully in ${miningDuration.toFixed(2)} seconds!`);
                    
                    // Recharger la page pour mettre à jour l'interface
                    window.location.reload();
                } else {
                    miningStatus.textContent = 'Mining failed.';
                    alert('Mining failed.');
                }

            } catch (error) {
                console.error('Mining error:', error);
                miningStatus.textContent = 'Mining failed unexpectedly.';
                alert('Mining failed unexpectedly: ' + error.message);
            }
        });
    });
    </script>
</body>
</html>