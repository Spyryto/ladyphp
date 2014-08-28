# LadyPHP makes your code look beautiful

## Introduction

Do you also hate all these dollars, arrows and $this everywhere in you PHP files? Well, PHP just isn't the sexiest one when compared to other languages. But still you're not going to switch language just because the syntax, right? Now you can switch syntax without leaving the language, meet LadyPHP!

LadyPHP is a <abbr title="Hey, that's not weird at all!">preprocessor for PHP</abbr>, that allows you to write code with cleaner syntax. It can do conversion in **both ways**, so you can start writing your current projects with lady syntax right away and save them back to PHP, your collaborators won't notice.

This software is still **under heavy development**, syntax and API can be changed.

## Demo

You can [try JavaScript version](http://ladyphp.honzanovak.com) of LadyPHP right in your browser.

## Download

- [Source code on GitHub](http://github.com/unu/ladyphp)
- [Executable PHAR file](http://db.tt/ITnDm5KI)
- [Plugin for Sublime Text 3](http://github.com/unu/sublime-ladyphp)
- [Plugin for Vim](http://github.com/unu/vim-ladyphp)

## Syntax reference

<table>
  <tr><th>LadyPHP</th><th>PHP</th></tr>
  <tr><td><code>x</code></td><td><code>$x</code></td></tr>
  <tr><td><code>obj.x</code></td><td><code>$obj->x</code></td></tr>
  <tr><td><code>Cls.x</code></td><td><code>Cls::$x</code></td></tr>
  <tr><td><code>@x</code></td><td><code>$this->x</code></td></tr>
  <tr><td><code>@@x</code></td><td><code>self::$x</code></td></tr>
  <tr><td><code>public f()</code></td><td><code>public function f()</code></td></tr>
  <tr><td><code>[k: 'v']</code></td><td><code>['k' => 'v']</code></td></tr>
  <tr><td><code>x ~ y</code></td><td><code>x . y</code></td></tr>
  <tr><td><code>&lt;?</code></td><td><code>&lt;?php</code></td></tr>
</table>

Semicolons at the end of lines are optional.

To write operators `@` and `~`, you have to escape them with `\`.

## Usage from command line

```sh
ladyphp file.lady  # creates file.php
ladyphp file.php   # creates file.lady
ladyphp -r dir/    # converts all php files in directory to ladyphp
ladyphp -w dir/    # watches directory and converts updated lady files
```

## Credits

LadyPHP is created by [Honza Novák](http://honzanovak.com) and it's licensed under a [Creative Commons BY-SA](http://creativecommons.org/licenses/by-sa/4.0/).
