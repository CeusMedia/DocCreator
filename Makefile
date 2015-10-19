doc:
	@composer install
	@php doc-creator.php --config-file=config/DocCreator.doc.xml --s=./ --t=./ --trace

