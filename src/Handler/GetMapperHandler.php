<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Handler;

use PBaszak\MessengerMapperBundle\GetMapper;
use PBaszak\MessengerMapperBundle\Mapper;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
class GetMapperHandler
{
    public function __construct()
    {
        
    }

    public function __invoke(GetMapper $query): Mapper
    {
        
    }
}
