<?php
// Définir l'en-tête pour renvoyer du JSON
header('Content-Type: application/json');

// Liste des fichiers JSON à vider
$jsonFiles = [
    '../Data/accounts.json',
    '../Data/blocks.json',
    '../Data/mempool.json',
    '../Data/candidate.json',
    '../Data/stats.json'
];

$success = true;
$message = '';

try {
    foreach ($jsonFiles as $file) {
        // Vérifier si le fichier existe
        if (file_exists($file)) {
            if (strpos($file, 'stats.json') !== false) {
                $default = [
                    [
                        "blocks" => 0,
                        "coinbase" => 50,
                        "value" => 0
                    ]
                ];
                file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT));
            }
            // Pour les autres fichiers, on les vide simplement
            else {
                file_put_contents($file, '[]');
            }
        } else {
            throw new Exception("File not found: $file");
        }
    }
} catch (Exception $e) {
    $success = false;
    $message = $e->getMessage();
}

// Renvoyer le résultat
echo json_encode([
    'success' => $success,
    'message' => $message
]);
?>