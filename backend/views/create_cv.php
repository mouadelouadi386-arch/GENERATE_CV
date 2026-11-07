<?php
$templates = $templates ?? [];
$errors = $errors ?? [];
$old = $old ?? [];
$editId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $editId > 0;

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

function form_value($key, $old) {
    return e($old[$key] ?? '');
}

$skills = $old['skills'] ?? [['skill_name' => '', 'level' => 'Intermediate']];
$languages = $old['languages'] ?? [['language_name' => '', 'level' => 'Intermediate']];
$experiences = $old['experiences'] ?? [['job_title' => '', 'company' => '', 'start_date' => '', 'end_date' => '', 'description' => '']];
$education = $old['education'] ?? [['degree' => '', 'school' => '', 'start_year' => '', 'end_year' => '']];
$selectedTemplate = (int) ($old['template_id'] ?? 1);
$hasPhoto = (string) ($old['has_photo'] ?? '1');
$levels = ['Basic', 'Intermediate', 'Advanced', 'Expert'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $isEdit ? 'Edit CV' : 'Create CV' ?></title>
    <link rel="stylesheet" href="public/style/style.css">
</head>
<body>
    <form class="layout" action="index.php?action=<?= $isEdit ? 'edit&id=' . $editId : 'create_cv' ?>" method="POST" enctype="multipart/form-data" novalidate>
        <main class="form-panel">
            <div class="editor-head">
                <div>
                    <p class="eyebrow">CV builder</p>
                    <h1><?= $isEdit ? 'Edit your CV' : 'Build your professional CV' ?></h1>
                </div>
                <a class="text-link" href="index.php?action=history">My CVs</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <h2 class="section-title">Profile</h2>
            
            <div class="photo-switch">
                <!--<label class="switch-option">
                    <span>Without photo</span>
                    <input type="radio" name="has_photo" value="0" id="noPhoto" <?= $hasPhoto === '0' ? 'checked' : '' ?>>
                </label>-->
                <label class="switch-option">
                    <span>With photo</span>
                    <input type="radio" name="has_photo" value="1" id="withPhoto" <?= $hasPhoto !== '0' ? 'checked' : '' ?>>
                </label>
            </div>

            <div class="grid grid--photo">
                <label class="photo-box" id="photoBox">
                    <span class="photo-box__icon" aria-hidden="true">+</span>
                    <span id="photoLabel">Upload a professional photo</span>
                    <input type="file" name="photo" id="photoInput" accept="image/png, image/jpeg, image/webp">
                    <?php if ($isEdit && !empty($old['photo'])): ?>
                        <img src="<?= e($old['photo']) ?>" alt="Current profile photo">
                    <?php endif; ?>
                </label>

                <div class="field">
                    <label for="full_name">Full name *</label>
                    <input type="text" id="full_name" name="full_name" value="<?= form_value('full_name', $old) ?>" required>
                </div>
                <div class="field">
                    <label for="title">Professional title</label>
                    <input type="text" id="title" name="title" placeholder="e.g. Front-end developer" value="<?= form_value('title', $old) ?>">
                </div>
                <div class="field">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?= form_value('email', $old) ?>" required>
                </div>
                <div class="field">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" value="<?= form_value('phone', $old) ?>">
                </div>
                <div class="field">
                    <label for="address">City / Address</label>
                    <input type="text" id="address" name="address" value="<?= form_value('address', $old) ?>">
                </div>
                <div class="field">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" min="14" max="99" value="<?= form_value('age', $old) ?>">
                </div>
            </div>

            <div class="field field--wide">
                <label for="about_me">Professional summary</label>
                <textarea id="about_me" name="about_me" rows="4" placeholder="A concise introduction highlighting your experience and strengths."><?= form_value('about_me', $old) ?></textarea>
            </div>

            <section class="editor-section">
                <div class="section-heading">
                    <div>
                        <h2 class="section-title">Work experience</h2>
                        <p>Show your impact with roles, companies, dates, and responsibilities.</p>
                    </div>
                    <button type="button" class="btn-add" data-add-row="experienceList">Add role</button>
                </div>
                <div id="experienceList" class="repeat-list">
                    <?php foreach ($experiences as $i => $experience): ?>
                        <div class="repeat-card" data-row="experience">
                            <button type="button" class="btn-icon js-remove-row" aria-label="Remove work experience">x</button>
                            <div class="grid grid--2">
                                <div class="field">
                                    <label>Job title</label>
                                    <input type="text" name="experiences[<?= $i ?>][job_title]" value="<?= e($experience['job_title'] ?? '') ?>">
                                </div>
                                <div class="field">
                                    <label>Company</label>
                                    <input type="text" name="experiences[<?= $i ?>][company]" value="<?= e($experience['company'] ?? '') ?>">
                                </div>
                                <div class="field">
                                    <label>Start date</label>
                                    <input type="date" name="experiences[<?= $i ?>][start_date]" value="<?= e($experience['start_date'] ?? '') ?>">
                                </div>
                                <div class="field">
                                    <label>End date</label>
                                    <input type="date" name="experiences[<?= $i ?>][end_date]" value="<?= e($experience['end_date'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="field">
                                <label>Key responsibilities and achievements</label>
                                <textarea name="experiences[<?= $i ?>][description]" rows="3" placeholder="Use one line per achievement."><?= e($experience['description'] ?? '') ?></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="editor-section">
                <div class="section-heading">
                    <div>
                        <h2 class="section-title">Education</h2>
                        <p>Add degrees, schools, and study dates.</p>
                    </div>
                    <button type="button" class="btn-add" data-add-row="educationList">Add education</button>
                </div>
                <div id="educationList" class="repeat-list">
                    <?php foreach ($education as $i => $item): ?>
                        <div class="repeat-card" data-row="education">
                            <button type="button" class="btn-icon js-remove-row" aria-label="Remove education">x</button>
                            <div class="grid grid--2">
                                <div class="field">
                                    <label>Degree or qualification</label>
                                    <input type="text" name="education[<?= $i ?>][degree]" value="<?= e($item['degree'] ?? '') ?>">
                                </div>
                                <div class="field">
                                    <label>School / university</label>
                                    <input type="text" name="education[<?= $i ?>][school]" value="<?= e($item['school'] ?? '') ?>">
                                </div>
                                <div class="field">
                                    <label>Start year</label>
                                    <input type="number" min="1900" max="2100" name="education[<?= $i ?>][start_year]" value="<?= e($item['start_year'] ?? '') ?>">
                                </div>
                                <div class="field">
                                    <label>End year</label>
                                    <input type="number" min="1900" max="2100" name="education[<?= $i ?>][end_year]" value="<?= e($item['end_year'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <div class="grid grid--2 split-sections">
                <section class="editor-section">
                    <div class="section-heading">
                        <h2 class="section-title">Skills</h2>
                        <button type="button" class="btn-add" data-add-row="skillList">Add skill</button>
                    </div>
                    <div id="skillList" class="repeat-list compact-list">
                        <?php foreach ($skills as $i => $skill): ?>
                            <div class="repeat-row" data-row="skill">
                                <div class="field">
                                    <label>Skill</label>
                                    <input type="text" name="skills[<?= $i ?>][name]" value="<?= e($skill['skill_name'] ?? $skill['name'] ?? '') ?>">
                                </div>
                                <div class="field">
                                    <label>Level</label>
                                    <select name="skills[<?= $i ?>][level]">
                                        <?php foreach ($levels as $level): ?>
                                            <option value="<?= $level ?>" <?= (($skill['level'] ?? '') === $level) ? 'selected' : '' ?>><?= $level ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="button" class="btn-icon js-remove-row" aria-label="Remove skill">x</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="editor-section">
                    <div class="section-heading">
                        <h2 class="section-title">Languages</h2>
                        <button type="button" class="btn-add" data-add-row="languageList">Add language</button>
                    </div>
                    <div id="languageList" class="repeat-list compact-list">
                        <?php foreach ($languages as $i => $language): ?>
                            <div class="repeat-row" data-row="language">
                                <div class="field">
                                    <label>Language</label>
                                    <input type="text" name="languages[<?= $i ?>][name]" value="<?= e($language['language_name'] ?? $language['name'] ?? '') ?>">
                                </div>
                                <div class="field">
                                    <label>Level</label>
                                    <select name="languages[<?= $i ?>][level]">
                                        <?php foreach ($levels as $level): ?>
                                            <option value="<?= $level ?>" <?= (($language['level'] ?? '') === $level) ? 'selected' : '' ?>><?= $level ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="button" class="btn-icon js-remove-row" aria-label="Remove language">x</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <div class="submit-wrap">
                <button type="submit" class="btn-submit"><?= $isEdit ? 'Save changes' : 'Create my CV' ?></button>
            </div>
        </main>

        <aside class="template-panel">
            <div class="template-panel__head">
                <div>
                    <p class="eyebrow eyebrow--light">Design</p>
                    <h2 class="template-panel__title">Choose your template</h2>
                </div>
            </div>
            
            <p class="template-panel__copy">Each visual preview is a different CV layout. Your choice is saved with the CV.</p>
            
            <div class="template-grid">
                <?php foreach ($templates as $template): $tid = (int) $template['template_id']; ?>
                    <label class="template-card">
                        <input type="radio" name="template_id" value="<?= $tid ?>" <?= $tid === $selectedTemplate ? 'checked' : '' ?>>
                        <span class="template-card__thumb">
                            <img src="public/assets/img/template<?= $tid ?>.jpeg" alt="Preview of <?= e($template['template_name']) ?>" loading="lazy">
                        </span>
                        <span class="template-card__label"><?= e($template['template_name']) ?></span>
                        <span class="template-card__check" aria-hidden="true">OK</span>
                    </label>
                <?php endforeach; ?>
            </div>
        </aside>
    </form>
    
    <script src="public/js/script.js"></script>
</body>
</html>