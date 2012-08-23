Aura Session
============

The Aura Session provides session management functionality, including session
segments, read-once ("flash") values, CSRF tools, and lazy session starting.

This package is compliant with [PSR-0][], [PSR-1][], and [PSR-2][]. If you
notice compliance oversights, please send a patch via pull request.

[PSR-0]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md


Getting Started
===============

Instantiation
-------------

The easiest way to get started is to use the `scripts/instance.php` script to
instantiate a session `Manager` object.

    <?php
    $session = include "/path/to/Aura.Session/scripts/instance.php";

You can then use the `Manager` to work with the session values.

Instantiating a session manager *does not* start a session. The manager is
lazy; it will only start a session when we call `getSegment()` to get a
session segment, or when we call `getCsrfToken()` to get the CSRF token. Of
course, we can force a session start or continuation by calling the `start()`
method, but that defeats the purpose of lazy-loaded sessions.


Segments
--------

A session segment is a reference to an array key in the `$_SESSION`
superglobal. For example, if you ask for a segment named `ClassName`, the
segment will be a reference to `$_SESSION['ClassName']`. All values in the
`ClassName` segment will be stored in an array under that key.

    <?php
    // get a session segment; starts the session if it is not already,
    // and creates the $_SESSION key if it does not exist.
    $segment = $session->getSegment('Vendor\Package\ClassName');
    
    // set some values on the segment
    $segment->foo = 'bar';
    $segment->baz = 'dib';
    
    // the $_SESSION superglobal is now:
    // $_SESSION = [
    //      'Vendor\Package\ClassName' => [
    //          'foo' => 'bar',
    //          'baz' => 'dib',
    //      ],
    // ];
    
    // get the values from the segment
    echo $segment->foo; // 'bar'

    // because the segment is a reference to $_SESSION, you can modify
    // the superglobal directly and the segment values will also change.
    $_SESSION['Vendor\Package\ClassName']['zim'] = 'gir'
    echo $segment->zim; // 'gir'
    
The benefit of a session segment is that we can deconflict the keys in the
`$_SESSION` superglobal by using class names (or some other unique name) for
the segment names. With segments, different packages can use the `$_SESSION`
superglobal without stepping on each other's toes.


Session Security
----------------

When you are done with a session and want its data to be available later, call
the `commit()` method:

    <?php
    $session->commit();

> N.b.: The `commit()` method is the equivalent of `session_write_close()`. 
> If you do not commit the session, its values will not be available when we 
> continue the session later.

Any time an authenticated user has a change in privilege (that is, gaining or
losing access rights within a system) be sure to regenerate the session ID:

    <?php
    $session->regenerateId();
    
> N.b.: The `regenerateId()` method also regenerates the CSRF token value.

To clear the in-memory session data, but leave the session active, use the
`clear()` method:

    <?php
    $session->clear();

To end a session and remove its data (both committed and in-memory), generally
after a user signs out or when authentication timeouts occur, call the
`destroy()` method:

    <?php
    $session->destroy();


Read-Once ("Flash") Values
--------------------------

Session segment values persist until a session is cleared or destroyed.
However, sometimes it is useful to set a value that propagates only until it
is used, and then automatically clears itself. These are called "flash" or
"read-once" values.

To set a read-once value on a segment, use the `setFlash()` method.

    <?php
    // get a segment
    $segment = $session->getSegment('Vendor\Package\ClassName');
    
    // set a read-once value on the segment
    $segment->setFlash('message', 'Hello world!');

Then, in subsequent sessions, we can read the flash value using `getFlash()`:

    <?php
    // get a segment
    $segment = $session->getSegment('Vendor\Package\ClassName');
    
    // get the read-once value
    $message = $segment->getFlash('message'); // 'Hello world!'
    
    // if we try to read it again, it won't be there
    $not_there = $segment->getFlash('message'); // null

Sometimes we need to know if a flash value exists, but don't want to read it
yet (thereby removing it from the session). In these cases, we can use the
`hasFlash()` method:

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
    
To clear all flash values on a segment, use the `clearFlash()` method:

    <?php
    // get a segment
    $segment = $session->getSegment('Vendor\Package\ClassName');
    
    // clear all flash values, but leave all other segment values in place
    $segment->clearFlash();


CSRF Token
----------

A "cross-site request forgery" is an security issue where the attacker, via
malicious JavaScript or other means, issues a request in-the-blind from a
client browser to a server where the user has already authenticated. The
request *looks* valid to the server, but in fact is a forgery, since the user
did not actually make the request (the malicious JavaScript did).

<http://en.wikipedia.org/wiki/Cross-site_request_forgery>

To defend against CSRF attacks, server-side logic should:

1. Place a token value unique to each authenticated user session in each form;
   and

2. Check that all incoming POST/PUT/DELETE (i.e., "unsafe") requests contain
   that value.

> N.b.: If our application uses GET requests to modify resources (which
> incidentally is an improper use of GET), we should also check for CSRF on
> GET requests from authenticated users.

For this example, the form field name will `'__csrf_value''`. In each form we
want to protect against CSRF, we use the session CSRF token value for that
field:

    <?php
    /**  
     * @var Vendor\Package\User $user A user-authentication object.
     * @var Aura\Session\Manager $session A session management object.
     */
    ?>
    <form method="post">
    
        <?php if ($user->isAuthenticated()) {
            $csrf_value = $session->getCsrfToken()->getValue();
            echo '<input type="hidden" name="__csrf_value" value="'
               . $csrf_value
               . '"></input>";
        } ?>
        
        <!-- other form fields -->
        
    </form>

When processing the request, check to see if the incoming CSRF token is valid
for the authenticated user:

    <?php
    /**  
     * @var Vendor\Package\User $user A user-authentication object.
     * @var Aura\Session\Manager $session A session management object.
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

* * *
