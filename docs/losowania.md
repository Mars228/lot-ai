## LOSOWANIA

Baza wyników losowań gier liczbowych.

Możliwość importu danych z pliku .csv, w przypadku pojawienia się istniejących już w bazie danych nadpisujemy je.

Za pomocą LOTTO OpenAPI możliwość uzupełnienia brakujących danych, jeśli brakuje 30 ostatnich losowań, w przeciwnym razie potrzebny jest plik .csv z brakującymi danymi.

EuroJackpot: numeracja PL rozpoczęta 15.09.2017 (świat #287 = PL #1)

- import danych z plików *.csv konkretnej gry i zapisanie do bazy danych,

- możliwość pobrania konkretnego numeru losowania korzystając z lotto OpenAPI, istniejące wpisy nadpisujemy

- możliwość "uzupełniania" brakujących wpisów przy pomocy tego samego OpenAPI,

- struktura pliku *.csv będzie identyczna dla wszystkich gier: numer_losowania, data (YYYY-MM-DD), godzina, liczby_a, liczby_b (dla drugiego zestawu liczb jak w eurojackpot); identyczna dla wszystkich gier. 

- upload pliku do /public/uploads/csv

- uwzględnij numerację światową i polską dla eurojackpot(!) żeby unikąć problemu przy imporcie danych przez OpenAPI. EuroJackpot 15 września 2017 na świecie - numer 287 a w polsce numer 1

- index -> (domyślnie) lista wyników ostatniego miesiąca Lotto, informacja o źródle csv, czy api; możliwość zmiany gry, i zakresu czasu (rok, miesiące); info ogólne gra + logo; informacje o najstarszym numerze losowania i ostatnim wprowadzonym

- create ->  jako "button", formularz do importu pliku w oknie "modal" (tylko pliki *.csv!)

- "uzupełnij" lub "synchronizuj" losowania w celu dodania brakujących wyników; jako "button" (OpenAPI pozwala na pobrania po jednym numerze dlatego wymagana ewentualna interacja)





- index() - wyświetla listę losowań z filtrowaniem
- import CSV - podstawowy import działa
- pojedyncze pobieranie z API - działa dla konkretnego numeru
