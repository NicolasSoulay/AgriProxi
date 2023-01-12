<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230112171100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            "INSERT INTO departement (name) VALUES
                ('Ain'),
                ('Aisne'),
                ('Allier'),
                ('Alpes-de-Haute-Provence'),
                ('Hautes-Alpes'),
                ('Alpes-Maritimes'),
                ('Ardèche'),
                ('Ardennes'),
                ('Ariège'),
                ('Aube'),
                ('Aude'),
                ('Aveyron'),
                ('Bouches-du-Rhône'),
                ('Calvados'),
                ('Cantal'),
                ('Charente'),
                ('Charente-Maritime'),
                ('Cher'),
                ('Corrèze'),
                ('Corse-du-sud'),
                ('Haute-corse'),
                ('Côte-d''or'),
                ('Côtes-d''armor'),
                ('Creuse'),
                ('Dordogne'),
                ('Doubs'),
                ('Drôme'),
                ('Eure'),
                ('Eure-et-Loir'),
                ('Finistère'),
                ('Gard'),
                ('Haute-Garonne'),
                ('Gers'),
                ('Gironde'),
                ('Hérault'),
                ('Ile-et-Vilaine'),
                ('Indre'),
                ('Indre-et-Loire'),
                ('Isère'),
                ('Jura'),
                ('Landes'),
                ('Loir-et-Cher'),
                ('Loire'),
                ('Haute-Loire'),
                ('Loire-Atlantique'),
                ('Loiret'),
                ('Lot'),
                ('Lot-et-Garonne'),
                ('Lozère'),
                ('Maine-et-Loire'),
                ('Manche'),
                ('Marne'),
                ('Haute-Marne'),
                ('Mayenne'),
                ('Meurthe-et-Moselle'),
                ('Meuse'),
                ('Morbihan'),
                ('Moselle'),
                ('Nièvre'),
                ('Nord'),
                ('Oise'),
                ('Orne'),
                ('Pas-de-Calais'),
                ('Puy-de-Dôme'),
                ('Pyrénées-Atlantiques'),
                ('Hautes-Pyrénées'),
                ('Pyrénées-Orientales'),
                ('Bas-Rhin'),
                ('Haut-Rhin'),
                ('Rhône'),
                ('Haute-Saône'),
                ('Saône-et-Loire'),
                ('Sarthe'),
                ('Savoie'),
                ('Haute-Savoie'),
                ('Paris'),
                ('Seine-Maritime'),
                ('Seine-et-Marne'),
                ('Yvelines'),
                ('Deux-Sèvres'),
                ('Somme'),
                ('Tarn'),
                ('Tarn-et-Garonne'),
                ('Var'),
                ('Vaucluse'),
                ('Vendée'),
                ('Vienne'),
                ('Haute-Vienne'),
                ('Vosges'),
                ('Yonne'),
                ('Territoire de Belfort'),
                ('Essonne'),
                ('Hauts-de-Seine'),
                ('Seine-Saint-Denis'),
                ('Val-de-Marne'),
                ('Val-d''oise'),
                ('Mayotte'),
                ('Guadeloupe'),
                ('Guyane'),
                ('Martinique'),
                ('Réunion')
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM departement');
    }
}
