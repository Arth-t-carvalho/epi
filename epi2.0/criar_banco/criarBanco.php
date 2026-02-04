-- ===============================
-- BANCO DE DADOS
-- ===============================
CREATE DATABASE IF NOT EXISTS epi_guard
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE epi_guard;

-- ===============================
-- TABELA DE CARGOS
-- ===============================
CREATE TABLE IF NOT EXISTS cargos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_cargo VARCHAR(50) NOT NULL UNIQUE
);

-- ===============================
-- TABELA DE USUÁRIOS
-- ===============================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cargo_id INT NOT NULL,

    FOREIGN KEY (cargo_id) REFERENCES cargos(id)
);

-- ===============================
-- TABELA DE CURSOS
-- ===============================
CREATE TABLE IF NOT EXISTS cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    sigla VARCHAR(20),
    qtd_alunos INT DEFAULT 0,
    professor_id INT,

    FOREIGN KEY (professor_id) REFERENCES usuarios(id)
);

-- ===============================
-- TABELA DE ALUNOS
-- ===============================
CREATE TABLE IF NOT EXISTS alunos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    curso_id INT NOT NULL,
    professor_id INT,
    turno VARCHAR(20),
    foto VARCHAR(255),

    FOREIGN KEY (curso_id) REFERENCES cursos(id),
    FOREIGN KEY (professor_id) REFERENCES usuarios(id)
);

-- ===============================
-- TABELA DE EPIs
-- ===============================
CREATE TABLE IF NOT EXISTS epis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE
);

-- ===============================
-- TABELA DE INFRAÇÕES
-- ===============================
CREATE TABLE IF NOT EXISTS infracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    curso_id INT NOT NULL,
    data_hora DATETIME NOT NULL,
    foto VARCHAR(255) NOT NULL,
    status VARCHAR(30) DEFAULT 'Pendente',

    FOREIGN KEY (aluno_id) REFERENCES alunos(id),
    FOREIGN KEY (curso_id) REFERENCES cursos(id)
);

-- ===============================
-- INFRAÇÃO x EPIs
-- ===============================
CREATE TABLE IF NOT EXISTS infracao_epis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    infracao_id INT NOT NULL,
    epi_id INT NOT NULL,

    FOREIGN KEY (infracao_id) REFERENCES infracoes(id),
    FOREIGN KEY (epi_id) REFERENCES epis(id)
);

-- ===============================
-- TABELA DE OCORRÊNCIAS
-- ===============================
CREATE TABLE IF NOT EXISTS ocorrencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    infracao_id INT NOT NULL,
    usuario_id INT NOT NULL,
    tipo VARCHAR(50),
    observacao TEXT,
    data_registro DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (infracao_id) REFERENCES infracoes(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
