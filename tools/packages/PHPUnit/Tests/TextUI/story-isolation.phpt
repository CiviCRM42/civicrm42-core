--TEST--
phpunit --process-isolation --story BowlingGameSpec ../Samples/BowlingGame/BowlingGameSpec.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--process-isolation';
$_SERVER['argv'][3] = '--story';
$_SERVER['argv'][4] = 'BowlingGameSpec';
$_SERVER['argv'][5] = '../Samples/BowlingGame/BowlingGameSpec.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

The story result printer cannot be used in process isolation.
