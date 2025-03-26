<?php
// Chargement des blocs depuis le fichier JSON
$blocks = json_decode(file_get_contents('../Data/blocks.json'), true);

// Fonctions de recherche
function searchByIndex($blocks, $index) {
    foreach ($blocks as $block) {
        if ($block['index'] === $index) {
            return $block;
        }
    }
    return null;
}

function searchByHash($blocks, $hash) {
    return array_filter($blocks, function($block) use ($hash) {
        return stripos($block['hash'], $hash) !== false;
    });
}

function searchByData($blocks, $keyword) {
    return array_filter($blocks, function($block) use ($keyword) {
        return stripos($block['data'], $keyword) !== false;
    });
}

// Traitement des recherches
$searchResults = null;
$searchType = null;
$searchQuery = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['search_type'])) {
        $searchType = $_GET['search_type'];
        
        switch ($searchType) {
            case 'list_all':
                $searchResults = $blocks;
                break;
            
            case 'index':
                if (isset($_GET['index']) && is_numeric($_GET['index'])) {
                    $searchQuery = intval($_GET['index']);
                    $searchResults = searchByIndex($blocks, $searchQuery) ? [searchByIndex($blocks, $searchQuery)] : [];
                }
                break;
            
            case 'hash':
                if (isset($_GET['hash'])) {
                    $searchQuery = trim($_GET['hash']);
                    $searchResults = searchByHash($blocks, $searchQuery);
                }
                break;
            
            case 'data':
                if (isset($_GET['keyword'])) {
                    $searchQuery = trim($_GET['keyword']);
                    $searchResults = searchByData($blocks, $searchQuery);
                }
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Explorateur de Blocs Blockchain</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }
        .search-section {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        .search-form {
            flex: 1;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            min-width: 250px;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            box-sizing: border-box;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        .results {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Explorateur de Blocs Blockchain</h1>
        
        <div class="search-section">
            <div class="search-form">
                <form method="get">
                    <select name="search_type" onchange="toggleSearchFields(this)">
                        <option value="">Choisir un type de recherche</option>
                        <option value="list_all">Lister Tous les Blocs</option>
                        <option value="index">Recherche par Index</option>
                        <option value="hash">Recherche par Hash</option>
                        <option value="data">Recherche par Données</option>
                    </select>
                    
                    <div id="index_field" style="display:none;">
                        <input type="number" name="index" placeholder="Numéro du bloc">
                    </div>
                    
                    <div id="hash_field" style="display:none;">
                        <input type="text" name="hash" placeholder="Hash (ou partie)">
                    </div>
                    
                    <div id="data_field" style="display:none;">
                        <input type="text" name="keyword" placeholder="Mot-clé dans les données">
                    </div>
                    
                    <button type="submit">Rechercher</button>
                </form>
            </div>
        </div>

        <?php if ($searchResults !== null): ?>
            <div class="results">
                <h2>
                    <?php 
                    switch ($searchType) {
                        case 'list_all': echo "Liste de Tous les Blocs"; break;
                        case 'index': echo "Résultat pour l'Index: " . $searchQuery; break;
                        case 'hash': echo "Résultats pour le Hash: " . $searchQuery; break;
                        case 'data': echo "Résultats pour le Mot-clé: " . $searchQuery; break;
                    }
                    ?>
                </h2>
                
                <?php if (empty($searchResults)): ?>
                    <p>Aucun résultat trouvé.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Index</th>
                                <th>Timestamp</th>
                                <th>Hash</th>
                                <th>Previous Hash</th>
                                <th>Nonce</th>
                                <th>Données</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($searchResults as $block): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($block['index']); ?></td>
                                <td><?php echo date('Y-m-d H:i:s', $block['timestamp']); ?></td>
                                <td><?php echo htmlspecialchars($block['hash']); ?></td>
                                <td><?php echo htmlspecialchars($block['previous_hash']); ?></td>
                                <td><?php echo htmlspecialchars($block['nonce']); ?></td>
                                <td><?php echo htmlspecialchars($block['data']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function toggleSearchFields(select) {
        // Masquer tous les champs supplémentaires
        document.getElementById('index_field').style.display = 'none';
        document.getElementById('hash_field').style.display = 'none';
        document.getElementById('data_field').style.display = 'none';

        // Afficher le champ correspondant au type de recherche
        switch(select.value) {
            case 'index':
                document.getElementById('index_field').style.display = 'block';
                break;
            case 'hash':
                document.getElementById('hash_field').style.display = 'block';
                break;
            case 'data':
                document.getElementById('data_field').style.display = 'block';
                break;
        }
    }
    </script>
</body>
</html>