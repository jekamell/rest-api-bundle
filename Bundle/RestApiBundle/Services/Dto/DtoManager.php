<?php

namespace Mell\Bundle\RestApiBundle\Services\Dto;

use Mell\Bundle\RestApiBundle\Helpers\DtoHelper;
use Mell\Bundle\RestApiBundle\Model\Dto;
use Mell\Bundle\RestApiBundle\Services\RequestManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class DtoManager
{
    /** @var RequestManager */
    protected $requestManager;
    /** @var DtoValidator */
    protected $dtoValidator;
    /** @var DtoHelper */
    protected $dtoHelper;
    /** @var Yaml */
    protected $yaml;
    /** @var  FileLocator */
    protected $fileLocator;
    /** @var string */
    protected $configPath;
    /** @var array */
    protected $dtoConfig;

    /**
     * DtoManager constructor.
     * @param RequestManager $requestManager
     * @param DtoValidator $dtoValidator
     * @param DtoHelper $dtoHelper
     * @param FileLocator $fileLocator
     * @param string $configPath
     */
    public function __construct(
        RequestManager $requestManager,
        DtoValidator $dtoValidator,
        DtoHelper $dtoHelper,
        FileLocator $fileLocator,
        $configPath
    ) {
        $this->requestManager = $requestManager;
        $this->dtoValidator = $dtoValidator;
        $this->dtoHelper = $dtoHelper;
        $this->fileLocator = $fileLocator;
        $this->configPath = $configPath;
    }

    /**
     * @param $data
     * @param string $dtoType
     * @return Dto
     */
    public function createDto($data, $dtoType)
    {
        $dtoConfig = $this->getDtoConfig();
        $this->validateDto($dtoConfig, $dtoType, $data);

        $dtoData = [];
        foreach ($dtoConfig[$dtoType]['fields'] as $field => $options) {
            $getter = isset($options['getter']) ? $options['getter'] : $this->dtoHelper->getFieldGetter($field);
            $dtoData[$field] = call_user_func([$data, $getter]);
        }

        return new Dto($dtoData);
    }

    public function createDtoCollection(array $data, $dtoType)
    {

    }

    /**
     * @return array
     */
    protected function getDtoConfig()
    {
        // TODO: caching
        if ($this->dtoConfig === null) {
            $absolutePath = $this->fileLocator->locate($this->configPath);
            $this->dtoConfig = Yaml::parse(file_get_contents($absolutePath));
        }

        return $this->dtoConfig;
    }

    /**
     * @param array $dtoConfig
     * @param string $dtoType
     * @param \stdClass $object
     */
    protected function validateDto($dtoConfig, $dtoType, $object)
    {
        $this->dtoValidator->validateDto($dtoConfig, $object, $dtoType);
    }
}
