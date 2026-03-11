<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * [TECH-02] Aggiunge indici sulle colonne di scadenza per migliorare le performance
 * delle query di dashboard che filtrano per data.
 *
 * [FEAT-03] Aggiunge campo esente_revisione su vettura.
 */
final class Version20260310005_Vettura_Indici extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[TECH-02 + FEAT-03] Indici su date scadenza + campo esente_revisione su vettura';
    }

    public function up(Schema $schema): void
    {
        // Indici performance
        $this->addSql('ALTER TABLE vettura ADD INDEX idx_scad_revisione (data_scadenza_revisione)');
        $this->addSql('ALTER TABLE patente  ADD INDEX idx_scad_patente   (data_scadenza_patente)');

        // Campo esente_revisione (veicoli con "NO REVISIONE")
        $this->addSql('ALTER TABLE vettura
            ADD COLUMN esente_revisione TINYINT(1) NOT NULL DEFAULT 0 COMMENT "1 = veicolo escluso dal calendario revisioni"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vettura DROP INDEX IF EXISTS idx_scad_revisione');
        $this->addSql('ALTER TABLE patente  DROP INDEX IF EXISTS idx_scad_patente');
        $this->addSql('ALTER TABLE vettura  DROP COLUMN esente_revisione');
    }
}
