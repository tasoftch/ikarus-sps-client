<?php
/**
 * Copyright (c) 2019 TASoft Applications, Th. Abplanalp <info@tasoft.ch>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Ikarus\SPS\Client;


use Ikarus\SPS\Client\Command\CommandInterface;
use Ikarus\SPS\Client\Exception\ClientException;
use Ikarus\SPS\Client\Exception\CommunicationException;
use Ikarus\SPS\Client\Exception\SocketException;
use Throwable;

abstract class AbstractClient implements ClientInterface
{
    private $timeout = 1.0;

    const SOCK_BUFFER_SIZE = 2048;

    /**
     * AbstractCommunication constructor.
     * @param float $timeout
     */
    public function __construct(float $timeout = 1.0)
    {
        $this->timeout = $timeout;
    }

    /**
     * This method must be able to establish a connection and return a socket to read and write.
     *
     * Called to establish the connection.
     * @return resource
     */
    abstract protected function establishConnection();

    /**
     * Called after transaction to close the connection.
     *
     * @param $socket
     */
    abstract protected function closeConnection($socket);

    /**
     * Send silently to SPS
     *
     * @param CommandInterface $command
     * @param $error
     * @return string|null
     */
    public function sendSilentCommand(CommandInterface $command, &$error = NULL) {
        try {
            return $this->sendCommand($command);
        } catch (Throwable $exception) {
            $error = $exception;
        }
        return static::STATUS_ERR;
    }

    protected function serializeCommand(CommandInterface $command): string {
        $cmd = $command->getName();
        if($args = $command->getArguments()) {
            $cmd .= " " . implode(" ", array_map(function($arg) {
                    if(preg_match("/\"/i", $arg)) {
                        return "\"" . str_replace('""', '\\"', $arg) . "\"";
                    }
                    return $arg;
                }, $args));
        }
        return $cmd;
    }

    /**
     * @inheritDoc
     */
    public function sendCommand(CommandInterface $command): int
    {
        $socket = $this->establishConnection();
        if(!is_resource($socket)) {
            $e = new SocketException("No connected socket available");
            $e->setSocket($socket);
            throw $e;
        }

        $e = function() {
            $error = error_get_last();
            if($error) {
                $e = new CommunicationException($error["message"], $error["code"]);
                throw $e;
            }
            error_clear_last();
        };

        try {
            error_clear_last();

            fwrite($socket, $this->serializeCommand($command));

            $e();

            $buffer = "";

            while (!feof($socket)) {
                $buffer .= fread($socket, static::SOCK_BUFFER_SIZE);
            }

            $e();

            return $command->setResponse($buffer);
        } catch (ClientException $exception) {
            throw $exception;
        } finally {
            $this->closeConnection($socket);
        }
    }


    /**
     * @return float
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }
}