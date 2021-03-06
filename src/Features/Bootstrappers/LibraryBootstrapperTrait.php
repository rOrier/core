<?php

namespace ROrier\Core\Features\Bootstrappers;

use Exception;
use ROrier\Config\Tools\CollectionTool;
use ROrier\Container\Interfaces\ServiceLibraryInterface;
use ROrier\Core\Interfaces\ConfigLoaderInterface;
use ROrier\Core\Interfaces\KernelInterface;
use ROrier\Core\Interfaces\PackageInterface;
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
            $this->buildServicesData(),
            $this->getService('compilator.spec.services')
        );
    }

    protected function buildServicesData()
    {
        $data = [];

        /** @var KernelInterface $kernel */
        $kernel = $this->getService('kernel');

        /** @var PackageInterface $package */
        foreach ($kernel->getPackages() as $package) {
            $path = $package->getServicesConfigPath();

            if (!empty($path) && is_dir($path)) {
                /** @var ConfigLoaderInterface $configLoader */
                $configLoader = $this->getPackageLoader($package);

                CollectionTool::merge($data, $configLoader->load($path));
            }
        }

        foreach ($this->additionalServicesData as $additionalData) {
            CollectionTool::merge($data, $additionalData);
        }

        return $data;
    }

    /**
     * @return Compilator
     */
    protected function buildSpecCompilator()
    {
        return new Compilator([
            new InheritanceCompiler(),
            new FactoryCompiler()
        ]);
    }
}
