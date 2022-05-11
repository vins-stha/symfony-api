# Note API

Symfony based note API. Provides JSON API endpoints to create, read, update and delete notes.

## Setup

1. Install [docker](https://www.docker.com/get-started)
2. Start up docker compose instance - `docker-compose up --build -d`
3. Gain access to php bash shell - `docker exec -it php bash`
4. Go to root of symfony project - `cd code`
5. Install dependencies - `composer install`
6. Open site in browser [localhost:8001](http://localhost:8001)

## Tasks

1. Copy this repository to your own GitHub account.
2. Add phpmyadmin container to docker compose. Document access in README.md
3. Create Note entity. Note has id, title, created time and text.
4. Make sure to generate migrations for database tables/schema.
5. Write code for all routes in NoteController so that application fulfills CRUD tasks using JSON format.
   1. `/notes/add` - Add new note.
   2. `/notes/{id}` - Get note by id.
   3. `/notes/{id}` - Put an update to note by id.
   4. `/notes/{id}` - Delete a note by id.
   5. `/notes` - Get all notes ordered by date (The newest first). Add options to limit results, change sorting order and search note by text.
6. Add documentation and comments as needed using best practices.
7. Create **Pull Request on your own repository describing changes**. Add manual testing scenarios for each functionality in PR.
8. (Optional) Write unit or web tests.
9. Send us a link to your PR.
# symfony-rest-api
