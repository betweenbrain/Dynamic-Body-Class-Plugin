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

		$classes = implode(' ', $this->generateClasses());

		preg_match_all('/<body[^>]*class="([a-zA-Z0-9-_ ]*)"[^>]*>/', $buffer, $matches, PREG_SET_ORDER);

		$buffer = str_replace($matches[0][1], $matches[0][1] . ' ' . $classes, $buffer);

		JResponse::setBody($buffer);

		return TRUE;
	}

	function generateClasses() {

		// To get an application object
		$app = JFactory::getApplication();

		// Returns a reference to the global language object
		$lang = JFactory::getLanguage();

		// Returns a reference to the menu object
		$menu = $app->getMenu();

		// Get the current view
		$view = JRequest::getCmd('view');

		// The default menu item
		$default = $menu->getActive() == $menu->getDefault($lang->getTag());

		if ($default) {
			$classes[] = 'default';
		}

		// Component Name
		$classes[] = JRequest::getCmd('option');

		// Item ID
		$itemId = JRequest::getInt('Itemid', 0);

		$classes[] = 'item-' . $itemId;

		// Article ID
		if ($view == 'article') {
			$classes[] = 'article-' . JRequest::getInt('id');
		}

		// Section ID
		function getSection($id) {
			$database = JFactory::getDBO();
			if ((substr(JVERSION, 0, 3) >= '1.6')) {
				return NULL;
			} elseif (JRequest::getCmd('view', 0) == "section") {
				return $id;
			} elseif (JRequest::getCmd('view', 0) == "category") {
				$sql = "SELECT section FROM #__categories WHERE id = $id ";
				$database->setQuery($sql);

				return $database->loadResult();
			} elseif (JRequest::getCmd('view', 0) == "article") {
				$temp = explode(":", $id);
				$sql  = "SELECT sectionid FROM #__content WHERE id = " . $temp[0];
				$database->setQuery($sql);

				return $database->loadResult();
			}
		}

		$secId = getSection(JRequest::getInt('id'));

		if ($secId) {
			$classes[] = 'section-' . $secId;
		}

		// Category ID
		function getCategory($id) {
			$database = JFactory::getDBO();
			if (JRequest::getCmd('view', 0) == "section") {
				return NULL;
			} elseif ((JRequest::getCmd('view', 0) == "category") || (JRequest::getCmd('view', 0) == "categories")) {
				return $id;
			} elseif (JRequest::getCmd('view', 0) == "article") {
				$temp = explode(":", $id);
				$sql  = "SELECT catid FROM #__content WHERE id = " . $temp[0];
				$database->setQuery($sql);

				return $database->loadResult();
			}
		}

		$catId = getCategory(JRequest::getInt('id'));

		if ($catId) {
			$classes[] = 'category-' . $catId;
		}

		// Menu item alias
		if ($itemId) {
			$classes[] = $app->getMenu()->getActive()->alias;
		}

		return $classes;
	}
}