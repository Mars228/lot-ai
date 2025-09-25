# Analizy Gier Liczbowych

Aplikacja do analiz gier liczbowych (Lotto, Multi Multi, EuroJackpot)

## INFORMACJE PODSTAWOWE

### Środowisko

Lokalne, bez autentykacji

Wersja serwera: Apache 2.4.58
PHP Version: 8.4.11

- Zend Engine v4.4.12

- Zend OPcache v8.4.12

- Xdebug v3.4.5,

MySQL 8.0.43

### Technologia

- **Backend:** CodeIgniter 4.6.x
- **Database:** MySQL
- **Frontend:** Bootstrap 5.3.x, jQuery 3.7.x, AdminLTE v4.0.0-rc4
- **LOTTO OpenAPI key:** LlQ2nogAEteK1zd7SkD5IGyPQt7phMdeVr0ZLLfHdiE=

### Konwencje

- **Nazewnictwo:** klasy/metody/tabele po angielsku, komentarze po polsku
- **CRUD:** Create (add), Read (index), Update (edit), Delete (delete)
- **Daty:** format DD.MM.YYYY
- **Zasady:** DRY, powtarzalne elementy w Helpers

## DB – dump i restore

`schema.sql` – **AUTO-generowany** zrzut struktury (DDL only). Commitujemy po każdej zmianie tabel.
`schema_DESIGN.sql` – **ręcznie pielęgnowany wzorzec** struktury (kolumny, indeksy, FK). Służy jako referencja. 

## MODUŁY / SEKCJE aplikacji

- [HOME](home.md)
- [GRY](gry.md)
- [LOSOWANIA](losowania.md)
- [STATYSTYKI](statystyki.md)
- [STRATEGIE](strategie.md)
- [ZAKŁADY](zaklady.md)
- [WYNIKI](wyniki.md)
- [USTAWIENIA](ustawienia.md)

## STRUKTURA PROJEKTU

```
/app
  /Controllers
    - Home.php
    - Games.php
    - Draws.php
    - Statistics.php
    - [...]
  /Models
    - GameModel.php
    - DrawModel.php
    - [...]
  /Views
      /home
    /games
    /draws
    /[...]
/public
  /assets
  /uploads
```

## Ścieżki zasobów - lokalnie

```
/public
  /assets
    /css
      - site.css
    /fonts
      /fira_sans
      /fontawesome
        /7.0.1      
      /oswald
      /roboto
    /img
      - logo.svg
    /js
      - site.js
    /vendor
      /adminlte
        /4.0.0-rc4    
      /bootstrap
        /5.3.8
      /jquery/
        - jquery-3.7.1.min.js
      /inputmask
      /toastr
        /2.1.4
  /uploads
    /csv
    /image
```

## MODUŁY / SEKCJE aplikacji

- [HOME](home.md)
- [GRY](gry.md)
- [LOSOWANIA](losowania.md)
- [STATYSTYKI](statystyki.md)
- [STRATEGIE](strategie.md)
- [ZAKŁADY](zaklady.md)
- [WYNIKI](wyniki.md)
- [USTAWIENIA](ustawienia.md)

## NOTATKI IMPLEMENTACYJNE

### Helpers:

`lottery_helper.php`

- walidacja sktruktury pliku CSV dla losowań
- Konwertuje numer EuroJackpot między numeracją polską a światową, EuroJackpot 15 września 2017: świat - numer 287, Polska - numer 1
- format polskiej waluty
  
  

`date_helper.php`

- format daty po polsku
  
  

`system_helper.php`

- formatowanie wielkości plików w czytelnej formie
- System info: wersja PHP, wersja CodeIgniter, użycie pamięci, limit pamięci, strefa czasowa, aktualny czas i data
- API status
- DB wielkość
