<?php
/**
 * TEMPLATE 3 - "Créatif"
 * En-tête coloré, avatar rond, sidebar foncée et contenu principal clair.
 */
require_once __DIR__ . '/_demo_data.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= cv_esc($full_name) ?> - CV</title>
    <style><?php readfile(__DIR__ . '/../css/templates/template3.css'); ?></style>
</head>
<body>
<div class="cv-wrapper">

    <aside class="sidebar">
        <div class="profile-photo">
            <?php if (!empty($photo)): ?>
                <img class="avatar" src="<?= cv_esc(cv_image_src($photo)) ?>" alt="<?= cv_esc($full_name) ?>">
            <?php else: ?>
                <div class="avatar-placeholder"><?= cv_esc(substr($full_name, 0, 1)) ?></div>
            <?php endif; ?>
        </div>

        <div class="sidebar-content">

            <section class="sidebar-section contact-section">
                <h3>Contact</h3>
                <div class="contact-pills">
                    <?php if (!empty($email)): ?><span>✉ <?= cv_esc($email) ?></span><?php endif; ?>
                    <?php if (!empty($phone)): ?><span>☎ <?= cv_esc($phone) ?></span><?php endif; ?>
                    <?php if (!empty($address)): ?><span>📍 <?= cv_esc($address) ?></span><?php endif; ?>
                    <?php if (!empty($age)): ?><span>🎂 <?= cv_esc($age) ?> ans</span><?php endif; ?>
                </div>
            </section>

            <?php if (!empty($skills)): ?>
                <section class="sidebar-section skills-section">
                    <h3>Compétences</h3>
                    <div class="badges">
                        <?php foreach ($skills as $skill): ?>
                            <div class="badge"><?= cv_esc($skill['skill_name']) ?></div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (!empty($languages)): ?>
                <section class="sidebar-section languages-section">
                    <h3>Langues</h3>
                    <div class="lang-grid">
                        <?php foreach ($languages as $lang): ?>
                            <div class="lang-chip"><b><?= cv_esc($lang['language_name']) ?></b> — <?= cv_esc($lang['level']) ?></div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

        </div>
    </aside>

    <main class="main">
        <header class="profile-header">
            <h1><?= cv_esc($full_name) ?></h1>
            <div class="role"><?= cv_esc($title) ?></div>
        </header>

        <div class="main-content">

            <?php if (!empty($about_me)): ?>
                <section class="main-section about-section">
                    <h3>À propos</h3>
                    <div class="content-card">
                        <p class="about"><?= cv_esc($about_me) ?></p>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (!empty($experiences)): ?>
                <section class="main-section experience-section">
                    <h3>Expériences</h3>
                    <div class="content-card experience-card">
                        <?php foreach ($experiences as $exp): ?>
                            <div class="exp-item">
                                <div class="row-top"><span><?= cv_esc($exp['job_title']) ?></span><span><?= cv_esc($exp['start_date']) ?> → <?= cv_esc($exp['end_date']) ?></span></div>
                                <div class="company"><?= cv_esc($exp['company']) ?></div>
                                <div class="desc"><?= cv_esc($exp['description']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (!empty($education)): ?>
                <section class="main-section education-section">
                    <h3>Formation</h3>
                    <div class="content-card education-card">
                        <?php foreach ($education as $edu): ?>
                            <div class="edu-item">
                                <div class="row-top"><span><?= cv_esc($edu['degree']) ?></span><span><?= cv_esc($edu['start_year']) ?> → <?= cv_esc($edu['end_year']) ?></span></div>
                                <div class="school"><?= cv_esc($edu['school']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

        </div>
    </main>

</div>
</body>
</html>