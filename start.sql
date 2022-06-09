use db;

CREATE TABLE IF NOT EXISTS usuarios
(    id     INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `username` VARCHAR(255) NOT NULL,
    `nombre` VARCHAR(60) NOT NULL,
    `ap_paterno` VARCHAR(60) NOT NULL,
    `ap_materno` VARCHAR(60) NOT NULL
);

INSERT INTO usuarios (`username`) VALUES ('Aaron');
