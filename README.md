# UNIPR Aula Studio Booking — app-base (scheletro)

Punto di partenza **vuoto** per il team: stessa architettura MVP/REST del progetto root, **senza** dati finti, mock o logica già implementata.

## Cosa c'è

- Docker: nginx (**8081**) + PHP 8.2 + MySQL 8.0
- Schema DB in [docker/sql/init.sql](docker/sql/init.sql) (solo `CREATE TABLE`, tabelle **vuote**)
- Backend a strati: Router → Controller → Service → Repository → Model (stub / `TODO`)
- Frontend: 4 View HTML + `apiClient.js` + Presenter vuoti
- Contratto API: [swagger.yml](swagger.yml)

## Cosa manca (da implementare)

1. Query SQL nei **Repository**
2. **AuthService** + login reale o mock controllato
3. **BookingModel** (transazione, `FOR UPDATE`, gestione 409)
4. Logica nei **Presenter** JS
5. Eventuali **INSERT** seed o import dati aule/posti

Riferimento implementazione completa: cartella `../` (root del repo).

## Avvio

```bash
cd app-base
docker compose up -d --build
```

- App: http://localhost:8081
- Swagger: http://localhost:8081/swagger.yml

Reset DB (schema vuoto):

```bash
docker compose down -v && docker compose up -d --build
```

## Verifica scheletro

```bash
curl -s http://localhost:8081/api/v1/rooms
# {"success":true,"data":[]}

curl -s -X POST http://localhost:8081/api/v1/auth/login \
  -H "Content-Type: application/json" -d '{"email":"a@unipr.it","password":"x"}'
# {"success":false,"error":"Da implementare"}

docker compose exec database mysql -u unipr_user -punipr_password unipr_booking -e "SHOW TABLES;"
```

## Come procedere

1. Implementare `RoomRepository::findAllActive()` e testare `GET /rooms`
2. Copiare/adattare pezzi dal root (`../backend/src/Repository/`, `BookingModel`, presenter)
3. Aggiungere seed SQL solo quando servono dati di test reali

## Porte

| Stack | URL |
|-------|-----|
| Root (demo completa) | http://localhost:8080 |
| app-base (scheletro) | http://localhost:8081 |
