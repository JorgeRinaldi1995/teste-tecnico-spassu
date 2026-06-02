<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260602220407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE assunto (codas INT AUTO_INCREMENT NOT NULL, descricao VARCHAR(40) NOT NULL, PRIMARY KEY (codas)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE livro_assunto (livro_codl INT NOT NULL, assunto_codas INT NOT NULL, INDEX IDX_53C2C52AA53550D7 (livro_codl), INDEX IDX_53C2C52A831BDC55 (assunto_codas), PRIMARY KEY (livro_codl, assunto_codas)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE livro_assunto ADD CONSTRAINT FK_53C2C52AA53550D7 FOREIGN KEY (livro_codl) REFERENCES livro (codl)');
        $this->addSql('ALTER TABLE livro_assunto ADD CONSTRAINT FK_53C2C52A831BDC55 FOREIGN KEY (assunto_codas) REFERENCES assunto (codas)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE livro_assunto DROP FOREIGN KEY FK_53C2C52AA53550D7');
        $this->addSql('ALTER TABLE livro_assunto DROP FOREIGN KEY FK_53C2C52A831BDC55');
        $this->addSql('DROP TABLE assunto');
        $this->addSql('DROP TABLE livro_assunto');
    }
}
