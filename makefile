test: example-test self-test

example-test:
	@printf 'Testing Lady.toPhp(): '
	@./bin/ladyphp -q -o test/toPhp.actual test/example.lady
	@diff -u test/example.php test/toPhp.actual > test/toPhp.diff
	@rm test/toPhp.actual test/toPhp.diff
	@echo 'PASSED'

	@printf 'Testing Lady.toLady(): '
	@./bin/ladyphp -q -o test/toLady.actual test/example.php
	@diff -u test/example.lady test/toLady.actual > test/toLady.diff
	@rm test/toLady.actual test/toLady.diff
	@echo 'PASSED'

self-test:
	@printf 'Testing on Lady itself: '
	@./bin/ladyphp -q -o test/Lady.lady src/Lady.php
	@./bin/ladyphp -q test/Lady.lady
	@diff -u src/Lady.php test/Lady.php > test/Lady.diff
	@rm test/Lady.lady test/Lady.php test/Lady.diff
	@echo 'PASSED'

.PHONY: test example-test self-test
