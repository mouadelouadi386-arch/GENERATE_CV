<?php
/**
 * TEMPLATE 1 - "Moderne"
 * Variables expected (defined by CvController before the include):
 * $full_name, $title, $email, $phone, $address, $about_me, $photo,
 * $experiences[], $education[], $skills[], $languages[]
 */
require_once __DIR__ . '/_demo_data.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= cv_esc($full_name) ?> - CV</title>
    <style><?php readfile(__DIR__ . '/../css/templates/template1.css'); ?></style>
</head>
<body>
<div class="cv-wrapper">

    <header class="cv-header">
        <div class="header-photo">
            <?php if (!empty($photo)): ?>
                <img class="avatar" src="<?= cv_esc(cv_image_src($photo)) ?>" alt="<?= cv_esc($full_name) ?>">
            <?php else: ?>
                <div class="avatar-placeholder"><?= cv_esc(substr($full_name, 0, 1)) ?></div>
            <?php endif; ?>
        </div>

        <div class="header-identity">
            <h1><?= cv_esc($full_name) ?></h1>
            <div class="role"><?= cv_esc($title) ?></div>
        </div>
    </header>

    <div class="cv-body">
        <aside class="sidebar">

            <?php if (!empty($about_me)): ?>
                <section class="sidebar-section about-section">
                    <h3>À propos</h3>
                    <p class="about"><?= cv_esc($about_me) ?></p>
                </section>
            <?php endif; ?>

            <section class="sidebar-section contact-section">
                <h3>Contact</h3>

                <?php if (!empty($email)): ?>
                    <div class="contact-item">✉ <span><?= cv_esc($email) ?></span></div>
                <?php endif; ?>

                <?php if (!empty($phone)): ?>
                    <div class="contact-item">☎ <span><?= cv_esc($phone) ?></span></div>
                <?php endif; ?>

                <?php if (!empty($address)): ?>
                    <div class="contact-item">📍 <span><?= cv_esc($address) ?></span></div>
                <?php endif; ?>
                <?php if (!empty($age)): ?>
                    <div class="contact-item">🎂 <span><?= cv_esc($age) ?> ans</span></div>
                <?php endif; ?>
            </section>

            <?php if (!empty($skills)): ?>
                <section class="sidebar-section skills-section">
                    <h3>Compétences</h3>

                    <?php foreach ($skills as $skill): ?>
                        <div class="skill-bar">
                            <div class="name"><?= cv_esc($skill['skill_name']) ?></div>
                            <div class="track">
                                <div class="fill <?= cv_esc($skill['level'] === 'Avancé' ? 'is-advanced' : ($skill['level'] === 'Intermédiaire' ? 'is-intermediate' : 'is-basic')) ?>"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

            <?php if (!empty($languages)): ?>
                <section class="sidebar-section languages-section">
                    <h3>Langues</h3>

                    <?php foreach ($languages as $lang): ?>
                        <div class="lang-item">
                            <span><?= cv_esc($lang['language_name']) ?></span>
                            <span><?= cv_esc($lang['level']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

        </aside>

        <main class="main">

            <?php if (!empty($education)): ?>
                <section class="main-section education-section">
                    <h3>Formation</h3>

                    <?php foreach ($education as $edu): ?>
                        <div class="edu-item">
                            <div class="row-top">
                                <span><?= cv_esc($edu['degree']) ?></span>
                                <span><?= cv_esc($edu['start_year']) ?> - <?= cv_esc($edu['end_year']) ?></span>
                            </div>
                            <div class="school"><?= cv_esc($edu['school']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

            <?php if (!empty($experiences)): ?>
                <section class="main-section experience-section">
                    <h3>Expériences</h3>

                    <?php foreach ($experiences as $exp): ?>
                        <div class="exp-item">
                            <div class="row-top">
                                <span><?= cv_esc($exp['job_title']) ?></span>
                                <span><?= cv_esc($exp['start_date']) ?> - <?= cv_esc($exp['end_date']) ?></span>
                            </div>
                            <div class="company"><?= cv_esc($exp['company']) ?></div>
                            <div class="desc"><?= cv_esc($exp['description']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

        </main>
    </div>
</div>
</body>
</html>