<?php

include "../Pages/Header.php"
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlockChainMail</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/elliptic/6.5.4/elliptic.min.js"></script>
    <link rel="stylesheet" href="../Styles/accounts.css">
</head>
<body>
    <div class="accounts-body">
        <h4>ECDSA ACCOUNTS</h4>
    
        <div class="container1">
            <div class="alert" id="error-message"></div>
        
            <div class="form-group">
                <label for="account-name"><u>Create an account</u></label>
                <input type="text" id="account-name" placeholder="Enter account name">
            </div>
        
            <button class="create-btn">Create</button>
        </div>
    
        <div class="container2">
            <label><u>Existing accounts</u></label>
            <div id="accounts-container">
                <table id="accounts-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Public key</th>
                            <th>Private key</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="accounts-list"></tbody>
                </table>
                <div id="empty-message" class="empty-message">No account has been created</div>
            </div>
        </div>
    </div>
    <script src="../Scripts/accounts.js"></script>
</body>
</html>