<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * [DB-02] Rimuove UNIQUE su assicurazione.vettura_id e aggiunge campi per storico completo.
 *
 * MySQL non permette DROP INDEX su un indice usato da FK.
 * Soluzione: DROP FK → DROP UNIQUE INDEX → ADD regular INDEX → ADD FK
 */
final class Version20260310002_Assicurazione_Storico extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[DB-02] Assicurazione: rimuove UNIQUE su vettura_id, aggiunge attiva/data_inizio/compagnia/numero_polizza/created_at';
    }

    public function up(Schema $schema): void
    {
        // 1. Rimuovi la FK
        $this->addSql('ALTER TABLE assicurazione DROP FOREIGN KEY FK_8C972D79FC739189');
        // 2. Droppa il UNIQUE index
        $this->addSql('ALTER TABLE assicurazione DROP INDEX UNIQ_8C972D79FC739189');
        // 3. Aggiungi indice normale
        $this->addSql('ALTER TABLE assicurazione ADD INDEX IDX_8C972D79FC739189 (vettura_id)');
        // 4. Ricrea la FK
        $this->addSql('ALTER TABLE assicurazione ADD CONSTRAINT FK_8C972D79FC739189 FOREIGN KEY (vettura_id) REFERENCES vettura (id)');
        // 5. Aggiungi nuovi campi
        $this->addSql('ALTER TABLE assicurazione
            ADD COLUMN attiva         TINYINT(1)   NOT NULL DEFAULT 1  COMMENT "1 = polizza attiva/corrente",
            ADD COLUMN data_inizio    DATE         NULL                COMMENT "Data inizio copertura",
            ADD COLUMN compagnia      VARCHAR(100) NULL,
            ADD COLUMN numero_polizza VARCHAR(100) NULL,
            ADD COLUMN created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
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
        $this->addSql('ALTER TABLE assicurazione DROP INDEX idx_scad_assicurazione');
        $this->addSql('ALTER TABLE assicurazione DROP INDEX idx_assicurazione_attiva');
        // Ripristina UNIQUE
        $this->addSql('ALTER TABLE assicurazione DROP FOREIGN KEY FK_8C972D79FC739189');
        $this->addSql('ALTER TABLE assicurazione DROP INDEX IDX_8C972D79FC739189');
        $this->addSql('ALTER TABLE assicurazione ADD UNIQUE INDEX UNIQ_8C972D79FC739189 (vettura_id)');
        $this->addSql('ALTER TABLE assicurazione ADD CONSTRAINT FK_8C972D79FC739189 FOREIGN KEY (vettura_id) REFERENCES vettura (id)');
    }
}
