<?php

class AloEasymail_Placeholders extends AloEasymail_UnitTestCase {

	function test_placeholder_customlink_with_external_link() {
		$placeholder = '[CUSTOM-LINK href="http://www.wordpress.org"]';

		$replaced = alo_em_placeholders_replace_customlink_tag ( $placeholder, 1, false );

		$this->assertEquals(
			'<a href="http://www.wordpress.org" class="alo-easymail-link" style="">http://www.wordpress.org</a>'
			, $replaced
		);
	}

	function test_placeholder_customlink_with_external_link_and_title() {
		$placeholder = '[CUSTOM-LINK href="http://www.wordpress.org" title="visit WordPress site"]';

		$replaced = alo_em_placeholders_replace_customlink_tag ( $placeholder, 1, false );

		$this->assertEquals(
			'<a href="http://www.wordpress.org" class="alo-easymail-link" style="">visit WordPress site</a>',
			$replaced
		);
	}
}

