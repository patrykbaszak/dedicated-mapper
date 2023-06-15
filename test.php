<?php

return function (mixed $data): mixed {
    $constructorArguments = [];
    $mapped = new PBaszak\MessengerMapperBundle\Tests\Performance\Post(...$constructorArguments);
    $mapped->title = $data['title'];
    $mapped->content = $data['content'];
    $mapped->author = $data['author'];
    $mapped->createdAt = new \DateTime($data['createdAt']);

    return $mapped;
};
