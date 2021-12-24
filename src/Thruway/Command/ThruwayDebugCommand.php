<?php

namespace App\Thruway\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Thruway\Annotation\AnnotationInterface;
use App\Thruway\Annotation\Register;
use App\Thruway\Annotation\Subscribe;
use App\Thruway\Mapping\URIClassMapping;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ThruwayDebugCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected static $defaultName = 'app:thruway:debug';
    protected static $defaultDescription = 'List registered RPC and Subscriptions';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument('uri', InputArgument::OPTIONAL, 'URI name to get additional information');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name           = $input->getArgument('uri');
        $kernel         = $this->container->get('wamp_kernel');
        $resourceMapper = $kernel->getResourceMapper();

        if ($name) {
            $mappings = $resourceMapper->getMappings();
            if (isset($mappings[$name])) {
                /** @var URIClassMapping $mapping */
                $mapping = $mappings[$name];
                $output->writeln("URI:    {$name}");
                $output->writeln("File:   {$mapping->getMethod()->getFileName()}");
                $output->writeln("Method: {$mapping->getMethod()->getName()}");
                $output->writeln("Type:   {$this->getAnnotationType($mapping->getAnnotation())}");
                $output->writeln("Arguments:");

                $table = new Table($output);
                $table->setStyle('compact');
                $table->setHeaders(['Name', 'Type', 'Optional', 'Default', 'Position']);

                $params = $mapping->getmethod()->getParameters();

                /** @var \ReflectionParameter $param */
                foreach ($params as $param) {

                    $table->addRow([
                        $param->getName(),
                        $this->getType($param),
                        $param->isOptional() ? 'true' : 'false',
                        $param->isDefaultValueAvailable() ? $param->getDefaultValue() : '',
                        $param->getPosition()
                    ]);
                }
                $table->render();

            } else {
                $output->writeln("Sorry, we couldn't find {$name}");
            }

        } else {
            $workers = $resourceMapper->getAllMappings();

            $table = new Table($output);
            $table->setStyle('compact');
            $table->setHeaders(['URI', 'Type', 'Worker', 'File', 'Method']);

            /** @var  URIClassMapping[] $mappings */
            foreach ($workers as $workerName => $mappings) {
                foreach ($mappings as $uri => $mapping) {

                    $table->addRow([
                        $uri,
                        $this->getAnnotationType($mapping->getAnnotation()),
                        $workerName,
                        $mapping->getMethod()->getFileName(),
                        $mapping->getMethod()->getName()
                    ]);
                }
            }

            $table->render();
        }
    }

    /**
     * @param AnnotationInterface $annotation
     * @return string
     */
    private function getAnnotationType(AnnotationInterface $annotation)
    {
        if ($annotation instanceof Register) {
            return "RPC";
        }

        if ($annotation instanceof Subscribe) {
            return "Subscription";
        }
    }

    private function getType(\ReflectionParameter $param)
    {
        $type = $param->getType();

        if ($type instanceof \ReflectionNamedType) {
            return $type->getName();
        }
    }
}
