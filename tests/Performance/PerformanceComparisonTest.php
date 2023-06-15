<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Tests\Performance;

use PBaszak\MessengerCacheBundle\Contract\Replaceable\MessengerCacheManagerInterface;
use PBaszak\MessengerMapperBundle\Attribute\MappingCallback;
use PBaszak\MessengerMapperBundle\Mapper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/** @group performance */
class PerformanceComparisonTest extends KernelTestCase
{
    private Serializer $serializer;
    private Mapper $mapper;

    protected function setUp(): void
    {
        /** @var MessengerCacheManagerInterface $cacheManager */
        $cacheManager = self::getContainer()->get(MessengerCacheManagerInterface::class);
        $cacheManager->clear(pool: 'messenger_mapper');
        $this->serializer = self::getContainer()->get(SerializerInterface::class);
        $this->mapper = self::getContainer()->get(Mapper::class);
    }

    public function postAsRawArray(): array
    {
        return [
            'title' => 'Lorem ipsum',
            'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam',
            'author' => 'John Doe',
            'createdAt' => '2020-01-01 00:00:00',
        ];
    }

    public function commentAsRawArray(): array
    {
        return [
            'post' => $this->postAsRawArray(),
            'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam, nisl nisl aliquet nisl, nec aliquet nunc nisl nec nisl. Nullam auctor, nisl nec ultricies aliquam',
            'author' => 'John Doe',
            'createdAt' => '2022-01-01 00:00:00',
        ];
    }

    public function postAsObject(): Post
    {
        $post = new Post();
        $rawArray = $this->postAsRawArray();
        $post->title = $rawArray['title'];
        $post->content = $rawArray['content'];
        $post->author = $rawArray['author'];
        $post->createdAt = new \DateTime($rawArray['createdAt']);

        return $post;
    }

    public function commentAsObject(): Comment
    {
        $comment = new Comment();
        $rawArray = $this->commentAsRawArray();
        $comment->post = $this->postAsObject();
        $comment->content = $rawArray['content'];
        $comment->author = $rawArray['author'];
        $comment->createdAt = new \DateTime($rawArray['createdAt']);

        return $comment;
    }

    /** @test */
    public function testSimpleObjectDenormalizationPerformance(): void
    {
        $expected = $this->postAsObject();

        $serializerActual = $this->serializer->denormalize($this->postAsRawArray(), Post::class, 'array');
        $this->assertEquals($expected, $serializerActual);
        $mapperActual = $this->mapper->fromArrayToClassObject($this->postAsRawArray(), Post::class);
        $this->assertEquals($expected, $mapperActual);

        $times = [];
        for ($i = 0; $i < 1000; $i++) {
            $start = microtime(true);
            $serializerActual = $this->serializer->denormalize($this->postAsRawArray(), Post::class, 'array');
            $stop = microtime(true);
            $this->assertEquals($expected, $serializerActual);
            $times['serializer'][] = $stop - $start;

            $start = microtime(true);
            $mapperActual = $this->mapper->fromArrayToClassObject($this->postAsRawArray(), Post::class);
            $stop = microtime(true);
            $this->assertEquals($expected, $mapperActual);
            $times['mapper'][] = $stop - $start;
        }

        $avgSerializer = array_sum($times['serializer']) / count($times['serializer']);
        $avgMapper = array_sum($times['mapper']) / count($times['mapper']);

        $this->assertLessThan($avgSerializer / 2, $avgMapper); // Mapper is at least 2 times faster than Symfony Serializer

        /**
         * Results:
         * - Symfony Serializer: 0.000447s
         * - Messenger Mapper: 0.000151s
         * 
         * Messenger Mapper is ~3 times faster than Symfony Serializer
         */
    }

    /** @test */
    public function testNestedObjectDenormalizationPerformance(): void
    {
        $expected = $this->commentAsObject();

        $serializerActual = $this->serializer->denormalize($this->commentAsRawArray(), Comment::class, 'array');
        $this->assertEquals($expected, $serializerActual);
        $mapperActual = $this->mapper->fromArrayToClassObject($this->commentAsRawArray(), Comment::class);
        $this->assertEquals($expected, $mapperActual);

        $times = [];
        for ($i = 0; $i < 1000; $i++) {
            $start = microtime(true);
            $serializerActual = $this->serializer->denormalize($this->commentAsRawArray(), Comment::class, 'array');
            $stop = microtime(true);
            $this->assertEquals($expected, $serializerActual);
            $times['serializer'][] = $stop - $start;

            $start = microtime(true);
            $mapperActual = $this->mapper->fromArrayToClassObject($this->commentAsRawArray(), Comment::class);
            $stop = microtime(true);
            $this->assertEquals($expected, $mapperActual);
            $times['mapper'][] = $stop - $start;
        }

        $avgSerializer = array_sum($times['serializer']) / count($times['serializer']);
        $avgMapper = array_sum($times['mapper']) / count($times['mapper']);

        $this->assertLessThan($avgSerializer / 4, $avgMapper); // Mapper is at least 4 times faster than Symfony Serializer

        /**
         * Results:
         * - Symfony Serializer: 0.000810s
         * - Messenger Mapper: 0.000157s
         * 
         * Messenger Mapper is ~5 times faster than Symfony Serializer
         */
    }
}

class Post
{
    public string $title;
    public string $content;
    public string $author;
    #[MappingCallback('new \DateTime(%s)', activateOnMapping: MappingCallback::TO_DESTINATION)]
    public \DateTime $createdAt;
}

class Comment
{
    public Post $post;
    public string $content;
    public string $author;
    #[MappingCallback('new \DateTime(%s)', activateOnMapping: MappingCallback::TO_DESTINATION)]
    public \DateTime $createdAt;
}
