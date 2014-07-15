# Aura Session

Provides session management functionality, including lazy session starting,
session segments, next-request-only ("flash") values, and CSRF tools.

## Foreword

### Installation

This library requires PHP 5.3 or later, and has no userland dependencies.

It is installable and autoloadable via Composer as [aura/session](https://packagist.org/packages/aura/session).

Alternatively, [download a release](https://github.com/auraphp/Aura.Session/releases) or clone this repository, then require or include its _autoload.php_ file.

### Quality

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/auraphp/Aura.Session/badges/quality-score.png?s=b80c5c00129306b48a885ac641f7c39f78988762)](https://scrutinizer-ci.com/g/auraphp/Aura.Session/)
[![Code Coverage](https://scrutinizer-ci.com/g/auraphp/Aura.Session/badges/coverage.png?s=3afb424a5ffd1f469cefc6790a557cb280411f65)](https://scrutinizer-ci.com/g/auraphp/Aura.Session/)
[![Build Status](https://travis-ci.org/auraphp/Aura.Session.png?branch=develop-2)](https://travis-ci.org/auraphp/Aura.Session)

To run the [PHPUnit][] tests at the command line, go to the _tests_ directory and issue `phpunit`.

This library attempts to comply with [PSR-1][], [PSR-2][], and [PSR-4][]. If
you notice compliance oversights, please send a patch via pull request.

[PHPUnit]: http://phpunit.de/manual/
[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

### Community

To ask questions, provide feedback, or otherwise communicate with the Aura community, please join our [Google Group](http://groups.google.com/group/auraphp), follow [@auraphp on Twitter](http://twitter.com/auraphp), or chat with us on #auraphp on Freenode.


## Getting Started

### Instantiation

The easiest way to get started is to use the _SessionFactory_ to create a _Session_ manager object.

```php
<?php
$session_factory = new \Aura\Session\SessionFactory;
$session = $session_factory->newInstance($_COOKIE);
?>
```

We can then use the _Session_ instance to create _Segment_ objects to manage session values and flashes. (In general, we should not need to manipulate the _Session_ manager directly -- we will work mostly with _Segment_ objects.)


### Segments

In normal PHP, we keep session values in the `$_SESSION` array. However, when different libraries and projects try to modify the same keys, the resulting conflicts can result in unexpected behavior. To resolve this, we use _Segment_ objects. Each _Segment_ addresses a named key within the `$_SESSION` array for deconfliction purposes.

For example, if we create a _Segment_  for _Vendor\\Package\\ClassName_, that _Segment_ will contain a reference to `$_SESSION['Vendor\Package\ClassName']`. We can then `set()` and `get()` values on the _Segment_, and the values will reside in an array under that reference.

```php
<?php
// get a segment object
$segment = $session->getSegment('Vendor\Package\ClassName');

// try to get a value that does not exist yet
echo $segment->get('foo', 'not set'); // 'not set'

// set some values on the segment
$segment->set('foo', 'bar');

// the $_SESSION array is now:
// $_SESSION = [
//      'Vendor\Package\ClassName' => [
//          'foo' => 'bar',
//      ],
// ];

// now get a value from the segment
echo $segment->get('foo', 'not set'); // 'bar'

// because the segment is a reference to $_SESSION, we can modify
// the superglobal directly and the segment values will also change.
$_SESSION['Vendor\Package\ClassName']['zim'] = 'gir'
echo $segment->get('zim'); // 'gir'
?>
```

Again, the benefit of a session segment is that we can deconflict the keys in the `$_SESSION` superglobal by using class names (or some other unique name) for the segment names. With segments, different packages can use the `$_SESSION` superglobal without stepping on each other's toes.


### Lazy Session Starting

Merely instantiating the `Session` and getting a session segment does *not*
start a session automatically. Instead, the session is started only when you
read or write to a session segment.  This means we can create segments at
will, and no session will start until we read from or write to one them.

If we *read* from a session segment, it will check to see if a previously
available session exists, and reactivate it if it does. Reading from a segment
will not start a new session.

If we *write* to a session segment, it will check to see if a previously
available session exists, and reactivate it if it does. If there is no
previously available session, it will start a new session, and write to it.

Of course, we can force a session start or reactivation by calling the
`Session`'s `start()` method, but that defeats the purpose of lazy-loaded
sessions.

### Read-Once ("Flash") Values

Session segment values persist until a session is cleared or destroyed.
However, sometimes it is useful to set a value that propagates only until it
is used, and then automatically clears itself. These are called "flash" or
"read-once" values.

To set a read-once value on a segment, use the `setFlash()` method.

```php
<?php
// get a segment
$segment = $session->getSegment('Vendor\Package\ClassName');

// set a read-once value on the segment
$segment->setFlash('message', 'Hello world!');
?>
```

Then, in subsequent sessions, we can read the flash value using `getFlash()`:

```php
<?php
// get a segment
$segment = $session->getSegment('Vendor\Package\ClassName');

// get the read-once value
$message = $segment->getFlash('message'); // 'Hello world!'

// if we try to read it again, it won't be there
$not_there = $segment->getFlash('message'); // null
?>
```

Sometimes we need to know if a flash value exists, but don't want to read it
yet (thereby removing it from the session). In these cases, we can use the
`hasFlash()` method:

```php
<?php
// get a segment
$segment = $session->getSegment('Vendor\Package\ClassName');

// is there a read-once 'message' available?
// this will *not* cause a read-once removal.
if ($segment->hasFlash('message')) {
    echo "Yes, there is a message available.";
} else {
    echo "No message available.";
}
?>
```

To clear all flash values on a segment, use the `clearFlash()` method:

```php
<?php
// get a segment
$segment = $session->getSegment('Vendor\Package\ClassName');

// clear all flash values, but leave all other segment values in place
$segment->clearFlash();
?>
```

### Saving Session Data

When you are done with a session and want its data to be available later, call
the `commit()` method:

```php
<?php
$session->commit();
?>
```

> N.b.: The `commit()` method is the equivalent of `session_write_close()`.
> If you do not commit the session, its values will not be available when we
> continue the session later.

## Session Security

### Session ID Regeneration

Any time a user has a change in privilege (that is, gaining or losing access
rights within a system) be sure to regenerate the session ID:

```php
<?php
$session->regenerateId();
?>
```

> N.b.: The `regenerateId()` method also regenerates the CSRF token value.

### Clearing and Destroying Sessions

To clear the in-memory session data, but leave the session active, use the
`clear()` method:

```php
<?php
$session->clear();
?>
```

To end a session and remove its data (both committed and in-memory), generally
after a user signs out or when authentication timeouts occur, call the
`destroy()` method:

```php
<?php
$session->destroy();
?>
```

### Cross-Site Request Forgery

A "cross-site request forgery" is a security issue where the attacker, via
malicious JavaScript or other means, issues a request in-the-blind from a
client browser to a server where the user has already authenticated. The
request *looks* valid to the server, but in fact is a forgery, since the user
did not actually make the request (the malicious JavaScript did).

<http://en.wikipedia.org/wiki/Cross-site_request_forgery>

#### Defending Against CSRF

To defend against CSRF attacks, server-side logic should:

1. Place a token value unique to each authenticated user session in each form;
   and

2. Check that all incoming POST/PUT/DELETE (i.e., "unsafe") requests contain
   that value.

> N.b.: If our application uses GET requests to modify resources (which
> incidentally is an improper use of GET), we should also check for CSRF on
> GET requests from authenticated users.

For this example, the form field name will be `'__csrf_value''`. In each form
we want to protect against CSRF, we use the session CSRF token value for that
field:

```php
<?php
/**
 * @var Vendor\Package\User $user A user-authentication object.
 * @var Aura\Session\Session $session A session management object.
 */
?>
<form method="post">

    <?php if ($user->isAuthenticated()) {
        $csrf_value = $session->getCsrfToken()->getValue();
        echo '<input type="hidden" name="__csrf_value" value="'
           . $csrf_value
           . '"></input>';
    } ?>

    <!-- other form fields -->

</form>
```

When processing the request, check to see if the incoming CSRF token is valid
for the authenticated user:

```php
<?php
/**
 * @var Vendor\Package\User $user A user-authentication object.
 * @var Aura\Session\Session $session A session management object.
 */

$unsafe = $_SERVER['REQUEST_METHOD'] == 'POST'
       || $_SERVER['REQUEST_METHOD'] == 'PUT'
       || $_SERVER['REQUEST_METHOD'] == 'DELETE';

if ($unsafe && $user->isAuthenticated()) {
    $csrf_value = $_POST['__csrf_value'];
    $csrf_token = $session->getCsrfToken();
    if (! $csrf_token->isValid($csrf_value)) {
        echo "This looks like a cross-site request forgery.";
    } else {
        echo "This looks like a valid request.";
    }
} else {
    echo "CSRF attacks only affect unsafe requests by authenticated users.";
}
?>
```

#### CSRF Value Generation

For a CSRF token to be useful, its random value must be cryptographically
secure. Using things like `mt_rand()` is insufficient. Aura.Session comes with
a `Randval` class that implements a `RandvalInterface`, and uses either the
`openssl` or the `mcrypt` extension to generate a random value. If you do not
have one of these extensions installed, you will need your own random-value
implementation of the `RandvalInterface`. We suggest a wrapper around
[RandomLib](https://github.com/ircmaxell/RandomLib).
