<?php
$cv = $cv ?? [];
$cvId = (int) ($cv['cv_id'] ?? 0);
$templateId = (int) ($cv['template_id'] ?? 1);
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($cv['full_name'] ?? 'CV') ?> - CV</title>
    <link rel="stylesheet" href="public/style/style.css">
</head>
<body class="cv-result template-tone-<?= $templateId ?>">
    <div class="result-bar no-print">
        <div>
            <p class="eyebrow eyebrow--light">Votre CV est prêt</p>
            <h1 class="result-bar__title">Aperçu du modèle choisi</h1>
        </div>
        <div class="head-actions">
            <a class="btn-ghost" href="index.php?action=history">Historique</a>
            <a class="btn-ghost" href="index.php?action=edit&id=<?= $cvId ?>">Modifier</a>
            <a class="btn-pill" target="_blank" rel="noopener" href="index.php?action=export_pdf&id=<?= $cvId ?>">Télécharger le PDF</a>
        </div>
    </div>
    <main class="template-result-shell">
        <iframe
            class="template-result-frame"
            src="index.php?action=view_cv&id=<?= $cvId ?>"
            title="Aperçu du CV"
            loading="eager"
        ></iframe>
    </main>
</body>
</html>