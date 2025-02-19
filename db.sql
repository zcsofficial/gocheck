CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    contact_number VARCHAR(15) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE users ADD COLUMN is_first_login TINYINT(1) DEFAULT 1;

ALTER TABLE users ADD COLUMN profile_completed TINYINT(1) DEFAULT 0;

CREATE TABLE user_profiles (
    user_id INT PRIMARY KEY,
    profile_photo VARCHAR(255),
    age INT CHECK (age BETWEEN 0 AND 120),
    gender ENUM('Male', 'Female', 'Other'),
    height DECIMAL(5,2),
    height_unit ENUM('cm', 'ft') DEFAULT 'cm',
    weight DECIMAL(5,2),
    weight_unit ENUM('kg', 'lbs') DEFAULT 'kg',
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE medical_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    allergies TEXT,  -- Comma-separated values or JSON format
    additional_info TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE vital_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    systolic_bp INT CHECK (systolic_bp > 0),
    diastolic_bp INT CHECK (diastolic_bp > 0),
    hdl_cholesterol DECIMAL(5,2),
    ldl_cholesterol DECIMAL(5,2),
    fasting_blood_sugar DECIMAL(5,2),
    post_meal_blood_sugar DECIMAL(5,2),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE renal_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    urea DECIMAL(5,2) CHECK (urea BETWEEN 7 AND 20), -- Normal range
    creatinine DECIMAL(5,2) CHECK (creatinine BETWEEN 0.7 AND 1.3),
    uric_acid DECIMAL(5,2) CHECK (uric_acid BETWEEN 3.5 AND 7.2),
    calcium DECIMAL(5,2) CHECK (calcium BETWEEN 8.5 AND 10.5),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
