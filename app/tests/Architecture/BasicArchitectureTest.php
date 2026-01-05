<?php

declare(strict_types=1);

namespace App\Tests\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

final class BasicArchitectureTest
{
    public function testDependencyLayer_Application(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('App\Application'))
            ->canOnlyDependOn()
            ->classes(
                Selector::inNamespace('App\Application'),
                Selector::inNamespace('App\Domain'),
                Selector::inNamespace('Webmozart\Assert'),
                Selector::inNamespace('Symfony\Component\Uid'),
                Selector::classname('Psr\Log\LoggerInterface'),
                Selector::classname('RuntimeException'),
                Selector::classname('DomainException'),
                Selector::classname('InvalidArgumentException'),
                Selector::classname('Exception'),
            );
    }

    public function testDependencyLayer_Infrastructure(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('App\Infrastructure'))
            ->canOnlyDependOn()
            ->classes(
                Selector::inNamespace('App\Infrastructure'),
                Selector::inNamespace('App\Domain'),
                Selector::classname('/^App\\\Application\\\(Storage|Notifier|Contract)\\\(Command|Query|Service|).+Interface$/', true),
                Selector::classname('/^App\\\Application\\\Exception\\\.+(Exception|Interface)$/', true),
                Selector::classname('/^App\\\Application\\\Bus\\\Command\\\.+Interface$/', true),
                Selector::classname('/^App\\\Application\\\UseCases\\\.+\\\.+(?<!Service|Handler)$/', true),
                Selector::inNamespace('App\Application\ValueObject'),
                Selector::inNamespace('App\Application\Enum'),
                Selector::inNamespace('App\Application\Exception'),
                Selector::inNamespace('App\Application\Dto'),
                Selector::inNamespace('App\Application\Permission'),
                Selector::inNamespace('App\Application\Service'),
                Selector::inNamespace('App\Application\UseCases'),
                Selector::inNamespace('App\UserInterface\Http\Model'),
                Selector::inNamespace('Psr'),
                Selector::inNamespace('Jose'),
                Selector::inNamespace('Monolog'),
                Selector::inNamespace('Http\Client'),
                Selector::inNamespace('Faker'),
                Selector::inNamespace('Webmozart\Assert'),
                Selector::inNamespace('Doctrine'),
                Selector::inNamespace('Symfony'),
                Selector::inNamespace('Symfony\Contracts\HttpClient'),
                Selector::classname('DateTimeImmutable'),
                Selector::classname('DateTimeInterface'),
                Selector::classname('Exception'),
                Selector::classname('RuntimeException'),
                Selector::classname('Throwable'),
            );
    }

    public function testDependencyLayer_UserInterface(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('App\UserInterface'))
            ->canOnlyDependOn()
            ->classes(
                Selector::inNamespace('App\UserInterface'),
                Selector::inNamespace('App\Application\Dto'),
                Selector::inNamespace('App\Application\Enum'),
                Selector::inNamespace('App\Application\Exception'),
                Selector::inNamespace('App\Application\ValueObject'),
                Selector::inNamespace('App\Application\UseCases'),
                Selector::inNamespace('App\Application\Storage'),
                Selector::inNamespace('App\Application\Permission'),
                Selector::inNamespace('App\Application\Service'),
                Selector::classname('App\Application\Bus\Command\CommandBusInterface'),
                Selector::classname('App\Infrastructure\Security\Voter\AccessVoter'),
                Selector::inNamespace('Psr'),
                Selector::inNamespace('Symfony\Component\Console'),
                Selector::inNamespace('Symfony\Component\Security\Core'),
                Selector::inNamespace('Symfony\Component\Validator'),
                Selector::inNamespace('Symfony\Component\Uid'),
                Selector::inNamespace('Symfony\Component\HttpFoundation'),
                Selector::inNamespace('Symfony\Component\HttpKernel\Attribute'),
                Selector::inNamespace('Symfony\Component\HttpKernel\Exception'),
                Selector::inNamespace('Symfony\Component\WebLink'),
                Selector::inNamespace('Symfony\Component\Routing'),
                Selector::inNamespace('Symfony\Component\Serializer'),
                Selector::inNamespace('Webmozart\Assert'),
                Selector::inNamespace('OpenApi\Attributes'),
                Selector::inNamespace('Nelmio\ApiDocBundle\Attribute'),
            );
    }

    public function testDependencyLayer_Domain(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('App\Domain'))
            ->canOnlyDependOn()
            ->classes(
                Selector::inNamespace('App\Domain'),
                Selector::inNamespace('Symfony\Component\Uid'),
                Selector::classname('Webmozart\Assert\Assert'),
                Selector::classname('Traversable'),
                Selector::classname('Countable'),
                Selector::classname('IteratorAggregate'),
                Selector::classname('DateTimeImmutable'),
                Selector::classname('DateTimeInterface'),
                Selector::classname('BackedEnum'),
            );
    }
}
