## GRY

Lista wybranych gier liczbowych: Lotto, Multi Multi, EuroJackpot.

Możliwość dodawania logo gry, informacje o zasadach gry, lista wygranych w zależności od wariantu gry jak w przypadku Multi Multi gdzie losujemy od 1 do 10 liczb i w każdym zakresie mamy inne wygrane.

Informacje o grach liczbowych:

- możliwość dodania logo gry (logo zapisujemy /public/assets/img)

- wprowadzanie informacji o zasadach gier i ich wariantach oraz cenach zakładów

- wysokości wygranych lub procent/współczynnik wygranej, warianty 10 lub 8 typowanych liczb mogą mieć różne stawki wygranych

- Struktura bazy danych dla wygranych z jedną uniwersalną z polem game_id.

- Poziomy wygranych zapisujemy w oddzielnej tabeli dla każdej gry i jej wariantów
  
  



- index() - lista gier z logo

- create() - podstawowy formularz dodawania

- edit() - edycja podstawowych danych

- delete() - usuwanie z potwierdzeniem
