<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * [DB-02] Rimuove UNIQUE su assicurazione.vettura_id e aggiunge campi per storico completo.
 */
final class Version20260310002_Assicurazione_Storico extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[DB-02] Assicurazione: rimuove UNIQUE su vettura_id, aggiunge attiva/data_inizio/compagnia/numero_polizza/created_at';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assicurazione DROP INDEX IF EXISTS UNIQ_8C972D79FC739189');
        $this->addSql('ALTER TABLE assicurazione
            ADD COLUMN attiva          TINYINT(1)   NOT NULL DEFAULT 1     COMMENT "1 = polizza attiva/corrente",
            ADD COLUMN data_inizio     DATE         NULL                   COMMENT "Data inizio copertura",
            ADD COLUMN compagnia       VARCHAR(100) NULL,
            ADD COLUMN numero_polizza  VARCHAR(100) NULL,
            ADD COLUMN created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
        ');
        $this->addSql('ALTER TABLE assicurazione ADD INDEX idx_scad_assicurazione (data_scadenza_assicurazione)');
        $this->addSql('ALTER TABLE assicurazione ADD INDEX idx_assicurazione_attiva (attiva)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assicurazione
            DROP COLUMN attiva,
            DROP COLUMN data_inizio,
            DROP COLUMN compagnia,
            DROP COLUMN numero_polizza,
            DROP COLUMN created_at
        ');
        $this->addSql('ALTER TABLE assicurazione DROP INDEX IF EXISTS idx_scad_assicurazione');
        $this->addSql('ALTER TABLE assicurazione DROP INDEX IF EXISTS idx_assicurazione_attiva');
        $this->addSql('ALTER TABLE assicurazione ADD UNIQUE INDEX UNIQ_8C972D79FC739189 (vettura_id)');
    }
}
