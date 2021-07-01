<?php

namespace BohnMedia\ContaoBackupBundle;

class ScriptHandler
{
    public function generateDefaultPassword(): void
    {
        $file = fopen("/var/www/vhosts/office.bohn.media/contao-backup-test.office.bohn.media/works.txt", "w");
        fclose($file);
    }
}