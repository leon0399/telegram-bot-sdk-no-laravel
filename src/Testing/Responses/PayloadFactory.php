<?php

namespace Telegram\Bot\Testing\Responses;

use Faker\Generator;
use Faker\Factory as Faker;
use Illuminate\Support\Collection;
use Telegram\Bot\Objects\ResponseObject;

/**
 * @method static array user(array $payload = [])
 * @method static array message(array $payload = [])
 * @method static array update(array $payload = [])
 */
class PayloadFactory
{
    protected int $count = 1;
    protected ?int $seed = null;
    protected bool $asCollection = false;
    protected bool $asResult = false;
    protected bool $asResponseObject = false;
    protected bool $asJson = false;

    public static function create(): static
    {
        return new static();
    }

    public function times(int $count): static
    {
        $this->count = $count;

        return $this;
    }

    public function asCollection(): static
    {
        $this->asCollection = true;

        return $this;
    }

    public function asResult(): static
    {
        $this->asResult = true;

        return $this;
    }

    public function asResponseObject(): static
    {
        $this->asResponseObject = true;

        return $this;
    }

    public function asJson(): static
    {
        $this->asJson = true;

        return $this;
    }

    public function seed(int $seed): static
    {
        $this->seed = $seed;

        return $this;
    }

    public function faker(): Generator
    {
        $faker = Faker::create(Faker::DEFAULT_LOCALE);
        $faker->seed($this->seed);

        return $faker;
    }

    public function __call(string $name, array $arguments)
    {
        if (method_exists(Payload::class, $name)) {
            return $this->makePayloads($this->generate($name, ...$arguments));
        }

        throw new \RuntimeException("Method {$name} does not exist");
    }

    private function makePayloads(array $payloads): array|string|ResponseObject|Collection
    {
        if($this->count === 1) {
            $payloads = $payloads[0];
        }

        if ($this->asCollection) {
            return new Collection($payloads);
        }

        if ($this->asResult) {
            return [
                'ok' => true,
                'result' => $payloads,
            ];
        }

        if($this->asResponseObject) {
            return ResponseObject::make($payloads);
        }

        if($this->asJson) {
            return ResponseObject::make($payloads)->__toJson();
        }

        return $payloads;
    }

    private function generate(string $name, array $payload = []): array
    {
        $payloads = [];
        for ($i = 0; $i < $this->count; $i++) {
            $payloadFormat = Payload::create()->{$name}();
            $data = $this->makeWithFaker($payloadFormat);
            $payloads[] = $this->mergePayloads($data, $payload);
        }

        return $payloads;
    }

    private function makeWithFaker(array $payloadFormat): array
    {
        return (new Collection($payloadFormat))->map(function ($value, $key) {
            if(is_string($value)) {
                if(str_contains($value, ':')) {
                    [$method, $val] = explode(':', $value, 2);

                    return $this->faker()->$method($val);
                }

                try {
                    return $this->faker()->$value();
                } catch (\InvalidArgumentException $e) {
                }

                return $value;
            }

            if(is_array($value)) {
                return $this->makeWithFaker($value);
            }

            return $value;
        })->toArray();
    }

    private function mergePayloads(array &$array1, array &$array2): array
    {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->mergePayloads($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}
