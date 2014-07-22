<?php

class LadyCommand {
  public $quiet = false;

  public function run($argv) {
    $options = 'o:rwtdqh';
    $opt = getopt($options, array('help'));

    foreach ($opt as $o => $a) {
      while ($k = array_search("-" . $o, $argv)) {
        if ($k !== false) unset($argv[$k]);
        if (preg_match('/^.*'.$o.':.*$/i', $options)) unset($argv[++$k]);
      }
    }
    $argv = array_values($argv);
    $this->quiet = isset($opt['q']);

    $inputFile = isset($argv[1]) ? $argv[1] : null;
    if (!$inputFile || isset($opt['h']) || isset($opt['help'])) {
      self::showHelp();
    }
    if (!file_exists($inputFile) || !is_readable($inputFile)) {
      return $this->log("Cannot read file: $inputFile");
    }
    $convertToLady = preg_match('{\.php$}', $inputFile) || isset($opt['r']);

    if (is_dir($inputFile)) {
      if (isset($opt['t'])) {
        $this->testDirectory($inputFile, isset($opt['d']));
      } elseif (isset($opt['w'])) {
        $this->watchDirectory($inputFile, $convertToLady);
      } else {
        $files = $this->convertDirectory($inputFile, $convertToLady);
        $this->log(count($files) ? 'All files are up to date.'
          : 'There are no files to convert.');
      }
    } else {
      if (isset($opt['t'])) {
        $this->testFile($inputFile, isset($opt['d']));
      } else {
        $this->convertFile($inputFile, isset($opt['o']) ? $opt['o'] : null, $convertToLady);
      }
    }
  }

  public function convertFile($inputFile, $outputFile = null, $convertToLady = false) {
    if (!$outputFile) {
      if ($convertToLady) {
        $outputFile = preg_replace('{\.php$}', '', $inputFile) . '.lady';
      } else {
        $outputFile = preg_replace('{\.lady$}', '', $inputFile) . '.php';
      }
    }
    $input = file_get_contents($inputFile);
    $output = $convertToLady ? Lady::toLady($input) : Lady::toPhp($input);
    $this->log("Generating file: $outputFile");
    file_put_contents($outputFile, $output);
  }

  public function convertDirectory($dir, $convertToLady = false) {
    $ext = $convertToLady ? 'php' : 'lady';
    $files = array();
    foreach ($this->getFilesFromDirectory($dir, $ext) as $file) {
      $newFile = substr($file, 0, -strlen($ext)) . ($convertToLady ? 'lady' : 'php');
      $files[] = $newFile;
      if (!is_file($newFile) || filemtime($newFile) <= filemtime($file)) {
        $this->convertFile($file, $newFile, $ext == 'php');
      }
    }
    return $files;
  }

  public function watchDirectory($dir, $convertToLady = false) {
    $this->log("Watching directory: $dir");
    while (true) {
      $this->convertDirectory($dir, $convertToLady);
      sleep(1);
    }
  }

  public function testFile($phpFile, $showDiff = false) {
    $originalCode = file_get_contents($phpFile);
    $ladyCode = Lady::toLady($originalCode);
    $generatedCode = Lady::toPhp($ladyCode);
    $success = ($generatedCode == $originalCode);
    $this->log("Test " . ($success ? 'PASSED' : 'FAILED') . ": $phpFile");
    if (!$success && $showDiff) {
      $generatedFile = tempnam(sys_get_temp_dir(), 'lady');
      file_put_contents($generatedFile, $generatedCode);
      $diff = shell_exec('diff -u ' . escapeshellarg($phpFile) . ' '
        . escapeshellarg($generatedFile));
      $this->log($diff);
      unlink($generatedFile);
    }
    return $success;
  }

  public function testDirectory($dir, $showDiff = false) {
    $ok = $ko = 0;
    foreach ($this->getFilesFromDirectory($dir, 'php') as $file) {
      if ($this->testFile($file, $showDiff)) {
        $ok++;
      } else {
        $ko++;
      }
    }
    $this->log(sprintf("Test score: %2d OK / %2d KO", $ok, $ko));
    return ($ko == 0);
  }

  protected function getFilesFromDirectory($dir, $ext = null) {
    $files = array();
    $it = new RecursiveDirectoryIterator($dir ? $dir : '.');
    $it = new RecursiveIteratorIterator($it);
    while ($it->valid()) {
      if (!$it->isDot() && $it->isFile()
          && (!$ext || pathinfo($it->key(), PATHINFO_EXTENSION) == $ext)) {
        $files[$it->key()] = true;
      }
      $it->next();
    }
    return array_keys($files);
  }

  protected function log($text) {
    if (!$this->quiet) {
      file_put_contents('php://stderr', "$text\n");
    }
  }

  protected function showHelp() {
    die("Usage:\n"
      . "  ladyphp [OPTIONS] INPUT_FILE\n"
      . "Example:\n"
      . "  ladyphp file.lady  # creates file.php\n"
      . "  ladyphp file.php   # creates file.lady\n"
      . "  ladyphp -w dir/    # watches directory and converts updated lady files\n"
      . "Options:\n"
      . "  -o FILE   Output file\n"
      . "  -w        Watch directory for changes\n"
      . "  -r        Convert PHP to LadyPHP\n"
      . "  -t        Test that files converted to lady and back are same as original\n"
      . "  -d        Show diff for tests\n"
      . "  -q        Hide messages\n");
  }
}
