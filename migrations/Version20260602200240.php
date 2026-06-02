<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260602200240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE autor (codau INT AUTO_INCREMENT NOT NULL, nome VARCHAR(40) NOT NULL, PRIMARY KEY (codau)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE livro_autor (livro_codl INT NOT NULL, autor_codau INT NOT NULL, INDEX IDX_6749992A53550D7 (livro_codl), INDEX IDX_67499926696BB47 (autor_codau), PRIMARY KEY (livro_codl, autor_codau)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE livro_autor ADD CONSTRAINT FK_6749992A53550D7 FOREIGN KEY (livro_codl) REFERENCES livro (codl)');
        $this->addSql('ALTER TABLE livro_autor ADD CONSTRAINT FK_67499926696BB47 FOREIGN KEY (autor_codau) REFERENCES autor (codau)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE livro_autor DROP FOREIGN KEY FK_6749992A53550D7');
        $this->addSql('ALTER TABLE livro_autor DROP FOREIGN KEY FK_67499926696BB47');
        $this->addSql('DROP TABLE autor');
        $this->addSql('DROP TABLE livro_autor');
    }
}
