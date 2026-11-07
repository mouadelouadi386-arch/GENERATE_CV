<?php 
require_once 'backend/models/CvModel.php';
class CvController{
    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
    }
    private function uploadPhoto($currentPhoto = null) {
        if (($_POST['has_photo'] ?? '1') !== '1') {
            return null;
        }
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
            return $currentPhoto;
        }
        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            return $currentPhoto;
        }
        $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return $currentPhoto;
        }
        $uploadDir = 'public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $destination = $uploadDir . uniqid('cv_', true) . '.' . $extension;
        return move_uploaded_file($_FILES['photo']['tmp_name'], $destination) ? $destination : $currentPhoto;
    }

    private function saveDetails(CvModel $cvModel, $cvId, array $data) {
        foreach (($data['experiences'] ?? []) as $experience) {
            if (!empty(trim($experience['job_title'] ?? '')) || !empty(trim($experience['company'] ?? ''))) {
                $cvModel->addExperience($cvId, $experience);
            }
        }
        foreach (($data['education'] ?? []) as $education) {
            if (!empty(trim($education['degree'] ?? '')) || !empty(trim($education['school'] ?? ''))) {
                $cvModel->addEducation($cvId, $education);
            }
        }
        foreach (($data['skills'] ?? []) as $skill) {
            $name = trim($skill['name'] ?? '');
            if ($name !== '') {
                $cvModel->addSkill($cvId, $name, $skill['level'] ?? null);
            }
        }
        foreach (($data['languages'] ?? []) as $language) {
            $name = trim($language['name'] ?? '');
            if ($name !== '') {
                $cvModel->addLanguage($cvId, $name, $language['level'] ?? null);
            }
        }
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cvModel = new CvModel($this->pdo);
            $dataToSave = $_POST;
            $dataToSave['photo'] = $this->uploadPhoto();
            $cvId = $cvModel->createCv(session_id(), $dataToSave);
            $this->saveDetails($cvModel, $cvId, $dataToSave);
            header("Location: index.php?action=cv_resulte&id=" . $cvId);
            exit();
        }
        if (!function_exists('e')) {
            function e($value) {
                return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
            }
        }

        $cvModel = new CvModel($this->pdo);
        $templates = $cvModel->getAllTemplates();
        $selectedTemplate = 1;

        require 'backend/views/create_cv.php';
    }
    public function edit($cvId) {
        $cvModel = new CvModel($this->pdo);
        $cv = $cvModel->getCvById($cvId);
        if (!$cv) {
            header('Location: index.php?action=history');
            exit();
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['photo'] = $this->uploadPhoto($cv['photo'] ?? null);
            $cvModel->updateCv($cvId, $data);
            $cvModel->clearDetails($cvId);
            $this->saveDetails($cvModel, $cvId, $data);
            header("Location: index.php?action=cv_resulte&id=" . $cvId);
            exit();
        }
        $templates = $cvModel->getAllTemplates();
        $old = $cv;
        $old['has_photo'] = empty($cv['photo']) ? '0' : '1';
        require 'backend/views/create_cv.php';
    }
    public function view($cvId){
        $cvModel = new CvModel($this->pdo);
        $cv = $cvModel->getCvById($cvId);
        if(!$cv){
            die("Erreur : Le CV demandé est introuvable dans la base de données.");
        }
        if (!function_exists('cv_esc')){
            function cv_esc($value){
                return htmlspecialchars((string)$value, ENT_QUOTES , 'UTF-8');
            }
        }
        extract($cv);
        $templateId = $template_id ?? 1;
        $templatePath = "public/templates/template{$templateId}.php";
        
        if (file_exists($templatePath)){
            require $templatePath;
        } else {
            die("Erreur : Le fichier de design 'template{$templateId}.php' est introuvable.");
        }
    }
    public function exportPdf($cvId){
        $cvModel = new CvModel($this->pdo);
        $cv = $cvModel->getCvById($cvId);
        if (!$cv) {
            http_response_code(404);
            die("Erreur : Ce CV n'existe pas.");
        }

        if (!function_exists('cv_esc')) {
            function cv_esc($value){
                return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }
        }

        extract($cv);
        $templateId = max(1, min(6, (int)($cv['template_id'] ?? 1)));
        $templatePath = "public/templates/template{$templateId}.php";
        if (!is_file($templatePath)) {
            http_response_code(500);
            die("Erreur : Le template du CV est introuvable.");
        }

        ob_start();
        require $templatePath;
        $html = (string) ob_get_clean();

        require_once 'backend/helpers/BrowserPdf.php';
        try {
            $pdfBytes = (new BrowserPdf(__DIR__ . '/../..'))->renderHtml($html);
        } catch (Throwable $e) {
            $safeMessage = htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $fallback = '<style>.pdf-print-help{position:fixed;z-index:999999;left:50%;top:18px;transform:translateX(-50%);max-width:760px;padding:12px 18px;border-radius:12px;background:#111827;color:#fff;font:14px/1.4 Arial,sans-serif;box-shadow:0 8px 30px rgba(0,0,0,.25)}@media print{.pdf-print-help{display:none!important}}</style>'
                . '<div class="pdf-print-help">Le CV est prêt. Dans la fenêtre qui s’ouvre, choisissez <b>Enregistrer au format PDF</b>. <small style="opacity:.7">' . $safeMessage . '</small></div>'
                . '<script>window.addEventListener("load",function(){setTimeout(function(){window.print();},500);});</script>';
            $html = str_ireplace('</body>', $fallback . '</body>', $html);
            while (ob_get_level() > 0) { @ob_end_clean(); }
            header('Content-Type: text/html; charset=UTF-8');
            echo $html;
            exit();
        }

        $pdfDir = 'public/uploads/pdfs';
        if (!is_dir($pdfDir)) {
            @mkdir($pdfDir, 0755, true);
        }

        $rawName = trim((string)($cv['full_name'] ?? 'CV'));
        if (function_exists('iconv')) {
            $latinName = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $rawName);
            if ($latinName !== false) $rawName = $latinName;
        }
        $safeName = preg_replace('/[^A-Za-z0-9_-]+/', '_', $rawName) ?: 'CV';
        $safeName = trim($safeName, '_') ?: 'CV';
        $fileName = 'CV_' . $safeName . '_' . $cvId . '.pdf';
        $filePath = $pdfDir . '/' . $fileName;

        if (is_dir($pdfDir) && is_writable($pdfDir)) {
            @file_put_contents($filePath, $pdfBytes);
            try {
                $cvModel->saveGeneratedPdf($cvId, $fileName, $filePath);
            } catch (Throwable $e) {
            }
        }

        while (ob_get_level() > 0) { @ob_end_clean(); }
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . strlen($pdfBytes));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        echo $pdfBytes;
        exit();
    }
    public function history() {
        $sessionId = session_id();
        $cvModel = new CvModel($this->pdo);
        $cvs = $cvModel->getCvsBySession($sessionId);
        if (!function_exists('e')) {
            function e($value) {
                return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
            }
        }
        require 'backend/views/history.php';
    }

    public function show($id){
        $cvModel = new CvModel($this->pdo);
        $cv = $cvModel->getCvById($id);
        if (!$cv) {
            header('Location: index.php?action=history');
            exit();
        }
        require 'backend/views/cv_resulte.php';

    }
}
?>
