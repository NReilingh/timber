<?php

class TestTimberFunctionWrapper extends Timber_UnitTestCase
{
    public function testToStringWithException()
    {
        ob_start();
        $wrapper = new Timber\FunctionWrapper('TestTimberFunctionWrapper::isNum', ['hi']);
        echo $wrapper;
        $content = trim(ob_get_contents());
        ob_end_clean();
        $this->assertEquals('Caught exception: Argument must be of type integer', $content);
    }

    public function testToStringWithoutException()
    {
        ob_start();
        $wrapper = new Timber\FunctionWrapper('TestTimberFunctionWrapper::isNum', [4]);
        echo $wrapper;
        $content = trim(ob_get_contents());
        ob_end_clean();
        $this->assertEquals(1, $content);
    }

    public function testToStringWithClassObject()
    {
        ob_start();
        $wrapper = new Timber\FunctionWrapper([$this, 'isNum'], [4]);
        echo $wrapper;
        $content = trim(ob_get_contents());
        ob_end_clean();
        $this->assertEquals(1, $content);
    }

    public function testToStringWithClassString()
    {
        ob_start();
        $wrapper = new Timber\FunctionWrapper([get_class($this), 'isNum'], [4]);
        echo $wrapper;
        $content = trim(ob_get_contents());
        ob_end_clean();
        $this->assertEquals(1, $content);
    }

    public function testWPHead()
    {
        return $this->markTestSkipped('@todo Twig\Error\RuntimeError: An exception has been thrown during the rendering of a template ("readfile(/srv/www/wordpress-trunk/public_html/src/wp-includes/js/wp-emoji-loader.js): failed to open stream: No such file or directory")');
        $context = Timber::context();
        $str = Timber::compile_string('{{ function("wp_head") }}', $context);
        $this->assertMatchesRegularExpression('/<title>Test Blog/', trim($str));
    }

    public function testFunctionInTemplate()
    {
        $context = Timber::context();
        $str = Timber::compile_string("{{ function('my_boo') }}", $context);
        $this->assertEquals('bar!', trim($str));
    }

    public function testSoloFunctionUsingWrapper()
    {
        if (version_compare(Timber::$version, 2.0, '>=')) {
            return $this->markTestSkipped(
                'This functionality is disabled in Timber 2.0'
            );
        }
        new Timber\FunctionWrapper('my_boo');
        $str = Timber::compile_string("{{ my_boo() }}");
        $this->assertEquals('bar!', trim($str));
    }

    public function testNakedSoloFunction()
    {
        add_filter('timber/twig', function ($twig) {
            $twig->addFunction(new \Twig\TwigFunction('your_boo', [$this, 'your_boo']));
            return $twig;
        });
        $context = Timber::context();
        $str = Timber::compile_string("{{ your_boo() }}", $context);
        $this->assertEquals('yourboo', trim($str));
    }

    /* Sample function to test exception handling */

    public static function isNum($num)
    {
        if (!is_int($num)) {
            throw new Exception("Argument must be of type integer");
        } else {
            return true;
        }
    }

    public function your_boo()
    {
        return 'yourboo';
    }
}



function my_boo()
{
    return 'bar!';
}
