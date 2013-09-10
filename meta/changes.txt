- [CHG] Manager::destroy() now checks whether the session is started; if not,
  starts it, and then destroys.  This is because sessions are lazy-loading.

- [DOC] Add PHP 5.5 to the Travis build and update docs
