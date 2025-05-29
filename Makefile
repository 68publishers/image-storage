CONTAINER_NAME=68publishers.image-storage

init:
	make stop
	make start

stop:
	docker compose stop

start:
	docker compose up -d

down:
	docker compose down

restart:
	make stop
	make start

exec-sh:
ifndef PHP
	docker exec -it $(CONTAINER_NAME).84 sh
else
	docker exec -it $(CONTAINER_NAME).$(PHP) sh
endif

tests.all:
	PHP=81 make tests.run
	PHP=82 make tests.run
	PHP=83 make tests.run
	PHP=84 make tests.run

cs.fix:
	PHP=84 make composer.update
	docker exec -e PHP_CS_FIXER_IGNORE_ENV=1 $(CONTAINER_NAME).84 vendor/bin/php-cs-fixer fix -v

cs.check:
	PHP=84 make composer.update
	docker exec -e PHP_CS_FIXER_IGNORE_ENV=1 $(CONTAINER_NAME).84 vendor/bin/php-cs-fixer fix -v --dry-run

stan:
	PHP=84 make composer.update
	docker exec $(CONTAINER_NAME).84 vendor/bin/phpstan analyse --memory-limit=-1

coverage:
	PHP=84 make composer.update
	docker exec $(CONTAINER_NAME).84 vendor/bin/tester -C -s --coverage ./coverage.xml --coverage-src ./src ./tests

composer.update:
ifndef PHP
	$(error "PHP argument not set.")
endif
	@echo "========== Installing dependencies with PHP $(PHP) ==========" >&2
	docker exec $(CONTAINER_NAME).$(PHP) composer update --no-progress --prefer-dist --prefer-stable --optimize-autoloader --quiet

composer.update-lowest:
ifndef PHP
	$(error "PHP argument not set.")
endif
	@echo "========== Installing dependencies with PHP $(PHP) (prefer lowest dependencies) ==========" >&2
	docker exec $(CONTAINER_NAME).$(PHP) composer update --no-progress --prefer-dist --prefer-lowest --prefer-stable --optimize-autoloader --quiet

tests.run:
ifndef PHP
	$(error "PHP argument not set.")
endif
	PHP=$(PHP) make composer.update
	@echo "========== Running tests with PHP $(PHP) ==========" >&2
	docker exec $(CONTAINER_NAME).$(PHP) vendor/bin/tester -C -s ./tests
	PHP=$(PHP) make composer.update-lowest
	@echo "========== Running tests with PHP $(PHP) (prefer lowest dependencies) ==========" >&2
	docker exec $(CONTAINER_NAME).$(PHP) vendor/bin/tester -C -s ./tests
