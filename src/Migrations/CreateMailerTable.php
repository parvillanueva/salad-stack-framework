<?php
class CreateMailerTable
{
    public function up(PDO $pdo)
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS dressing_mailer (
            id INT AUTO_INCREMENT PRIMARY KEY,
            host VARCHAR(255),
            username VARCHAR(255),
            password VARCHAR(255),
            encryption VARCHAR(255),
            port VARCHAR(255),
            address VARCHAR(255)
        )");
    }

    public function down(PDO $pdo)
    {
        $pdo->exec("DROP TABLE IF EXISTS dressing_mailer");
    }
}
