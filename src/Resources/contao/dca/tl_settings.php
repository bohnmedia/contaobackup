<?php

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] = preg_replace('/(?=\{files_legend\b)/', '{backup_legend},backupKey;', $GLOBALS['TL_DCA']['tl_settings']['palettes']['default']);

$GLOBALS['TL_DCA']['tl_settings']['fields']['backupKey'] = [
    'inputType'               => 'text',
    'eval'                    => ['tl_class'=>'w50']
];