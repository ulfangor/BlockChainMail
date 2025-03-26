<?php
$blocks = json_decode(file_get_contents('../Data/blocks.json'), true);
$search_results = [];
$search_type = '';

// Traitement des recherches
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search_term = trim($_GET['search_term'] ?? '');
    $search_type = $_GET['search_type'] ?? '';
    
    if (!empty($search_term)) {
        switch ($search_type) {
            case 'index':
                if (is_numeric($search_term)) {
                    $search_results = array_filter($blocks, fn($b) => $b['index'] == $search_term);
                }
                break;
                
            case 'hash':
                $search_results = array_filter($blocks, fn($b) => stripos($b['block_hash'], $search_term) !== false);
                break;
                
            case 'data':
                $search_results = array_filter($blocks, function($b) use ($search_term) {
                    $search_json = json_encode($b);
                    return stripos($search_json, $search_term) !== false;
                });
                break;
        }
    }
}

// Calcul des totaux de transactions
function calculateTransactionTotals($transactions) {
    $total_amount = 0;
    $total_fees = 0;
    
    foreach ($transactions as $tx) {
        // Ajustez ces calculs selon la structure réelle de vos transactions
        $total_amount += $tx['amount'] ?? 0;
        $total_fees += $tx['fee'] ?? 0;
    }
    
    return [
        'total_amount' => $total_amount,
        'total_fees' => $total_fees
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>BlockChainMail</title>
    <link rel="stylesheet" href="../Styles/explorer.css">
</head>
<body>
    <?php
        include '../Pages/Header.php';
    ?>
    <div class="blockchain-explorer">
        <div class="search-panel">
            <form class="search-form" method="GET">
                <div class="search-type">
                    <label>
                        <input type="radio" name="search_type" value="index" <?= $search_type === 'index' ? 'checked' : '' ?>> Index
                    </label>
                    <label>
                        <input type="radio" name="search_type" value="hash" <?= $search_type === 'hash' ? 'checked' : '' ?>> Hash
                    </label>
                    <label>
                        <input type="radio" name="search_type" value="data" <?= $search_type === 'data' ? 'checked' : '' ?>> Data
                    </label>
                </div>
                <input type="text" 
                       class="search-input" 
                       name="search_term" 
                       placeholder="Search..." 
                       value="<?= htmlspecialchars($_GET['search_term'] ?? '') ?>">
                <button type="submit" class="search-input">Search</button>
                <button type="button" class="show-all-btn" onclick="window.location.href='?show_all=true'">Display all blocks</button>
            </form>
        </div>

        <div class="block-grid">
            <?php 
            // Gestion de l'affichage des blocs
            $display_blocks = (!empty($_GET['show_all']) || !empty($search_results)) ? 
                ($search_results ?: $blocks) : 
                [];
            
            if (empty($display_blocks)): ?>
                <div class="no-results">No block to display</div>
            <?php else: ?>
                <?php foreach ($display_blocks as $block): 
                    $tx_totals = calculateTransactionTotals($block['transactions']);
                ?>
                    <div class="block-card <?= $block['index'] === 0 ? 'block-genesis' : '' ?>">
                        <div class="block-header">
                            <h2 class="block-title">
                                BLOCK#<?= $block['index'] ?>
                                <?= $block['index'] === 0 ? '<span>(Genesis)</span>' : '' ?>
                            </h2>
                        </div>
                        
                        <div class="block-main-details">
                            <div>
                                <div class="data-label">Hash</div>
                                <div class="full-text"><?= $block['block_hash'] ?></div>
                            </div>
                            <div>
                                <div class="data-label">Timestamp</div>
                                <div><?= date('d/m/Y H:i', $block['timestamp']) ?></div>
                            </div>
                            <div>
                                <div class="data-label">Previous Hash</div>
                                <div class="full-text"><?= $block['previous_hash'] ?></div>
                            </div>
                            <div>
                                <div class="data-label">Nonce</div>
                                <div><?= $block['nonce'] ?></div>
                            </div>
                            <div>
                                <div class="data-label">Transactions</div>
                                <div><?= count($block['transactions']) ?></div>
                            </div>
                        </div>

                        <div class="block-extended-details">
                            <div class="block-main-details">
                                <div>
                                    <div class="data-label">Merkle Root</div>
                                    <div class="full-text"><?= $block['merkle_root'] ?></div>
                                </div>
                                <div>
                                    <div class="data-label">Miner</div>
                                    <div class="full-text"><?= $block['miner'] ?></div>
                                </div>
                                <div>
                                    <div class="data-label">Value</div>
                                    <div><?= $tx_totals['total_amount'] ?></div>
                                </div>
                                <div>
                                    <div class="data-label">Fee</div>
                                    <div><?= $tx_totals['total_fees'] ?></div>
                                </div>
                            </div>

                            <?php if (!empty($block['transactions'])): ?>
                                <div class="transactions-list">
                                    <div class="data-label">Transactions List</div>
                                    <pre><?= json_encode($block['transactions'], JSON_PRETTY_PRINT) ?></pre>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>