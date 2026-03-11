<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * [DB-03] Rimuove UNIQUE su bollo.vettura_id e aggiunge campi per storico e pagamento.
 *
 * MySQL non permette DROP INDEX su un indice usato da FK.
 * Soluzione: DROP FK → DROP UNIQUE INDEX → ADD regular INDEX → ADD FK
 *
 * I ~468 record esistenti non vengono modificati.
 * Avranno attiva = 1 di default.
 */
final class Version20260310001_Bollo_StoricoPagamento extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[DB-03] Bollo: rimuove UNIQUE su vettura_id, aggiunge attiva/importo/super_bollo/pagato/data_pagamento/created_at';
    }

    public function up(Schema $schema): void
    {
        // 1. Rimuovi la FK (che tiene in vita il UNIQUE index)
        $this->addSql('ALTER TABLE bollo DROP FOREIGN KEY FK_131D5E43FC739189');
        // 2. Ora puoi droppare il UNIQUE index
        $this->addSql('ALTER TABLE bollo DROP INDEX UNIQ_131D5E43FC739189');
        // 3. Aggiungi un indice normale al suo posto (la FK ne ha bisogno)
        $this->addSql('ALTER TABLE bollo ADD INDEX IDX_131D5E43FC739189 (vettura_id)');
        // 4. Ricrea la FK sul nuovo indice non-unique
        $this->addSql('ALTER TABLE bollo ADD CONSTRAINT FK_131D5E43FC739189 FOREIGN KEY (vettura_id) REFERENCES vettura (id)');
        // 5. Aggiungi i nuovi campi
        $this->addSql('ALTER TABLE bollo
            ADD COLUMN attiva         TINYINT(1)   NOT NULL DEFAULT 1  COMMENT "1 = bollo attivo/corrente",
            ADD COLUMN importo        DECIMAL(8,2) NULL                COMMENT "Importo bollo in euro",
            ADD COLUMN super_bollo    DECIMAL(8,2) NULL                COMMENT "Eventuale super-bollo in euro",
            ADD COLUMN pagato         TINYINT(1)   NOT NULL DEFAULT 0  COMMENT "1 = pagato",
            ADD COLUMN data_pagamento DATE         NULL,
            ADD COLUMN created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
        ');
        $this->addSql('ALTER TABLE bollo ADD INDEX idx_scad_bollo (data_scadenza_bollo)');
        $this->addSql('ALTER TABLE bollo ADD INDEX idx_bollo_attiva (attiva)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bollo
            DROP COLUMN attiva,
            DROP COLUMN importo,
            DROP COLUMN super_bollo,
            DROP COLUMN pagato,
            DROP COLUMN data_pagamento,
            DROP COLUMN created_at
        ');
        $this->addSql('ALTER TABLE bollo DROP INDEX idx_scad_bollo');
        $this->addSql('ALTER TABLE bollo DROP INDEX idx_bollo_attiva');
        // Ripristina UNIQUE: drop FK → drop index normale → add UNIQUE → ricrea FK
        $this->addSql('ALTER TABLE bollo DROP FOREIGN KEY FK_131D5E43FC739189');
        $this->addSql('ALTER TABLE bollo DROP INDEX IDX_131D5E43FC739189');
        $this->addSql('ALTER TABLE bollo ADD UNIQUE INDEX UNIQ_131D5E43FC739189 (vettura_id)');
        $this->addSql('ALTER TABLE bollo ADD CONSTRAINT FK_131D5E43FC739189 FOREIGN KEY (vettura_id) REFERENCES vettura (id)');
    }
}
