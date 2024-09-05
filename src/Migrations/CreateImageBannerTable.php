<?php
class CreateImageBannerTable
{
    public function up(PDO $pdo)
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS dressing_image_banner (
            id INT AUTO_INCREMENT PRIMARY KEY,
            image_path TEXT,
            status INT DEFAULT 0
        )");
    }

    public function down(PDO $pdo)
    {
        $pdo->exec("DROP TABLE IF EXISTS dressing_image_banner");
    }
}
