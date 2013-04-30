# $Id$

# required environment variables: PHPUNIT

.PHONY: tests
tests:
	cd tests/; $(PHPUNIT) --colors .; cd ..
