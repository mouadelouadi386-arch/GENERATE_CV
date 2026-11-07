<?php
require_once 'backend/models/CvModel.php';
require_once 'backend/helpers/CvPdfRenderer.php';

class PdfController {
    private $pdo;
    public function __construct($pdo) { $this->pdo = $pdo; }

    public function generate($cvId) {
        $cvModel = new CvModel($this->pdo);
        $cv = $cvModel->getCvById((int)$cvId);
        if (!$cv) {
            http_response_code(404);
            die('Erreur : CV introuvable.');
        }

        $renderer = new CvPdfRenderer($cv, __DIR__ . '/../..');
        $pdfBytes = $renderer->render();
        $fileName = 'cv_' . (int)$cvId . '.pdf';

        while (ob_get_level() > 0) { @ob_end_clean(); }
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . strlen($pdfBytes));
        echo $pdfBytes;
        exit();
    }
}
?>
