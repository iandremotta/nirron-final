<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220910043848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE configuracoes_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE configuracoes (id INT NOT NULL, path VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE solicitacao (id INT NOT NULL, usuario_id INT NOT NULL, aprovador_id INT DEFAULT NULL, administrador_id INT DEFAULT NULL, recusador_id INT DEFAULT NULL, titulo VARCHAR(255) NOT NULL, empresa VARCHAR(255) NOT NULL, nota_fiscal VARCHAR(255) DEFAULT NULL, valor VARCHAR(255) NOT NULL, tipo SMALLINT NOT NULL, vencimento TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, justificativa TEXT DEFAULT NULL, recusa TEXT DEFAULT NULL, status SMALLINT NOT NULL, image_name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A84F9E16DB38439E ON solicitacao (usuario_id)');
        $this->addSql('CREATE INDEX IDX_A84F9E16D0A9A988 ON solicitacao (aprovador_id)');
        $this->addSql('CREATE INDEX IDX_A84F9E1648DFEBB7 ON solicitacao (administrador_id)');
        $this->addSql('CREATE INDEX IDX_A84F9E16CDECD250 ON solicitacao (recusador_id)');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, username VARCHAR(180) NOT NULL, nome VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON "user" (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64954BD530C ON "user" (nome)');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE solicitacao ADD CONSTRAINT FK_A84F9E16DB38439E FOREIGN KEY (usuario_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE solicitacao ADD CONSTRAINT FK_A84F9E16D0A9A988 FOREIGN KEY (aprovador_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE solicitacao ADD CONSTRAINT FK_A84F9E1648DFEBB7 FOREIGN KEY (administrador_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE solicitacao ADD CONSTRAINT FK_A84F9E16CDECD250 FOREIGN KEY (recusador_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE configuracoes_id_seq CASCADE');
        $this->addSql('ALTER TABLE solicitacao DROP CONSTRAINT FK_A84F9E16DB38439E');
        $this->addSql('ALTER TABLE solicitacao DROP CONSTRAINT FK_A84F9E16D0A9A988');
        $this->addSql('ALTER TABLE solicitacao DROP CONSTRAINT FK_A84F9E1648DFEBB7');
        $this->addSql('ALTER TABLE solicitacao DROP CONSTRAINT FK_A84F9E16CDECD250');
        $this->addSql('DROP TABLE configuracoes');
        $this->addSql('DROP TABLE solicitacao');
        $this->addSql('DROP TABLE "user"');
    }
}
