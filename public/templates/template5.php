<?php
// template5.php
require_once __DIR__ . '/_demo_data.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= cv_esc($full_name) ?> - CV</title>
    <style><?php readfile(__DIR__ . '/../css/templates/template5.css'); ?></style>
</head>
<body>
<div class="cv-wrapper">
    <div class="corner-decoration" aria-hidden="true"></div>

    <aside class="sidebar">
        <div class="profile">
            <?php if (!empty($photo)): ?>
                <img class="avatar" src="<?= cv_esc(cv_image_src($photo)) ?>" alt="Photo de <?= cv_esc($full_name) ?>">
            <?php else: ?>
                <div class="avatar-placeholder"><?= cv_esc(substr($full_name, 0, 1)) ?></div>
            <?php endif; ?>
        </div>

        <div class="sidebar-panel">
            <section class="sidebar-section">
                <h2 class="sidebar-heading">Coordonnées</h2>

                <?php if (!empty($phone)): ?>
                    <p class="contact-item"><span class="contact-icon" aria-hidden="true">☎</span><span><?= cv_esc($phone) ?></span></p>
                <?php endif; ?>

                <?php if (!empty($email)): ?>
                    <p class="contact-item"><span class="contact-icon" aria-hidden="true">✉</span><span><?= cv_esc($email) ?></span></p>
                <?php endif; ?>

                <?php if (!empty($address)): ?>
                    <p class="contact-item"><span class="contact-icon" aria-hidden="true">⌂</span><span><?= cv_esc($address) ?></span></p>
                <?php endif; ?>
                <?php if (!empty($age)): ?>
                    <p class="contact-item"><span class="contact-icon" aria-hidden="true">🎂</span><span><?= cv_esc($age) ?> ans</span></p>
                <?php endif; ?>
            </section>

            <?php if (!empty($languages)): ?>
                <section class="sidebar-section">
                    <h2 class="sidebar-heading">Langues</h2>

                    <?php foreach ($languages as $lang): ?>
                        <?php
                        $level = (string) ($lang['level'] ?? '');
                        $normalizedLevel = strtolower($level);
                        $levelClass = 'level-medium';

                        if (
                            strpos($normalizedLevel, 'courant') !== false ||
                            strpos($normalizedLevel, 'avanc') !== false ||
                            strpos($normalizedLevel, 'biling') !== false ||
                            strpos($normalizedLevel, 'mater') !== false
                        ) {
                            $levelClass = 'level-high';
                        } elseif (
                            strpos($normalizedLevel, 'debut') !== false ||
                            strpos($normalizedLevel, 'notion') !== false
                        ) {
                            $levelClass = 'level-low';
                        }
                        ?>
                        <div class="language-item">
                            <div class="language-label">
                                <span><?= cv_esc($lang['language_name'] ?? '') ?></span>
                                <span class="language-level"><?= cv_esc($level) ?></span>
                            </div>
                            <div class="language-track">
                                <span class="language-fill <?= cv_esc($levelClass) ?>"></span>
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
                                ? ($skill['skill_name'] ?? '')
                                : $skill;
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
                                ? ($interest['interest_name'] ?? $interest['name'] ?? '')
                                : $interest;
                            ?>
                            <?php if ($interestName !== ''): ?>
                                <li><?= cv_esc($interestName) ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>
        </div>
    </aside>

    <main class="cv-main">
        <header class="hero">
            <h1><?= cv_esc($full_name) ?></h1>
            <?php if (!empty($title)): ?>
                <p class="role"><?= cv_esc($title) ?></p>
            <?php endif; ?>
        </header>

        <?php if (!empty($education)): ?>
            <section class="main-section">
                <h2 class="main-heading">Formation</h2>

                <div class="timeline">
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

                        $schoolDetails = array_filter([
                            (string) ($edu['school'] ?? ''),
                            (string) ($edu['city'] ?? ''),
                        ]);
                        ?>
                        <article class="timeline-item education-item">
                            <div class="entry-heading">
                                <h3><?= cv_esc($edu['degree'] ?? '') ?></h3>
                                <?php if ($period !== ''): ?>
                                    <span class="entry-period"><?= cv_esc($period) ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($schoolDetails)): ?>
                                <p class="entry-meta"><?= cv_esc(implode(' - ', $schoolDetails)) ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($experiences)): ?>
            <section class="main-section">
                <h2 class="main-heading">Expérience professionnelle</h2>

                <div class="timeline">
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

                        $companyDetails = array_filter([
                            (string) ($exp['company'] ?? ''),
                            (string) ($exp['city'] ?? ''),
                        ]);

                        $tasks = $exp['responsibilities'] ?? $exp['tasks'] ?? $exp['description'] ?? [];

                        if (!is_array($tasks)) {
                            $tasks = preg_split('/\r\n|\r|\n|;/', trim((string) $tasks));
                        }

                        $tasks = array_filter($tasks, static function ($task) {
                            return trim((string) $task) !== '';
                        });
                        ?>
                        <article class="timeline-item experience-item">
                            <div class="entry-heading">
                                <h3><?= cv_esc($exp['job_title'] ?? '') ?></h3>
                                <?php if ($period !== ''): ?>
                                    <span class="entry-period"><?= cv_esc($period) ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($companyDetails)): ?>
                                <p class="entry-meta"><?= cv_esc(implode(' - ', $companyDetails)) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($tasks)): ?>
                                <ul class="responsibility-list">
                                    <?php foreach ($tasks as $task): ?>
                                        <li><?= cv_esc($task) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>
</div>
</body>
</html>