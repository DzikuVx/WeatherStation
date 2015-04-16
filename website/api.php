<?php

require_once 'common.php';

if (isset($_GET['callback']))  {
  echo "{$_GET['callback']}(" . \Controller\Api::getInstance()->get() . ")";
} else {
  echo \Controller\Api::getInstance()->get();
}
