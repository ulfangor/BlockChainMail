<?php
$accountsFile = '../Data/accounts.json';
$accounts = json_decode(file_get_contents($accountsFile), true);

// Définir les couleurs des numéros
$rougeNumbers = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];
$noirNumbers = [2, 4, 6, 8, 10, 11, 13, 15, 17, 20, 22, 24, 26, 28, 29, 31, 33, 35];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BlockChainMail</title>
    <?php include "../Pages/header.php"; ?>
    <link rel="stylesheet" href="../Styles/minigame.css">
</head>
<body class="min-h-screen flex flex-col">
    <div class="game-container flex-grow">
        <div class="all-container">
            <h1>Casino Roulette</h1>
            
            <div class="player-selection">
                <label for="player">Select Player:</label>
                <select id="player" name="player">
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?= htmlspecialchars($account['name']) ?>" 
                                data-balance="<?= $account['balance'] ?>">
                            <?= htmlspecialchars($account['name']) ?> 
                            (<?= $account['balance'] ?> CMC)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="roulette-board">
                <h2>Betting Table</h2>
                <div class="bets">
                    <div class="bet-section">
                        <h3>Numbers</h3>
                        <?php for ($i = 0; $i <= 36; $i++): ?>
                            <button class="bet-number 
                                <?= in_array($i, $rougeNumbers) ? 'bg-red-600' : '' ?>
                                <?= in_array($i, $noirNumbers) ? 'bg-black' : '' ?>" 
                                data-number="<?= $i ?>"
                                data-color="<?= 
                                    in_array($i, $rougeNumbers) ? 'rouge' : 
                                    (in_array($i, $noirNumbers) ? 'noir' : 'vert') 
                                ?>">
                                <?= $i ?>
                            </button>
                        <?php endfor; ?>
                    </div>

                    <div class="bet-section">
                        <h3>Bet Types</h3>
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
                <label for="bet-amount">Bet Value:</label>
                <input type="number" id="bet-amount" min="1" step="1">
                <button id="place-bet">Place Bet</button>
            </div>
        </div>

        <div class="game-result-container">
            <div class="game-result">
                <h2>Result</h2>
                <div id="result-display"></div>
            </div>
        </div>
    </div>

    <script src="../Scripts/roulette.js"></script>
</body>
</html>