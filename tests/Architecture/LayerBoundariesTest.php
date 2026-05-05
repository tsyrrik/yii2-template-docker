<?php

declare(strict_types=1);

namespace tests\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

class LayerBoundariesTest
{
    public function testJobsDoNotDependOnControllers(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('app\jobs'))
            ->shouldNotDependOn()
            ->classes(Selector::inNamespace('app\controllers'));
    }
}
