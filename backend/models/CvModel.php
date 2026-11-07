<?php
class CvModel{
    private $pdo;
    public function __construct($pdo){
        $this->pdo =$pdo;
    }
    public function createCv($sessionId, $data) {
        $sql = "INSERT INTO cv (session_id, template_id, full_name, email, title, photo, phone, address, about_me, age, school, school_field, school_year, bac, bac_field, bac_year) 
                VALUES (:session_id, :template_id, :full_name, :email, :title, :photo, :phone, :address, :about_me, :age, :school, :school_field, :school_year, :bac, :bac_field, :bac_year)";

        $stmt = $this->pdo->prepare($sql);
        
        $stmt->execute([
            ':session_id'   => $sessionId,
            ':template_id'  => $data['template_id'] ?? 1,
            ':full_name'    => $data['full_name'] ?? '',
            ':email'        => $data['email'] ?? '',
            ':title'        => $data['title'] ?? null,
            ':photo'        => $data['photo'] ?? null,
            ':phone'        => $data['phone'] ?? '',
            ':address'      => $data['address'] ?? null,
            ':about_me'     => $data['about_me'] ?? null,
            ':age'          => !empty($data['age']) ? (int)$data['age'] : null,
            ':school'       => $data['school'] ?? null,
            ':school_field' => $data['school_field'] ?? null,
            ':school_year'  => $data['school_year'] ?? null,
            ':bac'          => $data['bac'] ?? null,
            ':bac_field'    => $data['bac_field'] ?? null,
            ':bac_year'     => $data['bac_year'] ?? null
        ]);
        
        return $this->pdo->lastInsertId();     
    }
    public function updateCv($cvId, $data) {
        $sql = "UPDATE cv SET template_id = :template_id, full_name = :full_name, email = :email,
                title = :title, photo = :photo, phone = :phone, address = :address,
                about_me = :about_me, age = :age WHERE cv_id = :cv_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':cv_id' => $cvId,
            ':template_id' => $data['template_id'] ?? 1,
            ':full_name' => $data['full_name'] ?? '',
            ':email' => $data['email'] ?? '',
            ':title' => $data['title'] ?? null,
            ':photo' => $data['photo'] ?? null,
            ':phone' => $data['phone'] ?? '',
            ':address' => $data['address'] ?? null,
            ':about_me' => $data['about_me'] ?? null,
            ':age' => !empty($data['age']) ? (int) $data['age'] : null,
        ]);
    }
    public function clearDetails($cvId) {
        foreach (['experiences', 'education', 'skills', 'languages'] as $table) {
            $stmt = $this->pdo->prepare("DELETE FROM {$table} WHERE cv_id = :cv_id");
            $stmt->execute([':cv_id' => $cvId]);
        }
    }
    public function addExperience($cvId, $data) {$sql = "INSERT INTO experiences (cv_id, job_title, company, start_date, end_date, description) 
                VALUES (:cv_id, :job_title, :company, :start_date, :end_date, :description)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':cv_id'       => $cvId,
            ':job_title'   => $data['job_title'] ?? '',
            ':company'     => $data['company'] ?? '',
            ':start_date'  => !empty($data['start_date']) ?$data['start_date'] : null,
            ':end_date'    => !empty($data['end_date']) ?$data['end_date'] : null,
            ':description' => $data['description'] ?? null
        ]);
    }
    public function addEducation($cvId, $data) {$sql = "INSERT INTO education (cv_id,degree,school,start_year,end_year)
                                              Values (:cv_id, :degree, :school, :start_year, :end_year)";
                                              
                      $stmt = $this->pdo->prepare($sql);
                      return $stmt->execute([
                        ':cv_id' => $cvId,
                        ':degree' =>$data['degree'] ?? '',
                        ':school' =>$data['school'] ?? '',
                        ':start_year' =>!empty($data['start_year']) ? (int)$data['start_year'] : null,
                        ':end_year' => !empty($data['end_year']) ? (int)$data['end_year'] : null
                      ]);                                             
                }
    public function addSkill($cvId,$SkillName,$level = null) {$sql = "INSERT INTO skills (cv_id,skill_name,level)
                VALUES(:cv_id,:skill_name,:level)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':cv_id' => $cvId,
                ':skill_name' =>$SkillName,
                ':level' => $level
            ]);
    }            
    public function addLanguage($cvId,$languageName, $level = null) {$sql = "INSERT INTO languages (cv_id, language_name, level) 
                VALUES (:cv_id, :language_name, :level)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':cv_id'         => $cvId,
            ':language_name' => $languageName,
            ':level'         => $level
        ]);
    }
    public function saveGeneratedPdf($cvId,$fileName, $filePath) {$sql = "INSERT INTO generated_pdfs (cv_id, file_name, file_path) 
                VALUES (:cv_id, :file_name, :file_path)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':cv_id'     => $cvId,
            ':file_name' => $fileName,
            ':file_path' => $filePath
        ]);
    }
    public function getCvById($cvId){
        $stmt =$this->pdo->prepare("SELECT * FROM cv WHERE cv_id = :cv_id");
        $stmt->execute([':cv_id' =>$cvId]);
        $cv =$stmt->fetch();
        if(!$cv){
            return null;
        }
        $stmtExp = $this->pdo->prepare("SELECT * FROM experiences where cv_id = :cv_id ORDER BY start_date DESC");
        $stmtExp ->execute([':cv_id' => $cvId]);
        $cv['experiences'] =$stmtExp->fetchAll();

        $stmtEdu = $this->pdo->prepare("SELECT * FROM education  where cv_id = :cv_id ORDER BY start_year DESC" );
        $stmtEdu -> execute([':cv_id' =>$cvId]);
        $cv['education'] =$stmtEdu->fetchAll();

        $stmtSkills = $this->pdo->prepare("SELECT * FROM skills where cv_id= :cv_id");
        $stmtSkills ->execute([':cv_id' => $cvId]);
        $cv['skills'] =$stmtSkills->fetchAll();

        $stmtLang =$this->pdo->prepare("SELECT * FROM languages WHERE cv_id= :cv_id");
        $stmtLang->execute([':cv_id' =>$cvId]);
        $cv['languages'] =$stmtLang->fetchAll();
        return $cv;
    }
   public function getCvsBySession($sessionId) {
        $stmt = $this->pdo->prepare("SELECT * FROM cv WHERE session_id = :session_id ORDER BY create_at DESC");
        $stmt->execute([':session_id' => $sessionId]);
        return $stmt->fetchAll();
    }
    public function getAllTemplates(){
        $stmt = $this->pdo->query("SELECT * FROM templates");
        return $stmt->fetchall();
    }
}
?>
