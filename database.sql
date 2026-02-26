CREATE DATABASE IF NOT EXISTS knowledge_tribe;
USE knowledge_tribe;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'student') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE
);

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    dob DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    pin_code VARCHAR(20),
    country VARCHAR(50),
    hobbies TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS student_courses (
    student_id INT,
    course_id INT,
    PRIMARY KEY (student_id, course_id),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS qualifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    level VARCHAR(50), -- e.g., '10th', '12th', 'Graduation'
    board VARCHAR(100),
    percentage DECIMAL(5,2),
    year_of_passing INT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Insert some initial courses
INSERT INTO courses (name, start_date, end_date) VALUES 
('Adobe Family', '2024-03-21', '2024-06-21'),
('Graphics Design', '2024-03-21', '2024-06-21'),
('Basic Computer Skills', '2024-03-21', '2024-06-21'),
('Web Development', '2024-03-21', '2024-07-21'),
('AutoCAD', '2024-03-21', '2024-07-21'),
('Python', '2024-03-21', '2024-08-21'),
('Java', '2024-03-21', '2024-09-21'),
('C++', '2024-03-21', '2024-05-21');
