<?php

declare(strict_types=1);

use App\Controller\DefaultController;
use App\Controller\RoomController;
use App\Repository\InMemoryRoomRepository;
use App\Routing;
use App\Support\HttpException;
use App\Support\Translator;
use App\Support\View;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off';

session_name('roomly_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $https,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

$requestedLocale = $_GET['lang'] ?? null;

if (is_string($requestedLocale) && Translator::supports($requestedLocale)) {
    $_SESSION['locale'] = $requestedLocale;
}

$locale = $_SESSION['locale'] ?? 'pl';

if (!is_string($locale) || !Translator::supports($locale)) {
    $locale = 'pl';
}

$translator = Translator::fromDirectory(dirname(__DIR__) . '/translations', $locale);
$view = new View(dirname(__DIR__) . '/public/views', $translator);
$router = new Routing();

$roomController = new RoomController($view, $translator, new InMemoryRoomRepository());

$router->get('/', [new DefaultController($view, $translator), 'index']);
$router->get('/rooms', [$roomController, 'index']);
$router->get('/rooms/{id:\d+}', [$roomController, 'show']);

$method = (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');
$uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$currentPath = parse_url($uri, PHP_URL_PATH);
$currentPath = is_string($currentPath) && $currentPath !== '' ? $currentPath : '/';

try {
    $router->dispatch($method, $uri);
} catch (HttpException $exception) {
    $status = $exception->statusCode();

    http_response_code($status);
    $view->render('errors/error', [
        'title' => $translator->translate('error.' . $status . '.title'),
        'status' => $status,
        'message' => $translator->translate('error.' . $status . '.message'),
        'currentPath' => $currentPath,
    ]);
} catch (Throwable $exception) {
    error_log(sprintf(
        "[%s] %s in %s:%d\n%s",
        date(DATE_ATOM),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString(),
    ));

    http_response_code(500);
    $view->render('errors/error', [
        'title' => $translator->translate('error.500.title'),
        'status' => 500,
        'message' => $translator->translate('error.500.message'),
        'currentPath' => $currentPath,
    ]);
}
