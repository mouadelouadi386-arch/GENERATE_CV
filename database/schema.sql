CREATE DATABASE cv_generator;
USE cv_generator;
CREATE TABLE templates(
    template_id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(50) NOT NULL
);
CREATE TABLE cv(
    cv_id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    template_id INT DEFAULT 1,
    full_name varchar(50) not null,
    email varchar(200) not null,
    title VARCHAR(255),
    photo VARCHAR(255),
    phone VARCHAR(20),
    address VARCHAR(150),
    about_me TEXT,
    age INT,
    school VARCHAR(150),
    school_field VARCHAR(150),
    school_year VARCHAR(20),
    bac VARCHAR(150),
    bac_field VARCHAR(150),
    bac_year VARCHAR(20),
    create_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    update_at DATETIME DEFAULT CURRENT_TIMESTAMP on UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES templates(template_id) ON DELETE SET NULL
);
CREATE TABLE experiences(
    experience_id INT AUTO_INCREMENT PRIMARY KEY,
    cv_id INT NOT NULL,
    job_title VARCHAR(100) NOT NULL,
    company VARCHAR(100) NOT NULL,
    start_date DATE,
    end_date DATE,
    description TEXT,
    FOREIGN KEY (cv_id) REFERENCES cv(cv_id) on DELETE CASCADE
);
CREATE TABLE education (
    education_id INT AUTO_INCREMENT PRIMARY KEY,
    cv_id INT NOT NULL,
    degree VARCHAR(100) NOT NULL,
    school VARCHAR(100) NOT NULL,
    start_year INT,
    end_year INT,
    FOREIGN KEY (cv_id) REFERENCES cv(cv_id) ON DELETE CASCADE
);
CREATE TABLE skills (
    skill_id INT AUTO_INCREMENT PRIMARY KEY,
    cv_id INT NOT NULL,
    skill_name VARCHAR(250) NOT NULL,
    level VARCHAR(50),
    FOREIGN KEY (cv_id) REFERENCES cv(cv_id) ON DELETE CASCADE
);
CREATE TABLE languages (
    language_id INT AUTO_INCREMENT PRIMARY KEY,
    cv_id INT NOT NULL,
    language_name VARCHAR(50) NOT NULL,
    level VARCHAR(50),
    FOREIGN KEY (cv_id) REFERENCES cv(cv_id) ON DELETE CASCADE
);
CREATE TABLE generated_pdfs (
    pdf_id INT AUTO_INCREMENT PRIMARY KEY,
    cv_id INT NOT NULL,
    file_name VARCHAR(150),
    file_path VARCHAR(255),
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cv_id) REFERENCES cv(cv_id) ON DELETE CASCADE
);

INSERT INTO templates (template_id, template_name) VALUES
    (1, 'Classique'),
    (2, 'Moderne'),
    (3, 'Minimaliste'),
    (4, 'Créatif'),
    (5, 'Professionnel'),
    (6, 'Élégant');
