test: example-test self-test

example-test:
	@printf 'Testing Lady.toPhp(): '
	@./bin/ladyphp -q -i test/example.lady -o test/toPhp.actual
	@diff -u test/example.php test/toPhp.actual > test/toPhp.diff
	@rm test/toPhp.actual test/toPhp.diff
	@echo 'PASSED'

	@printf 'Testing Lady.toLady(): '
	@./bin/ladyphp -q -l -i test/example.php -o test/toLady.actual
	@diff -u test/example.lady test/toLady.actual > test/toLady.diff
	@rm test/toLady.actual test/toLady.diff
	@echo 'PASSED'

self-test:
	@printf 'Testing on Lady itself: '
	@./bin/ladyphp -q -l -i src/Lady.php -o test/Lady.lady
	@./bin/ladyphp -q -i test/Lady.lady -o test/Lady.php
	@diff -u src/Lady.php test/Lady.php > test/Lady.diff
	@rm test/Lady.lady test/Lady.php test/Lady.diff
	@echo 'PASSED'

.PHONY: test example-test self-test
