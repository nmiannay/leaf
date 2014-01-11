<?php
/**
*
*/
class Test1 extends Test
{

  public function test_simple_tag()
  {
    $expect  = '<?xml version="1.0" encoding="UTF-8"?><html></html>';
    $reality = $this->evalLeaf('html');

    $this->assertEqual($reality, $expect);
  }
  public function test_short_id()
  {
    $expect  = '<?xml version="1.0" encoding="UTF-8"?><div id="container"></div>';
    $reality = $this->evalLeaf('div#container');

    $this->assertEqual($reality, $expect);
  }
  public function test_short_class()
  {
    $expect  = '<?xml version="1.0" encoding="UTF-8"?><div class="user-details"></div>';
    $reality = $this->evalLeaf('div.user-details');

    $this->assertEqual($reality, $expect);
  }
  public function test_short_id_and_short_class()
  {
    $expect  = '<?xml version="1.0" encoding="UTF-8"?><div id="foo" class="bar baz"></div>';
    $reality = $this->evalLeaf('div#foo.bar.baz');

    $this->assertEqual($reality, $expect);
  }

  public function test_syntactic_sugar_for_div()
  {
    $expect  = '<?xml version="1.0" encoding="UTF-8"?><div id="foo"></div><div class="bar"></div>';
    $reality = $this->evalLeaf(
<<<HTML
#foo
.bar
HTML
);
    $this->assertEqual($reality, $expect);
  }


  public function test_tag_text()
  {
    $expect  = '<?xml version="1.0" encoding="UTF-8"?><p>wahoo!</p>';
    $reality = $this->evalLeaf('p wahoo!');

    $this->assertEqual($reality, $expect);
  }

  public function test_large_bodies_text()
  {
    $expect  = '<?xml version="1.0" encoding="UTF-8"?><p>foo bar baz rawr rawr super cool go Leaf go</p>';
    $reality = $this->evalLeaf(
<<<HTML
p
  | foo bar baz
  | rawr rawr
  | super cool
  | go Leaf go
HTML
);
    $this->assertEqual($reality, $expect);
  }

  public function test_echo()
  {
    $expect  = '<?xml version="1.0" encoding="UTF-8"?><p><?php echo $something; ?></p>';
    $reality = $this->evalLeaf(
<<<'HTML'
p
  = $something
HTML
);
    $this->assertEqual($reality, $expect);
  }

  public function test_nesting()
  {
    $expect  = '<?xml version="1.0" encoding="UTF-8"?><ul><li>one</li><li><?php echo $two; ?></li><li>three</li></ul>';
    $reality = $this->evalLeaf(
<<<'HTML'
ul
  li one
  li = $two
  li three
HTML
);
    $this->assertEqual($reality, $expect);
  }

  public function test_attributes()
  {
    $expect  = '<?xml version="1.0" encoding="UTF-8"?><a href="/login" title="View login page"></a>';
    $reality = $this->evalLeaf("a href='/login' title='View login page'");

    $this->assertEqual($reality, $expect);
  }

  public function test_attributes_and_text()
  {
    $expect  = '<?xml version="1.0" encoding="UTF-8"?><a href="/login" title="View login page">Login</a>';
    $reality = $this->evalLeaf("a href='/login' title='View login page' Login");

    $this->assertEqual($reality, $expect);
  }

  public function test_doctype()
  {
    $expect  = '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE html>';
    $reality = $this->evalLeaf("doctype html");

    $this->assertEqual($reality, $expect);
  }

  public function test_pure_html()
  {
    $expect  = '<?xml version="1.0" encoding="UTF-8"?><div class="foo"><p id="myP">bar</p><div><p>foo</p></div></div>';
    $reality = $this->evalLeaf(
<<<HTML
div.foo
  <p id="myP">bar</p>
  <div>
    p foo
  </div>
HTML
);

    $this->assertEqual($reality, $expect);
  }
//COMMENT
  public function test_comment()
  {
    $expect  = '<?xml version="1.0" encoding="UTF-8"?><?php $foo = "bar"; ?><p><?php echo $foo ?></p>';
    $reality = $this->evalLeaf(
<<<'HTML'
/ TODO
/ = $notnow
- $foo = "bar"
p
  / = "bar"
  = $foo
HTML
);

    $this->assertEqual($reality, $expect);
  }

//CODE
  public function test_affectation()
  {
    $expect  = '<?xml version="1.0" encoding="UTF-8"?><?php var $foo = \'bar\'; ?>';
    $reality = $this->evalLeaf('- var $foo = \'bar\';');

    $this->assertEqual($reality, $expect);
  }

  public function test_foreach()
  {
    $expect  = '<?xml version="1.0" encoding="UTF-8"?><?php foreach ($items as $item): ?><p><?php echo $item; ?></p><?php endforeach; ?>';
    $reality = $this->evalLeaf(
<<<'HTML'
- foreach $items as $item
  p = $item
HTML
);

    $this->assertEqual($reality, $expect);
  }

  public function test_if_else()
  {
    $expect  = '<?xml version="1.0" encoding="UTF-8"?><?php if ($foo): ?><ul><li>yay</li><li>foo</li><li>worked</li><?php else: ?><li><p>hey! didnt work</p></li></ul><?php endif; ?>';
    $reality = $this->evalLeaf(
<<<'HTML'
- if $foo
  ul
    li yay
    li foo
    li worked
- else
  p hey! didnt work
HTML
);

    $this->assertEqual($reality, $expect);
  }

  public function test_echoed()
  {
    $expect  = '<?xml version="1.0" encoding="UTF-8"?><?php $foo = \'bar\' ?><?php echo $foo; ?><h1><?php echo $foo; ?></h1>';
    $reality = $this->evalLeaf(
<<<'HTML'
- $foo = 'bar'
= $foo
h1 = $foo
HTML
);

    $this->assertEqual($reality, $expect);
  }

  public function test_while()
  {
    $expect  = '<?xml version="1.0" encoding="UTF-8"?><ul><?php while (true): ?><li>item</li><?php endwhile; ?></ul>';
    $reality = $this->evalLeaf(
<<<HTML
ul
  - while true
    li item
HTML
);
    $this->assertEqual($reality, $expect);
  }

// TEMPLATE BLOCKS
}
?>