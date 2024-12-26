CREATE TABLE IF NOT EXISTS tutoring_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tutor_id INT NOT NULL,
    tutee_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'refused') DEFAULT 'pending',
    request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    response_date DATETIME NULL,
    FOREIGN KEY (tutor_id) REFERENCES users(id),
    FOREIGN KEY (tutee_id) REFERENCES users(id)
);