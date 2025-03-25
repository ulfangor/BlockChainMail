<?php
header('Content-Type: application/json');

function updatePlayerBalance($playerName, $amount) {
    $accountsFile = '../Data/accounts.json';
    $accounts = json_decode(file_get_contents($accountsFile), true);

    foreach ($accounts as &$account) {
        if ($account['name'] === $playerName) {
            $account['balance'] += $amount;
            break;
        }
    }

    file_put_contents($accountsFile, json_encode($accounts, JSON_PRETTY_PRINT));
}

function spinRoulette($bet, $betAmount) {
    $result = rand(0, 36);
    $isWin = false;
    $winnings = 0;

    // Logique de gain selon le type de mise
    switch ($bet['type']) {
        case 'number':
            $isWin = $result == $bet['value'];
            $winnings = $isWin ? $betAmount * 35 : -$betAmount;
            break;
        
        case 'color':
            $redNumbers = [1,3,5,7,9,12,14,16,18,19,21,23,25,27,30,32,34,36];
            $isRed = in_array($result, $redNumbers);
            $isWin = ($bet['value'] == 'rouge' && $isRed) || 
                     ($bet['value'] == 'noir' && !$isRed && $result != 0);
            $winnings = $isWin ? $betAmount : -$betAmount;
            break;
        
        case 'parity':
            $isWin = (($result % 2 == 0 && $bet['value'] == 'pair') || 
                      ($result % 2 != 0 && $bet['value'] == 'impair')) && 
                     $result != 0;
            $winnings = $isWin ? $betAmount : -$betAmount;
            break;
        
        case 'range':
            $isWin = (($bet['value'] == 'manque' && $result >= 1 && $result <= 18) || 
                      ($bet['value'] == 'passe' && $result >= 19 && $result <= 36));
            $winnings = $isWin ? $betAmount : -$betAmount;
            break;
    }

    return [
        'result' => $result,
        'win' => $isWin,
        'winnings' => $winnings
    ];
}

$input = json_decode(file_get_contents('php://input'), true);
$result = spinRoulette($input['bet'], $input['betAmount']);

if ($result['win']) {
    updatePlayerBalance($input['playerName'], $result['winnings']);
} else {
    updatePlayerBalance($input['playerName'], -$input['betAmount']);
}

echo json_encode($result);
?>