<?php

require 'ClassLoader.class.php';

ClassLoader::registerNamespaces(array(
  'Tags'                    => __DIR__.'/',
  'Tags/TagStrategies'      => __DIR__.'/Tags',
  'Tags/TemplateStrategies' => __DIR__.'/Tags',
));
ClassLoader::register();
