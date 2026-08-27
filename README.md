# HuMo-genealogy


**Free & open‑source web‑based genealogy software**.

Download the current version: [github.com/HuubMons/HuMo-genealogy/releases](https://github.com/HuubMons/HuMo-genealogy/releases).


## Screenshot

![HuMo-genealogy screenshot](docs/assets/HuMo-genealogy_home.png)

## Links

- [Website](https://huubmons.github.io/HuMo-genealogy/)

- [Demo website](https://humo-gen.com/humo-gen/)

- [Report bugs and issues](https://github.com/HuubMons/HuMo-genealogy/issues)  
  Use the issue tracker to report bugs, broken behavior, or other problems.

- [Requests and remarks](https://github.com/HuubMons/HuMo-genealogy/discussions)  
  Use the discussion forum for questions, suggestions, and general feedback about the project.

- [Documentation](https://huubmons.github.io/HuMo-genealogy/documentation.html)

- [Old PDF documentation](https://sourceforge.net/projects/humo-gen/files/HuMo-gen_Manual/)

## Docker deployment

The included Compose setup runs HuMo-genealogy with Apache/PHP and MariaDB
12.3.3.
Database data, media, GEDCOM uploads, and backups are stored in Docker volumes.

1. Create the environment file and replace both example passwords:

   ```sh
   cp .env.example .env
   ```

2. Build and start the services:

   ```sh
   docker compose up -d --build
   ```

3. Open `http://localhost:8080/admin` and complete the HuMo-genealogy
   installation. The database connection is supplied automatically by Docker.

To inspect the services, run `docker compose ps`. To stop them, run
`docker compose down`. Do not add `-v` unless you deliberately want to delete
the database and all other persisted Docker volumes.

Set `HUMOGEN_PORT` in `.env` to use another host port. For a public deployment,
place this service behind an HTTPS reverse proxy and expose that proxy instead
of publishing HuMo-genealogy directly to the internet.

For public deployments, `HUMOGEN_PUBLIC_ORIGIN` can be set to the trusted
external origin used in canonical and password-reset URLs, for example
`https://genealogy.example.com`. If the application is exclusively reachable
through a trusted reverse proxy, set `HUMOGEN_TRUST_PROXY_HEADERS=true` to use
its `X-Forwarded-Host` and `X-Forwarded-Proto` headers. Do not enable this when
clients can connect directly to the application.
