
DEFAULT_GOAL    := ci
current_user    := $(shell id -u)
current_group   := $(shell id -g)
BUILD_DIR       := $(PWD)
DOCKER_FLAGS    := --interactive --tty
DOCKER_IMAGE    := registry.gitlab.com/fun-tech/fundraising-frontend-docker:latest

install-php:
	docker run --rm $(DOCKER_FLAGS) --volume $(BUILD_DIR):/app -w /app --volume ~/.composer:/composer --user $(current_user):$(current_group) $(DOCKER_IMAGE) composer install $(COMPOSER_FLAGS)

update-php:
	docker run --rm $(DOCKER_FLAGS) --volume $(BUILD_DIR):/app -w /app --volume ~/.composer:/composer --user $(current_user):$(current_group) $(DOCKER_IMAGE) composer update $(COMPOSER_FLAGS)

ci: test cs

test: phpunit

cs: phpcs stan

phpunit:
	docker compose run --rm app ./vendor/bin/phpunit

phpcs:
	docker compose run --rm app ./vendor/bin/phpcs -p -s

stan:
	docker compose run --rm app ./vendor/bin/phpstan analyse --level=9 --no-progress src/ tests/

fix-cs:
	docker compose run --rm app ./vendor/bin/phpcbf -p -s


.PHONY: ci test phpunit cs stan composer fix-cs

