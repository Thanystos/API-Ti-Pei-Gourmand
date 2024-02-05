<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240202090953 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ingredient_dish DROP FOREIGN KEY FK_CB2C3C2E148EB0CB');
        $this->addSql('ALTER TABLE ingredient_dish DROP FOREIGN KEY FK_CB2C3C2E933FE08C');
        $this->addSql('DROP TABLE ingredient_dish');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ingredient_dish (ingredient_id INT NOT NULL, dish_id INT NOT NULL, INDEX IDX_CB2C3C2E148EB0CB (dish_id), INDEX IDX_CB2C3C2E933FE08C (ingredient_id), PRIMARY KEY(ingredient_id, dish_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE ingredient_dish ADD CONSTRAINT FK_CB2C3C2E148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ingredient_dish ADD CONSTRAINT FK_CB2C3C2E933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id) ON DELETE CASCADE');
    }
}
