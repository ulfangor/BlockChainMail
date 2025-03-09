<?php
// This file will handle account operations

// Function to get all accounts
function getAccounts() {
    $filePath = "../Data/accounts.json";
    
    // Check if file exists
    if (!file_exists($filePath)) {
        // Create directory if it doesn't exist
        if (!file_exists("../Data")) {
            mkdir("../Data", 0755, true);
        }
        
        // Create empty accounts file
        file_put_contents($filePath, json_encode([]));
        return [];
    }
    
    // Read and decode JSON
    $jsonContent = file_get_contents($filePath);
    return json_decode($jsonContent, true) ?: [];
}

// Function to save accounts
function saveAccounts($accounts) {
    $filePath = "../Data/accounts.json";
    
    // Create directory if it doesn't exist
    if (!file_exists("../Data")) {
        mkdir("../Data", 0755, true);
    }
    
    // Encode and save JSON
    $jsonContent = json_encode($accounts, JSON_PRETTY_PRINT);
    file_put_contents($filePath, $jsonContent);
    
    return true;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Get existing accounts
    $accounts = getAccounts();
    
    if ($action === 'create') {
        // Get data from post
        $name = $_POST['name'] ?? '';
        $publicKey = $_POST['publicKey'] ?? '';
        $privateKey = $_POST['privateKey'] ?? '';
        
        // Validate
        if (empty($name) || empty($publicKey) || empty($privateKey)) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        // Check if name exists
        foreach ($accounts as $account) {
            if ($account['name'] === $name) {
                echo json_encode(['success' => false, 'message' => 'Account name already exists']);
                exit;
            }
        }
        
        // Add new account with initial balance of 0
        $accounts[] = [
            'name' => $name,
            'publicKey' => $publicKey,
            'privateKey' => $privateKey,
            'balance' => 0
        ];
        
        // Save updated accounts
        saveAccounts($accounts);
        
        echo json_encode(['success' => true, 'accounts' => $accounts]);
        exit;
    }
    elseif ($action === 'delete') {
        $index = isset($_POST['index']) ? intval($_POST['index']) : -1;
        
        if ($index >= 0 && $index < count($accounts)) {
            // Remove account at index
            array_splice($accounts, $index, 1);
            
            // Save updated accounts
            saveAccounts($accounts);
            
            echo json_encode(['success' => true, 'accounts' => $accounts]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid account index']);
        }
        exit;
    }
    elseif ($action === 'getAll') {
        echo json_encode(['success' => true, 'accounts' => $accounts]);
        exit;
    }
    
    // Invalid action
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>