<?php
// Démarrage de la session pour stocker les données de transaction
session_start();

include '../Pages/Header.php'; 

// Réinitialisation du wallet et du mempool si le bouton reset est cliqué
if (isset($_GET['reset'])) {
    $randomAddress = bin2hex(random_bytes(16)); // Génère une nouvelle adresse aléatoire
    $_SESSION['wallet'] = [
        'balance' => 100, // Solde initial de 100 CMC
        'address' => hash('sha256', $randomAddress) // Adresse hashée en SHA256
    ];
    $_SESSION['mempool'] = []; // Vide le mempool
    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?')); // Redirige pour éviter la resoumission du formulaire
    exit();
}

// Génération d'une adresse de wallet aléatoire et hashée en SHA256
if (!isset($_SESSION['wallet'])) {
    $randomAddress = bin2hex(random_bytes(16)); // Génère une chaîne aléatoire
    $_SESSION['wallet'] = [
        'balance' => 100, // Solde initial de 100 CMC
        'address' => hash('sha256', $randomAddress) // Adresse hashée en SHA256
    ];
}

// Initialisation du mempool (stockage des transactions)
if (!isset($_SESSION['mempool'])) {
    $_SESSION['mempool'] = [];
}

// Fonction pour créer une transaction
function createTransaction($from, $to, $amount, $message) {
    $fee = 0.01 * $amount; // Frais de transaction de 1%
    $total = $amount + $fee;
    if ($_SESSION['wallet']['balance'] >= $total) {
        $transaction = [
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
            'fee' => $fee,
            'message' => $message,
            'hash' => hash('sha256', $from . $to . $amount . $message . time()) // Hash de la transaction
        ];
        $_SESSION['mempool'][] = $transaction;
        $_SESSION['wallet']['balance'] -= $total;
        return true;
    }
    return false;
}

// Traitement du formulaire de transaction
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $to = $_POST['to'];
    $amount = $_POST['amount'];
    $message = $_POST['message'];
    if (createTransaction($_SESSION['wallet']['address'], $to, $amount, $message)) {
        echo "<script>alert('Transaction réussie !');</script>";
    } else {
        echo "<script>alert('Échec de la transaction : Solde insuffisant.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Wallet CMC</title>
    <link rel="stylesheet" href="../Styles/wallet.css">
</head>
<body>
    <div class="header">
        <h1>Mon Wallet CMC</h1>
        <p>Solde: <?php echo $_SESSION['wallet']['balance']; ?> CMC</p>
        <p>Adresse: <?php echo $_SESSION['wallet']['address']; ?></p>
        <a href="?reset=true" class="reset-button">Reset</a>
    </div>
    <div class="footer">
        <div class="transactions">
            <h2>Effectuer une transaction</h2>
            <form method="post">
                <input type="text" name="to" placeholder="Hash du destinataire" required><br>
                <input type="number" name="amount" placeholder="Montant" required><br>
                <input type="text" name="message" placeholder="Message"><br>
                <button type="submit">Valider</button>
            </form>
        </div>
        <div class="mempool">
            <h2>Mempool</h2>
            <?php if (!empty($_SESSION['mempool'])): ?>
                <?php foreach ($_SESSION['mempool'] as $tx): ?>
                    <div class="transaction-item">
                        <p><strong>De:</strong> <?php echo substr($tx['from'], 0, 10); ?>...</p>
                        <p><strong>À:</strong> <?php echo substr($tx['to'], 0, 10); ?>...</p>
                        <p><strong>Montant:</strong> <?php echo $tx['amount']; ?> CMC</p>
                        <p><strong>Frais:</strong> <?php echo $tx['fee']; ?> CMC</p>
                        <p><strong>Message:</strong> <?php echo $tx['message']; ?></p>
                        <p><strong>Hash:</strong> <?php echo substr($tx['hash'], 0, 10); ?>...</p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune transaction en attente.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>