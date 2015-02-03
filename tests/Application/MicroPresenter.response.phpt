<?php

/**
 * Test: NetteModule\MicroPresenter
 */

use Nette\Application\Request,
    Nette\Http,
    Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class LatteFactory implements Nette\Bridges\ApplicationLatte\ILatteFactory
{
    private $engine;

    public function __construct(Latte\Engine $engine)
    {
        $this->engine = $engine;
    }

    public function create()
    {
        return $this->engine;
    }
}


class MicroContainer extends Nette\DI\Container
{

    protected $meta = array(
        'types' => array(
            'nette\\bridges\\applicationlatte\\ilattefactory' => array(1 => array('latte.latteFactory')),
        ),
    );

    public static function create()
    {
        $container = new self();
        $container->addService('latte.latteFactory', new LatteFactory(new Latte\Engine()));
        return $container;
    }
}


class Responder
{

    private static $httpRequest;
    private static $httpResponse;

    public static function init()
    {
        self::$httpRequest = new Http\Request(new Http\UrlScript());
        self::$httpResponse = new Http\Response(NULL);
    }

    public static function invoke(Nette\Application\Responses\TextResponse $response)
    {
        ob_start();
        $response->send(self::$httpRequest, self::$httpResponse);
        return ob_get_clean();
    }
}

Responder::init();


test(function () {
    $presenter = new NetteModule\MicroPresenter(MicroContainer::create());
    $response = $presenter->run(new Request('Nette:Micro', 'GET', array(
        'callback' => function () {
            return 'test';
        },
    )));

    Assert::type('Nette\Application\Responses\TextResponse', $response);
    Assert::same('test', Responder::invoke($response));
});


test(function () {
    $presenter = new NetteModule\MicroPresenter(MicroContainer::create());
    $response = $presenter->run(new Request('Nette:Micro', 'GET', array(
        'callback' => function ($param) {
            return $param;
        },
        'param' => 'test'
    )));

    Assert::type('Nette\Application\Responses\TextResponse', $response);
    Assert::same('test', Responder::invoke($response));
});


test(function () {
    $presenter = new NetteModule\MicroPresenter(MicroContainer::create());
    $response = $presenter->run(new Request('Nette:Micro', 'GET', array(
        'callback' => function () {
            return '{=date(Y)}';
        },
    )));

    Assert::type('Nette\Application\Responses\TextResponse', $response);
    Assert::same(date('Y'), Responder::invoke($response));
});


test(function () {
    $presenter = new NetteModule\MicroPresenter(MicroContainer::create());
    $response = $presenter->run(new Request('Nette:Micro', 'GET', array(
        'callback' => function () {
            return array(new SplFileInfo(__DIR__ . '/MicroPresenter.response.latte'), array());
        },
        'param' => 'test',
    )));

    Assert::type('Nette\Application\Responses\TextResponse', $response);
    Assert::same('test', Responder::invoke($response));
});


test(function () {
    $latteFactory = new LatteFactory(new Latte\Engine());
    $presenter = new NetteModule\MicroPresenter;

    $response = $presenter->run(new Request('Nette:Micro', 'GET', array(
        'callback' => function ($presenter) use ($latteFactory) {
            $template = $presenter->createTemplate(NULL, function () use ($latteFactory) {
                return $latteFactory->create();
            });
            $template->getLatte()->setLoader(new Latte\Loaders\StringLoader);
            $template->setFile('test');

            return $template;
        },
    )));

    Assert::type('Nette\Application\Responses\TextResponse', $response);
    Assert::same('test', Responder::invoke($response));
});


test(function () {
    $latteFactory = new LatteFactory(new Latte\Engine());
    $presenter = new NetteModule\MicroPresenter;

    $response = $presenter->run(new Request('Nette:Micro', 'GET', array(
        'callback' => function ($presenter) use ($latteFactory) {
            $template = $presenter->createTemplate(NULL, function () use ($latteFactory) {
                return $latteFactory->create();
            });
            $template->getLatte()->setLoader(new Latte\Loaders\FileLoader());
            $template->setFile(new SplFileInfo(__DIR__ . '/MicroPresenter.response.latte'));
            $template->setParameters(array('param' => 'test'));

            return $template;
        },
    )));

    Assert::type('Nette\Application\Responses\TextResponse', $response);
    Assert::same('test', Responder::invoke($response));
});


test(function () {
    $filename = 'notfound.latte';
    Assert::throws(function () use ($filename) {
        $latteFactory = new LatteFactory(new Latte\Engine());
        $presenter = new NetteModule\MicroPresenter;

        $response = $presenter->run(new Request('Nette:Micro', 'GET', array(
            'callback' => function ($presenter) use ($latteFactory, $filename) {
                $template = $presenter->createTemplate(NULL, function () use ($latteFactory) {
                    return $latteFactory->create();
                });
                $template->getLatte()->setLoader(new Latte\Loaders\FileLoader());
                $template->setFile($filename);
                $template->setParameters(array('param' => 'test'));

                return $template;
            },
        )));

        Responder::invoke($response);
    }, '\RuntimeException', "Missing template file '$filename'.");
});
