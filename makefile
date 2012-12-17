all: lady

lady:
	lady -i lady.lady -o lady.php~new
	mv lady.php lady.php~`date +%s`
	mv lady.php~new lady.php
	git add lady.php

doc:
	apigen -s lady.php -d doc/

.PHONY: all lady doc
