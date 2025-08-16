BEGIN TRANSACTION;
CREATE TABLE IF NOT EXISTS "auditoria_midias" (
	"id"	INT AUTO_INCREMENT NOT NULL,
	"midia_id"	INT NOT NULL,
	"usuario_id"	INT NOT NULL,
	"acao"	VARCHAR(50) NOT NULL,
	"detalhes_alteracao"	TEXT,
	"timestamp"	TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY("id"),
	FOREIGN KEY("midia_id") REFERENCES "midias"("id"),
	FOREIGN KEY("usuario_id") REFERENCES "users"("id")
);
CREATE TABLE IF NOT EXISTS "linhas" (
	"id"	INT AUTO_INCREMENT,
	"nome"	VARCHAR(255) NOT NULL UNIQUE,
	PRIMARY KEY("id")
);
CREATE TABLE IF NOT EXISTS "midias" (
	"id"	INT AUTO_INCREMENT NOT NULL,
	"titulo"	VARCHAR(255) NOT NULL,
	"link"	VARCHAR(255),
	"plataforma_id"	INT,
	"status_id"	INT NOT NULL,
	"responsavel_id"	INT NOT NULL,
	"linha_id"	INT NOT NULL,
	"sistema_id"	INT NOT NULL,
	"created_at"	TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY("id"),
	FOREIGN KEY("linha_id") REFERENCES "linhas"("id"),
	FOREIGN KEY("plataforma_id") REFERENCES "plataformas"("id"),
	FOREIGN KEY("responsavel_id") REFERENCES "users"("id"),
	FOREIGN KEY("sistema_id") REFERENCES "sistemas"("id"),
	FOREIGN KEY("status_id") REFERENCES "status_midia"("id")
);
CREATE TABLE IF NOT EXISTS "plataformas" (
	"id"	INT AUTO_INCREMENT,
	"nome"	VARCHAR(50) NOT NULL UNIQUE,
	PRIMARY KEY("id")
);
CREATE TABLE IF NOT EXISTS "sistemas" (
	"id"	INT AUTO_INCREMENT,
	"nome"	VARCHAR(255) NOT NULL UNIQUE,
	"linha_id"	INT NOT NULL,
	PRIMARY KEY("id"),
	FOREIGN KEY("linha_id") REFERENCES "linhas"("id")
);
CREATE TABLE IF NOT EXISTS "status_midia" (
	"id"	INT AUTO_INCREMENT,
	"nome"	VARCHAR(50) NOT NULL UNIQUE,
	PRIMARY KEY("id")
);
CREATE TABLE IF NOT EXISTS "user_linhas" (
	"user_id"	INT NOT NULL,
	"linha_id"	INT NOT NULL,
	PRIMARY KEY("user_id","linha_id"),
	FOREIGN KEY("linha_id") REFERENCES "linhas"("id"),
	FOREIGN KEY("user_id") REFERENCES "users"("id")
);
CREATE TABLE IF NOT EXISTS "usuarios" (
	"id"	INTEGER,
	"usuario"	TEXT NOT NULL,
	"senha"	TEXT NOT NULL,
	"nivel_permissao"	TEXT NOT NULL,
	PRIMARY KEY("id" AUTOINCREMENT)
);
COMMIT;
