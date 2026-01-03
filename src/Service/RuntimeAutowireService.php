<?php declare(strict_types=1);

namespace Tbessenreither\PhpMultithread\Service;

use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;
use Symfony\Component\HttpKernel\KernelInterface;


final class RuntimeAutowireService
{

    public function __construct(
        private KernelInterface $kernel,
        private LoggerInterface $logger,
    ) {
    }

    public function create(string $class): object
    {
        $ref = new ReflectionClass($class);

        // No constructor => no dependencies
        $constructor = $ref->getConstructor();
        if (!$constructor) {
            return $ref->newInstance();
        }

        $args = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {


                if ($type->getName() === "Psr\Log\LoggerInterface") {
                    $args[] = $this->logger;
                    continue;
                }

                $dependencyClass = $type->getName();

                if (!$this->kernel->getContainer()->has($dependencyClass)) {
                    throw new RuntimeException(sprintf(
                        'Cannot autowire argument $%s of %s: service %s not found.',
                        $param->getName(),
                        $class,
                        $dependencyClass
                    ));
                }

                $args[] = $this->kernel->getContainer()->get($dependencyClass);
                continue;
            }

            // Scalars or untyped parameters require defaults
            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
                continue;
            }

            throw new RuntimeException(sprintf(
                'Cannot autowire argument $%s of %s: unsupported parameter type.',
                $param->getName(),
                $class
            ));
        }

        return $ref->newInstanceArgs($args);
    }

}
