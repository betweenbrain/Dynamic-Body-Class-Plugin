<?php defined('_JEXEC') or die;

/**
 * File       dynamic_body_class.php
 * Created    4/26/13 3:11 PM
 * Author     Matt Thomas
 * Website    http://betweenbrain.com
 * Email      matt@betweenbrain.com
 * Support    https://github.com/betweenbrain/
 * Copyright  Copyright (C) 2013 betweenbrain llc. All Rights Reserved.
 * License    GNU GPL v3 or later
 */

jimport('joomla.plugin.plugin');

class plgSystemDynamic_body_class extends JPlugin {

	function onAfterRender() {

		$app = JFactory::getApplication();

		if ($app->isAdmin()) {
			return FALSE;
		}

		$buffer = JResponse::getBody();

		$mode = $this->params->get('mode');

		$buffer = preg_replace('/<body(?s:(?!class).)*class=\"([a-zA-Z0-9-_ ]*)\"(?s:(?!class).)/i', '$1' , $buffer);

		JResponse::setBody($buffer);

		return TRUE;
	}
}