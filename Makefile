# $Id$

# required environment variables: PHPUNIT PHPDOC PHPCS

PHPSOURCES=cmsimple/adminfuncs.php\
	   cmsimple/cms.php\
	   cmsimple/functions.php\
	   cmsimple/mailform.php\
	   cmsimple/search.php\
	   cmsimple/tplfuncs.php\
	   cmsimple/classes/FileEdit.php\
	   cmsimple/classes/LinkCheck.php\
	   cmsimple/classes/page_data_model.php\
	   cmsimple/classes/page_data_router.php\
	   cmsimple/classes/page_data_views.php

EMPTY=
SPACE=$(EMPTY) $(EMPTY)
COMMA=,

.PHONY: tests
tests:
	cd tests/; $(PHPUNIT) --colors .; cd ..

.PHONY: coverage
coverage:
	cd tests/; $(PHPUNIT) --coverage-html coverage/ .; cd ..

doc: doc/php/index.html

doc/php/index.html: $(PHPSOURCES)
	$(PHPDOC) --filename $(subst $(SPACE),$(COMMA),$(PHPSOURCES))\
		  --target doc/php/\
		  --defaultcategoryname CMSimple_XH\
		  --defaultpackagename XH

sniff:
	$(PHPCS) $(PHPSOURCES)
