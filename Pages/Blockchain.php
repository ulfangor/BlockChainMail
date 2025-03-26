<?php
$chain_data = json_decode(file_get_contents('../Data/blocks.json'), true);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>BlockChainMail</title>
    <link rel="stylesheet" href="../Styles/blockchain.css">
</head>
<body>
    <?php
        include '../Pages/Header.php';
    ?>

    <h1> Visualize the Blockchain </h1>

    <div class="block-chain">
        <?php foreach ($chain_data as $bloc): ?>
        <div class="block-node">
            <div class="block-header">
                <h2 class="block-title">
                    BLOC#<?= $bloc['index'] ?>
                    <?= $bloc['index'] === 0 ? '<span>(Genesis)</span>' : '' ?>
                </h2>
                <div class="block-meta">
                    <div class="data-label">Hash:</div>
                    <div><?= substr($bloc['block_hash'], 0, 16) ?>...</div>
                    
                    <div class="data-label">Timestamp:</div>
                    <div><?= date('d/m/Y H:i', $bloc['timestamp']) ?></div>
                    
                    <div class="data-label">Previous Hash:</div>
                    <div><?= substr($bloc['previous_hash'], 0, 16) ?>...</div>
                    
                    <div class="data-label">Nonce:</div>
                    <div><?= $bloc['nonce'] ?></div>
                </div>
            </div>
            
            <div class="block-hover-info">
                <div class="data-label">Merkle Root:</div>
                <div><?= $bloc['merkle_root'] ?></div>
                
                <div class="data-label">Miner:</div>
                <div><?= substr($bloc['miner'], 0, 16) ?>...</div>
                
                <div class="data-label">Transactions:</div>
                <div><?= count($bloc['transactions']) ?></div>
                
                <div class="data-label">Value:</div> 
                <div><?= array_sum(array_column($bloc['transactions'], 'amount')) ?: 0 ?> CMC</div>

                <div class="data-label">Fees:</div>
                <div><?= array_sum(array_column($bloc['transactions'], 'fee')) ?: 0 ?> CMC</div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <a href="/BlockChainMail/Pages/Explorer.php" class="search-redirection" >Search Specific Blocks</a>
</body>
</html>