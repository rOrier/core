<?php

namespace ROrier\Core\Features\Bootstrappers;

use Exception;
use ROrier\Config\Tools\CollectionTool;
use ROrier\Container\Interfaces\ServiceLibraryInterface;
use ROrier\Core\Interfaces\PackageInterface;
use ROrier\Container\Services\Compilator;
use ROrier\Container\Services\Libraries\ServiceLibrary;
use ROrier\Container\Services\ServiceSpecCompilers\FactoryCompiler;
use ROrier\Container\Services\ServiceSpecCompilers\InheritanceCompiler;

trait LibraryBootstrapperTrait
{
    private array $additionalData = array();

    /**
     * @param array $data
     * @return $this
     * @throws Exception
     */
    public function addServicesData(array $data): self
    {
        if (isset($this->boot['library.services'])) {
            throw new Exception("Library already built. Add services data before using buildParameters().");
        }

        $this->additionalData[] = $data;

        return $this;
    }

    public function buildLibrary(): self
    {
        $this->boot['library.services'] = new ServiceLibrary(
            $this->buildServicesData(),
            $this->buildSpecCompilator()
        );

        return $this;
    }

    protected function buildServicesData()
    {
        $data = [];

        /** @var PackageInterface $package */
        foreach ($this->getKernel()->getPackages() as $package) {
            CollectionTool::merge($data, $package->buildServices());
        }

        foreach ($this->additionalData as $additionalData) {
            CollectionTool::merge($data, $additionalData);
        }

        return $data;
    }

    /**
     * @return Compilator
     */
    protected function buildSpecCompilator()
    {
        return $this->boot['compilator.spec.services'] = new Compilator([
            new InheritanceCompiler(),
            new FactoryCompiler()
        ]);
    }

    /**
     * @return ServiceLibraryInterface
     * @throws Exception
     */
    protected function getLibrary(): ServiceLibraryInterface
    {
        if (!isset($this->boot['library.services'])) {
            throw new Exception("Library not found. Use buildLibrary() to make it available.");
        }

        return $this->boot['library.services'];
    }
}