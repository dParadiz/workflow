DOCKER_COMPOSE_RUN=docker-compose run --rm php-cli composer

tests:
	$(DOCKER_COMPOSE_RUN) run test

install: export UID = $(id -u)
install: export GID = $(id -g)
install:
	$(DOCKER_COMPOSE_RUN) install

update: export UID = $(id -u)
update: export GID = $(id -g)
update:
	$(DOCKER_COMPOSE_RUN) update

phpstan:
	$(DOCKER_COMPOSE_RUN) run phpstan
