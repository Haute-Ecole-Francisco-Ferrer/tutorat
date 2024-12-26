CREATE TABLE IF NOT EXISTS tutor_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tutee_id INT NOT NULL,
    tutor_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tutee_id) REFERENCES users(id),
    FOREIGN KEY (tutor_id) REFERENCES users(id),
    CONSTRAINT unique_tutor_tutee UNIQUE (tutee_id, tutor_id)
);