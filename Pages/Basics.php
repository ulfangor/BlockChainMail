<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Styles/basics.css">
    <title>SHA-256</title>
</head>
<body>
    <?php
        include '../Pages/Header.php';
    ?>
    <h1>SHA-256</h1>

    <form method="POST" action="">
        <div class="input-container">
            <textarea name="rawText" placeholder="Entrez votre texte ici..."></textarea>
            <button type="submit" name="hashButton">HASH</button>
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
</body>
</html>