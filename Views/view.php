html
  - $test = 'test'
  head
    title Hello world
      | plop
  body class="titi" class="0"
    #ps
      | test
      - if $test == 'test'
        span span1
          span span2
      - foreach str_split($test) as $char
        p
          = $char
    div.test
    @block: testblock
      p
    div.test