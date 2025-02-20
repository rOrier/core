<?php

namespace ROrier\Core\Features\Bootstrappers;

use Exception;
use ROrier\Config\Services\Analyzer;
use ROrier\Config\Services\ConfigParsers\ArrayParameterParser;
use ROrier\Container\Interfaces\ServiceLibraryInterface;
use ROrier\Core\Components\DataLoader;
use ROrier\Core\Interfaces\KernelInterface;
use ROrier\Container\Services\Compilator;
use ROrier\Container\Services\Libraries\ServiceLibrary;
use ROrier\Container\Services\ServiceSpecCompilers\FactoryCompiler;
use ROrier\Container\Services\ServiceSpecCompilers\InheritanceCompiler;

trait LibraryBootstrapperTrait
{
    private array $additionalServicesData = array();

    /**
     * @param array $data
     * @return $this
     * @throws Exception
     */
    public function addServicesData(array $data): self
    {
        if ($this->hasService('library.services')) {
            throw new Exception("Library already built. Add services data before using buildParameters().");
        }

        $this->additionalServicesData[] = $data;

        return $this;
    }

    /**
     * @return ServiceLibraryInterface
     */
    protected function buildLibrary(): ServiceLibraryInterface
    {
        return new ServiceLibrary(
            $this->getServicesData(),
            $this->getService('compilator.spec.services')
        );
    }

    protected function getServicesData(): array
    {
        /** @var KernelInterface $kernel */
        $kernel = $this->getService('kernel');

        $dataLoader = new DataLoader($kernel, $this);

        return $dataLoader->getData('getServicesConfigPath', 'services', $this->additionalServicesData);
    }

    /**
     * @return Compilator
     */
    protected function buildSpecCompilator(): Compilator
    {
        return new Compilator([
            new InheritanceCompiler($this->getService('analyzer.config.inheritance')),
            new FactoryCompiler()
        ]);
    }

    /**
     * @return Analyzer
     */
    protected function buildConfigInheritanceAnalyzer(): Analyzer
    {
        return new Analyzer([
            new ArrayParameterParser($this->getDelayedParameters())
        ]);
    }
}
