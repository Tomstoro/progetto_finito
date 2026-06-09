-- Schema UNIPR Aula Studio Booking (app-base)

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricola VARCHAR(20) NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    edificio VARCHAR(128) DEFAULT NULL,
    piano INT DEFAULT NULL,
    stato ENUM('attiva', 'chiusa', 'manutenzione') DEFAULT 'attiva',
    capienza INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    numero INT NOT NULL,
    sedie INT NOT NULL DEFAULT 2,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS seats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_id INT NOT NULL,
    numero INT NOT NULL,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE CASCADE
);


-- CREATE TABLE IF NOT EXISTS timeslots (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     room_id INT NOT NULL,
--     data DATE NOT NULL,
--     ora_inizio TIME NOT NULL,
--     ora_fine TIME NOT NULL,
--     FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
-- );

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    room_id INT NOT NULL,
    table_id INT NOT NULL,
    posti_richiesti INT NOT NULL,
    stato ENUM('confermata', 'cancellata') DEFAULT 'confermata',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_prenotazione DATE NOT NULL,
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    indirizzo VARCHAR(255) NOT NULL
);

-- POPOLAMENTO DB DI TEST

INSERT INTO locations (nome, indirizzo) VALUES
('Campus Parma Centro', 'Via Università 12, Parma'),
('Campus Scienze e Tecnologie', 'Parco Area delle Scienze 181/A, Parma');

INSERT INTO rooms (nome, edificio, piano, stato, capienza) VALUES
('Aula Studio A', 'Biblioteca Centrale', 1, 'attiva', 80),
('Aula Studio B', 'Biblioteca Centrale', 2, 'attiva', 60),
('Sala Silenzio', 'Edificio Polifunzionale', 0, 'chiusa', 40);

INSERT INTO tables (room_id, numero, sedie) VALUES
(1, 1, 4),
(1, 2, 4),
(1, 3, 6),
(2, 1, 4),
(2, 2, 2),
(3, 1, 6);

INSERT INTO seats (table_id, numero) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),

(2, 1),
(2, 2),
(2, 3),

(3, 1),
(3, 2);

-- INSERT INTO timeslots (room_id, data, ora_inizio, ora_fine) VALUES
-- (1, '2026-06-09', '09:00:00', '11:00:00'),
-- (1, '2026-06-09', '11:00:00', '13:00:00'),
-- (2, '2026-06-09', '14:00:00', '16:00:00'),
-- (3, '2026-06-09', '16:00:00', '18:00:00');

INSERT INTO students (matricola, nome, cognome, email, password_hash) VALUES
('123456', 'Marco', 'Rossi', 'marco.rossi@student.unipr.it', 'hash1'),
('123457', 'Giulia', 'Bianchi', 'giulia.bianchi@student.unipr.it', 'hash2'),
('123458', 'Luca', 'Verdi', 'luca.verdi@student.unipr.it', 'hash3');

INSERT INTO bookings (
    student_id,
    room_id,
    table_id,
    posti_richiesti,
    stato,
    data_prenotazione,
    ora_inizio,
    ora_fine
) VALUES
(1, 1, 1, 2, 'cancellata', '2026-04-09', '09:00:00', '11:00:00'),
(2, 1, 2, 1, 'confermata', '2026-06-25', '09:00:00', '15:00:00'),
(3, 2, 4, 3, 'confermata', '2026-06-09', '14:00:00', '16:00:00'),
(1, 3, 6, 2, 'cancellata',  '2026-07-03', '16:00:00', '18:00:00');