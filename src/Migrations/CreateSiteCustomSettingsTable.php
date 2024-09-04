<?php
class CreateSiteCustomSettingsTable
{
    public function up(PDO $pdo)
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS site_custom_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            custom_header TEXT,
            custom_style TEXT
        )");
    }

    public function down(PDO $pdo)
    {
        $pdo->exec("DROP TABLE IF EXISTS site_custom_settings");
    }
}
