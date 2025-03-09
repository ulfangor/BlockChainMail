document.addEventListener('DOMContentLoaded', function() {
    // Initialize elliptic curve 
    const ec = new elliptic.ec('secp256k1');
    
    // Get DOM elements
    const accountNameInput = document.getElementById('account-name');
    const createBtn = document.querySelector('.create-btn');
    const accountsList = document.getElementById('accounts-list');
    const emptyMessage = document.getElementById('empty-message');
    const errorMessage = document.getElementById('error-message');
    
    // Store accounts locally for UI management
    let accounts = [];
    
    // Load accounts from server on page load
    loadAccounts();
    
    // Event listener for create button
    createBtn.addEventListener('click', function() {
        const accountName = accountNameInput.value.trim();
        
        // Validation
        if (!accountName) {
            showError('Please enter an account name.');
            return;
        }
        
        // Check if name already exists
        if (accounts.some(account => account.name === accountName)) {
            showError('An account with this name already exists.');
            return;
        }
        
        try {
            // Generate new ECDSA key pair
            const keyPair = ec.genKeyPair();
            
            // Extract public and private keys
            const privateKey = keyPair.getPrivate('hex');
            const publicKey = keyPair.getPublic('hex');
            
            // Send to server
            createAccount(accountName, publicKey, privateKey);
            
        } catch (error) {
            showError('Error generating key pair: ' + error.message);
        }
    });
    
    // Function to load accounts from server
    function loadAccounts() {
        const formData = new FormData();
        formData.append('action', 'getAll');
        
        fetch('../Data/save_accounts.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                accounts = data.accounts;
                renderAccounts();
            } else {
                showError('Error loading accounts: ' + data.message);
            }
        })
        .catch(error => {
            showError('Network error: ' + error.message);
        });
    }
    
    // Function to create account on server
    function createAccount(name, publicKey, privateKey) {
        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('name', name);
        formData.append('publicKey', publicKey);
        formData.append('privateKey', privateKey);
        
        fetch('../Data/save_accounts.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                accounts = data.accounts;
                accountNameInput.value = '';
                renderAccounts();
                hideError();
            } else {
                showError('Error creating account: ' + data.message);
            }
        })
        .catch(error => {
            showError('Network error: ' + error.message);
        });
    }
    
    // Function to delete account on server
    function deleteAccount(index) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('index', index);
        
        fetch('../Data/save_accounts.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                accounts = data.accounts;
                renderAccounts();
            } else {
                showError('Error deleting account: ' + data.message);
            }
        })
        .catch(error => {
            showError('Network error: ' + error.message);
        });
    }
    
    // Function to render accounts
    function renderAccounts() {
        // Clear list
        accountsList.innerHTML = '';
        
        // Show empty message if needed
        if (accounts.length === 0) {
            emptyMessage.style.display = 'block';
            return;
        }
        
        // Hide empty message
        emptyMessage.style.display = 'none';
        
        // Add each account to the list
        accounts.forEach((account, index) => {
            const row = document.createElement('tr');
            
            row.innerHTML = `
                <td>${account.name}</td>
                <td class="key-cell">${account.publicKey}</td>
                <td class="key-cell">${account.privateKey}</td>
                <td>
                    <button class="delete-btn" data-index="${index}">Delete</button>
                </td>
            `;
            
            accountsList.appendChild(row);
        });
        
        // Add event listeners for delete buttons
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                deleteAccount(index);
            });
        });
    }
    
    // Functions to handle error messages
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
    }
    
    function hideError() {
        errorMessage.style.display = 'none';
    }
});