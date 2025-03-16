<?php

namespace Beam\BeamCore;

use Closure;
use Beam\BeamCore\Actions\Dumper;
use Beam\BeamCore\Actions\{Config, SendPayload, Support};
use Beam\BeamCore\Concerns\Colors;
use Beam\BeamCore\Payloads\{
    BenchmarkPayload,
    ClearPayload,
    ColorPayload,
    DumpPayload,
    JsonPayload,
    LabelPayload,
    Payload,
    PhpInfoPayload,
    ScreenPayload,
    TablePayload,
    TimeTrackPayload,
    ValidJsonPayload,
    ValidateStringPayload
};
use Ramsey\Uuid\Uuid;
use Spatie\Backtrace\{Backtrace, Frame};

class Beam
{
    use Colors;

    private array $backtraceExcludePaths = [
        '/vendor/laravel/framework/src/Illuminate',
        '/artisan',
        '/packages/beam',
        '/packages/beam-core',
        '/palzin/beam/',
        '/palzin/beam-core/',
    ];

    public static ?\Closure $beforeSend = null;

    public function __construct(
        private string $notificationId = '',
    ) {
        /** @var int $sleep */
        $sleep = Config::get('config.sleep', 0);

        if ($sleep > 0) {
            sleep($sleep);
        }

        $this->notificationId = Uuid::uuid4()->toString();
    }

    protected function beforeWrite(mixed $args): \Closure
    {
        return function () use ($args) {
            if (is_string($args) && Support::isJson($args)) {
                return [
                    new JsonPayload($args),
                    uniqid(),
                ];
            }

            [$pre, $id] = Dumper::dump($args);

            return [
                new DumpPayload($pre, $args, variableType: gettype($args)),
                $id,
            ];
        };
    }

    public function send(Payload $payload, bool $withFrame = true): Payload
    {
        Beam::macosAutoLaunch();

        if ($withFrame) {
            $backtrace = Backtrace::create();
            $backtrace = $backtrace->applicationPath(appBasePath());
            $frame     = $this->parseFrame($backtrace);

            if (!empty($frame)) {
                $payload->setFrame($frame);
            }
        }

        $payload->setNotificationId($this->notificationId);

        if ($closure = static::$beforeSend) {
            $closure($payload, $withFrame);
        }

        $sendPayload = new SendPayload();

        $sendPayload->handle(
            $payload->toArray()
        );

        return $payload;
    }

    public function write(mixed $args = null, ?bool $autoInvokeApp = null): self
    {
        [$payload, $id] = $this->beforeWrite($args)();

        if (empty($payload) && is_null($id)) {
            return $this;
        }

        /** @var Payload $payload */
        $payload->autoInvokeApp($autoInvokeApp);
        $payload->setDumpId($id);

        $this->send($payload);

        return $this;
    }

    /**
     * Send custom color
     */
    public function color(string $color): Beam
    {
        $payload = new ColorPayload($color);
        $this->send($payload);

        return $this;
    }

    /**
     * Add new screen
     */
    public function s(string $screen): Beam
    {
        return $this->toScreen($screen);
    }

    /**
     * Add new screen
     */
    public function w(string $screen): Beam
    {
        return $this->toScreenWindow($screen);
    }

    /**
     * Add new screen
     *
     * @param  int  $raiseIn  Delay in seconds for the app to raise and focus
     */
    public function toScreen(
        string $screenName,
        int $raiseIn = 0,
    ): Beam {
        $payload = new ScreenPayload($screenName, raiseIn: $raiseIn);
        $this->send($payload);

        return $this;
    }

    /**
     * Add new screen window
     */
    public function toScreenWindow(
        string $screenName
    ): Beam {
        $payload = new ScreenPayload($screenName, newWindow: true);
        $this->send($payload);

        return $this;
    }

    /**
     * Send custom label
     */
    public function label(string $label): Beam
    {
        $payload = new LabelPayload($label);

        $this->send($payload);

        return $this;
    }

    /**
     * Send dump and die
     */
    public function die(string $status = ''): void
    {
        exit($status);
    }

    /**
     * Clear screen
     */
    public function clear(): Beam
    {
        $this->send(new ClearPayload());

        return $this;
    }

    /**
     * Send JSON data and validate
     */
    public function isJson(): Beam
    {
        $payload = new ValidJsonPayload();

        $this->send($payload);

        return $this;
    }

    /**
     * Checks if content contains string.
     *
     * @param  bool  $caseSensitive  Search is case-sensitive
     * @param  bool  $wholeWord  Search for the whole words
     */
    public function contains(string $content, bool $caseSensitive = false, bool $wholeWord = false): Beam
    {
        $payload = new ValidateStringPayload('contains');
        $payload->setContent($content)
            ->setCaseSensitive($caseSensitive)
            ->setWholeWord($wholeWord)
            ->setFrame(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);

        $this->send($payload);

        return $this;
    }

    /**
     * Send PHPInfo
     */
    public function phpinfo(): Beam
    {
        $payload = new PhpInfoPayload();

        $this->send($payload);

        return $this;
    }

    /**
     * Send Table
     */
    public function table(iterable|object $data = [], string $name = ''): Beam
    {
        $payload = new TablePayload($data, $name);

        $this->send($payload);

        return $this;
    }

    /**
     * Starts clocking a code block execution time
     *
     * @param  string  $reference  Unique name for this time clocking
     */
    public function time(string $reference): void
    {
        $payload = new TimeTrackPayload($reference);

        $this->send($payload);
    }

    /**
     * Stops clocking a code block execution time
     *
     * @param  string  $reference  Unique name called on ds()->time()
     */
    public function stopTime(string $reference): void
    {
        $payload = new TimeTrackPayload($reference, true);
        $payload->setFrame(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);

        $this->send($payload);
    }

    /**
     * Benchmarking
     */
    public function benchmark(mixed ...$args): self
    {
        $benchmarkPayload = new BenchmarkPayload($args);
        $this->send($benchmarkPayload);

        return $this;
    }

    public function parseFrame(Backtrace $backtrace): Frame|array
    {
        $frames = [];

        foreach ($backtrace->frames() as $frame) {
            if ($frame->applicationFrame) {
                $normalizedPath = str_replace('\\', '/', $frame->file);
                $exclude        = false;

                foreach ($this->backtraceExcludePaths as $excludedPath) {
                    if (str_contains($normalizedPath, $excludedPath)) {
                        $exclude = true;

                        break;
                    }
                }

                if (!$exclude) {
                    $frames[] = $frame;
                }
            }
        }

        /** @var Frame $frame */
        $frame = $frames[array_key_first($frames)] ?? [];

        return $frame;
    }

    public static function beforeSend(?Closure $closure = null): void
    {
        static::$beforeSend = $closure;
    }

    public static function macosAutoLaunch(): void
    {
        if (PHP_OS_FAMILY != 'Darwin') {
            return;
        }

        if (!Config::get('config.macos_auto_launch', false)) {
            return;
        }

        static::$beforeSend = function () {
            $script = '
                tell application "System Events"
                    if not (exists (processes whose bundle identifier is "com.beam.app")) then
                        tell application "Beam" to activate
                        delay 1
                    end if
                end tell
            ';

            $command = 'osascript -e ' . escapeshellarg($script);
            shell_exec($command);
        };
    }
}
