<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230121224928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ligne_devis ADD users_id INT NOT NULL');
        $this->addSql('ALTER TABLE ligne_devis ADD CONSTRAINT FK_888B2F1B67B3B43D FOREIGN KEY (users_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_888B2F1B67B3B43D ON ligne_devis (users_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ligne_devis DROP FOREIGN KEY FK_888B2F1B67B3B43D');
        $this->addSql('DROP INDEX IDX_888B2F1B67B3B43D ON ligne_devis');
        $this->addSql('ALTER TABLE ligne_devis DROP users_id');
    }
}
