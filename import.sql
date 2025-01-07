CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    email VARCHAR(50),
    telephone VARCHAR(15)
);

CREATE TABLE IF NOT EXISTS vehicules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marque VARCHAR(50) NOT NULL,
    modele VARCHAR(50) NOT NULL,
    annee INT,
    client_id INT,
    FOREIGN KEY (client_id) REFERENCES clients(id)
);

CREATE TABLE IF NOT EXISTS rendezvous (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_heure DATETIME NOT NULL,
    vehicule_id INT,
    description TEXT,
    FOREIGN KEY (vehicule_id) REFERENCES vehicules(id)
);

CREATE TABLE IF NOT EXISTS administrateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    token VARCHAR(255) NOT NULL,
    expiration_date DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES administrateurs(id)
);

INSERT INTO administrateurs (username, password_hash) VALUES
('admin1', '$2y$10$TOBO0ipevsQEWJ7oME7iEegjPT7s3HL9K5PJB.qIiXwj1ED2ZhvTi'),
('admin2', '$2y$10$TOBO0ipevsQEWJ7oME7iEegjPT7s3HL9K5PJB.qIiXwj1ED2ZhvTi');

INSERT INTO clients (nom, email, telephone) VALUES
('John Doe', 'john.doe@email.com', '123456789'),
('Jane Smith', 'jane.smith@email.com', '987654321'),
('Bob Johnson', 'bob.johnson@email.com', '555555555');

INSERT INTO vehicules (marque, modele, annee, client_id) VALUES
('Toyota', 'Camry', 2015, 1),
('Honda', 'Civic', 2020, 2),
('Ford', 'Focus', 2018, 3);


INSERT INTO rendezvous (date_heure, vehicule_id, description) VALUES
('2023-12-01 10:00:00', 1, "Entretien régulier"),
('2023-12-15 14:30:00', 2, "Changement d'huile"),
('2023-12-20 09:15:00', 3, "Diagnostic moteur");

INSERT INTO tokens (user_id, token, expiration_date) VALUES
(1, 'abcdef12faketoken3456', '2023-12-31 23:59:59');