document.addEventListener('DOMContentLoaded', function() {
    // Initialiser la courbe elliptique (secp256k1 est utilisée par Bitcoin et Ethereum)
    const ec = new elliptic.ec('secp256k1');
    
    // Récupérer les éléments DOM
    const accountNameInput = document.getElementById('account-name');
    const createBtn = document.getElementById('create-btn');
    const accountsList = document.getElementById('accounts-list');
    const emptyMessage = document.getElementById('empty-message');
    const errorMessage = document.getElementById('error-message');
    
    // Charger les comptes depuis le localStorage
    let accounts = JSON.parse(localStorage.getItem('ecdsaAccounts')) || [];
    
    // Afficher les comptes existants
    renderAccounts();
    
    // Event listener pour le bouton de création
    createBtn.addEventListener('click', function() {
        const accountName = accountNameInput.value.trim();
        
        // Validation
        if (!accountName) {
            showError('Veuillez entrer un nom de compte.');
            return;
        }
        
        // Vérifier si le nom existe déjà
        if (accounts.some(account => account.name === accountName)) {
            showError('Un compte avec ce nom existe déjà.');
            return;
        }
        
        try {
            // Générer une nouvelle paire de clés ECDSA
            const keyPair = ec.genKeyPair();
            
            // Extraire les clés publique et privée
            const privateKey = keyPair.getPrivate('hex');
            const publicKey = keyPair.getPublic('hex');
            
            // Créer le nouveau compte
            const newAccount = {
                name: accountName,
                publicKey: publicKey,
                privateKey: privateKey
            };
            
            // Ajouter le compte à la liste
            accounts.push(newAccount);
            
            // Sauvegarder dans le localStorage
            localStorage.setItem('ecdsaAccounts', JSON.stringify(accounts));
            
            // Réinitialiser le formulaire
            accountNameInput.value = '';
            
            // Mettre à jour l'affichage
            renderAccounts();
            
            // Masquer les messages d'erreur
            hideError();
            
        } catch (error) {
            showError('Erreur lors de la génération de la paire de clés: ' + error.message);
        }
    });
    
    // Fonction pour afficher les comptes
    function renderAccounts() {
        // Vider la liste
        accountsList.innerHTML = '';
        
        // Afficher le message "vide" si nécessaire
        if (accounts.length === 0) {
            emptyMessage.style.display = 'block';
            return;
        }
        
        // Cacher le message "vide"
        emptyMessage.style.display = 'none';
        
        // Ajouter chaque compte à la liste
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
        
        // Ajouter les event listeners pour les boutons de suppression
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                
                // Supprimer le compte
                accounts.splice(index, 1);
                
                // Mettre à jour le localStorage
                localStorage.setItem('ecdsaAccounts', JSON.stringify(accounts));
                
                // Mettre à jour l'affichage
                renderAccounts();
            });
        });
    }
    
    // Fonctions pour gérer les messages d'erreur
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
    }
    
    function hideError() {
        errorMessage.style.display = 'none';
    }
});