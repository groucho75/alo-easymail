<?php

class SampleTest extends AloEasymail_UnitTestCase {

	function testIsPluginActive() {
		$this->assertTrue( is_plugin_active( 'alo-easymail/alo-easymail.php' ) );
	}
}

