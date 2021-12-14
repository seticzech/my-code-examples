<?php

namespace App\Service\Erp\Acl;

use App\Service\Erp\Acl\ResourceService;

class ActionService
{
    
    const CODE_CREATE = 'CREATE';
    const CODE_UPDATE = 'UPDATE';
    const CODE_VIEW = 'VIEW';
    const CODE_DELETE = 'DELETE';
    const CODE_DUPLICATE = 'DUPLICATE';
    const CODE_PARTICIPATE = 'PARTICIPATE';
    
    const CODE_USER_UPDATE_ROLE = 'UPDATE_ROLE';
    const CODE_APP_UPDATE_SETTINGS = 'UPDATE_SETTINGS';
    
    const CODE_LMM_ENROLL_OTHER_USERS_TO_COURSES = 'ENROLL_OTHER_USERS_TO_COURSES';
    const CODE_LMM_LIST_ENROLLED_COURSES = 'LIST_ENROLLED_COURSES';
    const CODE_LMM_LIST_COURSE_USERS = 'LIST_COURSE_USERS';
    const CODE_LMM_LIST_USER_COURSES = 'LIST_USER_COURSES';
    
    
    protected $resourceToActionMap = [
        ResourceService::CODE_APP => [
            self::CODE_APP_UPDATE_SETTINGS
        ],
        ResourceService::CODE_USER => [
            self::CODE_USER_UPDATE_ROLE
        ],
        ResourceService::CODE_LMM_COURSE => [
            self::CODE_CREATE,
            self::CODE_UPDATE,
            self::CODE_DELETE,
            self::CODE_DUPLICATE,
            self::CODE_PARTICIPATE,
            self::CODE_LMM_ENROLL_OTHER_USERS_TO_COURSES,
            self::CODE_LMM_LIST_ENROLLED_COURSES,
            self::CODE_LMM_LIST_COURSE_USERS,
            self::CODE_LMM_LIST_USER_COURSES,
        ],
        ResourceService::CODE_LMM_COURSE_CATEGORY => [
            self::CODE_CREATE,
            self::CODE_UPDATE,
            self::CODE_DELETE
        ],
        ResourceService::CODE_LMM_LESSON_TYPE => [
            self::CODE_CREATE,
            self::CODE_VIEW,
        ],
        ResourceService::CODE_LMM_SEGMENT_TYPE => [
            self::CODE_CREATE,
            self::CODE_VIEW,
        ],
    ];
    
}
