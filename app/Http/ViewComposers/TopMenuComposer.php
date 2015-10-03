<?php

namespace App\Http\ViewComposers;

class TopMenuComposer
{
    /**
     * @var array
     */
    protected $topMenu = [];

    public function __construct()
    {
        $this->topMenu = top_menu();
    }

    public function compose($view)
    {
        $this->checkPermission();

        if (empty($this->topMenu)) {
            return $view->with('topMenu', null);
        }

        $this->buildMenu();

        return $view->with('topMenu', \Menu::handler('topMenu')->render());
    }

    protected function checkPermission()
    {
        $user = \Sentry::getUser();
        foreach ($this->topMenu as $key => $item) {
            $permissions  = explode(',', $item['permission']);
            if (is_null($user) || ! $user->hasAccess($permissions)) {
                unset($this->topMenu[$key]);
            }
        }
    }

    public function buildMenu()
    {
        \Menu::handler('topMenu', ['class' => 'nav navbar-nav'])->hydrate(function () {
            return $this->topMenu;
        },
            function ($children, $item) {
                if (\Request::is($item['url'])) {
                    $item->addClass('active');
                }
                $children->add($item['url'], $item['label'], \Menu::items($item['label']));
            });

        \Menu::handler('topMenu')->getItemsAtDepth(0)->map(function ($item) {
            if ($item->hasChildren()) {
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
