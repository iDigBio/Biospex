<?php namespace Biospex\ServiceProvider;
/**
 * FormServiceProvider.php
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
use Biospex\Form\Login\LoginForm;
use Biospex\Form\Login\LoginFormLaravelValidator;
use Biospex\Form\Register\RegisterForm;
use Biospex\Form\Register\RegisterFormLaravelValidator;
use Biospex\Form\Group\GroupForm;
use Biospex\Form\Group\GroupFormLaravelValidator;
use Biospex\Form\User\UserForm;
use Biospex\Form\User\UserFormLaravelValidator;
use Biospex\Form\ResendActivation\ResendActivationForm;
use Biospex\Form\ResendActivation\ResendActivationFormLaravelValidator;
use Biospex\Form\ForgotPassword\ForgotPasswordForm;
use Biospex\Form\ForgotPassword\ForgotPasswordFormLaravelValidator;
use Biospex\Form\ChangePassword\ChangePasswordForm;
use Biospex\Form\ChangePassword\ChangePasswordFormLaravelValidator;
use Biospex\Form\SuspendUser\SuspendUserForm;
use Biospex\Form\SuspendUser\SuspendUserFormLaravelValidator;
use Biospex\Form\Project\ProjectForm;
use Biospex\Form\Project\ProjectFormLaravelValidator;
use Biospex\Form\Expedition\ExpeditionForm;
use Biospex\Form\Expedition\ExpeditionFormLaravelValidator;
use Biospex\Form\Invite\InviteForm;
use Biospex\Form\Invite\InviteFormLaravelValidator;

class FormServiceProvider extends ServiceProvider {

    /**
     * Register the binding
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;

        // Bind the Login Form
        $app->bind('Biospex\Form\Login\LoginForm', function($app)
        {
            return new LoginForm(
                new LoginFormLaravelValidator( $app['validator'] ),
                $app->make('Biospex\Repo\Session\SessionInterface')
            );
        });

        // Bind the Register Form
        $app->bind('Biospex\Form\Register\RegisterForm', function($app)
        {
            return new RegisterForm(
                new RegisterFormLaravelValidator( $app['validator'] ),
                $app->make('Biospex\Repo\User\UserInterface')
            );
        });

        // Bind the Group Form
        $app->bind('Biospex\Form\Group\GroupForm', function($app)
        {
            return new GroupForm(
                new GroupFormLaravelValidator( $app['validator'] ),
                $app->make('Biospex\Repo\Group\GroupInterface')
            );
        });

        // Bind the User Form
        $app->bind('Biospex\Form\User\UserForm', function($app)
        {
            return new UserForm(
                new UserFormLaravelValidator( $app['validator'] ),
                $app->make('Biospex\Repo\User\UserInterface')
            );
        });

        // Bind the Resend Activation Form
        $app->bind('Biospex\Form\ResendActivation\ResendActivationForm', function($app)
        {
            return new ResendActivationForm(
                new ResendActivationFormLaravelValidator( $app['validator'] ),
                $app->make('Biospex\Repo\User\UserInterface')
            );
        });

        // Bind the Forgot Password Form
        $app->bind('Biospex\Form\ForgotPassword\ForgotPasswordForm', function($app)
        {
            return new ForgotPasswordForm(
                new ForgotPasswordFormLaravelValidator( $app['validator'] ),
                $app->make('Biospex\Repo\User\UserInterface')
            );
        });

        // Bind the Change Password Form
        $app->bind('Biospex\Form\ChangePassword\ChangePasswordForm', function($app)
        {
            return new ChangePasswordForm(
                new ChangePasswordFormLaravelValidator( $app['validator'] ),
                $app->make('Biospex\Repo\User\UserInterface')
            );
        });

        // Bind the Suspend User Form
        $app->bind('Biospex\Form\SuspendUser\SuspendUserForm', function($app)
        {
            return new SuspendUserForm(
                new SuspendUserFormLaravelValidator( $app['validator'] ),
                $app->make('Biospex\Repo\User\UserInterface')
            );
        });

        // Bind the Project Form
        $app->bind('Biospex\Form\Project\ProjectForm', function($app)
        {
            return new ProjectForm(
                new ProjectFormLaravelValidator( $app['validator'] ),
                $app->make('Biospex\Repo\Project\ProjectInterface')
            );
        });

        // Bind the Expedition Form
        $app->bind('Biospex\Form\Expedition\ExpeditionForm', function($app)
        {
            return new ExpeditionForm(
                new ExpeditionFormLaravelValidator( $app['validator'] ),
                $app->make('Biospex\Repo\Expedition\ExpeditionInterface')
            );
        });

        // Bind the SendInvite Form
        $app->bind('Biospex\Form\Invite\InviteForm', function($app)
        {
            return new InviteForm(
                new InviteFormLaravelValidator( $app['validator'] ),
                $app->make('Biospex\Repo\Invite\InviteInterface')
            );
        });

    }

}