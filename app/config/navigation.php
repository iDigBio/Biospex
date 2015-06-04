<?php
return [
    'topmenu' => [
        // group
        ['id' => 1, 'url' => "/groups", 'label' => trans("groups.groups"), 'permission' => 'group_view'],
        ['id' => 2, 'url' => "/projects", 'label' => trans("projects.projects"), 'permission' => 'project_view'],
        // user
        ['id' => 3, 'url' => "#users", 'label' => trans("users.users"), 'permission' => 'user_view'],
        ['id' => 4, 'url' => "/users", 'label' => trans("pages.list"), 'permission' => 'user_view', 'parent_id' => 3],
        ['id' => 5, 'url' => "/users/create", 'label' => trans("pages.create"), 'permission' => 'user_create', 'parent_id' => 3],
        // Admin
        ['id' => 6, 'url' => "#admin", 'label' => trans("users.admin"), 'permission' => 'superuser'],
        ['id' => 7, 'url' => "/phpinfo", 'label' => trans("pages.server_info"), 'permission' => 'superuser', 'parent_id' => 6],
        ['id' => 8, 'url' => "/clear", 'label' => trans("pages.clear_cache"), 'permission' => 'superuser', 'parent_id' => 6],
        ['id' => 9, 'url' => "/ocr", 'label' => trans("pages.ocr_files"), 'permission' => 'superuser', 'parent_id' => 6],
    ]
];
