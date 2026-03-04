<?php
$reportPath = __DIR__ . '/../scripts/logs/simulation_summary.txt';
$reportContent = file_exists($reportPath)
    ? file_get_contents($reportPath)
    : "Rapport introuvable. Lancez d'abord scripts/test_restore_simulation.sh";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport simulation restauration</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; background: #f7f7f7; color: #1f2937; }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; max-width: 1100px; }
        h1 { margin-top: 0; }
        .meta { color: #4b5563; margin-bottom: 16px; }
        pre { background: #111827; color: #e5e7eb; padding: 16px; border-radius: 8px; overflow: auto; white-space: pre-wrap; }
        .ok { color: #065f46; font-weight: 700; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Simulation de restauration (perte de données + erreur de manipulation)</h1>
        <p class="meta">Source: scripts/logs/simulation_summary.txt</p>
        <?php if (strpos($reportContent, 'STATUT GLOBAL: SUCCÈS') !== false): ?>
            <p class="ok">Résultat global: SUCCÈS</p>
        <?php endif; ?>
        <pre><?php echo htmlspecialchars($reportContent, ENT_QUOTES, 'UTF-8'); ?></pre>
    </div>
</body>
</html>
