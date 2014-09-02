<?php

namespace Datatheke\Bundle\CoreBundle\Tests\Security\Voter;

use Datatheke\Bundle\CoreBundle\Security\Voter\LibraryAdminVoter;

use Datatheke\Bundle\CoreBundle\Document\Library;
use Datatheke\Bundle\CoreBundle\Document\User;

class LibraryAdminVoterTest extends \PHPUnit_Framework_TestCase
{
    public function testSupportsAttribute()
    {
        $voter = new LibraryAdminVoter();

        $this->assertFalse($voter->supportsAttribute('LIBRARY_FOO'));
        $this->assertTrue($voter->supportsAttribute('LIBRARY_ADMIN'));
    }

    public function testSupportsClass()
    {
        $voter = new LibraryAdminVoter();

        $this->assertFalse($voter->supportsClass('Foo\\Bar'));
        $this->assertTrue($voter->supportsClass('Datatheke\\Bundle\CoreBundle\\Document\\Library'));
    }

    public function testUnsupportedObject()
    {
        $voter   = new LibraryAdminVoter();
        $library = $this->getMock('Foo\\Bar\\Game');
        $token   = $this->getMock('Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface');

        $this->assertSame(LibraryAdminVoter::ACCESS_ABSTAIN, $voter->vote($token, $library, array()));
    }

    public function testUnsupportedAttributes()
    {
        $voter   = new LibraryAdminVoter();
        $library = new Library();
        $token   = $this->getMock('Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface');

        $this->assertSame(LibraryAdminVoter::ACCESS_ABSTAIN, $voter->vote($token, $library, array('FOO', 'Bar')));
    }

    public function testVoteDenied()
    {
        $voter   = new LibraryAdminVoter();
        $user    = new User();
        $library = new Library();
        $library->setOwner($user);

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue(new User()))
        ;

        $this->assertSame(LibraryAdminVoter::ACCESS_DENIED, $voter->vote($token, $library, array('LIBRARY_ADMIN')));
    }

    public function testVoteGranted()
    {
        $voter   = new LibraryAdminVoter();
        $user    = new User();
        $library = new Library();
        $library->setOwner($user);

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user))
        ;

        $this->assertSame(LibraryAdminVoter::ACCESS_GRANTED, $voter->vote($token, $library, array('LIBRARY_ADMIN')));
    }
}
