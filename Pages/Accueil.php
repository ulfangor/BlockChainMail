<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlockChainMail</title>
    <link rel="stylesheet" href="../Styles/accueil.css">
</head>
<body>
    <?php
        include '../Pages/Header.php';
    ?>

    <div class="accueil-container">
        <h1>Welcome to BlockChainMail !</h1>

        <div class="navigation-container">
            <div class="navigators">
                <a href="../Pages/Basics.php"><img src="../Images/tools-logo.png" alt="Logo Tools" class="navigator-logo"></a>
                <h2> Tools </h2>
                <h3> Sha-258 and ECDSA </h3>
            </div>
            <div class="navigators">
                <a href="../Documents/BlockchainDocumentation.pdf" target="_blank"><img src="../Images/documentation-logo.png" alt="Logo Documentation" class="navigator-logo"></a>
                <h2> Documentation </h2>
                <h3> Open a pdf file for documentation about Blockchain </h3>
            </div>
            <div class="navigators">
                <a href="../Pages/Accounts.php"><img src="../Images/accounts-logo.png" alt="Logo Accounts" class="navigator-logo"></a>
                <h2> Accounts </h2>
                <h3> Create and manage blockchain accounts </h3>
            </div>
            <div class="navigators">
                <a href="../Pages/Balances.php"><img src="../Images/balances-logo.png" alt="Logo Balances" class="navigator-logo"></a>
                <h2> Balances </h2>
                <h3> Check out accounts balances </h3>
            </div>
        </div>
        <div class="navigation-container">
            <div class="navigators">
                <a href="../Pages/Mempool.php"><img src="../Images/mempool-logo.png" alt="Logo Mempool" class="navigator-logo"></a>
                <h2> Mempool </h2>
                <h3> Create and manage transactions, add them into a candidate block </h3>
            </div>
            <div class="navigators">
                <a href="../Pages/Mining.php"><img src="../Images/mining-logo.png" alt="Logo Mining" class="navigator-logo"></a>
                <h2> Mining </h2>
                <h3> Mine blocks </h3>
            </div>
            <div class="navigators">
                <a href="../Pages/Blockchain.php"><img src="../Images/blockchain-logo.png" alt="Logo Blockchain" class="navigator-logo"></a>
                <h2> Blockchain </h2>
                <h3> Visualize current blockchain </h3>
            </div>
            <div class="navigators">
                <a href="../Pages/MiniGame.php"><img src="../Images/minigame-logo.png" alt="Logo Mini-Game" class="navigator-logo"></a>
                <h2> Mini-game </h2>
                <h3> Play a mini-game to try to win ChainMailCoins </h3>
            </div>
        </div>

        <button class="reset-btn" id="reset-btn"> RESET BLOCKCHAIN </button>
    </div>

    <div id="resetModal" class="modal">
        <div class="modal-content">
            <p>Are you sure you want to reset the blockchain?</p>
            <div class="modal-buttons">
                <button class="yes-btn" id="yesBtn">Yes</button>
                <button class="no-btn" id="noBtn">No</button>
            </div>
        </div>
    </div>

    <script>
        // Récupérer les éléments
        const resetBtn = document.getElementById('reset-btn');
        const modal = document.getElementById('resetModal');
        const yesBtn = document.getElementById('yesBtn');
        const noBtn = document.getElementById('noBtn');
        
        // Afficher le modal quand on clique sur le bouton reset
        resetBtn.addEventListener('click', function() {
            modal.style.display = 'block';
        });
        
        // Cacher le modal quand on clique sur "No"
        noBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        // Réinitialiser la blockchain quand on clique sur "Yes"
        yesBtn.addEventListener('click', function() {
            // Appel AJAX pour vider les fichiers JSON
            fetch('../Scripts/reset_blockchain.php')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('Blockchain reset successfully!');
                        location.reload(); // Rafraîchir la page
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while resetting the blockchain.');
                });
                
            // Cacher le modal
            modal.style.display = 'none';
        });
        
        // Cacher le modal si on clique en dehors
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    </script>

</body>
</html>