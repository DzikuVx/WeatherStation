<?php

require_once 'classes/General/Autoloader.php';
\General\Autoloader::register();

\General\Session::start();

\General\Environment::set();

\General\Debug::$displayErrors 			= true;
\General\Debug::$displayTrace 			= true;
\General\Debug::$sendTrace 				= false;
\General\Debug::$errorHoldsExecution 	= true;
\General\Debug::$writeTrace 			= true;
\General\Debug::$writeFile 				= 'logs/error.log';
\General\Debug::create();