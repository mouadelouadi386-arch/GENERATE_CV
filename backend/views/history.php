<?php
$cvs = $cvs ?? [];

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('initials')) {
    function initials($name) {
        $parts = preg_split('/\s+/', trim((string) $name));
        $out = '';
        foreach ($parts as $part) {
            if ($part !== '') {
                $letter = function_exists('mb_substr') ? mb_substr($part, 0, 1) : substr($part, 0, 1);
                $out .= function_exists('mb_strtoupper') ? mb_strtoupper($letter) : strtoupper($letter);
            }
            $length = function_exists('mb_strlen') ? mb_strlen($out) : strlen($out);
            if ($length >= 2) { break; }
        }
        return $out !== '' ? $out : '?';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historique de mes CV</title>
    <link rel="stylesheet" href="public/style/style.css">
</head>
<body>

<main class="page">

    <div class="page__head">
        <div>
            <h1 class="page__title">Historique</h1>
            <p class="page__subtitle">
                <?= count($cvs) ?> CV<?= count($cvs) > 1 ? 's' : '' ?> enregistré<?= count($cvs) > 1 ? 's' : '' ?>
            </p>
        </div>

        <div class="head-actions">
            <a class="btn-ghost" href="index.php">&larr; Accueil</a>
            <a class="btn-pill" href="index.php?action=create_cv" style="font-size:17px;padding:12px 28px;">+ Nouveau CV</a>
        </div>
    </div>

    <?php if (empty($cvs)): ?>

        <div class="empty-state">
            <div class="empty-state__icon" aria-hidden="true">&#128196;</div>
            <h2 class="empty-state__title">Aucun CV pour le moment</h2>
            <p class="empty-state__text">
                Les CV que tu génères apparaîtront ici, avec la date et le modèle utilisé.
            </p>
            <a class="btn-submit" href="index.php?action=create_cv" style="font-size:18px;padding:14px 32px;">
                Créer mon premier CV
            </a>
        </div>

    <?php else: ?>

        <div class="cv-grid">
            <?php foreach ($cvs as $cv): ?>
                <?php $cvId = (int) $cv['cv_id']; ?>
                <article class="cv-card">
                    <a class="cv-card__top" href="index.php?action=cv_resulte&amp;id=<?= $cvId ?>">
                        <span class="cv-card__avatar"><?= e(initials($cv['full_name'])) ?></span>
                        <span>
                            <span class="cv-card__name"><?= $cv['full_name'] !== '' ? e($cv['full_name']) : 'Sans nom' ?></span>
                            <span class="cv-card__meta"><?= e($cv['email'] ?? '') ?></span>
                        </span>
                    </a>

                    <span class="cv-card__tag">Template <?= (int) ($cv['template_id'] ?? 1) ?></span>

                    <p class="cv-card__meta">
                        Créé le <?= e(date('d/m/Y \à H:i', strtotime($cv['create_at'] ?? $cv['created_at'] ?? 'now'))) ?>
                    </p>

                    <div class="cv-card__actions">
                        <a class="btn-sm btn-sm--solid" href="index.php?action=cv_resulte&amp;id=<?= $cvId ?>">Voir</a>
                        <a class="btn-sm" href="index.php?action=edit&amp;id=<?= $cvId ?>">Modifier</a>
                        <a class="btn-sm" target="_blank" rel="noopener" href="index.php?action=export_pdf&amp;id=<?= $cvId ?>">PDF</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</main>

<script src="public/js/script.js"></script>
</body>
</html>