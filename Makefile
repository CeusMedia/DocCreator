
composer-install:
	@test ! -f vendor/autoload.php && composer install --no-dev || true

composer-install-dev:
	@test ! -d vendor/phpunit/phpunit && composer install || true

composer-update:
	composer update --no-dev

composer-update-dev:
	composer update

dev-test: composer-install-dev
	@vendor/bin/phpunit --configuration util/phpunit10.xml --testsuite default

dev-test-syntax:
	@find src -type f -print0 | xargs -0 -n1 xargs php -l

dev-doc: composer-install-dev
	@test -f doc/API/search.html && rm -Rf doc/API || true
	@php doc.php --config-file=util/doc.xml

create-docs: composer-install
	@php doc-creator.php --config-file=util/doc.xml


