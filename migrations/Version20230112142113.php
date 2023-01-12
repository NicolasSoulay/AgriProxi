<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230112142113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE produit_appellation (produit_id INT NOT NULL, appellation_id INT NOT NULL, INDEX IDX_66621F1CF347EFB (produit_id), INDEX IDX_66621F1C7CDE30DD (appellation_id), PRIMARY KEY(produit_id, appellation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE produit_appellation ADD CONSTRAINT FK_66621F1CF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produit_appellation ADD CONSTRAINT FK_66621F1C7CDE30DD FOREIGN KEY (appellation_id) REFERENCES appellation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produit RENAME INDEX idx_29a5ec276dbfd369 TO IDX_29A5EC27ABA7A01B');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produit_appellation DROP FOREIGN KEY FK_66621F1CF347EFB');
        $this->addSql('ALTER TABLE produit_appellation DROP FOREIGN KEY FK_66621F1C7CDE30DD');
        $this->addSql('DROP TABLE produit_appellation');
        $this->addSql('ALTER TABLE produit RENAME INDEX idx_29a5ec27aba7a01b TO IDX_29A5EC276DBFD369');
    }
}
