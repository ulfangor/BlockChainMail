<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Styles/basics.css">
    <title>BlockChainMail</title>
</head>
<body>
    <?php
        include '../Pages/Header.php';
    ?>
    
    <h1>Basic Tools</h1>

    <div class="basics-body">

        <div class="sha-container">
            <h2>SHA-256</h2>

            <form method="POST" action="">
                <div class="input-container">
                    <textarea class="input-sha" name="rawText" placeholder="Enter your text..."></textarea>
                    <button class="hash-btn" type="submit" name="hashButton">HASH</button>
                    <div class="output">
                        <?php
                        if (isset($_POST['hashButton'])) {
                            // Récupérer le texte saisi
                            $rawText = $_POST['rawText'];

                            // Hasher le texte en SHA-256
                            $hashedText = hash('sha256', $rawText);

                            // Afficher le résultat
                            echo htmlspecialchars($hashedText);
                        }
                        ?>
                    </div>
                </div>
            </form>
        </div>
        <div class="ecdsa-container">
            <h2>ECDSA</h2>

            <button class="generate-btn" type="submit">Generate Keys Pair</button>

            <div class="keys-head-display">
                <h3>Public Key</h3>
                <h3>Private Key</h3>
            </div>

            <div class="keys-display">
                <textarea class="public-key" name="rawText"></textarea>
                <textarea class="private-key" name="rawText"></textarea>
            </div>

            <div class="signature-display">
                <h3>Signature</h3>
                <div class="signature-output">
                    <?php
                        if (isset($_POST['hashButton'])) {
                            // Récupérer le texte saisi
                            $rawText = $_POST['rawText'];

                            // Hasher le texte en SHA-256
                            $hashedText = hash('sha256', $rawText);

                            // Afficher le résultat
                            echo htmlspecialchars($hashedText);
                        }
                    ?>
                </div>
            </div>
            
            <div class="validation-message">Validation</div>

        </div>
    </div>
</body>
</html>