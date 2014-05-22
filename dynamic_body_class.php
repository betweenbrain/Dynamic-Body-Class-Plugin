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

class plgSystemDynamic_body_class extends JPlugin
{

	function onAfterRender()
	{

		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			return false;
		}

		$buffer  = JResponse::getBody();
		$classes = implode(' ', $this->generateClasses());

		preg_match('/<body([^>]*)>/', $buffer, $body);

		if (preg_match('/class="([a-zA-Z0-9-_ ]*)"/', $body[1], $class))
		{
			$buffer = str_replace($class[1], $class[1] . ' ' . $classes, $buffer);
		}
		else
		{
			$buffer = str_replace('<body', '<body class="' . $classes . '" ', $buffer);
		}

		JResponse::setBody($buffer);

		return true;
	}

	function generateClasses()
	{

		// Application object
		$app = JFactory::getApplication();
		// Global language object
		$lang = JFactory::getLanguage();
		// Menu object
		$menu = $app->getMenu();
		// Class type mode
		$mode = $this->params->get('mode');
		// Current view
		$view = JRequest::getCmd('view');
		// Default menu item
		$default = $menu->getActive() == $menu->getDefault($lang->getTag());
		// Item ID
		$itemId = JRequest::getInt('Itemid', 0);
		// Article ID
		if ($view == 'article')
		{
			$articleId = JRequest::getInt('id');
		}

		// Section ID
		function getSection($id)
		{
			$database = JFactory::getDBO();
			if ((substr(JVERSION, 0, 3) >= '1.6'))
			{
				return null;
			}
			elseif (JRequest::getCmd('view', 0) == "section")
			{
				return $id;
			}
			elseif (JRequest::getCmd('view', 0) == "category")
			{
				$sql = "SELECT section FROM #__categories WHERE id = $id ";
				$database->setQuery($sql);

				return $database->loadResult();
			}
			elseif (JRequest::getCmd('view', 0) == "article")
			{
				$temp = explode(":", $id);
				$sql  = "SELECT sectionid FROM #__content WHERE id = " . $temp[0];
				$database->setQuery($sql);

				return $database->loadResult();
			}
		}

		$sectionId = getSection(JRequest::getInt('id'));

		// Category ID
		function getCategory($id)
		{
			$database = JFactory::getDBO();
			if (JRequest::getCmd('view', 0) == "section")
			{
				return null;
			}
			elseif ((JRequest::getCmd('view', 0) == "category") || (JRequest::getCmd('view', 0) == "categories"))
			{
				return $id;
			}
			elseif (JRequest::getCmd('view', 0) == "article")
			{
				$temp = explode(":", $id);
				$sql  = "SELECT catid FROM #__content WHERE id = " . $temp[0];
				$database->setQuery($sql);

				return $database->loadResult();
			}
		}

		$categoryId = getCategory(JRequest::getInt('id'));

		/*
		 * Build array of classes
		 */

		// Component Name
		$classes[] = JRequest::getCmd('option');

		if ($default)
		{
			$classes[] = 'default';
		}

		switch ($mode)
		{
			case 'id':
				// Item
				$classes[] = 'item-' . $itemId;
				// Article
				if ($articleId)
				{
					$classes[] = 'article-' . $articleId;
				}
				// Section
				if ($sectionId)
				{
					$classes[] = 'section-' . $sectionId;
				}
				// Category
				if ($categoryId)
				{
					$classes[] = 'category-' . $categoryId;
				}
				break;
			case 'alias':
				// Item
				$classes[] = $menu->getItem($itemId)->alias;
				// Article
				if ($articleId)
				{
					$article =& JTable::getInstance("content");
					$article->load($articleId);
					$classes[] = $article->get('alias');
				}
				// Section
				if ($sectionId)
				{
					$section =& JTable::getInstance("section");
					$section->load($sectionId);
					$classes[] = $section->get('alias');
				}
				// Category
				if ($categoryId)
				{
					$category =& JTable::getInstance("category");
					$category->load($categoryId);
					$classes[] = $category->get('alias');
				}
				break;
		}

		return $classes;
	}
}