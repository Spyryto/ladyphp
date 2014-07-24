#!/usr/bin/env nodejs
var fs = require('fs');
var path = require('path');
var lady = require('../src/lady');

if (!process.argv[2]) {
  process.stderr.write("Usage: ladyphp.js FILE\n");
} else {
  var inputFile = path.resolve(process.argv[2]);
  if (!fs.existsSync(inputFile)) {
    process.stderr.write("No such file " + inputFile + "\n");
  } else {
    var source = fs.readFileSync(inputFile);
    var toLady = inputFile.substr(inputFile.length - 4) == '.php';
    var out = toLady ? lady.toLady(source) : lady.toPhp(source);
    process.stdout.write(out);
  }
}