<?php
session_start();
require_once 'backend/config/database.php';
require_once 'backend/controllers/HomeController.php';
require_once 'backend/controllers/CvController.php';
$homeController = new HomeController();
$cvController = new CvController($pdo);
$action = isset($_GET['action']) ? $_GET['action'] : 'home';
switch ($action){
    case 'home':
        $homeController->index();
        break;

    case 'create_cv':
        $cvController->create();
        break;
    case 'history':
        $cvController->history();
        break;
    case 'edit':
        if (isset($_GET['id']) && (int) $_GET['id'] > 0) {
            $cvController->edit((int) $_GET['id']);
        } else {
            header('Location: index.php?action=history');
            exit();
        }
        break;
    default:
        echo "<h1> Erreur 404 </h1><p> La page que vous cherchez n'existe pas.</p>";
        break; 
    case 'cv_resulte':
        if (isset($_GET['id']) && !empty($_GET['id'])) {
           $cvId = (int) $_GET['id']; 
           $cvController->show($cvId); 
        } else {
           header('Location: index.php?action=history');
        exit();
        }
        break;
    case 'view_cv':
        if (isset($_GET['id']) && (int) $_GET['id'] > 0) {
            $cvController->view((int) $_GET['id']);
        } else {
            header('Location: index.php?action=history');
            exit();
        }
        break;
    case 'export_pdf':
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $cvId = (int) $_GET['id'];
            $cvController->exportPdf($cvId); 
       } else {
            header('Location: index.php?action=history');
        exit();
    }
    break;    
}
?>
