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


use Ikarus\SPS\Client\Exception\SocketException;

class UnixClient extends AbstractClient
{
    /** @var string */
    private $address;

    /**
     * UnixClient constructor.
     * @param string $address
     * @param float $timeout
     */
    public function __construct(string $address, float $timeout = 1.0)
    {
        parent::__construct($timeout);
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @inheritDoc
     */
    protected function establishConnection()
    {
        $socket = fsockopen($this->getAddress(), NULL, $errorno, $errstr, $this->getTimeout());
        if(!$socket) {
            $e = new SocketException($errstr, $errorno);
            throw $e;
        }
        return $socket;
    }

    /**
     * @inheritDoc
     */
    protected function closeConnection($socket)
    {
        fclose($socket);
    }
}