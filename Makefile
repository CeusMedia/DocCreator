
doc: _composer-install
	@test -f docs/API/home.html && rm -Rf docs/API || true
	@php doc-creator.php --config-file=config/DocCreator.doc.xml --s=./ --t=./ --trace


_composer-install:
	@test ! -f vendor/autoload.php && composer install || true

