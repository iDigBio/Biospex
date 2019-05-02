<?php

namespace App\Presenters;

class UserPresenter extends Presenter
{
    /**
     * Return full name or email of user.
     *
     * @return mixed|string
     */
    public function fullNameOrEmail()
    {
        $firstName = $this->model->profile->first_name;
        $lastName = $this->model->profile->last_name;
        $email = $this->model->email;

        $isNull = null === $firstName || null === $lastName ? true : false;

        return $isNull ? $email : $firstName.' '.$lastName;
    }

    /**
     * Return return delete icon.
     *
     * @return string
     */
    public function deleteGroupUserIcon()
    {
        return '<a href="'.route('admin.groups.deleteUser', [
                $this->model->pivot->group_id,
                $this->model->id,
            ]).'" class="prevent-default"
            title="'.__('pages.delete').' '.__('pages.member').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.__('pages.delete').' '.__('pages.member').'?" data-content="'.__('messages.group_delete_user_msg').'">
            <i class="fas fa-trash-alt"></i></a>';
    }
}