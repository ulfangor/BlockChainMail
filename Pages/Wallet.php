<?php
// Démarrage de la session pour stocker les données de transaction
session_start();

include "../Header.php"; 

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
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .header, .footer { padding: 20px; text-align: center; }
        .footer { display: flex; }
        .transactions, .mempool { width: 50%; padding: 10px; box-sizing: border-box; }
        .transactions { background-color: #f4f4f4; }
        .mempool { background-color: #e2e2e2; }
        input, button { margin-top: 10px; width: 100%; padding: 10px; box-sizing: border-box; }
        h1, h2 { margin: 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Mon Wallet CMC</h1>
        <p>Solde: <?php echo $_SESSION['wallet']['balance']; ?> CMC</p>
        <p>Adresse: <?php echo $_SESSION['wallet']['address']; ?></p>
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
                    <p>
                        <strong>De:</strong> <?php echo substr($tx['from'], 0, 10); ?>...<br>
                        <strong>À:</strong> <?php echo substr($tx['to'], 0, 10); ?>...<br>
                        <strong>Montant:</strong> <?php echo $tx['amount']; ?> CMC<br>
                        <strong>Frais:</strong> <?php echo $tx['fee']; ?> CMC<br>
                        <strong>Message:</strong> <?php echo $tx['message']; ?><br>
                        <strong>Hash:</strong> <?php echo substr($tx['hash'], 0, 10); ?>...
                    </p>
                    <hr>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune transaction en attente.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>