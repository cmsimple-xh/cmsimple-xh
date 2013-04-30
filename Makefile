# $Id$

# required environment variables: PHPUNIT

.PHONY: tests
tests:
	cd tests/; $(PHPUNIT) --colors .; cd ..

.PHONY: coverage
coverage:
	cd tests/; $(PHPUNIT) --coverage-html coverage/ .; cd ..
