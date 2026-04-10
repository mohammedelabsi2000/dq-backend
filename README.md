# DQ Tahfiz API

Laravel backend for DQ Tahfiz, running in Docker.

## Prerequisites

- Docker & Docker Compose
- Make

## Getting Started

```bash
# Start the application
make up

# Run database migrations
make migrate

# Seed the database
make seed
```

## Available Commands

Run `make help` to see all commands. Key ones:

| Command | Description |
|---|---|
| `make up` | Start containers and sync vendor |
| `make down` | Stop and remove containers |
| `make restart` | Restart containers |
| `make build` | Rebuild containers from scratch |
| `make logs` | Tail app logs |
| `make shell` | Open shell in app container |
| `make migrate` | Run migrations |
| `make migrate-fresh` | Drop all tables and re-migrate |
| `make seed` | Run database seeders |
| `make test` | Run all tests |
| `make test-unit` | Run unit tests only |
| `make test-feature` | Run feature tests only |

## Development Workflow

1. Create a new branch from `dev`:
   ```bash
   git checkout -b feature/your-feature-name
   ```
2. Make your changes and commit.
3. Push your branch and open a Pull Request (PR).
4. Wait for review and approval before merging.

> **Note:** Do not push directly to `dev`. All changes must go through a PR.
