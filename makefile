mkfile_path := $(abspath $(lastword $(MAKEFILE_LIST)))
mkfile_dir := $(dir $(mkfile_path))

NAME = ''

rebuild:
	@docker compose up -d --build && docker compose start

start:
	@docker compose up -d --remove-orphans && docker compose start

stop:
	@docker compose stop

restart: stop start

connect_app:
	@docker exec -it home_sauron_app bash

connect_nginx:
	@docker exec -it home_sauron_nginx bash

connect_redis:
	@docker exec -it home_sauron_redis bash

connect_psql:
	@docker exec -it home_sauron_psql bash

run_queue:
	@docker exec home_sauron_app php artisan queue:work --queue=default --timeout=0

run_schedule:
	@docker exec home_sauron_app php artisan schedule:work
