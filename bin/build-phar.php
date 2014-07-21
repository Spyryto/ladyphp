#!/usr/bin/env php
<?php
$phar = new Phar('ladyphp.phar');
$phar->buildFromDirectory(__DIR__ . '/../src');
$phar->setStub('#!/usr/bin/env php
<?php
Phar::mapPhar("ladyphp.phar");
require "phar://ladyphp.phar/Lady.php";
require "phar://ladyphp.phar/LadyCommand.php";
$command = new LadyCommand();
$command->run($argv);
__HALT_COMPILER();');
$phar->compressFiles(Phar::GZ);
chmod('ladyphp.phar', 0755);
echo "File ladyphp.phar created.\n";
