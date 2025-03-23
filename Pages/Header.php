<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlockChainMail</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="../Styles/stats.css"
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-900 text-white pt-16">
<header class="fixed top-0 left-0 right-0 z-50">
    <nav class="bg-gray-800">
        <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
            <div class="relative flex h-16 items-center justify-between">
                <div class="flex flex-1 items-center justify-center sm:items-stretch sm:justify-start">
                    <div class="flex shrink-0 items-center">
                        <img class="h-9 w-auto" src="https://media.forgecdn.net/avatars/thumbnails/513/992/256/256/637833979970061765.png" alt="logo">
                    </div>
                    <div class="hidden sm:ml-6 sm:block">
                        <div class="flex space-x-4">
                            <a href="/BlockChainMail/Pages/Accueil.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Accueil</a>
                            <a href="/BlockChainMail/Pages/Basics.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Basics</a>
                            <a href="/BlockChainMail/Pages/Accounts.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Accounts</a>
                            <a href="/BlockChainMail/Pages/Balances.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Balances</a>
                            <a href="/BlockChainMail/Pages/Mempool.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Mempool</a>
                            <a href="/BlockChainMail/Pages/Mining.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Mining</a>
                            <a href="/BlockChainMail/Pages/Blockchain.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Blockchain</a>
                            <a href="/BlockChainMail/Pages/MiniGame.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Mini-Game</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>

<?php include "../Pages/Stats.php" ?>

</body>
</html>