<?php

require_once 'classes/General/Autoloader.php';
\General\Autoloader::register();

require_once "../vendor/autoload.php";

\psDebug\Debug::$displayErrors = true;
\psDebug\Debug::$displayTrace = true;
\psDebug\Debug::$sendTrace  = false;
\psDebug\Debug::$errorHoldsExecution = true;
\psDebug\Debug::$writeTrace  = true;
\psDebug\Debug::$writeFile = 'logs/error.log';
\psDebug\Debug::create();