<?php

namespace Datatheke\Bundle\FrontendBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DatathekeFrontendBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
