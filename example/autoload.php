<?php

require 'ClassLoader.class.php';

ClassLoader::registerNamespaces(array(
  'Leaf'                    => __DIR__.'/../src',
));
ClassLoader::register();
