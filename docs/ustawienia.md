## USTAWIENIA

Lotto OpenAPI - zapiszmy API key. Korzystając z LOTTO OpenAPI API key: 'LlQ2nogAEteK1zd7SkD5IGyPQt7phMdeVr0ZLLfHdiE=' możemy pobrać dane:

### Pobieranie wyników:

#### Wyniki konkretnego losowania konkretnej gry

```bash
curl -X GET "https://developers.lotto.pl/api/open/v1/lotteries/info?gameType=Lotto" \
  -H "Accept: application/json" \
  -H "Secret: LlQ2nogAEteK1zd7SkD5IGyPQt7phMdeVr0ZLLfHdiE="
```

#### Wyniki dla określonej daty oraz typu gry

```bash
curl -X GET "https://developers.lotto.pl/api/open/v1/lotteries/draw-results/by-date-per-game?gameType=Lotto&drawDate=2025-05-29T20:00Z&sort=drawSystemId&order=ASC&index=1&size=10" \
  -H "Accept: application/json" \
  -H "Secret: LlQ2nogAEteK1zd7SkD5IGyPQt7phMdeVr0ZLLfHdiE="
```

#### Wartość kumulacji gry

```bash
curl -X GET "https://developers.lotto.pl/api/open/v1/lotteries/info/game-jackpot?gameType=Lotto" \
  -H "Accept: application/json" \
  -H "Secret: LlQ2nogAEteK1zd7SkD5IGyPQt7phMdeVr0ZLLfHdiE="
```

Synchronizacje z OpenAPI, codziennie min. godzinę PO losowaniu Multi Multi, ponieważ jest codziennie o 14:00 i 22:00.

Przy konfliktach (różne dane w bazie vs API) zawsze nadpisywać danymi z API.
