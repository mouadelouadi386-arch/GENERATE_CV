<?php
require_once __DIR__ . '/_demo_data.php';

if (!function_exists('cv_language_percent')) {
    function cv_language_percent($level): int
    {
        $levels = [
            'Langue maternelle' => 100,
            'Natif' => 100,
            'Bilingue' => 95,
            'Courant' => 85,
            'Avancé' => 85,
            'Intermédiaire' => 65,
            'Notions' => 45,
            'Débutant' => 35,
        ];

        return $levels[(string) $level] ?? 65;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= cv_esc($full_name) ?> - CV</title>
    <style><?php readfile(__DIR__ . '/../css/templates/template2.css'); ?></style>
</head>
<body>
<div class="cv-wrapper">
    <div class="top-decoration" aria-hidden="true">
        <span class="decoration-line decoration-line--white"></span>
        <span class="decoration-line decoration-line--navy"></span>
        <span class="decoration-line decoration-line--gray"></span>
    </div>

    <aside class="cv-sidebar">
        <div class="profile-photo">
            <?php if (!empty($photo)): ?>
                <img class="avatar" src="<?= cv_esc(cv_image_src($photo)) ?>" alt="<?= cv_esc($full_name) ?>">
            <?php else: ?>
                <div class="avatar-placeholder"><?= cv_esc(substr($full_name, 0, 1)) ?></div>
            <?php endif; ?>
        </div>
        <header class="identity">
            <h1 class="full-name"><?= cv_esc($full_name) ?></h1>

            <?php if ($title !== ''): ?>
                <p class="professional-title"><?= cv_esc($title) ?></p>
            <?php endif; ?>
        </header>

        <section class="sidebar-section contact-section">
            <h2 class="sidebar-heading">Coordonnées</h2>

            <?php if ($phone !== ''): ?>
                <p class="contact-item">
                    <span class="contact-icon" aria-hidden="true">☎</span>
                    <span><?= cv_esc($phone) ?></span>
                </p>
            <?php endif; ?>

            <?php if ($email !== ''): ?>
                <p class="contact-item">
                    <span class="contact-icon" aria-hidden="true">✉</span>
                    <span><?= cv_esc($email) ?></span>
                </p>
            <?php endif; ?>

            <?php if ($address !== ''): ?>
                <p class="contact-item">
                    <span class="contact-icon" aria-hidden="true">⌂</span>
                    <span><?= cv_esc($address) ?></span>
                </p>
            <?php endif; ?>
            <?php if (!empty($age)): ?>
                <p class="contact-item">
                    <span class="contact-icon" aria-hidden="true">🎂</span>
                    <span><?= cv_esc($age) ?> ans</span>
                </p>
            <?php endif; ?>
        </section>

        <?php if (!empty($languages)): ?>
            <section class="sidebar-section">
                <h2 class="sidebar-heading">Langues</h2>

                <?php foreach ($languages as $lang): ?>
                    <?php
                    $languageName = (string) ($lang['language_name'] ?? '');
                    $languageLevel = (string) ($lang['level'] ?? '');
                    $percent = cv_language_percent($languageLevel);
                    ?>
                    <div class="language-item">
                        <div class="language-label">
                            <span><?= cv_esc($languageName) ?></span>
                            <span class="language-level"><?= cv_esc($languageLevel) ?></span>
                        </div>
                        <div
                            class="language-track"
                            role="meter"
                            aria-label="<?= cv_esc('Niveau de ' . $languageName) ?>"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="<?= $percent ?>"
                        >
                            <span class="language-fill level-<?= $percent ?>"></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <?php if (!empty($skills)): ?>
            <section class="sidebar-section">
                <h2 class="sidebar-heading">Compétences</h2>
                <ul class="plain-list">
                    <?php foreach ($skills as $skill): ?>
                        <?php
                        $skillName = is_array($skill)
                            ? (string) ($skill['skill_name'] ?? $skill['name'] ?? '')
                            : (string) $skill;
                        ?>
                        <?php if ($skillName !== ''): ?>
                            <li><?= cv_esc($skillName) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>

        <?php if (!empty($interests)): ?>
            <section class="sidebar-section">
                <h2 class="sidebar-heading">Centres d’intérêt</h2>
                <ul class="plain-list">
                    <?php foreach ($interests as $interest): ?>
                        <?php
                        $interestName = is_array($interest)
                            ? (string) ($interest['interest_name'] ?? $interest['name'] ?? '')
                            : (string) $interest;
                        ?>
                        <?php if ($interestName !== ''): ?>
                            <li><?= cv_esc($interestName) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>
    </aside>

    <main class="cv-main">
        <?php if ($about_me !== ''): ?>
            <p class="profile-text"><?= cv_esc($about_me) ?></p>
        <?php endif; ?>

        <?php if (!empty($education)): ?>
            <section class="main-section">
                <h2 class="main-heading">Formation</h2>

                <?php foreach ($education as $edu): ?>
                    <?php
                    $period = trim((string) ($edu['period'] ?? ''));
                    if ($period === '') {
                        $periodParts = array_filter([
                            (string) ($edu['start_year'] ?? ''),
                            (string) ($edu['end_year'] ?? ''),
                        ]);
                        $period = implode(' - ', $periodParts);
                    }

                    $educationMeta = array_filter([
                        (string) ($edu['school'] ?? ''),
                        (string) ($edu['city'] ?? ''),
                        $period,
                    ]);
                    ?>
                    <article class="education-item">
                        <h3 class="item-title"><?= cv_esc($edu['degree'] ?? '') ?></h3>
                        <?php if (!empty($educationMeta)): ?>
                            <p class="item-meta"><?= cv_esc(implode(' - ', $educationMeta)) ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <?php if (!empty($experiences)): ?>
            <section class="main-section">
                <h2 class="main-heading">Expérience professionnelle</h2>

                <?php foreach ($experiences as $exp): ?>
                    <?php
                    $period = trim((string) ($exp['period'] ?? ''));
                    if ($period === '') {
                        $periodParts = array_filter([
                            (string) ($exp['start_date'] ?? ''),
                            (string) ($exp['end_date'] ?? ''),
                        ]);
                        $period = implode(' - ', $periodParts);
                    }

                    $experienceMeta = array_filter([
                        (string) ($exp['company'] ?? ''),
                        (string) ($exp['city'] ?? ''),
                        $period,
                    ]);

                    $details = $exp['responsibilities'] ?? $exp['tasks'] ?? $exp['description'] ?? [];
                    if (!is_array($details)) {
                        $details = preg_split('/\r\n|\r|\n|;/', (string) $details);
                    }

                    $details = array_values(array_filter(array_map(
                        static function ($item): string {
                            return trim((string) $item);
                        },
                        $details
                    )));
                    ?>
                    <article class="experience-item">
                        <h3 class="item-title"><?= cv_esc($exp['job_title'] ?? '') ?></h3>

                        <?php if (!empty($experienceMeta)): ?>
                            <p class="item-meta"><?= cv_esc(implode(' - ', $experienceMeta)) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($details)): ?>
                            <ul class="responsibility-list">
                                <?php foreach ($details as $detail): ?>
                                    <li><?= cv_esc($detail) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</div>
</body>
</html>