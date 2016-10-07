<?php

namespace UserBundle\Tests\Controller;

use ApiBundle\Tests\Controller\BaseControllerTest;

class DefaultControllerTest extends BaseControllerTest
{
    public function testAuth()
    {
        $this->auth();
    }
}
