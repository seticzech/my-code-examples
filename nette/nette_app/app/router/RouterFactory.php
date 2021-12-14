<?php

namespace App;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList;

        $router[] = new Route('login', 'Auth:login');
        $router[] = new Route('logout', 'Auth:logout');
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Home:default');

		return $router;
	}
}
