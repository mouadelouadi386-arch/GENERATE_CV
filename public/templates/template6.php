<?php
/**
 * TEMPLATE 6 - "Modern / Timeline"
 * En-tête avec nom, titre, contacts et photo ronde. Corps en deux colonnes :
 * gauche (À propos, compétences, formation, langues) / droite (expérience en
 * timeline, certifications, informations personnelles).
 */
require_once __DIR__ . '/_demo_data.php';

// Variables optionnelles (au cas où elles n'existent pas dans _demo_data.php)
$photo          = $photo          ?? '';
$linkedin       = $linkedin       ?? '';
$certifications = $certifications ?? [];   // ex: [['name' => 'Google Ads Certification', 'issuer' => 'Google']]

// Renvoie la première clé existante et non vide parmi $keys (sans warning)
function cv_field($row, array $keys, $default = '')
{
    foreach ($keys as $k) {
        if (isset($row[$k]) && $row[$k] !== '' && $row[$k] !== null) {
            return $row[$k];
        }
    }
    return $default;
}

// Construit "début – fin" en gérant les clés manquantes
function cv_period($row, array $startKeys, array $endKeys, $presentLabel = '')
{
    $start = cv_field($row, $startKeys);
    $end   = cv_field($row, $endKeys, $presentLabel);
    if ($start === '' && $end === '') return '';
    if ($start === '') return $end;
    if ($end === '')   return $start;
    return $start . ' – ' . $end;
}

// Clés possibles pour les dates (ajoutez les vôtres si besoin)
const CV_START_KEYS = ['start_date', 'start_year', 'date_start', 'date_debut', 'debut', 'start', 'from'];
const CV_END_KEYS   = ['end_date', 'end_year', 'date_end', 'date_fin', 'fin', 'end', 'to'];

// Transforme une description multi-lignes en tableau de puces
function cv_bullets($text)
{
    $lines = preg_split('/\r\n|\r|\n/', (string) $text);
    $lines = array_map(function ($l) { return trim(ltrim(trim($l), "-•*")); }, $lines);
    return array_values(array_filter($lines, 'strlen'));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= cv_esc($full_name) ?> - CV</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
<style><?php readfile(__DIR__ . '/../css/templates/template6.css'); ?></style>
</head>
<body>
  <div class="cv-wrapper">

    <!-- ============ HEADER ============ -->
    <header class="cv-header">
      <div class="header-info">
        <h1><?= cv_esc($full_name) ?></h1>
        <div class="role"><?= cv_esc($title) ?></div>

        <ul class="contact-list">
          <?php if (!empty($phone)): ?>
            <li>
              <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25c1.1.37 2.3.57 3.6.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1z"/></svg>
              <span><?= cv_esc($phone) ?></span>
            </li>
          <?php endif; ?>
          <?php if (!empty($email)): ?>
            <li>
              <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 4-8 5-8-5V6l8 5 8-5z"/></svg>
              <span><?= cv_esc($email) ?></span>
            </li>
          <?php endif; ?>
          <?php if (!empty($address)): ?>
            <li>
              <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg>
              <span><?= cv_esc($address) ?></span>
            </li>
          <?php endif; ?>
          <?php if (!empty($linkedin)): ?>
            <li>
              <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zM8.3 18H5.7V9.7h2.6V18zM7 8.6a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zM18.3 18h-2.6v-4c0-1 0-2.2-1.4-2.2s-1.6 1-1.6 2.1V18h-2.6V9.7h2.5v1.1c.4-.7 1.2-1.3 2.5-1.3 2.7 0 3.2 1.8 3.2 4.1V18z"/></svg>
              <span><?= cv_esc($linkedin) ?></span>
            </li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="photo">
        <?php if (!empty($photo)): ?>
          <img src="<?= cv_esc(cv_image_src($photo)) ?>" alt="<?= cv_esc($full_name) ?>">
        <?php else: ?>
          <div class="photo-placeholder"><?= cv_esc(substr($full_name, 0, 1)) ?></div>
        <?php endif; ?>
      </div>
    </header>

    <!-- ============ BODY ============ -->
    <div class="cv-body">

      <!-- ---------- Colonne gauche ---------- -->
      <aside class="col-left">

        <?php if (!empty($about_me)): ?>
          <section>
            <h2>À propos de moi</h2>
            <p class="about"><?= cv_esc($about_me) ?></p>
          </section>
        <?php endif; ?>

        <?php if (!empty($skills)): ?>
          <section>
            <h2>Compétences</h2>
            <ul class="bullet-list">
              <?php foreach ($skills as $skill): ?>
                <li>
                  <?= cv_esc($skill['skill_name']) ?>
                  <?php if (!empty($skill['level'])): ?>
                    <span class="level">– <?= cv_esc($skill['level']) ?></span>
                  <?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </section>
        <?php endif; ?>

        <?php if (!empty($education)): ?>
          <section>
            <h2>Éducation</h2>
            <?php foreach ($education as $edu): ?>
              <div class="edu-item">
                <div class="school"><?= cv_esc($edu['school']) ?></div>
                <?php if (!empty($edu['faculty'])): ?>
                  <div><?= cv_esc($edu['faculty']) ?></div>
                <?php endif; ?>
                <?php $eduPeriod = cv_period($edu, CV_START_KEYS, CV_END_KEYS); ?>
                <?php if ($eduPeriod !== ''): ?>
                  <div><?= cv_esc($eduPeriod) ?></div>
                <?php endif; ?>
                <div><?= cv_esc(cv_field($edu, ['degree', 'diploma', 'title'])) ?></div>
              </div>
            <?php endforeach; ?>
          </section>
        <?php endif; ?>

        <?php if (!empty($languages)): ?>
          <section>
            <h2>Langues</h2>
            <ul class="bullet-list">
              <?php foreach ($languages as $lang): ?>
                <li><?= cv_esc($lang['language_name']) ?> – <?= cv_esc($lang['level']) ?></li>
              <?php endforeach; ?>
            </ul>
          </section>
        <?php endif; ?>

      </aside>

      <!-- ---------- Colonne droite ---------- -->
      <main class="col-right">

        <?php if (!empty($experiences)): ?>
          <section>
            <h2>Expérience professionnelle</h2>
            <div class="timeline">
              <?php foreach ($experiences as $exp): ?>
                <article class="exp-item">
                  <h3><?= cv_esc($exp['job_title']) ?></h3>
                  <div class="company"><?= cv_esc($exp['company']) ?></div>
                  <?php $expPeriod = cv_period($exp, CV_START_KEYS, CV_END_KEYS); ?>
                  <?php if ($expPeriod !== ''): ?>
                    <div class="dates"><?= cv_esc($expPeriod) ?></div>
                  <?php endif; ?>
                  <?php $bullets = cv_bullets($exp['description'] ?? ''); ?>
                  <?php if (!empty($bullets)): ?>
                    <ul class="bullet-list small">
                      <?php foreach ($bullets as $b): ?>
                        <li><?= cv_esc($b) ?></li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </article>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>

        <?php if (!empty($certifications)): ?>
          <section>
            <h2>Certifications</h2>
            <ul class="bullet-list">
              <?php foreach ($certifications as $cert): ?>
                <li>
                  <?= cv_esc($cert['name']) ?>
                  <?php if (!empty($cert['issuer'])): ?> – <?= cv_esc($cert['issuer']) ?><?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </section>
        <?php endif; ?>

        <?php if (!empty($age)): ?>
          <section>
            <h2>Informații personale</h2>
            <div class="personal-row">Âge : <?= cv_esc($age) ?> ans</div>
          </section>
        <?php endif; ?>

      </main>
    </div>

    <div class="cv-footer-line"></div>
  </div>
</body>
</html>