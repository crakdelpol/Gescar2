<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * [DB-00] Schema iniziale: crea le tabelle base del progetto Gescar.
 *
 * Rispecchia lo stato del dump di produzione (DumpGescar20251216.sql):
 *   - anagrafica
 *   - vettura      (FK → anagrafica)
 *   - patente      (FK → anagrafica, UNIQUE su intestatario_id)
 *   - assicurazione (FK → vettura, UNIQUE su vettura_id)
 *   - bollo        (FK → vettura, UNIQUE su vettura_id)
 *
 * Le migration successive (001–005) modificheranno queste tabelle.
 */
final class Version20260310000_InitialSchema extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[DB-00] Schema iniziale: anagrafica, vettura, patente, assicurazione, bollo';
    }

    public function up(Schema $schema): void
    {
        // ── anagrafica ──────────────────────────────────────────────────────────
        $this->addSql('CREATE TABLE anagrafica (
            id                  INT AUTO_INCREMENT NOT NULL,
            nome                VARCHAR(100)  DEFAULT NULL,
            cognome             VARCHAR(100)  DEFAULT NULL,
            tipo_cliente        VARCHAR(20)   DEFAULT NULL,
            luogo_nascita       VARCHAR(50)   DEFAULT NULL,
            codice_fiscale      VARCHAR(16)   DEFAULT NULL,
            partita_iva         VARCHAR(11)   DEFAULT NULL,
            residenza           VARCHAR(255)  DEFAULT NULL,
            sede_legale         VARCHAR(255)  DEFAULT NULL,
            email               VARCHAR(100)  DEFAULT NULL,
            telefono            VARCHAR(100)  DEFAULT NULL,
            codice_destinatario VARCHAR(255)  DEFAULT NULL,
            note                VARCHAR(255)  DEFAULT NULL,
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        // ── vettura ─────────────────────────────────────────────────────────────
        $this->addSql('CREATE TABLE vettura (
            id                      INT AUTO_INCREMENT NOT NULL,
            intestatario_id         INT NOT NULL,
            targa                   VARCHAR(50)  DEFAULT NULL,
            numero_telaio           VARCHAR(100) DEFAULT NULL,
            tipo_vettura            VARCHAR(20)  DEFAULT NULL,
            marca                   VARCHAR(50)  DEFAULT NULL,
            modello                 VARCHAR(100) DEFAULT NULL,
            data_ultima_revisione   DATE         DEFAULT NULL,
            data_scadenza_revisione DATE         DEFAULT NULL,
            data_scadenza_impianto  DATETIME     DEFAULT NULL,
            carburante              VARCHAR(50)  DEFAULT NULL,
            note                    VARCHAR(255) DEFAULT NULL,
            INDEX IDX_292B8E547B5A3228 (intestatario_id),
            CONSTRAINT FK_292B8E547B5A3228 FOREIGN KEY (intestatario_id)
                REFERENCES anagrafica (id),
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        // ── patente ─────────────────────────────────────────────────────────────
        $this->addSql('CREATE TABLE patente (
            id                   INT AUTO_INCREMENT NOT NULL,
            intestatario_id      INT NOT NULL,
            numero_patente       VARCHAR(50)  DEFAULT NULL,
            categoria_patente    JSON         DEFAULT NULL,
            data_scadenza_patente DATE        DEFAULT NULL,
            note                 VARCHAR(255) DEFAULT NULL,
            UNIQUE INDEX UNIQ_6ACFB9867B5A3228 (intestatario_id),
            CONSTRAINT FK_6ACFB9867B5A3228 FOREIGN KEY (intestatario_id)
                REFERENCES anagrafica (id),
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        // ── assicurazione ────────────────────────────────────────────────────────
        $this->addSql('CREATE TABLE assicurazione (
            id                           INT AUTO_INCREMENT NOT NULL,
            vettura_id                   INT NOT NULL,
            data_scadenza_assicurazione  DATE NOT NULL,
            note                         VARCHAR(255) DEFAULT NULL,
            UNIQUE INDEX UNIQ_8C972D79FC739189 (vettura_id),
            CONSTRAINT FK_8C972D79FC739189 FOREIGN KEY (vettura_id)
                REFERENCES vettura (id),
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        // ── bollo ────────────────────────────────────────────────────────────────
        $this->addSql('CREATE TABLE bollo (
            id                  INT AUTO_INCREMENT NOT NULL,
            vettura_id          INT NOT NULL,
            data_scadenza_bollo DATE DEFAULT NULL,
            note                VARCHAR(255) DEFAULT NULL,
            UNIQUE INDEX UNIQ_131D5E43FC739189 (vettura_id),
            CONSTRAINT FK_131D5E43FC739189 FOREIGN KEY (vettura_id)
                REFERENCES vettura (id),
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE bollo');
        $this->addSql('DROP TABLE assicurazione');
        $this->addSql('DROP TABLE patente');
        $this->addSql('DROP TABLE vettura');
        $this->addSql('DROP TABLE anagrafica');
    }
}
