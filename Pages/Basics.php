<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/elliptic/6.5.4/elliptic.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
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
                <table>
                    <thead>
                        <tr>
                            <th>Public Key</th>
                            <th>Private Key</th>
                        </tr>
                    </thead>
                </table>

            </div>

            <div class="keys-display">
                <textarea class="public-key" name="rawText"></textarea>
                <textarea class="private-key" name="rawText"></textarea>
            </div>

            <div class="signature-display">
                <h3>Signature</h3>
                <div class="signature-output">
                    
                </div>
            </div>
            
            <div class="validation-message"></div>

        </div>
    </div>
</body>
<script>

document.addEventListener('DOMContentLoaded', () => {
    // Ensure elliptic library is accessible
    const ec = new elliptic.ec('secp256k1');

    const generateBtn = document.querySelector('.generate-btn');
    const publicKeyTextarea = document.querySelector('.public-key');
    const privateKeyTextarea = document.querySelector('.private-key');
    const signatureOutput = document.querySelector('.signature-output');
    const validationMessage = document.querySelector('.validation-message');

    let originalHash = null;
    let keyPair = null;

    generateBtn.addEventListener('click', () => {
        try {
            // Générer une paire de clés ECDSA
            keyPair = ec.genKeyPair();

            // Obtenir les clés au format hexadécimal
            const publicKey = keyPair.getPublic('hex');
            const privateKey = keyPair.getPrivate('hex');

            // Remplir les textarea
            publicKeyTextarea.value = publicKey;
            privateKeyTextarea.value = privateKey;

            // Générer le hash original
            originalHash = generateHash(publicKey, privateKey);
            signatureOutput.textContent = originalHash;

            // Message de validation initial
            validationMessage.textContent = 'Signature generated and valid';
            validationMessage.style.color = 'green';
        } catch (error) {
            console.error('Key generation error:', error);
            validationMessage.textContent = 'Erreur de génération de clés';
            validationMessage.style.color = 'red';
        }
    });

    // Écouter les changements dans les textarea
    publicKeyTextarea.addEventListener('input', validateSignature);
    privateKeyTextarea.addEventListener('input', validateSignature);

    function validateSignature() {
        if (originalHash === null) return;

        const currentPublicKey = publicKeyTextarea.value;
        const currentPrivateKey = privateKeyTextarea.value;

        const currentHash = generateHash(currentPublicKey, currentPrivateKey);

        if (currentHash !== originalHash) {
            validationMessage.textContent = 'Signature not valid - A modification had been made !';
            validationMessage.style.color = 'red';
        } else {
            validationMessage.textContent = 'Signature valid';
            validationMessage.style.color = 'green';
        }
    }

    function generateHash(publicKey, privateKey) {
        return CryptoJS.SHA256(publicKey + privateKey).toString();
    }
});
</script>
</html>