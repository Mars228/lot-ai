## STRATEGIE

Podobnie jak w przypadku statystyk definiujemy parametry strategii dla każdej gry osobno. 

a) Simple;
Wybierz określoną ilość liczb hot, pozostałe wymagane w wariancie gry dobierz z cold

b) Hybrid‑Blend;
Łączy modele statystyczne w jedną, wieloaspektową strategię

- hot‑set z ENSEMBLE + gap‑filter z Gap‑Analysis + EV‑opt.

c) Coverage (multi‑kupon)
Tworzy T kuponów tak, aby minimalizować nakładanie się liczb i maksymalizować szanse na trafienie progu. 
: wielokuponowe pokrycie jeśli stawiasz T kuponów, układaj je tak, by zminimalizować nakładanie się liczb i maksymalizować szanse, że przynajmniej jeden kupon trafi próg (np. ≥3 w Lotto, ≥7 w MM).
Parametry:
T (liczba kuponów), overlap_max (maks. część wspólna między dwoma kuponami, np. ≤2 dla Lotto), H/C splits z rekomendacji, k_ticket.
Implementacja: prosty greedy: dla każdego nowego kuponu losuj z wag p_i, ale karz (odrzuć/losuj ponownie), gdy przekracza overlap_max z istniejącymi. Trwa szybko, efekt duży.
Bonus: możesz dodać „podział na klastry” liczb (np. quartyle p_i) i wymagać, by każdy kupon obejmował wszystkie klastry → lepsza dywersyfikacja.

Analogicznie do modeli statystycznych tutaj również "szybki test" dla sprawdzenia strategii.

- index -> lista wprowadzonych strategii (nazwa gry, nazwa, model, parametry, start cyklu, następny, przetworzono, status)
- create -> formularz dodawania parametrów; select-box dla: gra; 
- edit -> formularz edycji zapisanych danych
- delete -> button z monitem o potwierddzenie czynności, usuwanie danych związanych ze strategią.
