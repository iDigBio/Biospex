<?php namespace Biospex\Composers;
/**
 * TopMenuComposer.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <79e6ef82@opayq.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */
use Cache;
use Menu\Menu;
use Cartalyst\Sentry\Sentry;
use Biospex\Repo\Navigation\NavigationInterface as Navigation;
use Illuminate\Http\Request;

class TopMenuComposer {
	/**
	 * @var Cache
	 */
	protected $cache;

	/**
	 * @var Sentry
	 */
	protected $sentry;

	/**
	 * @var Navigation
	 */
	protected $navigation;

	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * @var array
	 */
    protected $topmenu = array();

	public function __construct (
		Cache $cache,
		Sentry $sentry,
		Navigation $navigation,
		Request $request)
	{
		$this->cache = $cache;
		$this->sentry = $sentry;
        $this->navigation = $navigation;
        $this->request = $request;
    }

    public function compose($view)
    {
         $this->topmenu = Cache::rememberForever('topmenu', function()
        {
            return $this->navigation->getMenu('topmenu');
        });

        $this->checkPermission();

        if ($this->topmenu->isEmpty())
            return $view->with('topmenu', null);

        $this->buildMenu();

        return $view->with('topmenu', Menu::handler('topmenu')->render());
    }

    protected function checkPermission()
    {
		$user = $this->sentry->getUser();
        foreach ($this->topmenu as $key => $item)
        {
            $permissions  = explode(',', $item->permission);
            if(is_null($user) || ! $user->hasAccess($permissions))
                 unset($this->topmenu[$key]);
        }
    }

    public function buildMenu() {

        Menu::handler('topmenu', array('class' => 'nav navbar-nav'))->hydrate(function()
            {
                return $this->topmenu;
            },
            function($children, $item)
            {
                if ($this->request->is($item->url)) $item->addClass('active');
                $children->add($item->url, trans($item->name), Menu::items($item->name));
            });

        Menu::handler('topmenu')->getItemsAtDepth(0)->map(function($item)
        {
            if($item->hasChildren())
            {
                $item->addClass('dropdown');

                $item->getChildren()
                    ->addClass('dropdown-menu');

                $item->getContent()
                    ->addClass('dropdown-toggle')
                    ->dataToggle('dropdown')
                    ->nest(' <b class="caret"></b>');
            }
        });
    }
}
