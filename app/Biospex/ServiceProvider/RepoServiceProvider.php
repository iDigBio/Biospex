<?php namespace Biospex\ServiceProvider;
/**
 * RepoServiceProvider.php
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

use Illuminate\Support\ServiceProvider;
use Biospex\Repo\Group\GroupRepository;
use Biospex\Repo\User\UserRepository;
use Biospex\Repo\Session\SentrySession;
use Biospex\Repo\Permission\PermissionRepository;
use Permission;

class RepoServiceProvider extends ServiceProvider {

    /**
     * Register the binding
     */
    public function register()
    {
        $app = $this->app;

        // Bind the Session Repository
        $app->bind('Biospex\Repo\Session\SessionInterface', function($app)
        {
            return new SentrySession(
                $app['sentry']
            );
        });

        // Bind the User Repository
        $app->bind('Biospex\Repo\User\UserInterface', function($app)
        {
            return new UserRepository(
                $app['sentry'], new PermissionRepository(new Permission)
            );
        });

        // Bind the Group Repository
        $app->bind('Biospex\Repo\Group\GroupInterface', function($app)
        {
            return new GroupRepository(
                $app['sentry'],  new PermissionRepository(new Permission)
            );
        });

        // Bind project repository
        $app->bind('Biospex\Repo\Project\ProjectInterface', 'Biospex\Repo\Project\ProjectRepository');

        // Bind expedition repository
        $app->bind('Biospex\Repo\Expedition\ExpeditionInterface', 'Biospex\Repo\Expedition\ExpeditionRepository');

        // Bind permission repository
        $app->bind('Biospex\Repo\Permission\PermissionInterface', 'Biospex\Repo\Permission\PermissionRepository');

        // Bind navigation repository
        $app->bind('Biospex\Repo\Navigation\NavigationInterface', 'Biospex\Repo\Navigation\NavigationRepository');

        // Bind subject repository
        $app->bind('Biospex\Repo\Subject\SubjectInterface', 'Biospex\Repo\Subject\SubjectRepository');

        // Bind subjectdoc repository
        $app->bind('Biospex\Repo\SubjectDoc\SubjectDocInterface', 'Biospex\Repo\SubjectDoc\SubjectDocRepository');

        // bind import repository
        $app->bind('Biospex\Repo\Import\ImportInterface', 'Biospex\Repo\Import\ImportRepository');

        // bind meta repository
        $app->bind('Biospex\Repo\Meta\MetaInterface', 'Biospex\Repo\Meta\MetaRepository');

        // bind workflow manager
        $app->bind('Biospex\Repo\WorkflowManager\WorkflowManagerInterface', 'Biospex\Repo\WorkflowManager\WorkflowManagerRepository');

        // bind workflow repository
        $app->bind('Biospex\Repo\WorkFlow\WorkFlowInterface', 'Biospex\Repo\WorkFlow\WorkFlowRepository');

        // bind download repository
        $app->bind('Biospex\Repo\Download\DownloadInterface', 'Biospex\Repo\Download\DownloadRepository');

        // bind message bag
        $app->bind('Illuminate\Support\Contracts\MessageProviderInterface', 'Illuminate\Support\MessageBag');
    }

}