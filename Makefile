
composer-install:
	@test ! -f vendor/autoload.php && composer install --no-dev || true

composer-install-dev:
	@test ! -d vendor/phpunit/phpunit && composer install || true

composer-update:
	composer update --no-dev

composer-update-dev:
	composer update

dev-doc: composer-install-dev
	@test -f doc/api/search.html && rm -Rf doc/api || true
	@php vendor/ceus-media/doc-creator/doc.php --config-file=doc.xml

dev-test: composer-install-dev
	@XDEBUG_MODE=coverage vendor/bin/phpunit

dev-test-syntax:
	@find src -type f -print0 | xargs -0 -n1 xargs php -l

dev-phpstan:
	@vendor/bin/phpstan analyse --configuration tool/config/phpstan.neon --xdebug || true

dev-phpstan-flush-cache:
	@vendor/bin/phpstan clear-result-cache

dev-phpstan-save-baseline:
	@vendor/bin/phpstan analyse --configuration tool/config/phpstan.neon --generate-baseline tool/config/phpstan-baseline.neon || true


