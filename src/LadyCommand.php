<?php

class LadyCommand {
  public $quiet = false;

  public function run($argv) {
    $options = 'o:rqh';
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
      $files = self::convertDirectory($inputFile, $convertToLady);
      $this->log(count($files) ? 'All files are up to date.'
        : 'There are no files to convert.');
    } else {
      if (isset($opt['o'])) {
        $outputFile = $opt['o'];
      } elseif ($convertToLady) {
        $outputFile = preg_replace('{\.php$}', '', $inputFile) . '.lady';
      } else {
        $outputFile = preg_replace('{\.lady$}', '', $inputFile) . '.php';
      }
      $input = file_get_contents($inputFile);
      $output = $convertToLady ? Lady::toLady($input) : Lady::toPhp($input);
      $this->log("Generating file: $outputFile");
      file_put_contents($outputFile, $output);
    }
  }

  public function convertDirectory($dir, $convertToLady = false) {
    $extensions = array('lady', 'php');
    $ext = $convertToLady ? 'php' : 'lady';
    $newExt = $convertToLady ? 'lady' : 'php';
    $files = array();
    $it = new RecursiveDirectoryIterator($dir ? $dir : '.');
    $it = new RecursiveIteratorIterator($it);
    while ($it->valid()) {
      $file = $it->key();
      if (!$it->isDot() && $it->isFile()
          && pathinfo($file, PATHINFO_EXTENSION) == $ext) {
        $newFile = substr($file, 0, -strlen($ext)) . $newExt;
        $files[] = $newFile;
        if (!is_file($newFile) || filemtime($newFile) <= filemtime($file)) {
          $input = file_get_contents($file);
          $output = $newExt == 'lady' ? Lady::toLady($input) : Lady::toPhp($input);
          file_put_contents($newFile, $output);
          $this->log("Generating file: $newFile");
        }
      }
      $it->next();
    }
    return $files;
  }

  protected function log($text) {
    if (!$this->quiet) {
      file_put_contents('php://stderr', "$text\n");
    }
  }

  protected function showHelp() {
    die("Usage:\n"
      . "  ladyphp [-o FILE] [-d] [-r] [-q] INPUT_FILE\n"
      . "Example:\n"
      . "  ladyphp file.lady  # creates file.php\n"
      . "  ladyphp file.php   # creates file.lady\n"
      . "  ladyphp dir/       # convert updated lady files to php\n"
      . "Options:\n"
      . "  -o FILE   Output file\n"
      . "  -d        Convert all updated files in directory\n"
      . "  -r        Convert PHP to LadyPHP\n"
      . "  -q        Hide messages\n");
  }
}
