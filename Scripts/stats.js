// Fonction pour charger les données depuis le fichier JSON
async function loadStats() {
    try {
        // Faire une requête pour récupérer le fichier JSON
        const response = await fetch('../Data/stats.json');
        if (!response.ok) {
            throw new Error('Erreur lors du chargement du fichier JSON');
        }
        
        // Convertir la réponse en JSON
        const data = await response.json();
        
        // Vérifier que nous avons des données
        if (data && data.length > 0) {
            // Mettre à jour les champs avec les données du JSON
            document.getElementById('blocks-value').textContent = data[0].blocks;
            document.getElementById('coinbase-value').textContent = data[0].coinbase;
            document.getElementById('current-value').textContent = data[0].value;
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Charger les stats au chargement de la page
document.addEventListener('DOMContentLoaded', loadStats);