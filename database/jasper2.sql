-- Create Database
CREATE DATABASE jasper2;
USE jasper2;

-- Departments Table
CREATE TABLE Departments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- Sections Table
CREATE TABLE Sections (
    section_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    department_id INT DEFAULT NULL,  
    FOREIGN KEY (department_id) REFERENCES Departments(department_id) ON DELETE SET NULL
);

-- Users Table
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'professor', 'student') NOT NULL,
    department_id INT DEFAULT NULL,
    section_id INT DEFAULT NULL,
    FOREIGN KEY (department_id) REFERENCES Departments(department_id) ON DELETE SET NULL,
    FOREIGN KEY (section_id) REFERENCES Sections(section_id) ON DELETE SET NULL
);


-- NEW: Table for linking professors to multiple departments
CREATE TABLE Professor_Departments (
    professor_id INT,
    department_id INT,
    PRIMARY KEY (professor_id, department_id),
    FOREIGN KEY (professor_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES Departments(department_id) ON DELETE CASCADE
);

-- NEW: Table for linking professors to multiple sections
CREATE TABLE Professor_Sections (
    professor_id INT,
    section_id INT,
    PRIMARY KEY (professor_id, section_id),
    FOREIGN KEY (professor_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES Sections(section_id) ON DELETE CASCADE
);
CREATE TABLE Assessments (
    assessment_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    type ENUM('exam', 'quiz', 'assignment') NOT NULL DEFAULT 'quiz',
    professor_id INT NOT NULL,
    department_id INT DEFAULT NULL,
    assessment_time INT NOT NULL, -- Adding assessment time in minutes
    FOREIGN KEY (professor_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES Departments(department_id) ON DELETE SET NULL
);


-- Questions Table
CREATE TABLE Questions (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT DEFAULT NULL,
    question_text TEXT NOT NULL,
    type ENUM('mcq', 'short_answer') NOT NULL,
    FOREIGN KEY (assessment_id) REFERENCES Assessments(assessment_id) ON DELETE SET NULL
);

-- Answers Table
CREATE TABLE Answers (
    answer_id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT DEFAULT NULL,
    answer_text TEXT NOT NULL,
    is_correct BOOLEAN NOT NULL,
    FOREIGN KEY (question_id) REFERENCES Questions(question_id) ON DELETE SET NULL
);

-- Results Table
CREATE TABLE Results (
    result_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT DEFAULT NULL,
    assessment_id INT DEFAULT NULL,
    score INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES Users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (assessment_id) REFERENCES Assessments(assessment_id) ON DELETE SET NULL
);


CREATE TABLE Assessment_Sections (
    assessment_id INT NOT NULL,
    section_id INT NOT NULL,
    PRIMARY KEY (assessment_id, section_id),
    FOREIGN KEY (assessment_id) REFERENCES Assessments(assessment_id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES Sections(section_id) ON DELETE CASCADE
);

CREATE TABLE Assessment_Departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT,
    department_id INT,
    FOREIGN KEY (assessment_id) REFERENCES Assessments(assessment_id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES Departments(department_id) ON DELETE CASCADE
);


CREATE TABLE Submissions (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    assessment_id INT NOT NULL,
    submission_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (assessment_id) REFERENCES Assessments(assessment_id) ON DELETE CASCADE
);

---tangina hahaha daming table