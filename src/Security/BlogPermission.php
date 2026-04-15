<?php

namespace PrestaShop\Module\Everpsblog\Security;

final class BlogPermission
{
    public const READ = 'EVERPSBLOG_READ';
    public const CREATE = 'EVERPSBLOG_CREATE';
    public const UPDATE = 'EVERPSBLOG_UPDATE';
    public const DELETE = 'EVERPSBLOG_DELETE';
    public const PUBLISH = 'EVERPSBLOG_PUBLISH';

    public const RES_POST = 'post';
    public const RES_CATEGORY = 'category';
    public const RES_TAG = 'tag';
    public const RES_AUTHOR = 'author';
    public const RES_COMMENT = 'comment';
}
