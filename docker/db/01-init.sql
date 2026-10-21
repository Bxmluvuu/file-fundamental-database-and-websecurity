USE db_lab08;

CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    author VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    price DECIMAL(8,2) NOT NULL,
    isbn VARCHAR(20) NULL,
    publish_year INT NULL,
    stock INT NOT NULL DEFAULT 0
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO books (title, author, category, price, isbn, publish_year, stock)
VALUES ('Network Security', 'Alice Brown', 'Security', 480.50, '9780132350884', 2025, 10);

-- The official image creates this account before running this file.
-- The web app only needs SELECT and INSERT for this exercise.
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'webapp_user'@'%';
GRANT SELECT, INSERT ON db_lab08.books TO 'webapp_user'@'%';
