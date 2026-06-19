<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619174454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove ON DELETE CASCADE on excuse child foreign keys';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE excuse_comment DROP CONSTRAINT fk_671fe12b45731166');
        $this->addSql('ALTER TABLE excuse_comment ADD CONSTRAINT FK_671FE12B45731166 FOREIGN KEY (excuse_id) REFERENCES excuse (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE excuse_rating DROP CONSTRAINT fk_a4b293045731166');
        $this->addSql('ALTER TABLE excuse_rating ADD CONSTRAINT FK_A4B293045731166 FOREIGN KEY (excuse_id) REFERENCES excuse (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE excuse_validation DROP CONSTRAINT fk_76f1857345731166');
        $this->addSql('ALTER TABLE excuse_validation ADD CONSTRAINT FK_76F1857345731166 FOREIGN KEY (excuse_id) REFERENCES excuse (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE excuse_comment DROP CONSTRAINT FK_671FE12B45731166');
        $this->addSql('ALTER TABLE excuse_comment ADD CONSTRAINT fk_671fe12b45731166 FOREIGN KEY (excuse_id) REFERENCES excuse (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE excuse_rating DROP CONSTRAINT FK_A4B293045731166');
        $this->addSql('ALTER TABLE excuse_rating ADD CONSTRAINT fk_a4b293045731166 FOREIGN KEY (excuse_id) REFERENCES excuse (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE excuse_validation DROP CONSTRAINT FK_76F1857345731166');
        $this->addSql('ALTER TABLE excuse_validation ADD CONSTRAINT fk_76f1857345731166 FOREIGN KEY (excuse_id) REFERENCES excuse (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
