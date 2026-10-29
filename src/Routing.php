<?php

declare(strict_types=1);

namespace App;

use App\Support\HttpException;
use InvalidArgumentException;

final class Routing
{
    /**
     * @var list<array{
     *     method: string,
     *     regex: string,
     *     shape: string,
     *     parameters: list<string>,
     *     handler: callable
     * }>
     */
    private array $routes = [];

    public function get(string $pattern, callable $handler): self
    {
        return $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): self
    {
        return $this->add('POST', $pattern, $handler);
    }

    public function patch(string $pattern, callable $handler): self
    {
        return $this->add('PATCH', $pattern, $handler);
    }

    public function delete(string $pattern, callable $handler): self
    {
        return $this->add('DELETE', $pattern, $handler);
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = $this->normalizePath($uri);
        $method = strtoupper($method);
        $invalidParameter = false;

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['regex'], $path, $matches) === 1) {
                $arguments = [];

                foreach ($route['parameters'] as $name) {
                    $arguments[] = $matches[$name];
                }

                ($route['handler'])(...$arguments);

                return;
            }

            if (preg_match($route['shape'], $path) === 1) {
                $invalidParameter = true;
            }
        }

        if ($invalidParameter) {
            throw HttpException::badRequest('A route parameter has an invalid format.');
        }

        throw HttpException::notFound('The requested route does not exist.');
    }

    private function add(string $method, string $pattern, callable $handler): self
    {
        [$regex, $shape, $parameters] = $this->compile($pattern);

        $this->routes[] = [
            'method' => $method,
            'regex' => $regex,
            'shape' => $shape,
            'parameters' => $parameters,
            'handler' => $handler,
        ];

        return $this;
    }

    private function normalizePath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            $path = '/';
        }

        return '/' . trim($path, '/');
    }

    /**
     * @return array{0: string, 1: string, 2: list<string>}
     */
    private function compile(string $pattern): array
    {
        if ($pattern === '' || $pattern[0] !== '/') {
            throw new InvalidArgumentException('Route patterns must start with "/".');
        }

        $regex = '';
        $shape = '';
        $parameters = [];

        foreach ($this->tokenize($pattern) as $token) {
            if ($token['type'] === 'literal') {
                $literal = preg_quote($token['value'], '#');
                $regex .= $literal;
                $shape .= $literal;
                continue;
            }

            if (in_array($token['name'], $parameters, true)) {
                throw new InvalidArgumentException(sprintf('Duplicate route parameter "%s".', $token['name']));
            }

            $parameters[] = $token['name'];
            $regex .= sprintf('(?P<%s>%s)', $token['name'], $token['pattern']);
            $shape .= '[^/]+';
        }

        return [
            '#^' . $regex . '$#',
            '#^' . $shape . '$#',
            $parameters,
        ];
    }

    /**
     * @return list<array{type: 'literal', value: string}|array{type: 'parameter', name: string, pattern: string}>
     */
    private function tokenize(string $pattern): array
    {
        $tokens = [];
        $literal = '';
        $length = strlen($pattern);
        $index = 0;

        while ($index < $length) {
            if ($pattern[$index] !== '{') {
                $literal .= $pattern[$index];
                $index++;
                continue;
            }

            $name = '';
            $index++;

            while ($index < $length && preg_match('/[A-Za-z0-9_]/', $pattern[$index]) === 1) {
                $name .= $pattern[$index];
                $index++;
            }

            if ($name === '' || preg_match('/^[A-Za-z_]/', $name) !== 1) {
                throw new InvalidArgumentException('Route parameter names must start with a letter or underscore.');
            }

            $parameterPattern = '[^/]+';

            if ($index < $length && $pattern[$index] === ':') {
                $index++;
                $start = $index;
                $depth = 0;

                while ($index < $length) {
                    if ($pattern[$index] === '\\') {
                        $index += 2;
                        continue;
                    }

                    if ($pattern[$index] === '{') {
                        $depth++;
                    } elseif ($pattern[$index] === '}') {
                        if ($depth === 0) {
                            break;
                        }

                        $depth--;
                    }

                    $index++;
                }

                if ($index >= $length) {
                    throw new InvalidArgumentException('A route parameter regex is not closed.');
                }

                $parameterPattern = substr($pattern, $start, $index - $start);
            }

            if ($index >= $length || $pattern[$index] !== '}') {
                throw new InvalidArgumentException('A route parameter is not closed.');
            }

            $index++;

            if ($literal !== '') {
                $tokens[] = ['type' => 'literal', 'value' => $literal];
                $literal = '';
            }

            $tokens[] = [
                'type' => 'parameter',
                'name' => $name,
                'pattern' => $parameterPattern,
            ];
        }

        if ($literal !== '') {
            $tokens[] = ['type' => 'literal', 'value' => $literal];
        }

        return $tokens;
    }
}
