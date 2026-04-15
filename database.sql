  -- Create database
CREATE DATABASE IF NOT EXISTS knowledge_tribe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE knowledge_tribe;

-- Users table (FR-3: Password hashing, FR-4: RBAC)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('admin', 'student') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Courses table
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE
);

-- Students table (FR-10, FR-14, FR-31)
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    dob DATE NULL,
    gender ENUM('male', 'female', 'other') NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    city VARCHAR(50) NULL,
    pin_code VARCHAR(20) NULL,
    country VARCHAR(50) NULL,
    hobbies TEXT NULL,
    image VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Student-Course enrollment (junction table)
CREATE TABLE student_courses (
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (student_id, course_id),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Qualifications table
CREATE TABLE qualifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    level ENUM('10th', '12th', 'Graduation') NOT NULL,
    board VARCHAR(100) NOT NULL,
    percentage VARCHAR(10) NOT NULL,
    year_of_passing VARCHAR(4) NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Insert default admin (FR-4: Admin role)
-- Password: Admin123 (hashed)
INSERT INTO users (username, password, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@knowledgetribe.com', 'admin');

-- Insert courses (matching your HTML dropdown)
INSERT INTO courses (id, name, description, start_date, end_date) VALUES 
(1, 'Adobe Family', 'Master Adobe Creative Suite', '2024-03-21', '2024-06-21'),
(2, 'Graphics Design', 'Learn graphic design principles', '2024-03-21', '2024-06-21'),
(3, 'Basic Computer Skills', 'Computer fundamentals', '2024-03-21', '2024-06-21'),
(4, 'Web Development', 'Full-stack web development', '2024-03-21', '2024-07-21'),
(5, 'AutoCAD', '2D and 3D design with AutoCAD', '2024-03-21', '2024-07-21'),
(6, 'Python', 'Python programming from scratch', '2024-03-21', '2024-08-21'),
(7, 'Java', 'Java programming and OOP', '2024-03-21', '2024-09-21'),
(8, 'C++', 'C++ programming fundamentals', '2024-03-21', '2024-05-21');
