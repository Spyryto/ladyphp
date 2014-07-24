<?php

return [
  'keywords' => 'abstract|and|as|break|callable|case|catch|class|clone|const
    |continue|declare|default|do|echo|else(if)?|end(declare|for(each)?|if
    |switch|while)?|extends|false|final|for(each)?|function|global|goto|if
    |implements|include(_once)?|instanceof|insteadof|interface|namespace|new
    |null|or|parent|print|private|protected|public|require(_once)?|return
    |self|static|switch|throw|trait|true|try|use|var|while|xor|yield|array
    |binary|bool(ean)?|double|float|int(eger)?|object|real|string|unset',
  'methodPrefix' => '\\b(?:private|protected|public)(?:\\s+ static)?\\s+',
  'classId' => '(^|[^>$]|[^-]>) \\b(?:self|static|parent|[A-Z]\\w*|_+[A-Z])\\b',
  'varId' => '\\b (?:[a-z]\\w*|_+[a-z]\\w*|GLOBALS|_SERVER|_REQUEST|_POST|_GET
    |_FILES|_ENV|_COOKIE|_SESSION) \\b',
  'toPhp' => [
    '\\$' => '\\\\$', // escape dollars
    '(^|[^\\\\]) @@' => '$1self::', // @@ to self
    '(^|[^\\\\]) @' => '$1$this->', // @ to $this
    '\\.([^.=0-9])' => '->$1', // dots to arrows
    '\\.(\\.|->)' => '.', // duplicated dots to single dot
    '({classId}) ->' => '$1::', // arrows to two colons
    '(^|[^>$\\\\]) ({varId} (?!\\() )' => '$1$$2', // add dollars
    '(^|[^\\\\]) \\$ ({keywords}) \\b' => '$1$2', // remove dollars from keywords
    '<\\?\\$php \\b' => '<?php', // remove dollars from opening tags
    '(?m)^ (\\s* function \\s*) \\$' => '$1', // remove dollars from function names
    '(^|[^\\s\\\\]) : (\\s)' => '$1 =>$2', // colons to double arrows
    '(\\b case \\b [^\\v]*) \\s =>' => '$1:', // remove double arrows from cases
    '<\\? (?!php\\b|=)' => '<?php', // convert short opening tag to long tag
    '({methodPrefix}) ({varId} \\s*\\( )' => '$1function $2', // add functions
    '\\\\@' => '@', // unescape @
    '\\\\ \\$' => '$', // unescape dollars
    '\\\\:' => ':', // unescape colons
  ],
  'toLady' => [
    '@' => '\\@', // escape @
    '(->) \\$' => '$1\\\\$', // escape dollars before dynamic properties
    '\\$\\$' => '\\\\$\\\\$', // escape dollars before dynamic variables
    '\\$ ({keywords}) \\b' => '\\\\$$1', // escape dollars before keywords
    '(?m) (^|[^\\s]) : (\\s)' => '$1\\\\:$2', // escape colons after cases
    '\\$this->' => '@', // $this to @
    '\\b self::' => '@@', // self to @@
    '\\. (?![=0-9])' => '..', // dots to double dots
    '->' => '.', // arrows to dots
    '({classId}) ::' => '$1.', // double colons to dots
    '(^|[^\\\\]) \\$ ({varId} \\b (?!\\s*\\() )' => '$1$2', // remove dolars
    '(^|[^\\s]) \\s? => (\\s)' => '$1:$2', // double arrows to colons
    '<\\?php \\b' => '<?', //self::convert long opening tag to short tag
    '({methodPrefix}) function \\s+ ({varId} \\s*\\()' => '$1$2', // remove functions
    '\\\\ \\$' => '$', // unescape dollars before keywords
  ],
  // patterns for inline html, strings and comments
  'tokens' => '^(?: (?: \\?> (?:[^<]|<[^?])* (<\\?(?:php\\b)?)? )
    |(?: "[^"\\\\]*(?:\\\\[\\s\\S][^"\\\\]*)*" | \'[^\'\\\\]*(?:\\\\[\\s\\S][^\'\\\\]*)*\' )
    |(?: //[^\\n]*\\n | /\\* (?:[^*]|\\*(?!/))* \\*/) )',
];
