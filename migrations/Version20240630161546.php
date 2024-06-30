<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240630161546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE diet (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE diet_product (diet_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_23B95EF1E1E13ACE (diet_id), INDEX IDX_23B95EF14584665A (product_id), PRIMARY KEY(diet_id, product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE diet_product ADD CONSTRAINT FK_23B95EF1E1E13ACE FOREIGN KEY (diet_id) REFERENCES diet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE diet_product ADD CONSTRAINT FK_23B95EF14584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE diet_product DROP FOREIGN KEY FK_23B95EF1E1E13ACE');
        $this->addSql('ALTER TABLE diet_product DROP FOREIGN KEY FK_23B95EF14584665A');
        $this->addSql('DROP TABLE diet');
        $this->addSql('DROP TABLE diet_product');
    }
}
