<?php

declare(strict_types=1);

namespace Ondewo\Nlu\Tests\Generated;

use Grpc\BaseStub;
use Grpc\ChannelCredentials;
use Ondewo\Nlu\AgentsClient;
use Ondewo\Nlu\Auth\BearerTokenAuthenticator;
use Ondewo\Nlu\SessionsClient;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Constructs the generated service stubs against a dummy target. gRPC channels connect lazily, so
 * nothing here touches the network - but the stub, its channel options and its method surface are
 * all real.
 *
 * PRODUCT-SPECIFIC: the service and method names below come from ondewo-nlu-api.
 */
final class ServiceClientTest extends TestCase
{
    private const DUMMY_TARGET = 'localhost:50051';

    /**
     * @var list<BaseStub>
     */
    private array $openClients = [];

    protected function tearDown(): void
    {
        foreach ($this->openClients as $client) {
            $client->close();
        }
        $this->openClients = [];

        parent::tearDown();
    }

    public function testAServiceClientIsConstructedAgainstAnInsecureChannel(): void
    {
        $client = $this->open(new AgentsClient(self::DUMMY_TARGET, [
            'credentials' => ChannelCredentials::createInsecure(),
        ]));

        self::assertInstanceOf(BaseStub::class, $client);
        // Contains, not equals: gRPC canonicalises the target (`dns:///localhost:50051`) in some
        // core versions.
        self::assertStringContainsString(self::DUMMY_TARGET, $client->getTarget());
    }

    public function testAServiceClientAcceptsTheHandWrittenBearerAuthenticator(): void
    {
        $authenticator = new BearerTokenAuthenticator('a-token');

        // The point of the hand-written auth surface: its output IS a valid `$opts` array for a
        // generated stub. \Grpc\BaseStub rejects a missing `credentials` key and a non-callable
        // `update_metadata`, so constructing successfully proves both.
        $client = $this->open(new AgentsClient(self::DUMMY_TARGET, $authenticator->channelOptions()));

        self::assertStringContainsString(self::DUMMY_TARGET, $client->getTarget());
    }

    #[DataProvider('unaryMethods')]
    public function testTheExpectedUnaryMethodsExist(string $method): void
    {
        self::assertTrue(
            method_exists(AgentsClient::class, $method),
            AgentsClient::class . '::' . $method . '() is missing from the generated stub'
        );

        $reflected = new ReflectionMethod(AgentsClient::class, $method);
        self::assertTrue($reflected->isPublic());
        // <request message>, array $metadata = [], array $options = []
        self::assertSame(3, $reflected->getNumberOfParameters());
        self::assertSame(1, $reflected->getNumberOfRequiredParameters());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function unaryMethods(): iterable
    {
        foreach ([
            'CreateAgent',
            'UpdateAgent',
            'GetAgent',
            'DeleteAgent',
            'ListAgents',
            'TrainAgent',
            'ExportAgent',
            'ImportAgent',
            'RestoreAgent',
            'GetPlatformInfo',
        ] as $method) {
            yield $method => [$method];
        }
    }

    public function testABidirectionalStreamingMethodIsGenerated(): void
    {
        self::assertTrue(method_exists(SessionsClient::class, 'StreamingDetectIntent'));

        $reflected = new ReflectionMethod(SessionsClient::class, 'StreamingDetectIntent');
        // A bidi stream takes no request message - only $metadata and $options.
        self::assertSame(0, $reflected->getNumberOfRequiredParameters());
    }

    public function testTheGeneratedMethodSurfaceIsNotEmpty(): void
    {
        $methods = get_class_methods(AgentsClient::class);

        self::assertContains('CreateAgent', $methods);
        self::assertGreaterThan(
            20,
            count($methods),
            'AgentsClient exposes suspiciously few methods - the service proto may not have been compiled'
        );
    }

    private function open(BaseStub $client): BaseStub
    {
        $this->openClients[] = $client;

        return $client;
    }
}
