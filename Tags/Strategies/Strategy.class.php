<?php
namespace Tags\Strategies;

abstract class TagStrategy
{
  abstract public function apply(\Tags\Tag $Tag);
}