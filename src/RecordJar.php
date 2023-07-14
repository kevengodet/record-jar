<?php

declare(strict_types=1);

namespace Keven\RecordJar;

use Keven\Stream\Stream;

final class RecordJar
{
    /**
     * @param string|resource|\SplFileInfo|Stream $source
     * @return iterable [][]
     */
    public function parse(string $source): iterable
    {
        $stream = Stream::create($source);

        $record = [];
        foreach ($stream->readLines(Stream::DROP_NEW_LINE | Stream::SKIP_EMPTY) as $line) {
            if (1 === preg_match('/^\s*%%.*/', $line)) {
                if ($record) {
                    yield $record;
                    $record = [];
                }

                continue;
            }

            if (!empty($name)) {
                if (preg_match('/^\s*(?<value>.+)\\\$/', $line, $matches)) {
                    $value .= $matches['value'];
                } elseif (preg_match('/^\s*(?<value>.+)$/', $line, $matches)) {
                    $value .= $matches['value'];
                    $record[$name] = $value;
                    $name = $value = '';
                } else {
                    throw new \UnexpectedValueException('Unexpected char');
                }
                continue;
            }

            if (preg_match('/^\s*(?<name>[!-%\'-[\]-~]+)\s*:\s*(?<value>([^\\\]+))$/', $line, $matches)) {
                $record[$matches['name']] = $matches['value'];
                $matches = [];
            } elseif (preg_match('/^\s*(?<name>[!-%\'-[\]-~]+)\s*:\s*(?<value>.+)\\\$/', $line, $matches)) {
                extract($matches);
            }
        }

        if ($record) {
            yield $record;
        }
    }
}
