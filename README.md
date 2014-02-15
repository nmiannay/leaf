[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/miannay-nicolas/leaf/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

# Leaf - template compiler for PHP5.4

*Leaf* is a template compiler heavily influenced by [Haml](http://haml-lang.com)
and implemented for PHP 5.4.

## Features

  - high speed
  - easy to use
  - no tag prefix
  - caches your compiled files
  - template inheritance
  - template inclusion
  - clear & beautiful HTML output
  - PHP Stream Wrapper interface

## Usage
    require 'autoload.php';

    include 'leaf://Views/part1.php';
    
Note: Call \Leaf\Stream::__load() to register the stream wrapper if you don't use my autoload

## Syntax

### Indentation

Leaf is indentation based, however currently only supports a _2 space_ indent.

### Tags

A tag is simply a leading word:

    html

for example is converted to `<html></html>`

tags can also have ids:

    div#container

which would render `<div id="container"></div>`

or classes

    div.user-details

renders `<div class="user-details"></div>`

and sure, both:

    div#foo.bar.baz

renders `<div id="foo" class="bar baz"></div>`

there is also a syntactic sugar for div:

    #foo
    .bar

which outputs: `<div id="foo"></div><div class="bar"></div>`

### Tag Text

Simply place some content after the tag:

    p wahoo!

renders `<p>wahoo!</p>`.

well cool, but how about large bodies of text:

    p
      | foo bar baz
      | rawr rawr
      | super cool
      | go Leaf go

renders `<p>foo bar baz rawr.....</p>`


### Echo

Actually want `<?php echo ... ?>` instead:

    p
      = $something

now we have `<p><?php echo $something ?></p>`

### Nesting

    ul
      li one
      li = $two
      li three

### Attributes

To set yout html attributes just seprate them by a space:

    a href='/login' title='View login page'

Note 1: You can also embed text after an argument list

    a href='/login' title='View login page' Login
Note 2: Leading / Trailing whitespaces are _ignored_ for attributes.

### Doctypes

To add a doctype simply use `doctype` followed by your value:

    doctype html

Will output html 5's doctype

### Pure HTML

Leaf handles the pure HTML so you can mix the two syntaxes

    div.foo
      <p id="myP">bar</p>
      <div>
        p foo
      </div>
renders:

    <div class="foo">
      <p id="myP">bar</p>
      <div>
        <p>foo</p>
      </div>
    </div>

## Comments

### Leaf Comments

To comment one line start it with a '/'

    / TODO
    / = $notnow
    - $foo = "bar"
    p
      / = "bar"
      = $foo

will be compiled into:

    <?php $foo = "bar"; ?>
    <p>
      <?php echo $foo ?>
    </p>

## Code

### Buffered / Non-buffered output

Leaf currently supports two classifications of executable code. The first
is prefixed by `-`, and is not buffered:

    - var $foo = 'bar';

This can be used for conditionals, or iteration:

    - foreach $items as $item
      p = $item

Due to Leaf's buffering techniques the following is valid as well:

    - if $foo
      ul
        li yay
        li foo
        li worked
    - else
      p hey! didnt work

Second is echoed code, which is used to
echo a return value, which is prefixed by `=`:

    - $foo = 'bar'
    = $foo
    h1 = $foo

Which outputs

    <?php $foo = 'bar' ?>
    <?php echo $foo ?>
    <h1><?php echo $foo ?></h1>

### Code blocks

Also, Leaf has Code Blocks, that supports basic PHP template syntax:

    ul
      - while true
        li item

Will be rendered to:

    <ul>
      <?php while (true): ?>
        <li>item</li>
      <?php endwhile; ?>
    </ul>

Do not bother with parentheses if they are not present, they will be added.

There's bunch of default ones: `if`, `else`, `elseif`, `while`, `for`, `foreach`.

## Template blocks

  Template blocks always start with a `@`, they will be replaced at the compilation time not a the runtime

### Inheritance

To extend a template file, you must set as the first instruction `@extends ` in your file. So in child.php you will have something like this:

    @extends: 'Views/layout.php'

Then you can define block in your child view

    @block: body_content
      p Hello world!

If the block is defined in the parent view, then its contents will completely replaced by the contents of the block the child
layout.php:

    doctype html
    html
      head
        title Foo
      body
        @block: body_content
          | parent text

will output:

    <!DOCTYPE html>
    <html>
      <head>
        <title>Foo</title>
      </head>
      <body>
        <p>Hello world!</p>
      </body>
    </html>

To keep the parent content add `@parent` where you want to include its content. If we edit our child.php:

    @extends: 'Views/layout.php'

    @block: body_content
      p Hello world!
      @parent

Will be rendered to:

    <!DOCTYPE html>
    <html>
      <head>
        <title>Foo</title>
      </head>
      <body>
        <p>Hello world!</p>
        parent text
      </body>
    </html>

Note: Leaf files will be parsed from children to parent

### Inclusion

With Leaf you can also include template inside other with `@render`, child.php:

    <p>Hello world!</p>

layout.php:

    doctype html
    html
      head
        title Foo
      body
        @render: child.php

Will be rendered to:

    <!DOCTYPE html>
    <html>
      <head>
        <title>Foo</title>
      </head>
      <body>
        <p>Hello world!</p>
      </body>
    </html>

Note: Leaf files will be parsed from parent to included file
