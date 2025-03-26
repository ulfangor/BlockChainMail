<?php
$accountsFile = '../Data/accounts.json';
$accounts = json_decode(file_get_contents($accountsFile), true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BlockChainMail</title>
    <link rel="stylesheet" href="../Styles/minigame.css">
</head>
<body>
    <div class="all-container">
        <h1>Roulette du Casino</h1>
        
        <div class="player-selection">
            <label for="player">Sélectionnez un joueur :</label>
            <select id="player" name="player">
                <?php foreach ($accounts as $account): ?>
                    <option value="<?= htmlspecialchars($account['name']) ?>" 
                            data-balance="<?= $account['balance'] ?>">
                        <?= htmlspecialchars($account['name']) ?> 
                        (Solde: <?= $account['balance'] ?> CMC)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="roulette-board">
            <h2>Table de Mise</h2>
            <div class="bets">
                <!-- Cases de la roulette -->
                <div class="bet-section">
                    <h3>Numéros</h3>
                    <?php for ($i = 0; $i <= 36; $i++): ?>
                        <button class="bet-number" data-number="<?= $i ?>">
                            <?= $i ?>
                        </button>
                    <?php endfor; ?>
                </div>

                <div class="bet-section">
                    <h3>Types de Mise</h3>
                    <button class="bet-type" data-type="rouge">Rouge</button>
                    <button class="bet-type" data-type="noir">Noir</button>
                    <button class="bet-type" data-type="pair">Pair</button>
                    <button class="bet-type" data-type="impair">Impair</button>
                    <button class="bet-type" data-type="manque">1-18</button>
                    <button class="bet-type" data-type="passe">19-36</button>
                </div>
            </div>
        </div>

        <div class="betting-area">
            <label for="bet-amount">Montant de la mise :</label>
            <input type="number" id="bet-amount" min="1" step="1">
            <button id="place-bet">Placer la mise</button>
        </div>

        <div class="game-result">
            <h2>Résultat</h2>
            <div id="result-display"></div>
        </div>
    </div>

    <script src="../Scripts/roulette.js"></script>
</body>
</html>