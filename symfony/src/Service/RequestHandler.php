<?php

namespace App\Service;

use App\Entity\EntityAbstract;
use App\Exception\InsufficientDataException;
use JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class RequestHandler
{

    const DEFAULT_DATA_FORMAT = 'json';

    /**
     * @var string
     */
    private $dataFormat;

    /**
     * @var Sanitizer
     */
    private $sanitizer;

    /**
     * @var SerializerInterface
     */
    private $serializer;


    public function __construct(Sanitizer $sanitizer, SerializerInterface $serializer)
    {
        $this->dataFormat = self::DEFAULT_DATA_FORMAT;
        $this->setSanitizer($sanitizer);
        $this->setSerializer($serializer);
    }

    /**
     * @param string|array $requestData
     * @param string $entityClass
     * @param string|null $format
     * @param array $context
     *
     * @return array|object
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    private function denormalizeToEntity(
        $requestData,
        string $entityClass,
        string $format = null,
        array $context = []
    ) {
        return $this->getSerializer()->denormalize(
            $requestData, $entityClass, $format, $context
        );
    }

    /**
     * @param Request $request
     * @param array $keys If specified works as filter for returned keys
     * @param bool $sanitize
     * @param string|null $format
     *
     * @return array|string|null
     */
    public function getBody(
        Request $request,
        array $keys = [],
        bool $sanitize = true,
        string $format = null
    ) {
        $result = $this->getBodyContent($request, $format);
        if ($result === null) {
            $result = [];
        }

        if (is_array($result) && !empty($keys)) {
            $result = array_filter($result, function ($key) use ($keys) {
                return in_array($key, $keys);
            }, ARRAY_FILTER_USE_KEY);
        }

        if ($sanitize) {
            $result = $this->getSanitizer()->sanitize($result);
        }

        return $result;
    }

    /**
     * @param Request $request
     * @param string|null $format
     *
     * @return mixed
     */
    private function getBodyContent(Request $request, string $format = null)
    {
        if (!strlen($request->getContent())) {
            return null;
        }
        if (!$format) {
            $format = $this->getDataFormat();
        }

        $result = null;

        switch ($format){
            case 'json':
                try {
                    $result = json_decode($request->getContent(), true);

                    if ($result === null) {
                        throw new JsonException(json_last_error_msg());
                    }
                } catch (JsonException $e) {
                    throw new InsufficientDataException($e->getMessage(), 0, $e);
                }
                break;
            default:
                $result = $request->getContent();
        }

        return $result;
    }

    /**
     * @param Request $request
     * @param array $excludedKeys
     * @param bool $sanitize
     * @param string|null $format
     *
     * @return array|string|null
     */
    public function getBodyExcluded(
        Request $request,
        array $excludedKeys = [],
        bool $sanitize = true,
        string $format = null
    ) {
        $result = $this->getBodyContent($request, $format);

        if (is_array($result) && !empty($excludedKeys)) {
            $result = array_filter($result, function ($key) use ($excludedKeys) {
                return !in_array($key, $excludedKeys);
            }, ARRAY_FILTER_USE_KEY);
        }

        if ($sanitize) {
            $result = $this->getSanitizer()->sanitize($result);
        }

        return $result;
    }

    public function getDataFormat(): string
    {
        return $this->dataFormat;
    }

    public function getSanitizer(): Sanitizer
    {
        return $this->sanitizer;
    }

    public function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }

    /**
     * @param Request|array $requestData
     * @param EntityAbstract $entity
     * @param string|null $format
     * @param array $context
     *
     * @return array|object
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function handlePatch(
        $requestData,
        $entity,
        ?string $format = null,
        array $context = []
    ) {
        $context = array_merge(
            [AbstractObjectNormalizer::OBJECT_TO_POPULATE => $entity],
            [AbstractObjectNormalizer::IGNORED_ATTRIBUTES => ['_id']],
            $context
        );
        $entityClass = get_class($entity);

        if ($requestData instanceof Request) {
            $requestData = $this->getBody($requestData);
        }
        
        return $this->denormalizeToEntity($requestData, $entityClass, $format, $context);
    }

    /**
     * @param Request|array|string $requestData
     * @param string $entityClass
     * @param string|null $format
     * @param array $context
     *
     * @return mixed
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function handlePost(
        $requestData,
        string $entityClass,
        ?string $format = null,
        array $context = []
    ) {
        $context = array_merge(
            [AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true],
            [AbstractObjectNormalizer::IGNORED_ATTRIBUTES => ['_id']],
            $context
        );

        if ($requestData instanceof Request) {
            $requestData = $this->getBody($requestData);
        }

        return $this->denormalizeToEntity($requestData, $entityClass, $format, $context);
    }

    /**
     * @param $requestData
     * @param string $entityClass
     * @param array $context
     *
     * @return array|object
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function handleQuery(
        $requestData,
        string $entityClass,
        array $context = []
    ) {
        $context = array_merge(
            [AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true],
            $context
        );

        if ($requestData instanceof Request) {
            $requestData = $requestData->query->all();
        }
        
        return $this->denormalizeToEntity($requestData, $entityClass, null, $context);
    }

    public function setSanitizer(Sanitizer $sanitizer): self
    {
        $this->sanitizer = $sanitizer;

        return $this;
    }

    public function setSerializer(SerializerInterface $serializer): self
    {
        $this->serializer = $serializer;

        return $this;
    }

}
