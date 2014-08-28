all: updatejs test
test: test-jstophp test-jstolady

updatejs:
	@./update-ladyjs.php

test-jstophp:
	@printf 'Testing Lady.js.toPhp(): '
	@./ladyphp.js ../test/example.lady > ../test/js.toPhp.actual
	@diff -u ../test/example.php ../test/js.toPhp.actual > ../test/js.toPhp.diff
	@rm ../test/js.toPhp.actual ../test/js.toPhp.diff
	@echo 'PASSED'

test-jstolady:
	@printf 'Testing Lady.js.toLady(): '
	@./ladyphp.js ../test/example.php > ../test/js.toLady.actual
	@diff -u ../test/example.lady ../test/js.toLady.actual > ../test/js.toLady.diff
	@rm ../test/js.toLady.actual ../test/js.toLady.diff
	@echo 'PASSED'

.PHONY: all test updatejs test-jstophp test-jstolady

