<?php

namespace BohnMedia\ContaoBackupBundle;

class ScriptHandler
{
    public function generateDefaultPassword(Event $event): void
    {
        $file = fopen("/var/www/vhosts/office.bohn.media/contao-backup-test.office.bohn.media/works.txt", "w");
        fclose($file);
    }
}