all: phar test

phar:
	@./bin/build-phar

test: test-tophp test-tolady test-lint self-test

test-tophp:
	@printf 'Testing Lady.toPhp(): '
	@./bin/ladyphp -q -o test/toPhp.actual test/example.lady
	@diff -u test/example.php test/toPhp.actual > test/toPhp.diff
	@rm test/toPhp.actual test/toPhp.diff
	@echo 'PASSED'

test-tolady:
	@printf 'Testing Lady.toLady(): '
	@./bin/ladyphp -q -o test/toLady.actual test/example.php
	@diff -u test/example.lady test/toLady.actual > test/toLady.diff
	@rm test/toLady.actual test/toLady.diff
	@echo 'PASSED'

test-lint:
	@printf 'Linting example.php: '
	@php -l test/example.php > /dev/null
	@echo 'PASSED'

self-test:
	@printf 'Converts own source code: '
	@./bin/ladyphp -q -o test/Lady.lady src/Lady.php
	@./bin/ladyphp -q test/Lady.lady
	@diff -u src/Lady.php test/Lady.php > test/Lady.diff
	@rm test/Lady.lady test/Lady.php test/Lady.diff
	@echo 'PASSED'

.PHONY: all phar test

