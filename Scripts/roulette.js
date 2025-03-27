document.addEventListener('DOMContentLoaded', () => {
    const playerSelect = document.getElementById('player');
    const betAmountInput = document.getElementById('bet-amount');
    const placeBetButton = document.getElementById('place-bet');
    const resultDisplay = document.getElementById('result-display');

    // Créer l'overlay de résultat
    const resultOverlay = document.createElement('div');
    resultOverlay.classList.add('result-overlay');
    document.body.appendChild(resultOverlay);

    let selectedBet = null;

    // Sélection des types de mise
    document.querySelectorAll('.bet-number, .bet-type').forEach(element => {
        element.addEventListener('click', () => {
            document.querySelectorAll('.bet-number, .bet-type').forEach(e => e.classList.remove('selected'));
            element.classList.add('selected');
            
            selectedBet = element.classList.contains('bet-number') 
                ? { 
                    type: 'number', 
                    value: parseInt(element.dataset.number),
                    color: element.dataset.color
                }
                : { 
                    type: element.dataset.type === 'rouge' || element.dataset.type === 'noir' ? 'color' : 'other', 
                    value: element.dataset.type 
                };
        });
    });

    function showResultOverlay(resultData) {
        // Créer le contenu de la carte de résultat
        const resultCard = document.createElement('div');
        resultCard.classList.add('result-card');
        
        const resultText = resultData.win 
            ? `Winning number: ${resultData.result}. 
               You won ${Math.abs(resultData.winnings)} CMC !` 
            : `Winning number: ${resultData.result}. 
               You lost your bet (${Math.abs(resultData.winnings)} CMC).`;
        
        resultCard.innerHTML = `
            <h2>${resultData.win ? 'VICTORY' : 'DEFEAT'}</h2>
            <p>${resultText}</p>
            <button id="close-result">Close</button>
        `;

        // Vider l'overlay et ajouter la carte
        resultOverlay.innerHTML = '';
        resultOverlay.appendChild(resultCard);

        // Ajouter la roulette
        const wheel = document.createElement('div');
        wheel.classList.add('roulette-wheel');
        const ball = document.createElement('div');
        ball.classList.add('roulette-ball');
        
        wheel.appendChild(ball);
        resultOverlay.insertBefore(wheel, resultCard);

        // Montrer l'overlay
        resultOverlay.classList.add('show');

        // Gestion de la fermeture
        const closeButton = resultOverlay.querySelector('#close-result');
        closeButton.addEventListener('click', () => {
            resultOverlay.classList.remove('show');
        });

        // Mettre à jour le solde
        const player = playerSelect.value;
        const newBalance = parseFloat(playerSelect.selectedOptions[0].dataset.balance) + resultData.winnings;
        playerSelect.selectedOptions[0].dataset.balance = newBalance;
        playerSelect.selectedOptions[0].text = 
            `${player} (${newBalance} CMC)`;
    }

    placeBetButton.addEventListener('click', () => {
        const player = playerSelect.value;
        const betAmount = parseInt(betAmountInput.value);
        const currentBalance = parseFloat(playerSelect.selectedOptions[0].dataset.balance);

        // Validation des entrées
        if (!selectedBet) {
            alert('Select bet type');
            return;
        }

        if (isNaN(betAmount) || betAmount <= 0) {
            alert('Select valid bet value');
            return;
        }

        if (betAmount > currentBalance) {
            alert('Balance too low');
            return;
        }

        // Désactiver le bouton pendant le traitement
        placeBetButton.disabled = true;
        resultDisplay.innerHTML = 'The wheel is turning !';

        fetch('../Data/roulette.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                playerName: player,
                bet: selectedBet,
                betAmount: betAmount
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.message || 'Erreur serveur');
                });
            }
            return response.json();
        })
        .then(data => {
            // Attendre un peu avant d'afficher le résultat pour simuler le spin
            setTimeout(() => {
                showResultOverlay(data);
            }, 3500);  // Durée de l'animation
        })
        .catch(error => {
            console.error('Erreur :', error);
            resultDisplay.innerHTML = `Erreur : ${error.message}`;
        })
        .finally(() => {
            // Réactiver le bouton
            placeBetButton.disabled = false;
        });
    });
});