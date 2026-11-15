# Airsell Cargo portal

Responsive Airsell Cargo website and initial PostgreSQL-backed Express API, for local development and deployment planning.

## Run with Docker

Requirements: Docker Compose. Copy `.env.docker.example` to `.env.docker`, replace the sample password and session secret with strong random values, then run:

```sh
docker compose --env-file .env.docker up --build -d
docker compose --env-file .env.docker exec app npm run db:migrate
