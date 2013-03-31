<?php

namespace General;

/**
 * Debugging and error handling support class
 * 
 * @author Pawel Spychalski <pawel@spychalski.info>
 * @link http://www.spychalski.info
 * @version 0.6.5
 * @category Common
 * @copyright 2009 Lynx-IT Pawel Stanislaw Spychalski
 * @license MIT
 * @see wfErrorHandler by Grzegorz Godlewski
 * 
 */
class Debug  {
	
	/**
	 * Display error code and trace?
	 *
	 * @var boolean
	 */
	static $displayTrace = true;
	
	/**
	 * Send error code using psErrorSender?
	 *
	 * @var boolean
	 */
	static $sendTrace = false;
	
	/**
	 * Write error code?
	 *
	 * @var boolean
	 */
	static $writeTrace = false;
	
	static $writeFile = 'error.log';
	
	/**
	 * display error on uncaght exceptions and errors?
	 *
	 * @var boolean
	 */
	static $displayErrors = true;
	
	/**
	 * Standard error message
	 *
	 * @var string
	 */
	static $standardErrorText = 'Unexpected error!';
	
	/**
	 * Additional error text
	 *
	 * @var string
	 */
	static $additionalErrorText = '';
	
	/**
	 * error sender configuration array
	 *
	 * @var array
	 */
	static $senderConfig = array ('url' => '', 'path' => '', 'port' => 80, 'sender' => 'unknown' );
	
	/**
	 * Enter description here...
	 *
	 * @var boolean
	 */
	static $errorHoldsExecution = true;
	
	/**
	 * Error names
	 *
	 * @var array
	 */
	protected static $errorType = array (E_ERROR => 'ERROR', E_WARNING => 'WARNING', E_PARSE => 'PARSING ERROR', E_NOTICE => 'NOTICE', E_CORE_ERROR => 'CORE ERROR', E_CORE_WARNING => 'CORE WARNING', E_COMPILE_ERROR => 'COMPILE ERROR', E_COMPILE_WARNING => 'COMPILE WARNING', E_USER_ERROR => 'USER ERROR', E_USER_WARNING => 'USER WARNING', E_USER_NOTICE => 'USER NOTICE', E_STRICT => 'STRICT NOTICE', E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR' );
	
	/**
	 * Get prased microtime
	 *
	 * @return float
	 */
	static function sGetMicrotime() {
		list ( $usec, $sec ) = explode ( " ", microtime () );
		return (( float ) $usec + ( float ) $sec);
	}
	
	/**
	 * static constructor
	 *
	 */
	static public function create() {
		
		if (self::$displayErrors) {
			$flags = E_ALL;
			ini_set ( 'display_errors', 1 );
		} else {
			$flags = 0;
			ini_set ( 'display_errors', 0 );
		}
		
		set_error_handler ( array ('\General\Debug', "errorHandler" ), $flags );
		set_exception_handler ( array ('\General\Debug', "exceptionHandler" ) );
	
	}
	
	/**
	 * function formats error to readable form
	 *
	 * @param string $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param string $errline
	 * @return string
	 */
	static protected function formatErrorText($errno, $errstr, $errfile, $errline) {
		
		$retVal = '<div><b>' . self::$errorType [$errno] . ':</b> ' . $errstr . '</div>';
		$retVal .= '<div><b>File:</b> ' . $errfile . '</div>';
		$retVal .= '<div><b>Line:</b> ' . $errline . '</div>';
		
		return $retVal;
	
	}
	
	/**
	 * Formats error message and returns as string
	 *
	 * @param string $text
	 * @return string
	 */
	static protected function formatError($text) {
		
		$retVal = '<div style="margin: 10px; padding: 10px; width: 90ex; text-align: left; border: 1px solid #000; background-color: #D10000; color: black;">' . $text . '</div>';
		
		return $retVal;
	}
	
	/**
	 * Returns 'nice' formatted error message
	 *
	 * @param string $text
	 * @param array $config
	 * @return string
	 */
	static public function displayBox($text = '', $config = null) {
		
		if (! isset ( $config ['attach'] )) {
			$config ['attach'] = true;
		}
		
		if ($text == '') {
			$text = self::$standardErrorText;
		}
		
		$retVal = '<p style="margin: 0.2em; font-size: 1.5em; color: #000000;">' . $text . '<p>';
		
		if ($config ['attach'] && ! empty ( self::$additionalErrorText )) {
			$retVal .= '<p style="margin: 0.2em; color: black;">' . self::$additionalErrorText . '</p>';
		}
		
		$retVal = "<div style='font-size: 0.8em; margin: 10px auto; width: 60ex; text-align: center; border: 1px solid #000; padding: 1em; font-family: Tahoma; background-color: #FFFFA7;'>" . $retVal . "</div>";
		
		return $retVal;
	
	}
	
	/**
	 * Error handler
	 *
	 * @param string $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param string $errline
	 */
	static public function errorHandler($errno, $errstr, $errfile, $errline) {
		
		/*
     * Is line has been supressed with an @, do not throw an exception
     */
		if (error_reporting () == 0) {
			return;
		}
		
		$errorFullText = self::formatErrorText ( $errno, $errstr, $errfile, $errline );
		
		/*
     * Hold execution
     */
		if (self::$errorHoldsExecution) {
			throw new DebugException ( $errorFullText, $errno );
		} else {
			/*
     * display error
     */
			if (self::$displayErrors) {
				echo self::displayBox ();
			}
			/*
       * Message error
       */
			if (self::$displayTrace) {
				echo self::formatError ( $errorFullText );
			}
			
			/*
     * Send message do psHelpdesk
     */
			if (self::$sendTrace) {
				self::send ( $errorFullText );
			}
		}
	
	}
	
	/**
	 * Formats exception messages
	 *
	 * @param Exception $exception
	 * @return string
	 */
	static protected function formatExceptionMessage(\Exception $exception) {
		
		$errorFullText = '<div><b>New ' . get_class ( $exception ) . '</b></div>';
		$errorFullText .= '<div><b>File:</b> ' . $exception->getFile () . '</div>';
		$errorFullText .= '<div><b>Line:</b> ' . $exception->getLine () . '</div>';
		$errorFullText .= '<div><b>Message:</b> ' . $exception->getMessage () . '</div>';
		
		$trace = $exception->getTrace ();
		
		if (get_class ( $exception ) != 'DebugException') {
			array_unshift ( $trace, array ('file' => $exception->getFile (), 'line' => $exception->getLine (), 'function' => 'throw ' . get_class ( $exception ), 'args' => array ($exception->getMessage (), $exception->getCode () ) ) );
		}
		
		$traceString = '';
		foreach ( $trace as $tTraceItem ) {
			
			if (empty ( $tTraceItem ['file'] )) {
				$tTraceItem ['file'] = '';
			}
			if (empty ( $tTraceItem ['line'] )) {
				$tTraceItem ['line'] = '';
			}
			
			$traceString .= '<li>' . basename ( $tTraceItem ['file'] ) . " at line #" . $tTraceItem ['line'] . "\t";
			if (isset ( $tTraceItem ['class'] )) {
				$traceString .= $tTraceItem ['class'] . '::' . $tTraceItem ['function'] . '(';
			} elseif (isset ( $tTraceItem ['function'] )) {
				$traceString .= $tTraceItem ['function'] . '(';
			}
			
			if (! empty ( $tTraceItem ['args'] )) {
				$separator = '';
				foreach ( $tTraceItem ['args'] as $arg ) {
					$traceString .= $separator . self::getArgument ( $arg );
					$separator = ', ';
				}
				$traceString .= ')';
			}
			$traceString .= "</li>";
		}
		
		$errorFullText .= '<div><b>Backtrace:</b> <ul style="margin: 0.2em;">' . $traceString . '</ul></div>';
		
		return $errorFullText;
	}
	
	/**
	 * Exception handler
	 *
	 * @param exception $ex
	 */
	static public function exceptionHandler(\Exception $exception) {
		
		$errorFullText = self::formatExceptionMessage ( $exception );
		
		if (self::$displayErrors) {
			echo self::displayBox ();
		}
		
		if (self::$displayTrace) {
			echo self::formatError ( $errorFullText );
		}
		
		if (self::$sendTrace) {
			self::send ( $errorFullText );
		}
	
	}
	
	/**
	 * Converts variable into short text
	 *
	 * @param mixed $arg Variable
	 * @return string
	 */
	static protected function getArgument($arg) {
		
		switch (mb_strtolower ( gettype ( $arg ) )) {
			case 'string' :
				return ('"' . str_replace ( array ("\n", "\"" ), array ('', '\"' ), $arg ) . '"');
			
			case 'boolean' :
				return ( bool ) $arg;
			
			case 'object' :
				return 'object(' . get_class ( $arg ) . ')';
			
			case 'array' :
				return 'array[' . count ( $arg ) . ']';
			
			case 'resource' :
				return 'resource(' . get_resource_type ( $arg ) . ')';
			
			default :
				return var_export ( $arg, true );
		}
	}
	
	/**
	 * Error sending function, sends messages psHelpdesk
	 *
	 * @param string $text
	 * @return boolean
	 */
	static public function send($text) {
		
		/**
		 * If sender configuration is missing, skip sending
		 */
		if (! self::$sendTrace || empty ( self::$senderConfig ['url'] ) || empty ( self::$senderConfig ['port'] )) {
			return false;
		}
		
		$data ['company'] = self::$senderConfig ['sender'];
		$data ['text'] = urlencode ( $text );
		$data ['parameters'] = serialize ( $_REQUEST );
		$data ['referer'] = $_SERVER ['HTTP_HOST'];
		$data ['userName'] = '';
		
		$data = serialize ( $data );
		
		$fp = fsockopen ( self::$senderConfig ['url'], self::$senderConfig ['port'], $errno, $errstr, 10 );
		
		if (! $fp) {
			return false;
		}
		
		fputs ( $fp, "POST " . self::$senderConfig ['path'] . " HTTP/1.1\r\n" );
		fputs ( $fp, "Host: " . self::$senderConfig ['url'] . "\r\n" );
		fputs ( $fp, "Referer: " . $_SERVER ['HTTP_HOST'] . "\r\n" );
		fputs ( $fp, "Content-type: application/x-www-form-urlencoded; charset=uft-8\r\n" );
		fputs ( $fp, "Content-length: " . strlen ( $data ) . "\r\n" );
		fputs ( $fp, "Connection: close\r\n\r\n" );
		fputs ( $fp, $data );
		
		fclose ( $fp );
		
		return true;
	}
	
	/**
	 * Error write function
	 *
	 * @param string $text
	 * @return boolean
	 */
	static public function write($text) {
		
		$text = str_replace ( '<div>', '', $text );
		$text = str_replace ( '</div>', '; ', $text );
		
		$data ['text'] = "\n" . date ( 'Y-m-d H:i' ) . '; ' . $text;
		
		$data ['parameters'] = serialize ( $_REQUEST );
		
		$tFile = fopen ( self::$writeFile, 'a' );
		
		fputs ( $tFile, $data ['text'] );
		
		fclose ( $tFile );
		
		return true;
	}
	
	/**
	 * Controlled throw for catch block
	 *
	 * @param string $message
	 * @param Exception $exception
	 * @param array $config
	 * @return string
	 */
	static public function cThrow($message = null, \Exception $exception = null, $config = null) {
		
		if (! isset ( $config ['display'] )) {
			$config ['display'] = self::$displayTrace;
		}
		
		if (! isset ( $config ['send'] )) {
			$config ['send'] = self::$sendTrace;
		}
		
		if (! isset ( $config ['write'] )) {
			$config ['write'] = self::$writeTrace;
		}
		
		$retVal = '';
		
		if (empty ( $message )) {
			$message = self::$standardErrorText;
		}
		
		$retVal .= self::displayBox ( $message );
		
		/**
		 * if exception has been sent, try to display or send it
		 */
		if (! empty ( $exception )) {
			
			$errorFullText = self::formatExceptionMessage ( $exception );
			
			if ($config ['display']) {
				$retVal .= self::formatError ( $errorFullText );
			}
			
			if ($config ['send']) {
				self::send ( $errorFullText );
			}
			
			if ($config ['write']) {
				self::write ( $errorFullText );
			}
		
		}
		
		return $retVal;
	}
	
	/**
	 * Halt program execution
	 *
	 * @param string $message
	 * @param Exception $exception
	 * @param array $config
	 */
	static public function halt($message = null, \Exception $exception = null, $config = null) {
		
		echo self::cThrow ( $message, $exception, $config );
		exit ( 0 );
	}
	
	/**
	 * print_r alias with <pre> and border
	 *
	 * @param mixed $value
	 */
	public static function print_r($value) {
		
		echo "<div style=\" color: #000000; border: solid; border-width: 1px; width: 600px; position: absolute; background-color: #FFFED8; z-index: 100; padding: 6px;\"><pre>";
		print_r ( $value );
		echo "</pre></div>";
	}

}

/**
 * psDebug exception class
 * 
 * @author Pawel Spychalski <pawel@spychalski.info>
 * @link http://www.spychalski.info
 * @version 1
 * @category universal
 * @copyright 2009 Lynx-IT Pawel Stanislaw Spychalski
 * @see psDebug
 * 
 */
class DebugException extends \Exception {

}

/**
 * Exception for custom usage
 * 
 * @author Pawel Spychalski <pawel@spychalski.info>
 * @link http://www.spychalski.info
 * @version 1
 * @copyright 2009 Lynx-IT Pawel Stanislaw Spychalski
 * @see Debug
 */
class CustomException extends \Exception {

}