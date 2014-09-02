<?php

namespace Datatheke\Bundle\CoreBundle\Document;

use FOS\UserBundle\Document\User as BaseUser;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @MongoDB\Document
 *
 * @ExclusionPolicy("all")
 */
class User extends BaseUser
{
    /**
     * @MongoDB\Id(strategy="auto")
     *
     * @Expose
     */
    protected $id;

    /**
     * @Expose
     *
     * @Assert\Regex("/^[a-zA-Z0-9-_\.]+$/")
     */
    protected $username;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @Assert\True(message="This username is not available")
     */
    public function isAuthorizedName()
    {
        if (in_array($this->username, array('doc', 'docs', 'documentation', 'documentations', 'api', 'help',
            'contacts', 'contact', 'contactus', 'contact-us', 'public', 'private', 'datatheke',
            'shelf', 'collection', 'collections', 'shelves', 'library', 'libraries', 'about', 'aboutus', 'about-us', 'about-datatheke',
            'exemple', 'exemples', 'blog', 'blogs', 'template', 'templates', 'models', 'search',
            'try', 'demo', 'live', 'guide', 'userguide', 'dev', 'devel', 'developer', 'developers', 'user', 'users',
            'data', 'open', 'opendata', 'open-data', 'my-account', 'rest', 'autocomplete'
            ))) {
            return false;
        }

        return true;
    }
}
