## STATYSTYKI

Opracowanie algorytmu szukającego dokładności w modelu.

Definiowanie modelów statystyk dla każdej gry osobno, każdy model może mieć różne parametry. Koniecznie dodawaj opisy pól danych do wprowadzenia w zrozumiałe dla przeciętnego człowieka. Zapisujemy informacje o grze której model dotyczy, typ modelu, nazwę "uproszczoną" czytelną dla mnie, parametry modelu.

Statystyki tworzymy od najstarszego do najnowszego numeru, muszą posiadać numer losowania przy którym spełnione są określone warunki. KONIECZNY jest "szybki test" który sprawdza czy poprawnie zapisano dane modelu, a następnie czy poprawnie wykonuje się sam model ma maję próbce danych! Zapisujemy do bazy najczęście losowane liczby jako "hot", w przypadku eurojackpot trzeba podzielić na dwa zakresy "hot_a" dla zakresu 50 liczb oraz "hot_b" dla zakresu 12 liczb. Przy każdym numerze losowania zapisujemy ilość liczb które znalazły się na  stworzonej liści "hot" poprzedniego numeru losowania, w ten sposób będziemy można określać "dokładność" modelu. Lista modeli statystycznych powinna zwierać klawisz do uruchomiena cyklu "start" i zapisania wyników w bazie danych, a dysponująć wartościami które pozwalają szacować dokładność modelu stwórz algorytm tworzący serie wartości (żeby uniknąć problemów zapętlenia, przed pokazuj ile cykli zostało przewidzianych i szacuj czas potrzebny na ich wykonanie, oraz daj możliwość skrócenia takiej listy żeby maksymalnie zabierało to kilka godzin) do wyszukiwania najwyższej dokładności w ramach modelu, pozostaw dane które mają najwyższą dokładność i usuwaj pozostałe dane cykli; Wydaj się potrzebny dodakowy przycisk "start combo", konieczne jest okno modal w którym pokazujemy stopień postępu zadania w postaci np. przesuwającego się paska. Wszystkie modele konfigurowane przez użytkownika w tym Model III (PROB) - czy α,β.

### Modele do implementacji:

1. zakłada zliczenie tylu losowań żeby 'x' liczb powtórzyło się 'y' razy a następnie zapisanie tych liczb a także ilości zliczonych losowań np.: 
   
   - multi multi  - 28 liczb z tablicy 80 powtórzy się conajmniej 1 raz, 28 liczb z tablicy 80 powtórzy się conajmniej 2 razy, zapisujemy też ilość losowań która spełniła założenia
   - eurojackpot - 8 liczb z tablicy 50 powtórzy się conajmniej 1 raz a z tablicy 12 liczb niech 3 liczby powtórzą się conajmniej 1 raz, tutaj zapisujemy dwie wartości ilość potrzebnych losowań dla tablicy 50 oraz 12 liczb.
     Dla gier z dwoma zestawami liczb trzba przygotować algorytm który dla obu zakresu losowanych liczb będzie zliczał powtórzenia wskazanych ilości liczb, różnego dla każdego zakresu ale dla tego samego numeru losowania jeśli np. W eurojackpot mamy numeru losowania 1545 - to licząc wstecz sprawdzamy ile razy x liczb powtórzyło się y razy dla tablicy 50 liczb i ile razy x1 liczb powtórzyło się y1 razy dla tablicy 12 liczb, dla każdej z tablic zapisujemy ilość losowań przy których spełnione zostały założenia.

2. zakłada wybór 'x' liczb najczęściej losowanych przy założeniu, że każda liczba zakresu gry wystąpiła conajmniej 'y' razy, zapisujemy te liczby np.:
   
   - lotto - zapisz 8 liczb najczęściej losowanych, przy założeniu, że wszystkie liczby z zakresu gry lotto (49) zostały wylosowane conajmniej 1 raz
   - eurojackpot - zapisz 8 liczb najczęściej losowanych z 50 liczb oraz 3 z 12 liczb przy założeniu, że wszystkie liczby z obu zakresów 50 i 12 powtrórzyły się conajmniej 1 raz

3. probabilistyka PROB.
   Liczymy prawdopodobieństwo p_i dla każdej liczby i w puli gry na bazie ostatnich N losowań (N dobierasz w schemacie).
   Surowa częstość: k_i / N (ile razy i wystąpiła).
   Bayes (stabilniejsze dla małej próbki):
   A: p_i = (k_i + α) / (N + α + β)
   B: analogicznie.
   α,β to „priory” – zwykle α=1, β=1 albo „empiryczne”: α≈średnia trafień na liczbę, β≈N−α.
   (Opcjonalnie) ważymy recency: starszym losowaniom dajemy mniejszą wagę (połowiczny czas zaniku half_life), żeby świeże trendy miały większy wpływ.
   Ranking i próg „hot”: sortujemy liczby po p_i (albo po dolnym krańcu przedziału Wilsona dla zachowawczości). Bierzemy topK_A (i topK_B) jako „hot”. Reszta to „cold”.
   Rekomendacje H/C + parzystość: z hot/cold wyznaczamy proporcje (np. 60/40) i target parzystych/nieparzystych według obserwacji w ostatnim oknie (lub neutralnie 50/50, jeśli nie chcesz biasu).
   Generowanie zakładów: losowanie bez powtórzeń z wagą ∝ p_i – najpierw z „hot”, potem uzupełniamy „cold”, pilnując parzystości oraz limitów gry (6 z 49 itd.). Z automatu tworzymy też „baseline” losowy (na porównanie skuteczności).
   Backtest (ważne, żeby nie „wierzyć na słowo”): bieg wstecz (walk-forward) na historii – co draw, licz p_i z poprzedzających okien i sprawdzaj trafienia/EV (oczekiwana wygrana) vs SIMPLE/baseline.

4. ENSEMBLE: łączenie kilku PROB
   licz p_i z kilku okien (krótkie/średnie/długie), zrób ważoną średnią i wybierz topK.
   Parametry:
   win_a_list=[50,200,600], weights=[0.5,0.3,0.2], alpha/beta=1, half per window, top_k_a/b.
   Dlaczego to ma sens: krótki horyzont łapie „świeżość”, długi stabilizuje szum.
   Implementacja: w S4 licz PROB jak w S3, ale 3× i zlep p_i: p_i = Σ w_j * p_i^(win_j). Zapisz hot_* jak zwykle, opcjonalnie wrzuć pełny wektor p_i do extras_json.
   Backtest: walk-forward vs S3 i baseline.

5. EV-OPT: optymalizacja pod wypłaty (EV)
   wykorzystaj Twoje „Wysokości wygranych” i hipergeometrię, żeby dobrać rozmiar hot-setu H i proporcję H/C na kuponie (oraz A/B w EJ/MM), które maksymalizują oczekiwaną wypłatę dla zadanego budżetu.
   Matematyka (skrót): dla puli A o rozmiarze M, hot-set H i kuponie k:
   P(X=x)=(Hx)(M−Hk−x)/(Mk)
   EV = Σ_x P(X=x)·payout(x) (dla A i ew. B łączymy rozkłady).
   Parametry:
   k_ticket (rozmiar kuponu; np. 6 w Lotto, 10/9/… w MM), H_search_range (np. 10..40), target_t (próg trafień), opt=EV|P(X≥t).
   Implementacja: w S6 przeszukaj H (i dla B: HB), policz EV/P≥t, wybierz najlepszą parę. Hot = topK=H z S3/S4 p_i; Cold = reszta. Rekomendacje SUM/PAR wyliczasz jak dziś.
   Dlaczego to ma sens: zamiast „intuicji” masz matematycznie dobrane H do struktury nagród.

6. Gap‑Analysis
   Analizuje „odstępy” pomiędzy kolejnymi wystąpieniami każdej liczby. Wybiera te, które mają nietypowo długi gap (np. 85% percentyl): odstępy/„czasy oczekiwania” dla każdej liczby i policz liczbę losowań od ostatniego wystąpienia (gap) + rozkład gapów z historii. Następnie wybieraj te, które mają nietypowo długi gap wg empirycznego rozkładu (czyli rzadko bywały aż tak długo „w ciszy”).
   Parametry:
   win_a (okno do estymacji rozkładu gapów), q_thr_a (percentyl, np. 0.85), top_k_a (ile hot). Analogicznie B.
   Dlaczego to ma sens: jeśli losowania byłyby idealnie niezależne, zysk jest iluzoryczny, ale na realnych danych bywa delikatny bias/sezonowość sprzętowa. Filtr „dziwnie długi gap” bywa skuteczniejszy niż „ostatnio częste”.
   Implementacja: licz unormowany score: score_i = 1 - F_gap(observed_gap_i); sort malejąco, bierz topK jako hot.
   Uwaga: łatwo, szybko, bezpiecznie – ale koniecznie z backtestem.
