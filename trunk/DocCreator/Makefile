doc:
	@php create.php --config-file=config/DocCreator.doc.xml --s=./ --t=./ --trace

changelog:
	@echo Generating changelog...
	@php util/svn.changelog.php

install:
	@rm -Rf lib && mkdir lib
	@echo Getting cmClasses...
	@svn co --quiet http://cmclasses.googlecode.com/svn/trunk lib/cmClasses
	@php lib/cmClasses/go.php5 configure
	@rm -Rf lib/cmClasses/Test
	@echo Getting PHP-Markdown
	@git clone --quiet https://github.com/michelf/php-markdown.git lib/php-markdown
	


