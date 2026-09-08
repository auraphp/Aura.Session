# CHANGELOG

## 7.0.0

- (ADD) Add `RedisSessionHandler`, an optional `SessionHandlerInterface` implementation that stores each session as a single Redis string with a key TTL (the approach used by Symfony, Laravel, and the phpredis native handler). It refreshes only the TTL when data is unchanged (`lazy_write`), destroys empty sessions, and leaves expiry to Redis. It is decoupled from any specific client via `Aura\Session\Redis\RedisClientInterface`, with bundled `PhpredisClient` (ext-redis) and `PredisClient` (predis/predis) adapters. No hard Redis-client dependency: `ext-redis` and `predis/predis` are listed under `suggest`.
- (ADD) Add `Segment::getFlashAll()` and `Segment::getFlashNextAll()`, which return every flash value for the current or the next request, so flash messages can be rendered without knowing their keys. Both return an empty array when nothing is set. Originally proposed by Jake Johns in #47/#52.
- (ADD) Depend on the new `aura/session-interface` (`^7.0`) package, which provides the shared session/segment contracts.
- (CHG) `Session` now implements `Aura\Session_Interface\SessionInterface`.
- (CHG) `SegmentInterface` no longer declares its own methods; it now composes the shared `Aura\Session_Interface\SegmentInterface` (get/set), `ManageableSegmentInterface` (getSegment/clear/remove), and `FlashSegmentInterface` (flash values). The full method set is unchanged, so existing implementations remain compatible.
- (CHG) Add native return types (`bool`, `mixed`, `void`) to `Session::start()`/`resume()`/`regenerateId()` and to all `Segment` methods. This is a BC break for subclasses that override these methods without declaring matching return types.

## 6.0.0

- PHP 8.4+ is now required.
- (CHG) Add `string` type declarations to `$key` parameters in `Segment`, `SegmentInterface` to fix PHP 8.5 deprecation of null as array offset.
- (CHG) Change `remove()` parameter to `?string $key = null` in `Segment` and `SegmentInterface`.

## 4.0.0

- PHP 7.2+ is now required.
- Github CI workflow added.
- Scrutinizer CI updated.
- MIT License.

## 2.1.0

- (ADD) Add support for hash_equals() in CsrfToken::isValid()
- (ADD) Add support for random_bytes() in Randval::generate()
- (TST) Update tests and test support files
- (DOC) Update license year and remove branch alias

## 2.0.1

This release modifies the testing structure and updates other support files.


## 2.0.0

This is the first stable release of Aura.Session 2.0.0.

- Fix #23

- Merge pull request #33 from harikt/issue-23.

- Merge pull request #35 from iansltx/php7-compat: Fix FakeSessionHandler::write() (fixes PHP7 tests)

- Merge pull request #36 from fiahfy/spike: fix param type

- Merge pull request #37 from tomkyle/develop-2: Removed redundant paragraph

- Merge pull request #38 from tomkyle/develop-2: Clarified parameter descriptions

- Updated documentation and support files.

## 2.0.0-beta2

- TST: Update testing structure, use plain old PHPUnit for tests

- CHG: Use new service naming rules

- CHG: Disable auto-resolve for container tests

## 2.0.0-beta1

First 2.0.0 beta release.

## 1.0.2

Hygiene release.

- Fix #8 related to unit tests failing because of ini_set values. Thanks @mindplay-dk

- Merge pull request #12 from harikt/v2config, adds v2 config files.

## 1.0.1

- [CHG] Manager::destroy() now checks whether the session is started; if not,
  starts it, and then destroys.  This is because sessions are lazy-loading.

- [DOC] Add PHP 5.5 to the Travis build and update docs

## 1.0.0

There are BC breaks in this release, but it's a Google beta, so ...

- [SEC] Based on conversation at
  http://www.eschrade.com/page/generating-secure-cross-site-request-forgery-tokens-csrf/
  start using openssl and mcrypt for CSRF tokens instead of mt_rand().

- [NEW] SegmentInterface, Randval, RandvalInterface.

- [BRK] The Manager now requires $_COOKIE as its third param to Manager.

- [CHG] Segments now lazy-load themselves. On reads, they will reactivate an
  available session, but will not start a new one. On writes, they will
  reactivate an available session, or start a new one if one is not available.
  This means that creating a segment object no longer starts a session; you
  have to read from or write to one for the session to start.

- [BRK] Renamed Manager::isActive() to isAvailable(), to differentiate from
  PHP_SESSION_ACTIVE. ( Previously, isActive() only told you if a session had
  started, not if one was available to be activated.)

- [CHG] Manager::getSegment() no longer starts a session

- [CHG] Manager::isStarted() now checks getStatus() for PHP_SESSION_ACTIVE
  instead of session_id().

- [CHG] Segment::__get() no longer returns a reference

- [BRK] Renamed Manager::getSegment() to newSegment()

- [CHG] Manager no longer retains session segments

- [CHG] Various typo and doc fixes by Akihito Koriyama
