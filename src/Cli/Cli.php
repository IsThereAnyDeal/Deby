<?php
namespace IsThereAnyDeal\Tools\Deby\Cli;

class Cli
{
    /**
     * @param Style|Color ...$options
     */
    public static function write(string $message, mixed ...$options): void {
        if (empty($options)) {
            echo $message;
        } else {
            $params = implode(";", array_map(fn(Style|Color $option) => $option->value, $options));
            echo "\e[{$params}m{$message}\e[0m";
        }
    }

    /**
     * @param Style|Color ...$options
     */
    public static function writeLn(string $message="", mixed ...$options): void {
        self::write($message, ...$options);
        echo "\n";
    }

    public static function padding(int $length): void {
        echo str_repeat(" ", $length);
    }

    public static function input(): string {
        $f = fopen("php://stdin", "r");
        if ($f === false) {
            throw new \ErrorException();
        }

        $result = fgets($f);
        fclose($f);

        if ($result === false) {
            throw new \ErrorException();
        }
        return trim($result);
    }
}
