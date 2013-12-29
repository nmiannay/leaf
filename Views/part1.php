@extends: 'Views/view.php'

@block:testblock
  div.test.#teplop class="tet" id="te"
    | it works
  <div class="test">
    <div>
      @parent
    </div>
  </div>
  p
    | plop