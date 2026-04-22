<?php

namespace App\Services\PW;

/**
 * Low-level socket client untuk daemon perantara PW (:65000).
 *
 * Protokol (sama dengan iweb):
 *   packet = packInt(opcode) . packString(server_key) . data
 *   packString(s) = packInt(strlen(s)) . s
 *
 * Opcode penting:
 *   2   check process (ps) by program name
 *   3   forward ke gdeliveryd / gprovider (chat, mail, forbid)
 *   4   forward ke gamedbd (writeCharacter, etc.)
 *   5   forward GMRoleOnline
 *   6   forward gdeliveryd (DebugAddCash, DeleteRole, writeCharacter)
 *   57  read file dari FS game server (/proc/meminfo, config)
 *   60  execute SQL (INSERT/UPDATE/DELETE) di DB game
 *   61  execute SQL SELECT
 */
final class Socket
{
    /** @var resource|null */
    private $socket = null;

    public function __construct(
        public readonly string $host,
        public readonly int $port,
        public readonly string $serverKey,
        public readonly int $connectTimeout = 5,
        public readonly int $readTimeout = 10,
    ) {}

    public static function fromCurrent(): self
    {
        $s = Server::current();
        $cfg = config('pw.socket');
        return new self(
            host: $s->socket_host,
            port: $s->socket_port,
            serverKey: $s->server_key,
            connectTimeout: $cfg['connect_timeout'] ?? 5,
            readTimeout: $cfg['read_timeout'] ?? 10,
        );
    }

    public function connect(): void
    {
        $this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (! $this->socket) {
            throw new PwSocketException('socket_create failed: ' . socket_strerror(socket_last_error()));
        }

        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $this->readTimeout, 'usec' => 0]);
        socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => $this->connectTimeout, 'usec' => 0]);

        $ok = @socket_connect($this->socket, $this->host, $this->port);
        if (! $ok) {
            $err = socket_strerror(socket_last_error($this->socket));
            $this->close();
            throw new PwSocketException("connect {$this->host}:{$this->port} failed: {$err}");
        }
    }

    public function close(): void
    {
        if ($this->socket) {
            @socket_close($this->socket);
            $this->socket = null;
        }
    }

    public function send(string $data): void
    {
        $len = strlen($data);
        $sent = @socket_send($this->socket, $data, $len, 0);
        if ($sent === false) {
            throw new PwSocketException('socket_send failed: ' . socket_strerror(socket_last_error($this->socket)));
        }
    }

    public function recv(int $len = 2048, int $flag = 0): string
    {
        $buf = '';
        $byte = @socket_recv($this->socket, $buf, $len, $flag);
        if ($byte === false) {
            return '';
        }
        return $buf ?? '';
    }

    public static function packInt(int $v): string
    {
        return pack('i', $v);
    }

    public static function packString(string $s): string
    {
        return self::packInt(strlen($s)) . $s;
    }

    /**
     * Kirim packet dengan handshake key, baca response, auto close.
     */
    public function sendPacket(int $opcode, string $data = '', int $recvLen = 2048, int $flag = 0): string
    {
        $this->connect();
        try {
            $pack = self::packInt($opcode) . self::packString($this->serverKey);
            $this->send($pack . $data);
            $recv = $this->recv($recvLen, $flag);
            if ($recv === 'key:0') {
                throw new PwSocketException('Server rejected key (key:0) — cek PW_SERVER_KEY');
            }
            return $recv;
        } finally {
            $this->close();
        }
    }
}
