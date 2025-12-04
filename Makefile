COMPOSER := composer
PHP_CS_FIXER := vendor/bin/php-cs-fixer
PHPCS := vendor/bin/phpcs
PSALM := vendor/bin/psalm
PHPUNIT := vendor/bin/phpunit

SRC := src
TESTS := tests

.PHONY: all install fix cs phpcs psalm test clean

install:
	$(COMPOSER) install --no-interaction --optimize-autoloader

fix:
	$(PHP_CS_FIXER) fix $(SRC) $(TESTS)

cs:
	$(PHP_CS_FIXER) fix $(SRC) $(TESTS) --dry-run --diff

phpcs:
	$(PHPCS) --standard=phpcs.xml $(SRC) $(TESTS)

psalm:
	$(PSALM)

test:
	$(PHPUNIT) --testdox

clean:
	rm -rf var/cache/*
	rm -rf var/log/*
	rm -rf var/test-cache/*
