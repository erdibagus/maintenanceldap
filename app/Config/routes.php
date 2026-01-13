<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
 
/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/View/Pages/home.ctp)...
 */
	// Router::connect('/', array('controller' => 'pages', 'action' => 'display', 'home'));
	// Router::connect('/index', array('controller' => 'pages', 'action' => 'display', 'home'));
	Router::connect('/', array('controller' => 'homes', 'action' => 'index'));
	Router::connect('/index', array('controller' => 'homes', 'action' => 'index'));
/**
 * ...and connect the rest of 'Pages' controller's URLs.
 */
	Router::connect('/pages/*', array('controller' => 'pages', 'action' => 'display'));
	Router::connect('/noscript', array('controller' => 'pages', 'action' => 'display', 'noscript'));
	Router::connect('/nologin', array('controller' => 'pages', 'action' => 'display', 'nologin'));
	Router::connect('/helps', array('controller' => 'pages', 'action' => 'display', 'help'));
	Router::connect('/logout', array('controller' => 'pages', 'action' => 'logout'));
	Router::connect('/logProcess', array('controller' => 'pages', 'action' => 'logProcess'));
	Router::connect('/getSetting', array('controller' => 'pages', 'action' => 'getSetting'));
	Router::connect('/openPage', array('controller' => 'pages', 'action' => 'openPage'));
	Router::parseExtensions('pdf');
/**
 * Load all plugin routes. See the CakePlugin documentation on
 * how to customize the loading of plugin routes.
 */
	CakePlugin::routes();

/**
 * Load the CakePHP default routes. Only remove this if you do not want to use
 * the built-in default routes.
 */
	require CAKE . 'Config' . DS . 'routes.php';
