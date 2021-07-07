# ContaoBackupBundle

Hierbei handelt es sich um eine Hilfsklasse die dazu dient, Backups einer Contao-Instanz zu erstellen. In den Einstellungen hat man die Möglichkeit, einen Backup-Key zu erstellen, der dann folgendermaßen verwendet werden kann.

### Liste

```
https://[HOSTNAME]]/contaobackup/list?key=[BACKUP-KEY]
```

Gibt eine Liste aller zum Download stehenden Dateien samt MD5-Hash zurück. Auf diese Weise kann im Falle von Caching vor dem Download entschieden werden, ob diese erneut herunter geladen werden muss.

### Datei

```
https://[HOSTNAME]]/contaobackup/file?&name=[DATEINAME]&key=[BACKUP-KEY]
```

Lädt eine Datei herunter. Der Pfad ist in diesem Fall relativ zum Root-Dir der Contao-Instanz.

### Datenbank

```
https://[HOSTNAME]]/contaobackup/dumpdb?&key=[BACKUP-KEY]
```

Lädt einen Dump der Datenbank herunter.

Backups können mit Hilfe des NodeJS-Scripts [bohnmedia/backupserver](https://github.com/bohnmedia/backupserver) automatisiert herunter geladen werden.
