CREATE DATABASE IF NOT EXISTS shirt_business;
USE shirt_business;

CREATE TABLE IF NOT EXISTS karigars (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(20),
  address TEXT
);

CREATE TABLE IF NOT EXISTS work_entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  karigar_id INT NOT NULL,
  date DATE,
  category VARCHAR(100),
  quantity INT,
  price DECIMAL(10,2),
  total DECIMAL(10,2),
  FOREIGN KEY (karigar_id) REFERENCES karigars(id) ON DELETE CASCADE
);
