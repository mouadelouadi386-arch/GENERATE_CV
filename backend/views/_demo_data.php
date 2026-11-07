<?php
if (!isset($full_name)) {
    $full_name = 'Sacha Dubois';
    $title = 'Étudiante';
    $email = 'hello@reallygreatsite.com';
    $phone = '+123-456-7890';
    $address = '123 Anywhere St., Any City';
    $photo = '';
    $age = '';

    $about_me = 'Étudiante motivée, sérieuse et dynamique, je recherche un emploi alimentaire afin de développer mes compétences relationnelles, acquérir de l’expérience professionnelle et financer mes études.';

    $education = [
        [
            'degree' => 'Licence en Gestion Administrative',
            'school' => 'Really Great School',
            'city' => 'Any City',
            'period' => '2024 à aujourd’hui',
            'start_year' => '2024',
            'end_year' => 'Aujourd’hui',
        ],
        [
            'degree' => 'Baccalauréat Mention Bien',
            'school' => 'Really Great School',
            'city' => 'Any City',
            'period' => '2024',
            'start_year' => '',
            'end_year' => '2024',
        ],
    ];

    $experiences = [
        [
            'job_title' => 'Équipier polyvalent – Restauration rapide',
            'company' => 'Really Great Company',
            'city' => 'Any City',
            'period' => '2025',
            'start_date' => '2025',
            'end_date' => '',
            'responsibilities' => [
                'Accueil et prise de commandes clients',
                'Préparation des produits en respectant les normes d’hygiène et de sécurité',
                'Travail en équipe pour assurer un service efficace aux heures de pointe',
                'Gestion des encaissements',
            ],
            'description' => 'Accueil et prise de commandes clients ; préparation des produits en respectant les normes d’hygiène et de sécurité ; travail en équipe pour assurer un service efficace aux heures de pointe ; gestion des encaissements.',
        ],
        [
            'job_title' => 'Employé polyvalent – Grande distribution',
            'company' => 'Really Great Company',
            'city' => 'Any City',
            'period' => '2024',
            'start_date' => '2024',
            'end_date' => '',
            'responsibilities' => [
                'Mise en rayon et étiquetage des produits',
                'Réassort et gestion des stocks en réserve',
                'Orientation et aide aux clients dans leurs achats',
                'Participation à l’inventaire et rangement du magasin',
            ],
            'description' => 'Mise en rayon et étiquetage des produits ; réassort et gestion des stocks en réserve ; orientation et aide aux clients dans leurs achats ; participation à l’inventaire et rangement du magasin.',
        ],
        [
            'job_title' => 'Bénévole – Association étudiante',
            'company' => 'Really Great School',
            'city' => 'Any City',
            'period' => '2024 à aujourd’hui',
            'start_date' => '2024',
            'end_date' => 'Aujourd’hui',
            'responsibilities' => [
                'Accueil et orientation des participants lors des événements',
                'Service de boissons et restauration lors de soirées étudiantes',
                'Développement de compétences relationnelles et organisationnelles',
            ],
            'description' => 'Accueil et orientation des participants lors des événements ; service de boissons et restauration lors de soirées étudiantes ; développement de compétences relationnelles et organisationnelles.',
        ],
    ];

    $skills = [
        ['skill_name' => 'Accueil et relation client'],
        ['skill_name' => 'Gestion de caisse'],
        ['skill_name' => 'Service en salle'],
        ['skill_name' => 'Organisation'],
        ['skill_name' => 'Travail en équipe'],
        ['skill_name' => 'Adaptabilité'],
        ['skill_name' => 'Réactivité'],
    ];

    $languages = [
        ['language_name' => 'Anglais', 'level' => 'Courant'],
        ['language_name' => 'Espagnol', 'level' => 'Intermédiaire'],
    ];

    $interests = [
        'Lecture',
        'Randonnée',
        'Voyages',
    ];
}


if (!function_exists('cv_image_src')) {
    /**
     * Convert a local CV photo into an embedded data URI.
     * Embed local photos directly for reliable preview rendering.
     */
    function cv_image_src($value): string
    {
        $value = trim((string) $value);
        if ($value === '' || str_starts_with($value, 'data:image/')) {
            return $value;
        }

        $root = realpath(dirname(__DIR__, 2));
        if ($root === false) {
            return $value;
        }

        $candidates = [];

        // Normal saved value: public/uploads/xxx.jpg
        if (!preg_match('#^https?://#i', $value)) {
            $relative = ltrim(str_replace(['\\\\', '\\'], '/', $value), '/');
            $candidates[] = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        } else {
            // Also support an old absolute localhost URL by extracting /public/...
            $urlPath = (string) (parse_url($value, PHP_URL_PATH) ?? '');
            $publicPos = strpos($urlPath, '/public/');
            if ($publicPos !== false) {
                $relative = ltrim(substr($urlPath, $publicPos + 1), '/');
                $candidates[] = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
            }
        }

        foreach ($candidates as $candidate) {
            $real = realpath($candidate);
            if ($real === false || !is_file($real)) {
                continue;
            }
            $rootPrefix = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            if (strncmp($real, $rootPrefix, strlen($rootPrefix)) !== 0) {
                continue;
            }

            $mime = '';
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo) {
                    $mime = (string) finfo_file($finfo, $real);
                    finfo_close($finfo);
                }
            }
            if (!str_starts_with($mime, 'image/')) {
                $ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
                $mime = [
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'webp' => 'image/webp',
                    'gif' => 'image/gif',
                ][$ext] ?? '';
            }
            if (!str_starts_with($mime, 'image/')) {
                continue;
            }

            $bytes = @file_get_contents($real);
            if ($bytes !== false) {
                return 'data:' . $mime . ';base64,' . base64_encode($bytes);
            }
        }
        return $value;
    }
}

if (!function_exists('cv_esc')) {
    function cv_esc($value): string
    {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}
?>