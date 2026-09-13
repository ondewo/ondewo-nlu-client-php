# Release History

*****************

## Release ONDEWO NLU PHP Client 7.1.0

### New Features

* Initial release of the ONDEWO NLU (Natural Language Understanding) gRPC client for PHP. The whole client surface
  is generated from the [ondewo-nlu-api](https://github.com/ondewo/ondewo-nlu-api) protocol buffer definitions by the
  `ondewo-php-proto-compiler` image of
  [ondewo-proto-compiler 5.15.0](https://github.com/ondewo/ondewo-proto-compiler/releases/tag/5.15.0),
  which is vendored as a git submodule and pinned to that tag: protoc's built-in `--php_out` for the messages
  and enums, `grpc_php_plugin` for the `<Service>Client` stubs, and a composer package whose optimized
  classmap autoloader is built and verified inside the image.
* The generated stubs are **committed** under `src/` — 838 files covering 17 NLU services plus the QA service,
  their messages and enums, and the non-well-known `google/*` protos they import. Packagist serves the tree of
  a git tag verbatim and composer has no build step, so stubs that are not committed do not exist for anybody
  who installs the package.
* Ships as the composer package `ondewo/nlu-client-php`, installable with
  `composer require ondewo/nlu-client-php`. Requires PHP >= 8.1 and the `grpc` PHP extension, which every
  generated `<Service>Client` needs because it extends `\Grpc\BaseStub`.
* Hand-written sources live in `auth/` at the repository root, never in the compiler-owned `src/`; the image
  adds that directory to the shipped autoloader's classmap on its own. `Ondewo\Nlu\Auth\BearerTokenAuthenticator`
  turns a token into the `$opts` array a generated stub is constructed with and stamps
  `authorization: Bearer <token>` onto the metadata of every call.
* `make build` reproduces the stubs end to end — pinned submodules, compiler image, generation, ownership
  hand-back and version propagation into `composer.json`.

### Testing

* A real PHPUnit suite under `tests/` exercises the generated code rather than asserting around it: every
  committed class is loaded through the autoloader, `initOnce()` is called on every `GPBMetadata` descriptor
  (so a missing transitive import fails CI rather than a consumer's first RPC), messages are round-tripped
  through the binary and JSON wire formats, `proto3 optional` fields are asserted to keep their zero values on
  the wire, enum zero constants are pinned, and the service stubs are constructed against a dummy channel and
  checked for the RPC methods and arities the api declares.
* `make coverage` measures the hand-written sources (`phpunit.xml.dist`'s `<source>` is `auth/`) and fails the
  build below 100% line coverage. Generated code is excluded from that metric and covered by the tests above.
* GitHub Actions runs `composer validate`, `php -l`, the suite and the coverage gate on PHP 8.1 and 8.4
  against the committed stubs — no docker image is built and no submodule is checked out there.
* The dev tool chain (PHPUnit, the coverage gate) lives in its own composer project under `tools/`. It is
  deliberately **not** `require-dev` in the root manifest: `composer update --no-dev` still resolves dev
  requirements, and the compiler image resolves the merged manifest with the network disabled, so one
  `require-dev` entry would break `make generate_ondewo_protos`.

*****************
