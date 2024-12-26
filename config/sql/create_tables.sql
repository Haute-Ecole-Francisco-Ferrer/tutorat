CREATE TABLE IF NOT EXISTS tutor_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    tutor_id INT,
    message TEXT,
    status ENUM('pending','accepted','refused') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    response_message TEXT,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (tutor_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;